<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 14:45:17
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 14:46:55
 **/

namespace Opus\controller\query\insert;

use Opus\controller\query\TraitQuery;
use Opus\controller\query\insert\AbstractInsert;

class InsertQuery extends AbstractInsert
{
	use TraitQuery;

	public function __construct(array $config, public readonly array $data, object $insert)
	{
		$this->config = $config;
		$this->insert = $insert;
	}

	public static function create(array $conf, ?array $data, ?object $objQuery): array|string
	{
		$insertQuery = new InsertQuery($conf, $data, $objQuery);
		$insertModel = $insertQuery->insert();
		$insert = [];

		foreach ($insertQuery->data as $index => $value) {
			$insert[$index] = $insertModel . $insertQuery->values($value);
		}

		return $insertQuery->chunk($insert);
	}

	/**
	 * Generates the VALUES portion of an SQL INSERT statement
	 *
	 * This method creates the VALUES clause for an INSERT statement based on the provided data.
	 * It handles different data types appropriately:
	 * - NULL values are inserted as empty values
	 * - String types (defined in QVALID_COLUMN_STRING_TYPE) are properly escaped and quoted
	 * - Subqueries (strings starting with SELECT, WITH, etc.) are placed in brackets
	 * - Other types are inserted as is
	 *
	 * @param array $data The data to be inserted, with column names as keys
	 * @return string SQL fragment for the VALUES portion of an INSERT statement ending with semicolon
	 */
	private function values(array $data): string
	{
		$query = 'VALUES (';

		foreach ($this->insert->columnToInsert as $index => $value) {
			$isLast = $index == $this->insert->countColumnToInsert - 1;
			$comma = $isLast ? '' : ', ';
			$columnName = $value['name'];

			// Extract just the first word of the type
			$baseType = isset($value['type'])
				? strtok(strtoupper($value['type']), ' ')
				: null;

			// Format value based on type and content
			$query .= match (true) {
				is_null($data[$columnName]) => '' . $comma,

				isset($value['type']) && in_array($baseType, self::QVALID_COLUMN_STRING_TYPE) => '\'' . addslashes($data[$columnName]) . '\'' . $comma,

				// Check if value is a subquery (starts with SELECT, WITH, etc.)
				is_string($data[$columnName]) && preg_match('/^\s*(SELECT|WITH)\s/i', $data[$columnName]) => '(' . $data[$columnName] . ')' . $comma,

				default => $data[$columnName] . $comma
			};
		}

		return $query . ');';
	}

	/**
	 * Splits SQL statements into chunks of specified size
	 *
	 * This method takes an array of SQL statements and combines them into chunks
	 * based on the chunk size specified in the configuration. If the total number
	 * of statements is less than the chunk size, all statements are combined into
	 * a single string.
	 *
	 * @param array $data Array of SQL statements to be chunked
	 * @return array Array of chunked SQL statements
	 */
	private function chunk(array $data): array
	{
		// If data size is smaller than chunk size, combine all into one string
		if ($this->config['chunk'] > count($data)) {
			return [implode('', $data)];
		}

		// Split data into chunks and combine statements within each chunk
		$chunks = array_chunk($data, $this->config['chunk']);
		$result = [];

		foreach ($chunks as $chunk) {
			$result[] = implode('', $chunk);
		}

		return $result;
	}
}
