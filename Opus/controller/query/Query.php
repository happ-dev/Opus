<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 13:55:57
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 16:25:57
 **/

namespace Opus\controller\query;

use Opus\controller\query\TraitQuery;
use Opus\controller\query\table\Table;
use Opus\controller\query\insert\Insert;
use Opus\controller\query\delete\Delete;
use Opus\controller\query\select\Select;
use Opus\controller\query\update\Update;
use Opus\controller\exception\ControllerException;

class Query
{
	use TraitQuery;

	private static array $config;
	private static ?array $data;

	public function __construct(public readonly InterfaceQuery $strategy) {}

	/**
	 * Creates a database query based on the provided configuration
	 *
	 * This method serves as the main entry point for query creation.
	 * It sets up the configuration and data, selects the appropriate
	 * strategy based on the query type, and delegates the query creation
	 * to the selected strategy.
	 *
	 * @param array $conf The query configuration array containing:
	 *                    - 'mode': Operation mode (MODE_TRANSACTION, MODE_EXECUTE, etc.)
	 *                    - 'type': Query type (table, insert, select, update, delete)
	 *                    - Other configuration options specific to the query type
	 * @param array|null $data Optional data array for insert/update operations
	 * @return string|array|object The generated query, array of queries, or PDO object
	 *                            depending on the mode and type of query
	 * @throws ControllerException If the configuration is invalid
	 */
	public static function createQuery(array $conf, ?array $data = null): string|array|object
	{
		self::$config = $conf;
		self::$data = $data;
		$selectedStrategy = self::selectStrategy();
		return $selectedStrategy->strategy->createQuery();
	}

	/**
	 * Validates the basic query configuration
	 *
	 * This method checks:
	 * 1. If the query mode is valid (one of the predefined VALID_MODES)
	 * 2. If the query type is valid (one of the predefined QINPUT_STRATEGY)
	 * 3. Sets a default exception type if not specified
	 *
	 * @throws ControllerException If the mode or type is invalid
	 * @return void
	 */
	private static function validateConfig()
	{
		// Set default exception type if not provided
		self::$config['exception'] ??= ControllerException::TYPE_API_EXCEPTION;

		// Check if mode is valid
		if (!in_array(self::$config['mode'], self::VALID_MODES)) {
			throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['mode', self::$config['mode'] ?? null]],
				self::$config['exception']
			);
		}

		// Check if type is valid
		if (!in_array(self::$config['type'], self::QINPUT_STRATEGY)) {
			throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['type', self::$config['type'] ?? null]],
				self::$config['exception']
			);
		}
	}

	private static function selectStrategy()
	{
		// initial $config[all_columns] for all columns need for validation GROUP BY and ORDER BY
		self::$config['all_columns'] = [];

		// initial basic query configuration check
		self::validateConfig();

		return match (self::$config['type']) {
			'table' => new Query(new Table(self::$config)),
			'insert' => new Query(new Insert(self::$config, self::$data)),
			'delete' => new Query(new Delete(self::$config, self::$data)),
			'select' => new Query(new Select(self::$config)),
			'update' => new Query(new Update(self::$config, self::$data))
		};
	}
}
