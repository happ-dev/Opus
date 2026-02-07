<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-02-07 16:30:47
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-02-07 16:59:30
 **/

namespace Opus\storage\curl;

use stdClass;
use JsonException;
use Opus\controller\exception\ControllerException;

class Curl
{
	/**
	 * Executes HTTP requests using cURL with configurable options and error handling
	 *
	 * Performs HTTP requests to specified URLs with customizable request methods, headers,
	 * and error handling behavior. Validates URLs, handles cURL errors, and optionally
	 * decodes JSON responses with exception handling. Supports GET, POST, PUT, DELETE
	 * and other HTTP methods through the customrequest option.
	 *
	 * @param string $url The target URL for the HTTP request (must include query parameters)
	 * @param object $options Configuration object with optional properties:
	 *   - exception: Exception type for error handling (default: TYPE_API_EXCEPTION)
	 *   - customrequest: HTTP method (default: 'GET')
	 *   - httpheader: Array of HTTP headers (default: ['Content-Type:application/json'])
	 *   - returntransfer: Whether to return response as string (default: true)
	 *   - catchCurlError: Whether to throw exceptions on cURL errors (default: true)
	 *   - catchJsonException: Whether to handle JSON decode errors (default: true)
	 * @return mixed Decoded JSON object/array or raw response based on catchJsonException setting
	 * @throws ControllerException When URL validation fails, cURL execution errors, or JSON decode errors
	 * @example
	 * // Basic GET request
	 * $result = Curl::fetch('https://api.example.com/data?key=value');
	 *
	 * // POST request with custom options
	 * $options = new stdClass();
	 * $options->customrequest = 'POST';
	 * $options->httpheader = ['Authorization: Bearer token'];
	 * $result = Curl::fetch('https://api.example.com/endpoint?param=1', $options);
	 */
	final public static function fetch(string $url, object $options = new stdClass()): mixed
	{
		// Set default values for options
		$options->exception ??= ControllerException::TYPE_API_EXCEPTION;
		$options->customrequest ??= 'GET';
		$options->httpheader ??= ['Content-Type:application/json'];
		$options->returntransfer ??= true;
		$options->catchCurlError ??= true;
		$options->catchJsonException ??= true;

		// Check if the url meets the requirements
		if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED)) {
			throw new ControllerException(
				'storage\curl\url',
				['details' => $url],
				$options->exception
			);
		}

		$obj = curl_init();
		curl_setopt($obj, CURLOPT_CUSTOMREQUEST, $options->customrequest);
		curl_setopt($obj, CURLOPT_HTTPHEADER, $options->httpheader);
		curl_setopt($obj, CURLOPT_RETURNTRANSFER, $options->returntransfer);
		curl_setopt($obj, CURLOPT_URL, $url);
		$curlResult = curl_exec($obj);

		if (!empty(curl_error($obj)) && $options->catchCurlError === true) {
			throw new ControllerException(
				'storage\curl\curlExec',
				['details' => print_r(curl_error($obj), true)],
				$options->exception
			);
		}

		$result = match ($options->catchJsonException) {
			true => (function () use ($curlResult, $options) {
				try {
					$result = json_decode($curlResult, false, 512, JSON_THROW_ON_ERROR);
					return $result;
				} catch (JsonException $error) {
					throw new ControllerException(
						'storage\curl\jsonDecode',
						['details' => 'JSON error in: ' . $error->getMessage()],
						$options->exception
					);
				}
			})(),
			false => json_decode($curlResult)
		};

		return $result;
	}
}
