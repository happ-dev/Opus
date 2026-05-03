<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 14:54:24
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 14:55:13
 **/

namespace Opus\controller\query\delete;

use Opus\controller\query\TraitQuery;
use Opus\controller\query\delete\AbstractDelete;

class DeletePrepare extends AbstractDelete
{
	use TraitQuery;

	public function __construct(array $config, public readonly array $data, public readonly object $delete)
	{
		$this->config = $config;
	}

	public static function create(array $conf, ?array $data, ?object $objQuery): array|string
	{
		$deletePrepare = new DeletePrepare($conf, $data, $objQuery);
		return $deletePrepare->delete() . $deletePrepare->where();
	}

	/**
	 * Generates the WHERE clause for a prepared DELETE statement
	 *
	 * This method creates a WHERE clause with parameter placeholders for a prepared
	 * statement. It uses the column names from columnToDelete to create equality
	 * conditions (column = :column) joined by AND operators.
	 *
	 * @return string SQL WHERE clause with parameter placeholders
	 */
	private function where(): string
	{
		$query = 'WHERE ';
		$conditions = [];

		foreach ($this->delete->columnToDelete as $value) {
			$conditions[] = $value['name'] . ' = :' . $value['name'];
		}

		return $query . implode(' AND ', $conditions);
	}
}
