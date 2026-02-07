<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-01-27 05:41:04
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-02-07 17:05:24
 **/

namespace Opus\controller\request;

use Exception;
use JsonException;

class Request
{
	const TYPE_PAGE = 'page';
	const TYPE_API = 'api';
	const TYPE_CLI = 'cli';
	const TYPE_ASYNC_PAGE = 'apage';

	/**
	 * Retrieves and filters a GET parameter
	 *
	 * @param string|null $key The name of the GET parameter to retrieve
	 * @param int $filter The filter to apply (default: FILTER_DEFAULT)
	 * @param array|int $options Additional options or flags for the filter
	 * @return mixed The filtered value or null if the parameter doesn't exist
	 */
	public static function get(?string $key, int $filter = FILTER_DEFAULT, array|int $options = 0): mixed
	{
		return filter_input(INPUT_GET, $key, $filter, $options);
	}

	/**
	 * Retrieves and filters a POST parameter
	 *
	 * @param string|null $key The name of the POST parameter to retrieve
	 * @param int $filter The filter to apply (default: FILTER_DEFAULT)
	 * @param array|int $options Additional options or flags for the filter
	 * @return mixed The filtered value or null if the parameter doesn't exist
	 */
	public static function post(?string $key, int $filter = FILTER_DEFAULT, array|int $options = 0): mixed
	{
		return filter_input(INPUT_POST, $key, $filter, $options);
	}

	/**
	 * Retrieves and filters a parameter from GET or POST
	 *
	 * @param string $key The name of the parameter to retrieve
	 * @param int $filter The filter to apply (default: FILTER_DEFAULT)
	 * @param array|int $options Additional options or flags for the filter
	 * @return mixed The filtered value from GET, or POST if GET is null
	 */
	public static function filterInput(string $key, int $filter = FILTER_DEFAULT, array|int $options = 0): mixed
	{
		$inputGet = self::get($key, $filter, $options);
		$inputPost = self::post($key, $filter, $options);
		return !is_null($inputGet) ? $inputGet : $inputPost;
	}

	/**
	 * Retrieves the raw request body
	 *
	 * @param bool|null $json Whether to decode as JSON (default: true)
	 * @param bool|null $options JSON decode options (default: false)
	 * @return mixed The request body, decoded if JSON is true
	 * @throws Exception If JSON decoding fails
	 */
	public static function getBody(?bool $json = true, ?bool $options = false): mixed
	{
		try {
			$body = file_get_contents('php://input');

			if ($json === true) {
				$body = json_decode(
					$body,
					$options,
					512,
					JSON_THROW_ON_ERROR
				);
			}

			return $body;
		} catch (JsonException $error) {
			throw new Exception('JSON error in: ' . $error->getMessage());
		}
	}

	/**
	 * Returns the base URL path
	 *
	 * @return string The base URL path
	 * @throws Exception If PHP_SELF cannot be determined
	 */
	private static function homeUrl(): string
	{
		$phpSelf = filter_input(INPUT_SERVER, 'PHP_SELF');

		if ($phpSelf === false || is_null($phpSelf)) {
			throw new Exception('Unable to determine PHP_SELF');
		}

		return ($phpSelf !== '/index.php') ? substr($phpSelf, 0, strrpos($phpSelf, '/') + 1) : '/';
	}

	/**
	 * Extracts and validates a request parameter from URL
	 *
	 * @param mixed $request The request parameter name
	 * @return string The validated request value or '/'
	 * @throws Exception If REQUEST_URI cannot be determined or CRLF attack detected
	 */
	public static function fromUrl($request): string
	{
		$uri = filter_input(INPUT_SERVER, 'REQUEST_URI');

		if (($uri === false || is_null($uri)) && !isset($_SERVER['argv'])) {
			throw new Exception('Unable to determine REQUEST_URI');
		}

		$pattern = '/' . preg_quote($request, '/') . '/i';
		$req = filter_input(INPUT_GET, $request, FILTER_UNSAFE_RAW, FILTER_NULL_ON_FAILURE)
			?? throw new Exception('CRLF attack detected!!!');

		return (preg_match($pattern, $uri) == true) ? $req : '/';
	}

	/**
	 * Returns the last page from HTTP referer
	 *
	 * @return string|null The last page name or null if not available
	 */
	public static function lastUrl(): ?string
	{
		$last = filter_input(INPUT_SERVER, 'HTTP_REFERER');

		if ($last === false || is_null($last)) {
			return null;
		}

		return (preg_match('/page/i', $last)) ? substr(strrchr($last, '/'), 1) : null;
	}

	/**
	 * Returns the full request URI
	 *
	 * @return string The complete URI (host + request path)
	 * @throws Exception If host or URI cannot be determined
	 */
	public static function uri(): string
	{
		$host = filter_input(INPUT_SERVER, 'HTTP_HOST');
		$uri = filter_input(INPUT_SERVER, 'REQUEST_URI');

		if ($host === false || is_null($host) || $uri === false || is_null($uri)) {
			throw new Exception('Unable to determine full URI');
		}

		return $host . $uri;
	}

	/**
	 * Validates URL for security threats
	 *
	 * @param string $url The URL to validate
	 * @return bool True if URL is safe, false otherwise
	 */
	private static function isValidUrl(string $url): bool
	{
		// Check for CRLF injection and other malicious patterns
		return !preg_match('/[\r\n\0]/', $url) &&
			!str_contains($url, '..') &&
			!str_contains($url, '<') &&
			!str_contains($url, '>');
	}

	/**
	 * Constructs a validated URL
	 *
	 * @param string|null $url The URL path to append to base URL
	 * @return string The complete validated URL
	 * @throws Exception If CRLF attack is detected
	 */
	public static function url(?string $url = null): string
	{
		if (!is_null($url)) {
			$fullUrl = self::homeUrl() . $url;
			return self::isValidUrl($fullUrl)
				? $fullUrl
				: throw new Exception('CRLF attack detected!!!');
		}

		return self::homeUrl() . self::lastUrl();
	}

	/**
	 * Checks if the request is an AJAX request
	 *
	 * @return bool True if request is AJAX, false otherwise
	 */
	public static function isAjax(): bool
	{
		$header = filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH');
		return $header === 'XMLHttpRequest';
	}

	/**
	 * Validates CSRF token from request
	 *
	 * @return bool|string True if valid, error message otherwise
	 */
	final public static function validateCsrfToken(): bool
	{
		// Check for token in header
		$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

		// Check for token in POST data if not in header
		if (empty($token)) {
			$token = self::post('csrf');
		}

		if (empty($token) || !isset($_SESSION['csrf'])) {
			return 'CSRF token missing';
		}

		if (!hash_equals($_SESSION['csrf'], $token)) {
			return 'CSRF token validation failed';
		}

		return true;
	}
}
