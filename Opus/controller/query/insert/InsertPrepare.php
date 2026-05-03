<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 14:43:28
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 14:46:29
 **/

namespace Opus\controller\query\insert;

use Opus\controller\query\TraitQuery;
use Opus\controller\query\insert\AbstractInsert;

class InsertPrepare extends AbstractInsert
{
	use TraitQuery;

	public function __construct(array $config, public readonly array $data, object $insert)
	{
		$this->config = $config;
		$this->insert = $insert;
	}

	public static function create(array $conf, ?array $data, ?object $objQuery): array|string
	{
		$insertPrepare = new InsertPrepare($conf, $data, $objQuery);
		return $insertPrepare->insert() . $insertPrepare->values();
	}

	/**
	 * Generates the VALUES clause with parameter placeholders for a prepared INSERT statement
	 *
	 * This method creates the VALUES portion of an INSERT statement using named parameter
	 * placeholders (e.g., :column_name) for each column to be inserted. These placeholders
	 * will be bound to actual values when the prepared statement is executed.
	 *
	 * @return string SQL VALUES clause with parameter placeholders ending with semicolon
	 */
	private function values(): string
	{
		$query = 'VALUES (';
		$placeholders = [];

		foreach ($this->insert->columnToInsert as $value) {
			$placeholders[] = ':' . $value['name'];
		}

		return $query . implode(', ', $placeholders) . ');';
	}
}
