<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 14:55:58
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 14:56:48
 **/

namespace Opus\controller\query\delete;

use Opus\controller\query\TraitQuery;
use Opus\controller\query\delete\AbstractDelete;
use Opus\controller\exception\ControllerException;

class DeleteQuery extends AbstractDelete
{
	use TraitQuery;

	public function __construct(array $config, public readonly array $data, public readonly object $delete)
	{
		$this->config = $config;
	}

	public static function create(array $conf, ?array $data, ?object $objQuery): array|string
	{
		$deleteQuery = new DeleteQuery($conf, $data, $objQuery);
		$deleteModel = $deleteQuery->delete();
		$delete = [];

		foreach ($deleteQuery->data as $index => $value) {
			$delete[$index] = $deleteModel . $deleteQuery->where($value);
		}

		return $deleteQuery->chunk($delete);
	}

	/**
	 * Generates a WHERE clause for a DELETE statement based on data values
	 *
	 * This method creates a WHERE clause with appropriate conditions based on the data type:
	 * - NULL values trigger an exception as they are not allowed in WHERE conditions
	 * - String types (defined in QVALID_COLUMN_STRING_TYPE) are properly escaped and quoted
	 * - Subqueries (strings starting with SELECT, WITH, etc.) are placed in parentheses
	 * - Other types are used as is
	 *
	 * The conditions are joined with AND operators and the clause ends with a semicolon.
	 *
	 * @param array $data The data to use for the WHERE conditions, with column names as keys
	 * @return string SQL WHERE clause ending with semicolon
	 * @throws ControllerException If any value is NULL
	 */
	private function where($data): string
	{
		$query = 'WHERE ';
		$conditions = [];

		foreach ($this->delete->columnToDelete as $value) {
			$columnName = $value['name'];

			// Extract just the first word of the type
			$baseType = isset($value['type'])
				? strtok(strtoupper($value['type']), ' ')
				: null;

			// Create condition based on data type
			$conditions[] = match (true) {
				!isset($data[$columnName]) => throw new ControllerException(
					'controller\query\validate\data',
					['message' => $columnName],
					$this->config['exception']
				),

				is_null($data[$columnName]) => $columnName . ' IS NULL',

				isset($value['type']) && in_array($baseType, self::QVALID_COLUMN_STRING_TYPE) => $columnName . ' = \'' . addslashes($data[$columnName]) . '\'',

				// Check if value is a subquery (starts with SELECT, WITH, etc.)
				is_string($data[$columnName]) && preg_match('/^\s*(SELECT|WITH)\s/i', $data[$columnName]) => '(' . $data[$columnName] . ')',

				default => $columnName . ' = ' . $data[$columnName]
			};
		}

		return $query . implode(' AND ', $conditions) . ';';
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
