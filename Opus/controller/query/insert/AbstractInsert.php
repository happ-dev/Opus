<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 14:17:00
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 14:20:00
 **/

namespace Opus\controller\query\insert;

abstract class AbstractInsert
{
	/**
	 * Creates SQL INSERT statements from configuration and data
	 *
	 * This static factory method generates SQL INSERT statements by:
	 * 1. Creating an InsertQuery instance with the provided configuration and data
	 * 2. Generating the INSERT INTO portion of the statement
	 * 3. Adding VALUES clauses for each data row
	 * 4. Chunking the resulting statements based on the configuration
	 *
	 * @param array $conf The query configuration array
	 * @param array|null $data The data to be inserted, as an array of rows
	 * @param object|null $objQuery The query object containing column information
	 * @return array|string An array of SQL INSERT statements, possibly chunked based on configuration
	 */
	abstract public static function create(array $conf, ?array $data, ?object $objQuery): array|string;

	protected array $config;
	protected object $insert;

	/**
	 * Generates the INSERT INTO portion of an SQL INSERT statement
	 *
	 * This method creates the first part of an INSERT statement including the table name
	 * and column names. It does not include the VALUES portion of the statement.
	 *
	 * @return string SQL fragment for the INSERT INTO portion of an INSERT statement
	 */
	final protected function insert(): string
	{
		// Start the INSERT INTO statement with table name
		$query = 'INSERT INTO ' . $this->config['table'] . ' (';

		// Add column names with appropriate comma separation
		foreach ($this->insert->columnToInsert as $index => $value) {
			$query .= ($index != $this->insert->countColumnToInsert - 1)
				? $value['name'] . ', '
				: $value['name'];
		}

		// Close the column list and return
		return $query . ') ';
	}
}
