<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-02-01 20:27:50
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-02-07 17:01:17
 **/

namespace Opus\libs;

use Exception;
use Random\RandomException;

class Common
{
	/**
	 * Generates a unique identifier using SHA-256 hash with additional entropy
	 *
	 * @param int $length Length of the random string before hashing (default: 7)
	 * @throws Exception When length is less than 1
	 * @throws RandomException When unable to generate cryptographically secure random bytes
	 * @return string SHA-256 hash (64 characters hexadecimal)
	 */
	public static function windowsUniqId(int $length = 7): string
	{
		// Validate input
		if ($length < 1) {
			throw new Exception('Length must be greater than 0');
		}

		try {
			// Define character set
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';

			// Add entropy sources
			$entropy = implode('', [
				uniqid(more_entropy: true),
				microtime(true),
				memory_get_usage(true),
				php_uname('n'),
				getmypid()
			]);

			// Generate random string using cryptographically secure function
			$bytes = random_bytes($length);

			for ($i = 0; $i < $length; $i++) {
				$randomString .= $characters[ord($bytes[$i]) % $charactersLength];
			}

			// Return SHA-256 hash
			return hash('sha256', $randomString . $entropy);
		} catch (RandomException $e) {
			throw new Exception(
				'Common::windowsUniqId: Unable to generate cryptographically secure random bytes' . PHP_EOL . $e->getMessage()
			);
		}
	}

	/**
	 * Adds a character to the beginning of a string until it reaches specified length
	 *
	 * @param string $str Original string to pad
	 * @param string $char Character to add at the beginning (should be single character)
	 * @param int $newStringLength Desired final string length (default: 8)
	 * @throws Exception When:
	 * 						- $char is not a single character
	 * 						- $newStringLength is less than 1
	 * @return string String padded with characters at the beginning
	 */
	public static function addCharToString(string $str, string $char, int $newStringLength = 8): string
	{
		// Validate character
		if (strlen($char) !== 1) {
			throw new Exception('Padding character must be a single character');
		}

		// Validate length
		if ($newStringLength < 1) {
			throw new Exception('New string length must be greater than 0');
		}

		// Return original string if it's already longer than desired length
		if (strlen($str) > $newStringLength) {
			return $str;
		}

		// Use built-in str_pad with left padding
		return str_pad($str, $newStringLength, $char, STR_PAD_LEFT);
	}

	/**
	 * Recursively removes duplicate values from a multi-dimensional array
	 *
	 * This function removes duplicate values from an array by serializing elements
	 * for comparison and then recursively processes any nested arrays.
	 *
	 * @param array $array The input array to process
	 * @return array Array with all duplicate values removed (including in nested arrays)
	 */
	final public static function arrayUniqueRecursive(array $array): array
	{
		$result = array_map('serialize', $array);
		$result = array_map('unserialize', array_unique($result));

		foreach ($result as $key => $value) {

			if (is_array($value) === true) {
				$result[$key] = self::arrayUniqueRecursive($value);
			}
		}

		return $result;
	}

	/**
	 * Replaces all occurrences of a specific value in an array with a replacement value
	 *
	 * This function performs a strict comparison (===) to find values that match
	 * the search parameter and replaces them with the specified replacement.
	 *
	 * @param array $array The input array to process
	 * @param mixed $search The value to search for in the array
	 * @param mixed $replacement The value to replace matches with
	 * @return array The array with replaced values
	 */
	final public static function searchAndReplaceValueInArray(array $array, $search, $replacement): array
	{
		return array_map(
			function ($value) use ($search, $replacement) {
				return $value === $search ? $replacement : $value;
			},
			$array
		);
	}
}
