<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 15:33:46
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 15:34:34
 **/

namespace Opus\controller\query\update;

use Opus\controller\query\TraitQuery;
use Opus\controller\query\update\AbstractUpdate;

class UpdatePrepare extends AbstractUpdate
{
	use TraitQuery;

	public function __construct(array $config, public readonly array $data, object $update)
	{
		$this->config = $config;
		$this->update = $update;
	}

	public static function create(array $conf, ?array $data, ?object $objQuery): array|string
	{
		$updatePrepare = new UpdatePrepare($conf, $data, $objQuery);
		return $updatePrepare->update()
			. $updatePrepare->values()
			. $updatePrepare->where();
	}

	/**
	 * Generates parameter placeholders for a prepared UPDATE statement
	 *
	 * This method creates a parenthesized list of named parameter placeholders
	 * (e.g., ':column_name') for each column in the update statement.
	 * These placeholders will be bound to actual values when the prepared
	 * statement is executed.
	 *
	 * @return string Parenthesized list of parameter placeholders
	 */
	private function values(): string
	{
		$placeholders = [];

		foreach ($this->update->columnToUpdate as $value) {
			$placeholders[] = ':' . $value['name'];
		}

		return '(' . implode(', ', $placeholders) . ') ';
	}

	/**
	 * Generates the WHERE clause for a prepared UPDATE statement
	 *
	 * This method creates a WHERE clause that restricts the update to rows
	 * matching the primary key (first column). It uses a named parameter
	 * placeholder for the primary key value.
	 *
	 * The first column is always assumed to be the primary key/ID column.
	 *
	 * @return string SQL WHERE clause with parameter placeholder ending with semicolon
	 */
	private function where(): string
	{
		$idColumn = $this->config['columns'][0]['name'];
		return 'WHERE ' . $idColumn . ' = :' . $idColumn . ';';
	}
}
