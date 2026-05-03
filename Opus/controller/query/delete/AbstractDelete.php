<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 14:03:44
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 14:05:02
 **/

namespace Opus\controller\query\delete;

abstract class AbstractDelete
{
	/**
	 * Creates SQL DELETE statements from configuration and data
	 *
	 * This static factory method generates SQL DELETE statements by:
	 * 1. Creating an DeleteQuery instance with the provided configuration and data
	 * 2. Generating the DELETE portion of the statement
	 * 3. Adding VALUES clauses for each data row
	 * 4. Chunking the resulting statements based on the configuration
	 *
	 * @param array $conf The query configuration array
	 * @param array|null $data The data to be inserted, as an array of rows
	 * @param object|null $objQuery The query object containing column information
	 * @return array|string An array of SQL DELETE statements, possibly chunked based on configuration
	 */
	abstract public static function create(array $conf, ?array $data, ?object $objQuery): array|string;

	protected array $config;

	/**
	 * Generates the DELETE FROM portion of an SQL DELETE statement
	 *
	 * This method creates the initial part of a DELETE statement with the table name.
	 * It does not include WHERE conditions or other clauses.
	 *
	 * @return string SQL fragment for the DELETE FROM portion of a DELETE statement
	 */
	protected function delete(): string
	{
		return 'DELETE FROM ' . $this->config['table'] . ' ';
	}
}
