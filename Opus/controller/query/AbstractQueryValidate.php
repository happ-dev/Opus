<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 13:31:22
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 13:55:09
 **/

namespace Opus\controller\query;

use Opus\controller\query\TraitQuery;
use Opus\controller\exception\ControllerException;
use Opus\config\Config;

abstract class AbstractQueryValidate
{
	use TraitQuery;

	protected array $config;
	protected object $query;

	/**
	 * Validates the table name in the configuration
	 *
	 * Checks if the table name follows the required format (schema.table)
	 * using a regular expression defined in QVALID_TABLE constant.
	 *
	 * @throws ControllerException If the table name is invalid or missing
	 * @return void
	 */
	protected function validateTable(): void
	{
		filter_var($this->config['table'], FILTER_VALIDATE_REGEXP, self::QVALID_TABLE)
			?: throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['table', $this->config['table'] ?? null]],
				$this->config['exception']
			);
	}

	/**
	 * Validates column definitions in the configuration
	 *
	 * This method checks:
	 * 1. If the query type is valid
	 * 2. If the column configuration exists and is a non-empty array
	 * 3. If each column name follows the required format
	 * 4. If each column type is valid
	 *
	 * @param string $queryType The type of query being validated (INSERT, SELECT, etc.)
	 * @param bool $table Whether the query is for a table or not (default: false)
	 * @throws ControllerException If any validation fails
	 * @return void
	 */
	protected function validateColumns(string $queryType, bool $table = false): void
	{
		// Validate query type
		filter_var(strtoupper($queryType), FILTER_VALIDATE_REGEXP, self::QVALID_COLUMN_QUERY_TYPES)
			?: throw new ControllerException(
				'controller\query\validate\columns\queryType',
				['message' => $queryType],
				$this->config['exception']
			);

		// Check if column configuration exists and is valid
		if (!isset($this->config['columns']) || !is_array($this->config['columns']) || empty($this->config['columns'])) {
			throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['columns', isset($this->config['columns']) ? print_r($this->config['columns'], true) : null]],
				$this->config['exception']
			);
		}

		// Validate each column name and column type
		foreach ($this->config['columns'] as $value) {
			// Validate column name
			filter_var($value['name'], FILTER_VALIDATE_REGEXP, self::QVALID_COLUMN)
				?: throw new ControllerException(
					'controller\query\validate\columns\name',
					[
						'message' => [$value['name'] ?? null, $value['type'] ?? null],
						'details' => strtoupper($queryType)
					],
					$this->config['exception']
				);

			// Validate column type if $table = true
			if ($table === true) {
				filter_var($value['type'], FILTER_VALIDATE_REGEXP, self::QVALID_COLUMN_TYPE)
					?: throw new ControllerException(
						'controller\query\validate\columns\type',
						[
							'message' => [$value['name'] ?? null, $value['type'] ?? null],
							'details' => strtoupper($queryType)
						],
						$this->config['exception']
					);
			}

			// For all columns need to be validation GROUP BY and ORDER BY
			array_push($this->config['all_columns'], $value['name']);
		}
	}

	/**
	 * Validates foreign key definitions in the configuration
	 *
	 * This method checks:
	 * 1. If foreign_key is defined and not false
	 * 2. If each foreign key has valid key, table, and id properties
	 *
	 * @throws ControllerException If any foreign key validation fails
	 * @return void
	 */
	protected function validateForeignKey(): void
	{
		// Set foreign_key on false if not grven in config
		$this->config['foreign_key'] ??= false;

		// Skip validation if foreign_key is false
		if ($this->config['foreign_key'] === false) {
			return;
		}

		// Check if foreign_key is an array
		if (!is_array($this->config['foreign_key'])) {
			throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['foreign_key', 'must be an array or false']],
				$this->config['exception']
			);
		}

		// Extract column names for quick lookup
		$columnNames = array_column($this->config['columns'], 'name');

		// Validate each foreign key definition
		foreach ($this->config['foreign_key'] as $value) {
			// Validate key format
			filter_var($value['key'], FILTER_VALIDATE_REGEXP, self::QVALID_FOREIGN_KEY_KEY)
				?: throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['foreign_key => key', $value['key'] ?? null]],
					$this->config['exception']
				);

			// Check if the foreign key exists as a column
			if (!in_array($value['key'], $columnNames)) {
				throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['foreign_key => key', "'{$value['key']}' must exist as a column in the table"]],
					$this->config['exception']
				);
			}

			// Validate table format
			filter_var($value['table'], FILTER_VALIDATE_REGEXP, self::QVALID_TABLE)
				?: throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['foreign_key => table', $value['table'] ?? null]],
					$this->config['exception']
				);

			// Validate id format
			filter_var($value['id'], FILTER_VALIDATE_REGEXP, self::QVALID_FOREIGN_KEY_ID)
				?: throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['foreign_key => id', $value['id'] ?? null]],
					$this->config['exception']
				);
		}
	}

	/**
	 * Validates the drop_table configuration option
	 *
	 * This method checks if the drop_table option is a valid boolean value.
	 * If drop_table is not set in the configuration, it defaults to false.
	 *
	 * @throws ControllerException If drop_table is set but not a valid boolean value
	 * @return void
	 */
	protected function validateDropTable(): void
	{
		// Set drop_table on false if not grven in config
		$this->config['drop_table'] ??= false;

		// Skip validation if drop_table is false
		if ($this->config['drop_table'] === false) {
			return;
		}

		$this->config['drop_table'] = filter_var($this->config['drop_table'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
			?? throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['drop_table', $this->config['drop_table']]],
				$this->config['exception']
			);
	}

	/**
	 * Validates grant table permissions in the configuration
	 *
	 * This method checks:
	 * 1. If grant is defined and not false
	 * 2. If each grant entry has a valid user
	 * 3. If table and sequence permissions are valid arrays with proper values
	 *
	 * @throws ControllerException If any grant validation fails
	 * @return void
	 */
	protected function validateGrantTable(): void
	{
		// Set grant on false if not grven in config
		$this->config['grant'] ??= false;

		// Skip validation if grant is false
		if ($this->config['grant'] === false) {
			return;
		}

		// Check if grant is an array
		if (!is_array($this->config['grant'])) {
			throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['grant', 'must be an array or false']],
				$this->config['exception']
			);
		}

		foreach ($this->config['grant'] as $value) {
			// Check if required properties exist
			if (!isset($value['user'])) {
				throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['grant', 'missing required properties: user']],
					$this->config['exception']
				);
			}

			// Validate user
			filter_var($value['user'], FILTER_VALIDATE_REGEXP, self::QVALID_GRANT_TABLE_USER)
				?: throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['grant => user', $value['user'] ?? null]],
					$this->config['exception']
				);

			// Validate table permissions
			if (!is_array($value['table']) || !isset($value['table'])) {
				throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['grant => table', $value['table'] ?? null]],
					$this->config['exception']
				);
			}

			foreach ($value['table'] as $table) {
				filter_var($table, FILTER_VALIDATE_REGEXP, self::QVALID_GRANT_TABLE_SEQ)
					?: throw new ControllerException(
						'controller\query\validate\param',
						['message' => ['grant => table[]', $table ?? null]],
						$this->config['exception']
					);
			}

			// Validate sequence permissions
			if (!is_array($value['sequence']) || !isset($value['sequence'])) {
				throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['grant => sequence', $value['sequence'] ?? null]],
					$this->config['exception']
				);
			}

			foreach ($value['sequence'] as $sequence) {
				filter_var($sequence, FILTER_VALIDATE_REGEXP, self::QVALID_GRANT_TABLE_SEQ)
					?: throw new ControllerException(
						'controller\query\validate\param',
						['message' => ['grant => sequence[]', $sequence ?? null]],
						$this->config['exception']
					);
			}
		}
	}

	/**
	 * Validates WHERE conditions in the configuration
	 *
	 * This method checks:
	 * 1. If where is defined and not false
	 * 2. If each condition has a valid left operand and operator
	 * 3. If right operand is provided when not in PREPARE mode
	 *
	 * @throws ControllerException If any where condition validation fails
	 * @return void
	 */
	protected function validateWhere(): void
	{
		// Set where on false if not grven in config
		$this->config['where'] ??= false;

		// Skip validation if where is false
		if ($this->config['where'] === false) {
			return;
		}

		// Check if where is an array
		if (!is_array($this->config['where'])) {
			throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['where', 'must be an array or false']],
				$this->config['exception']
			);
		}

		// Validate each where condition
		foreach ($this->config['where'] as $value) {
			// Validate left operand
			filter_var($value['left'], FILTER_VALIDATE_REGEXP, self::QVALID_WHERE_LEFT)
				?: throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['where => left', $value['left'] ?? null]],
					$this->config['exception']
				);

			// Validate param (operator)
			match (true) {
				!isset($value['param']) => throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['where => param', 'missing']],
					$this->config['exception']
				),

				!filter_var($value['param'], FILTER_VALIDATE_REGEXP, self::QVALID_WHERE_PARAM) => throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['where => param', $value['param']]],
					$this->config['exception']
				),

				default => null
			};

			// Validate right operand if not in PREPARE mode
			match (true) {
				$this->config['mode'] != self::MODE_PREPARE
					&& (!isset($value['right']) || is_null($value['right']) || $value['right'] === '') => throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['where => right', 'value required when not in PREPARE mode']],
					$this->config['exception']
				),

				default => null
			};
		}
	}

	/**
	 * Validates the distinct_on configuration option
	 *
	 * This method checks if distinct_on is a valid boolean value or the string "DISTINCT ON".
	 * If distinct_on is not set in the configuration, it defaults to null.
	 * Valid values:
	 * - null: No distinct clause will be used
	 * - "DISTINCT ON" (case insensitive): Will use "DISTINCT ON " in the query
	 * - true/yes/1: Will be converted to "DISTINCT ON "
	 * - false/no/0: Will be converted to null (no distinct clause)
	 *
	 * @throws ControllerException If distinct_on has an invalid value
	 * @return void
	 */
	protected function validateDistinctOn(): void
	{
		// Set distinct_on on null if not grven in config
		$this->config['distinct_on'] ??= null;

		// Skip validation if distinct_on is null
		if ($this->config['distinct_on'] === null) {
			return;
		}

		// Check for special string value "distinct on"
		if (is_string($this->config['distinct_on']) && strtoupper($this->config['distinct_on']) === 'DISTINCT') {
			$this->config['distinct_on'] = 'DISTINCT ';
			return;
		}

		// Check for boolean values including "yes"
		$distinctOn = filter_var($this->config['distinct_on'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

		if ($distinctOn === null) {
			throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['distinct_on', $this->config['distinct_on']]],
				$this->config['exception']
			);
		}

		// Convert boolean to string or null
		$this->config['distinct_on'] = $distinctOn ? 'DISTINCT ' : null;
	}

	/**
	 * Validates additional column names in the configuration
	 *
	 * This method checks if other_column_name is a non-empty array of valid column names.
	 * If other_column_name is not set in the configuration, it defaults to null.
	 *
	 * @throws ControllerException If any column name is invalid
	 * @return void
	 */
	protected function validateOtherColumnName(): void
	{
		// Set other_column_name on null if not grven in config
		$this->config['other_columns_name'] ??= null;

		// Skip validation if other_column_name is null
		if ($this->config['other_columns_name'] === null) {
			return;
		}

		// Check if other_column_name is an array
		if (!is_array($this->config['other_columns_name']) || empty($this->config['other_columns_name'])) {
			throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['other_columns_name', isset($this->config['other_columns_name']) ? print_r($this->config['colother_columns_nameumn'], true) : null]],
				$this->config['exception']
			);
		}

		// Validate each column name
		foreach ($this->config['other_columns_name'] as $value) {
			filter_var($value, FILTER_VALIDATE_REGEXP, self::QVALID_COLUMN)
				?: throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['other_column_name[]', $value ?? null]],
					$this->config['exception']
				);
		}

		// For all columns need to be validation GROUP BY and ORDER BY
		$this->config['all_columns'] = array_merge($this->config['all_columns'], $this->config['other_columns_name']);
	}

	/**
	 * Validates LEFT JOIN configurations in the query
	 *
	 * This method checks:
	 * 1. If left_join is defined and not false
	 * 2. If each join has a valid table, columns array, and ON condition
	 * 3. If each column in the columns array is valid
	 *
	 * @throws ControllerException If any LEFT JOIN validation fails
	 * @return void
	 */
	protected function validateLeftJoin(): void
	{
		// Set left_join on false if not grven in config
		$this->config['left_join'] ??= false;

		// Skip validation if left_join is false
		if ($this->config['left_join'] === false) {
			return;
		}

		// Check if left_join is a non-empty array
		if (!is_array($this->config['left_join']) || empty($this->config['left_join'])) {
			throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['left_join', isset($this->config['left_join']) ? print_r($this->config['left_join'], true) : null]],
				$this->config['exception']
			);
		}

		// Validate each LEFT JOIN definition
		foreach ($this->config['left_join'] as $value) {
			// Validate table name
			filter_var($value['table'], FILTER_VALIDATE_REGEXP, self::QVALID_TABLE)
				?: throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['left_join => table', $value['table'] ?? null]],
					$this->config['exception']
				);

			// Validate column array
			if (!isset($value['column']) || empty($value['column']) || !is_array($value['column'])) {
				throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['left_join => column', isset($value['column']) ? print_r($value['column'], true) : null]],
					$this->config['exception']
				);
			}

			// Validate each column name
			foreach ($value['column'] as $column) {
				filter_var($column, FILTER_VALIDATE_REGEXP, self::QVALID_COLUMN)
					?: throw new ControllerException(
						'controller\query\validate\param',
						['message' => ['left_join => column[]', $column ?? null]],
						$this->config['exception']
					);
			}

			// Validate ON condition
			filter_var($value['on'], FILTER_VALIDATE_REGEXP, self::QVALID_LEFT_JOIN_ON)
				?: throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['left_join => on', $value['on'] ?? null]],
					$this->config['exception']
				);

			// For all columns need to be validation GROUP BY and ORDER BY
			$this->config['all_columns'] = array_merge($this->config['all_columns'], $value['column']);
		}
	}

	/**
	 * Validates ORDER BY configurations in the query
	 *
	 * This method checks:
	 * 1. If order_by is defined and not false
	 * 2. If order_by is a non-empty array
	 * 3. If each entry has a valid column name and sort direction
	 *
	 * @throws ControllerException If any ORDER BY validation fails
	 * @return void
	 */
	protected function validateOrderBy(): void
	{
		// Set order_by on false if not grven in config
		$this->config['order_by'] ??= false;

		// Skip validation if order_by is false
		if ($this->config['order_by'] === false) {
			return;
		}

		// Check if order_by is a non-empty array
		if (!is_array($this->config['order_by']) || empty($this->config['order_by'])) {
			throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['order_by', isset($this->config['order_by']) ? print_r($this->config['order_by'], true) : null]],
				$this->config['exception']
			);
		}

		// Validate each ORDER BY entry
		foreach ($this->config['order_by'] as $value) {
			// Validate column name
			filter_var($value['column'], FILTER_VALIDATE_REGEXP, self::QVALID_COLUMN)
				?: throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['order_by => column', $value['column'] ?? null]],
					$this->config['exception']
				);

			// Validate if distinct_on is not null, column must be in select section
			if ($this->config['distinct_on'] !== null && !in_array($value['column'], $this->config['all_columns'])) {
				throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['order_by => column', $value['column'] ?? null]],
					$this->config['exception']
				);
			}

			// Validate sort direction
			filter_var($value['sort'], FILTER_VALIDATE_REGEXP, self::QVALID_SORT_ORDER_BY)
				?: throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['order_by => sort', $value['sort'] ?? null]],
					$this->config['exception']
				);
		}
	}

	/**
	 * Validates the LIMIT clause value in the configuration
	 *
	 * This method processes the limit value based on its type:
	 * - If null, true, or "yes": sets limit to 100
	 * - If false or "no": sets limit to null (no limit)
	 * - If integer or string integer: validates against QVALID_LIMIT regex
	 *
	 * @throws ControllerException If the limit value is not valid
	 * @return void
	 */
	protected function validateLimit(): void
	{
		// Set limit on 100 if not grven in config
		$this->config['limit'] ??= 100;

		// Check if limit is a boolean-like value
		$boolValue = filter_var($this->config['limit'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

		// Process limit based on its type
		$this->config['limit'] = match (true) {
			is_null($boolValue) => $this->config['limit'],
			$boolValue === true => 100,
			$boolValue === false => null,
			default => 100
		};

		// Skip further validation if limit is null
		if (is_null($this->config['limit'])) {
			return;
		}

		// Validate integer limit against regex
		filter_var($this->config['limit'], FILTER_VALIDATE_REGEXP, self::QVALID_LIMIT)
			?: throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['limit', $this->config['limit'] ?? null]],
				$this->config['exception']
			);
	}

	/**
	 * Validates the OFFSET clause value in the configuration
	 *
	 * This method checks if the offset value is a valid integer.
	 * If offset is not set in the configuration, it defaults to null.
	 * The OFFSET clause specifies the number of rows to skip before
	 * starting to return rows from the query.
	 *
	 * @throws ControllerException If the offset value is not a valid integer
	 * @return void
	 */
	protected function validateOffset(): void
	{
		// Set offset on null if not grven in config
		$this->config['offset'] ??= null;

		// Skip validation if offset is null
		if ($this->config['offset'] === null) {
			return;
		}

		// Validate integer offset against regex
		filter_var($this->config['offset'], FILTER_VALIDATE_REGEXP, self::QVALID_OFFSET)
			?: throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['offset', $this->config['offset'] ?? null]],
				$this->config['exception']
			);
	}

	/**
	 * Validates the chunk size for query results
	 *
	 * This method checks if the chunk value is a valid integer.
	 * If chunk is not set in the configuration, it defaults to 50.
	 * Chunking is used to split large result sets into smaller pieces.
	 *
	 * @throws ControllerException If the chunk value is not a valid integer
	 * @return void
	 */
	protected function validateChunk(): void
	{
		// Set chunk on 50 if not grven in config
		$this->config['chunk'] ??= 50;

		filter_var($this->config['chunk'], FILTER_VALIDATE_REGEXP, self::QVALID_CHUNK)
			?: throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['chunk', $this->config['chunk'] ?? null]],
				$this->config['exception']
			);
	}

	/**
	 * Validates GROUP BY configurations in the query
	 *
	 * This method checks:
	 * 1. If group_by is defined and not false
	 * 2. If group_by is a non-empty array
	 * 3. If each column name in the array is valid
	 *
	 * @throws ControllerException If any GROUP BY validation fails
	 * @return void
	 */
	protected function validateGroupBy(): void
	{
		// Set group_by on false if not grven in config
		$this->config['group_by'] ??= false;

		// Skip validation if group_by is false
		if ($this->config['group_by'] === false) {
			return;
		}

		// Check if group_by is a non-empty array
		if (!is_array($this->config['group_by']) || empty($this->config['group_by'])) {
			throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['group_by', isset($this->config['group_by']) ? print_r($this->config['group_by'], true) : null]],
				$this->config['exception']
			);
		}

		// Validate each column name in the GROUP BY clause
		foreach ($this->config['group_by'] as $value) {
			filter_var($value, FILTER_VALIDATE_REGEXP, self::QVALID_COLUMN)
				?: throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['group_by[]', $value ?? null]],
					$this->config['exception']
				);

			$columnName = strpos($value, '.') !== false ? explode('.', $value)[1] : $value;
			if (!in_array($columnName, $this->config['all_columns'])) {
				throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['group_by[]', $value ?? null]],
					$this->config['exception']
				);
			}
		}
	}

	/**
	 * Validates the database configuration
	 *
	 * This method checks if the db_config value is valid when in MODE_EXECUTE.
	 * If db_config is not set in the configuration, it defaults to the system's
	 * default storage configuration.
	 *
	 * @throws ControllerException If the db_config is invalid in MODE_EXECUTE
	 * @return void
	 */
	protected function validateDbConfig(): void
	{
		// Set db_config to default storage config if not given in config
		$this->config['db_config'] ??= Config::getConfig('storage')->default;

		if ($this->config['mode'] === self::MODE_EXECUTE) {
			// Validate db_config
			filter_var($this->config['db_config'], FILTER_VALIDATE_REGEXP, self::QVALID_DB_CONFIG)
				?: throw new ControllerException(
					'controller\query\validate\param',
					['message' => ['db_config', $this->config['db_config'] ?? null]],
					$this->config['exception']
				);
		}
	}
}
