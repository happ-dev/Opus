<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 15:04:26
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 17:15:32
 **/

namespace Opus\controller\query\select;

use stdClass;
use PDO;
use Opus\storage\db\Db;
use Opus\storage\db\AbstractDb;

class SelectExecute extends AbstractSelect
{
	public function __construct(array $config, object $select)
	{
		$this->config = $config;
		$this->select = $select;
	}

	public static function create(array $conf, ?object $objQuery): array|string|object
	{
		$selectExecute = new SelectExecute($conf, $objQuery);
		$execute = [
			'prepare' => SelectPrepare::create($conf, $objQuery)
		];
		$values = $selectExecute->values();
		return is_null($values) ? $execute : array_merge_recursive($execute, $values);
	}

	/**
	 * Generates named parameter bindings from WHERE conditions
	 *
	 * Extracts column names from 'table.column' format in WHERE clauses
	 * and maps them as PDO named parameters (:column) to their values.
	 *
	 * @return array<string, mixed>|null Associative array of named parameters or null if no conditions
	 */
	private function values(): ?array
	{
		$values = [];

		foreach ($this->config['where'] as $index => $row) {
			// Extract table and column from table.column format
			list($table, $columnName) = explode('.', $row['left']);
			$values[':' . $columnName] = $row['right'];
		}

		return empty($values) ? null : $values;
	}
}
