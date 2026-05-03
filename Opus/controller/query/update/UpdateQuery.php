<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 15:48:21
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 15:49:23
 **/

namespace Opus\controller\query\update;

use Opus\controller\query\TraitQuery;
use Opus\controller\query\update\AbstractUpdate;
use Opus\controller\exception\ControllerException;

class UpdateQuery extends AbstractUpdate
{
	use TraitQuery;

	public function __construct(array $config, public readonly array $data, object $update)
	{
		$this->config = $config;
		$this->update = $update;
	}

	public static function create(array $conf, ?array $data, ?object $objQuery): array|string
	{
		$updateQuery = new UpdateQuery($conf, $data, $objQuery);
		$updateModel = $updateQuery->update();
		$update = [];

		foreach ($updateQuery->data as $index => $value) {
			$update[$index] = $updateModel . $updateQuery->values($value) . $updateQuery->where($value);
		}

		return $updateQuery->chunk($update);
	}

	/**
	 * Generates the VALUES portion of an SQL UPDATE statement
	 *
	 * This method creates a parenthesized list of values for an UPDATE statement
	 * based on the provided data. It handles different data types appropriately:
	 * - NULL values are inserted as NULL
	 * - String types (defined in QVALID_COLUMN_STRING_TYPE) are properly escaped and quoted
	 * - Subqueries (strings starting with SELECT, WITH, etc.) are placed in brackets
	 * - Other types are inserted as is
	 *
	 * @param array $data The data to be updated, with column names as keys
	 * @return string Parenthesized list of values for the UPDATE statement
	 */
	private function values(array $data): string
	{
		$values = [];

		foreach ($this->update->columnToUpdate as $value) {
			$columnName = $value['name'];

			// Extract just the first word of the type
			$baseType = isset($value['type'])
				? strtok(strtoupper($value['type']), ' ')
				: null;

			// Format value based on type and content using match
			$values[] = match (true) {
				is_null($data[$columnName]) => 'NULL',

				isset($value['type']) && in_array($baseType, self::QVALID_COLUMN_STRING_TYPE) => '\'' . addslashes($data[$columnName]) . '\'',

				// Check if value is a subquery (starts with SELECT, WITH, etc.)
				is_string($data[$columnName]) && preg_match('/^\s*(SELECT|WITH)\s/i', $data[$columnName]) => '(' . $data[$columnName] . ')',

				default => $data[$columnName]
			};
		}

		return '(' . implode(', ', $values) . ') ';
	}

	/**
	 * Generates the WHERE clause for an SQL UPDATE statement
	 *
	 * This method creates a WHERE clause that restricts the update to rows
	 * matching the primary key (first column). It uses the provided data
	 * to get the value for the primary key.
	 *
	 * The first column is always assumed to be the primary key/ID column.
	 *
	 * @param array $data The data containing the primary key value
	 * @return string SQL WHERE clause ending with semicolon
	 */
	private function where(array $data): string
	{
		$idColumn = $this->config['columns'][0]['name'];

		// Check if ID value exists and is not null
		if (!isset($data[$idColumn]) || is_null($data[$idColumn])) {
			throw new ControllerException(
				'controller\query\validate\data',
				['message' => $idColumn],
				$this->config['exception']
			);
		}

		$idValue = $data[$idColumn];

		// Format ID value based on its type
		$formattedValue = is_string($idValue) ? "'" . addslashes($idValue) . "'" : $idValue;

		return 'WHERE ' . $idColumn . ' = ' . $formattedValue . ';';
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
