<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 15:30:58
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 15:33:00
 **/

namespace Opus\controller\query\update;

abstract class AbstractUpdate
{
	/**
	 * Creates a transaction object for UPDATE operations
	 *
	 * This static factory method generates a transaction object containing:
	 * 1. A prepared UPDATE statement
	 * 2. Parameter placeholders
	 * 3. PDO parameter types
	 * 4. Parameter values for each row to be updated
	 *
	 * @param array $conf The query configuration array
	 * @param array|null $data The data to use for the UPDATE operation
	 * @param object|null $objQuery The query object containing column information
	 * @return array|string Transaction object for UPDATE operations
	 */
	abstract public static function create(array $conf, ?array $data, ?object $objQuery): array|string;

	protected array $config;
	protected object $update;

	/**
	 * Generates the UPDATE portion of an SQL UPDATE statement
	 *
	 * This method creates the initial part of an UPDATE statement with the table name
	 * and column list. It formats the SET clause differently based on the number of columns:
	 * - For a single column, it uses the ROW constructor syntax
	 * - For multiple columns, it uses the standard SET clause syntax
	 *
	 * @return string SQL fragment for the UPDATE portion of an UPDATE statement
	 */
	protected function update(): string
	{
		// Use ROW constructor for single column updates
		$row = ($this->update->countColumnToUpdate <= 1) ? 'ROW ' : null;

		// Start the UPDATE statement with table name
		$query = 'UPDATE ' . $this->config['table'] . ' SET (';

		// Add column names with appropriate comma separation
		$columns = [];

		foreach ($this->update->columnToUpdate as $value) {
			$columns[] = $value['name'];
		}
		// Complete the SET clause
		$query .= implode(', ', $columns) . ') = ' . $row;
		return $query;
	}
}
