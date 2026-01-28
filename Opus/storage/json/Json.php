<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-01-27 13:20:39
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-01-27 13:22:02
**/

namespace Opus\storage\json;

use Exception;
use JsonException;

class Json {

	/**
	 * Function loads json file, throws Exception if any
	 * 
	 * @param string $filePath
	 * @return object Returns an object with the json data
	 * @throws Exception
	 */
	final public static function loadJsonFile(string $filePath): object
	{
		try {
			$json = new self();

			// check if json file exists
			file_exists($filePath) ?: throw new Exception('Json file not found: ' . $filePath);

			// check access rights to the json file
			is_readable($filePath) ?: throw new Exception('No access to json file: ' . $filePath);

			// load global config file, exception if file not found
			$jsonContent = @file_get_contents($filePath) ?: throw new Exception('Failed to read json file: ' . $filePath);

			// Validate JSON and get detailed error information if invalid
			$json->validateJsonString($jsonContent, $filePath);

			// If valid, decode it
			return json_decode($jsonContent, false, 512, JSON_THROW_ON_ERROR);
		} catch (JsonException $error) {
			throw new Exception('JSON error in: ' . $error->getMessage());
		}
	}

	/**
	 * Function validates json file
	 *
	 * @param string $json
	 * @param string $filePath
	 * @return bool Returns true if json is valid, throws exeptions otherwise
	 * @throws JsonException
	 */
	private function validateJsonString(string $json, string $filePath): bool
	{
		// return true if json is valid
		if (json_validate($json)) {
			return true;
		}

		$error = json_last_error_msg();
		$position = 0;
		$length = strlen($json);

		// Try to parse the JSON character by character to find error location
		for ($i = 1; $i <= $length; $i++) {
			$subset = substr($json, 0, $i);

			if (!json_validate($subset) && json_last_error_msg() !== $error) {
				$position = $i;
				break;
			}

		}

		// Get context around the error
		$start = max(0, $position - 20);
		$context = substr($json, $start, 40);
		$pointer = str_repeat(' ', min($position - $start, 20)) . '^';

		// Calculate line and column
		$beforeError = substr($json, 0, $position);
		$line = substr_count($beforeError, "\n") + 1;
		$column = $position - strrpos($beforeError, "\n");

		throw new JsonException(
			sprintf(
				"Invalid JSON format in %s\nError: %s\nLine: %d, Column: %d\nContext:\n%s\n%s",
				$filePath,
				$error,
				$line,
				$column,
				$context,
				$position,
				$pointer
			)
		);
	}

}