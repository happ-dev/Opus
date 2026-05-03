<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 14:47:52
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 14:48:42
 **/

namespace Opus\controller\query\insert;

use PDO;
use Opus\controller\query\TraitQuery;
use Opus\controller\query\insert\AbstractInsert;

class InsertTransaction extends AbstractInsert
{
	use TraitQuery;

	public function __construct(array $config, public readonly array $data, object $insert)
	{
		$this->config = $config;
		$this->insert = $insert;
	}

	public static function create(array $conf, ?array $data, ?object $objQuery): array|string
	{
		$transactionInsert = new InsertTransaction($conf, $data, $objQuery);
		$transaction = [
			'prepare' => InsertPrepare::create($conf, $data, $objQuery),
			'params' => $transactionInsert->param(),
			'pdoTypes' => $transactionInsert->dataType()
		];
		$values = [];

		foreach ($transactionInsert->data as $index => $value) {

			foreach ($transactionInsert->insert->columnToInsert as $column) {
				// Set parameter value (empty string for NULL values)
				$values[':' . $column['name']][$index] = (is_null($value[$column['name']])) ? '' : $value[$column['name']];
			}
		}

		return [
			array_merge_recursive($transaction, $values)
		];
	}

	/**
	 * Generates parameter placeholders for prepared statements
	 *
	 * This method creates an array of named parameter placeholders (e.g., ':column_name')
	 * for each column in the insert statement, which can be used in prepared statements.
	 *
	 * @return array Array of parameter placeholders
	 */
	private function param(): array
	{
		return array_map(
			fn($value) => ':' . $value['name'],
			$this->insert->columnToInsert
		);
	}

	/**
	 * Determines PDO parameter types for each column
	 *
	 * This method creates an array of PDO parameter types (PDO::PARAM_*) for each column
	 * in the insert statement. If a column doesn't specify a PDO parameter type,
	 * it will be set to false.
	 *
	 * @return array|bool Array of PDO parameter types indexed by column position,
	 *                    or false if no parameter types are defined
	 */
	private function dataType(): array|bool
	{
		$dataType = array_map(
			fn($value) => $value['pdoTypes'] ??= PDO::PARAM_STR,
			$this->insert->columnToInsert
		);

		return !empty($dataType) ? $dataType : false;
	}
}
