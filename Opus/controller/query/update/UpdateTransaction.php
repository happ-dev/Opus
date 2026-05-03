<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 15:49:54
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 15:55:35
 **/

namespace Opus\controller\query\update;

use PDO;
use Opus\controller\query\TraitQuery;
use Opus\controller\query\update\UpdatePrepare;
use Opus\controller\query\update\AbstractUpdate;

class UpdateTransaction extends AbstractUpdate
{
	use TraitQuery;

	public function __construct(array $config, public readonly array $data, object $update)
	{
		$this->config = $config;
		$this->update = $update;
	}

	public static function create(array $conf, ?array $data, ?object $objQuery): array|string
	{
		$updateTransaction = new UpdateTransaction($conf, $data, $objQuery);
		$transaction = [
			'prepare' => UpdatePrepare::create($conf, $data, $objQuery),
			'params' => $updateTransaction->param(),
			'pdoTypes' => $updateTransaction->dataType()
		];
		$values = [];

		foreach ($updateTransaction->data as $index => $row) {
			foreach ($updateTransaction->config['columns'] as $column) {
				$values[':' . $column['name']][$index] = is_null($row[$column['name']]) ? '' : $row[$column['name']];
			}
		}

		return [
			array_merge_recursive($transaction, $values)
		];
	}

	/**
	 * Generates parameter placeholders for a prepared UPDATE statement
	 *
	 * This method creates an array of named parameter placeholders (e.g., ':column_name')
	 * for each column in the update statement, which can be used in prepared statements.
	 *
	 * @return array Array of parameter placeholders
	 */
	private function param(): array
	{
		return array_map(
			fn($value) => ':' . $value['name'],
			$this->config['columns']
		);
	}

	/**
	 * Determines PDO parameter types for each column
	 *
	 * This method creates an array of PDO parameter types (PDO::PARAM_*) for each column
	 * in the update statement. If a column doesn't specify a PDO parameter type,
	 * it will be set to false.
	 *
	 * @return array|bool Array of PDO parameter types indexed by column position,
	 *                    or false if no parameter types are defined
	 */
	private function dataType(): array|bool
	{
		$dataType = array_map(
			fn($value) => $value['pdoTypes'] ??= PDO::PARAM_STR,
			$this->config['columns']
		);

		return !empty($dataType) ? $dataType : false;
	}
}
