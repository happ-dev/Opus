<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 14:57:37
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 14:58:11
 **/

namespace Opus\controller\query\delete;

use Opus\controller\query\TraitQuery;
use Opus\controller\query\delete\AbstractDelete;
use Opus\controller\exception\ControllerException;

class DeleteTransaction extends AbstractDelete
{
	use TraitQuery;

	public function __construct(array $config, public readonly array $data, public readonly object $delete)
	{
		$this->config = $config;
	}

	public static function create(array $conf, ?array $data, ?object $objQuery): array|string
	{
		$transactionDelete = new DeleteTransaction($conf, $data, $objQuery);
		$transaction = [
			'prepare' => DeletePrepare::create($conf, $data, $objQuery),
			'params' => $transactionDelete->param()
		];
		$values = [];

		foreach ($transactionDelete->data as $index => $value) {

			foreach ($transactionDelete->delete->columnToDelete as $column) {
				// Check if the column exists in the data
				if (!isset($value[$column['name']])) {
					throw new ControllerException(
						'controller\query\validate\data',
						['message' => $column['name']],
						$transactionDelete->config['exception']
					);
				}

				// Set parameter value (empty string for NULL values)
				$values[':' . $column['name']][$index] = is_null($value[$column['name']]) ? '' : $value[$column['name']];
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
			$this->delete->columnToDelete
		);
	}
}
