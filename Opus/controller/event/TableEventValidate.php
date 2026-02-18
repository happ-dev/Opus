<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-02-09 13:45:47
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-02-09 15:16:39
 **/

namespace Opus\controller\event;

use Opus\config\Config;
use Opus\controller\exception\ControllerException;
use Opus\controller\event\TraitValidEditorStrategy;

class TableEventValidate
{
	use TraitValidEditorStrategy;

	const VALID_TABLE = ['options' => ['regexp' => '/^[a-z_]+\.[a-z_]+$/']];
	const VALID_TABLE_ID = ['options' => ['regexp' => '/^(id__)+[^_]+[\w\D]+$/']];
	const VALID_COLUMN = ['options' => ['regexp' => '/^[\w\.\s\(\)"\',-]+$/i']];
	const VALID_JOIN = ['options' => ['regexp' => '/(LEFT\sJOIN\s)+[a-z_]+\.[a-z_]+(\sON\s)+(\()+[a-z_]+\.[a-zA-z0-9\._]+\s\=\s+[a-z_]+\.[a-zA-z0-9\._]+(\))/']];
	const VALID_SELECT_SQL = ['options' => ['regexp' => '/(SELECT\s)+(id__)+[^_]+[\w\D]+(,\s)+[\w\._\(\)\-\'\s]+(\sFROM\s)+[a-z_]+\.[a-z_;]/']];
	const VALID_SELECT_TEXT = ['options' => ['regexp' => '/^[\w\d\._@#]+$/']];
	const VALID_ACCESS_LEVEL = ['options' => ['regexp' => '/^(\d){1}$/']];
	const VALID_DEFAULT_ACCESS_LEVEL = 9;
	const VALID_BUTTONS_ATTRIBUTES_KEYS = ['type', 'text', 'attributes'];
	const VALID_TABLE_BUTTON_ADD = 'data-add';
	const VALID_TABLE_BUTTON_EDIT = 'data-edit';
	const VALID_TABLE_BUTTON_SHOW = 'data-show';
	const VALID_TABLE_BUTTON_DELETE = 'data-delete';

	public function __construct(public object $config) {}

	/**
	 * Validates and sets the database configuration
	 *
	 * If database is not specified in table configuration,
	 * assigns the default database from storage configuration.
	 *
	 * @throws ConfigException When storage configuration is not available
	 * @return void
	 *
	 * @see Config::getConfig()
	 */
	private function validateDb(): void
	{
		$this->config->table->db ??= Config::getConfig('storage')->default;
	}

	/**
	 * Validates table columns configuration and sets datatables indexes
	 *
	 * Performs the following validations:
	 * 1. Initializes columns to false if not set
	 * 2. If columns are provided as array:
	 *    - Validates each column name against VALID_COLUMN regex
	 *    - Sets datatables index (dt) for each column
	 *
	 * @throws ControllerException When:
	 *      - Column name fails regex validation
	 *      - Column format is invalid
	 * @return void
	 *
	 * @see self::VALID_COLUMN Regex pattern for valid column names
	 */
	private function validateColumns(): void
	{
		// Initialize columns if not set
		$this->config->table->columns ??= false;

		if (is_array($this->config->table->columns) && !empty($this->config->table->columns)) {

			foreach ($this->config->table->columns as $index => $value) {
				// Validate column name against regex pattern
				filter_var($value->db, FILTER_VALIDATE_REGEXP, self::VALID_COLUMN)
					?: throw new ControllerException(
						'controller\tableEvent\validateConfig\param',
						[
							'message' => ['column', $value->db],
							'details' => [$this->config->app, $this->config->event]
						],
						ControllerException::TYPE_API_EXCEPTION
					);

				// Set datatables index
				$this->config->table->columns[$index]->dt = $index;
			}
		}
	}

	/**
	 * Validates table join configuration
	 *
	 * Performs join validation in three steps:
	 * 1. Initializes join to false if not set
	 * 2. Validates boolean conversion of join value
	 * 3. If join is a non-empty string, validates against VALID_JOIN regex pattern
	 *
	 * @throws ControllerException When:
	 *      - Join string fails regex validation
	 *      - Join format does not match expected pattern
	 * @return void
	 *
	 * @see self::VALID_JOIN Regex pattern for valid join syntax
	 */
	private function validateJoin(): void
	{
		// Initialize join if not set
		$this->config->table->join ??= false;

		// Validate boolean conversion
		$this->config->table->join = (is_null(filter_var(
			$this->config->table->join,
			FILTER_VALIDATE_BOOLEAN,
			FILTER_NULL_ON_FAILURE
		)))
			? $this->config->table->join : false;

		// Validate join string if present
		if (
			$this->config->table->join !== false
			&& is_string($this->config->table->join)
			&& !empty($this->config->table->join)
		) {
			filter_var($this->config->table->join, FILTER_VALIDATE_REGEXP, self::VALID_JOIN)
				?: throw new ControllerException(
					'controller\tableEvent\validateConfig\param',
					[
						'message' => ['join', $this->config->table->join, false],
						'details' => [$this->config->app, $this->config->event]
					],
					ControllerException::TYPE_API_EXCEPTION
				);
		}
	}

	/**
	 * Validates and processes HTML select element configurations
	 *
	 * Processes select configurations in three formats:
	 * 1. String format: validates against select SQL pattern (for dynamic options loading)
	 * 2. Object with value array only: validates each option value and mirrors to text
	 * 3. Object with text array only: validates each option text and mirrors to value
	 * 4. Object with both: validates separate value and text arrays for options
	 *
	 * Example configurations:
	 * - SQL string: "SELECT id__user, name FROM users.list"
	 * - Value only: { value: ["1", "2", "3"] }
	 * - Text only: { text: ["Option 1", "Option 2"] }
	 * - Both: { value: ["1", "2"], text: ["First", "Second"] }
	 *
	 * @throws ControllerException When:
	 *      - Dynamic SQL select string is invalid
	 *      - Option values/texts fail validation pattern
	 *      - Configuration format is invalid
	 * @return void
	 *
	 * @see self::VALID_SELECT_SQL Pattern for dynamic options SQL
	 * @see self::VALID_SELECT_TEXT Pattern for option values/texts
	 */
	private function validateSelect(): void
	{
		// Initialize select if not set
		$this->config->table->select ??= false;

		// Unconditional exit if no configuration
		if (!$this->config->table->select || !is_object($this->config->table->select) || empty($this->config->table->select)) {
			return;
		}

		$validateText = fn($select) => filter_var($select, FILTER_VALIDATE_REGEXP, self::VALID_SELECT_TEXT)
			?: throw new ControllerException(
				'controller\tableEvent\validateConfig\param',
				[
					'message' => ['select', $select],
					'details' => [$this->config->app, $this->config->event]
				],
				ControllerException::TYPE_API_EXCEPTION
			);

		foreach ($this->config->table->select as $key => $value) {

			if (is_string($value)) {
				filter_var($value, FILTER_VALIDATE_REGEXP, self::VALID_SELECT_SQL)
					?: throw new ControllerException(
						'controller\tableEvent\validateConfig\param',
						[
							'message' => ['select', $value],
							'details' => [$this->config->app, $this->config->event]
						],
						ControllerException::TYPE_API_EXCEPTION
					);
				continue;
			}

			if (!is_object($value) || empty($value)) {
				continue;
			}

			match (true) {
				// Value only case
				isset($value->value) && is_array($value->value) && !empty($value->value) && !isset($value->text) => function () use ($value, $key, $validateText) {
					array_map($validateText, $value->value);
					$this->config->table->select->{$key}->value = array_merge([null], $value->value);
					$this->config->table->select->{$key}->text = $this->config->table->select->{$key}->value;
				},

				// Text only case
				isset($value->text) && is_array($value->text) && !empty($value->text) && !isset($value->value) => function () use ($value, $key, $validateText) {
					array_map($validateText, $value->text);
					$this->config->table->select->{$key}->text = array_merge([null], $value->text);
					$this->config->table->select->{$key}->value = $this->config->table->select->{$key}->text;
				},

				// Both value and text case
				isset($value->value, $value->text) && is_array($value->value) && is_array($value->text)
					&& !empty($value->value) && !empty($value->text) => function () use ($value, $key, $validateText) {
					array_map($validateText, $value->value);
					array_map($validateText, $value->text);
					$this->config->table->select->{$key}->value = array_merge([null], $value->value);
					$this->config->table->select->{$key}->text = array_merge([null], $value->text);
				},

				default => null
			};
		}
	}

	/**
	 * Validates button configurations and their attributes
	 *
	 * Validates:
	 * 1. Button configuration existence
	 * 2. Primary button attributes against VALID_BUTTONS_ATTRIBUTES_KEYS
	 * 3. Nested attributes when 'attributes' key is present
	 *
	 * Configuration structure:
	 * buttons: {
	 *   attname: {  // related to the field name in the table
	 *     attr1: value1,
	 *     attributes: {
	 *       nestedAttr1: value1,
	 *       nestedAttr2: value2
	 *     }
	 *   }
	 * }
	 *
	 * @throws ControllerException When:
	 *      - Invalid button attribute key is found
	 *      - Invalid nested attribute key is found
	 * @return void
	 *
	 * @see self::VALID_BUTTONS_ATTRIBUTES_KEYS Valid attribute keys
	 */
	private function validateButtons(): void
	{
		// Initialize buttons if not set
		$this->config->table->buttons ??= false;

		// Unconditional exit if no configuration
		if (!$this->config->table->buttons || !is_object($this->config->table->buttons) || empty($this->config->table->buttons)) {
			return;
		}

		$throwException = fn(string $attname, string $attr, ?string $nested = null) => throw new ControllerException(
			'controller\tableEvent\validateConfig\param',
			[
				'message' => ['buttons', $attname . ' => ' . ($nested ? "attributes => $attr" : $attr)],
				'details' => [$this->config->app, $this->config->event]
			],
			ControllerException::TYPE_API_EXCEPTION
		);

		$validateAttribute = fn(string $attname, string $attr) =>
		in_array($attr, self::VALID_BUTTONS_ATTRIBUTES_KEYS)
			?? $throwException($attname, $attr);

		foreach ($this->config->table->buttons as $attname => $button) {

			foreach ($button as $attr => $value) {
				$validateAttribute($attname, $attr);

				match ($attr) {
					'attributes' => array_walk(
						$value,
						fn($attrValue, $attrKey) => in_array($attrKey, self::VALID_BUTTONS_ATTRIBUTES_KEYS)
							?? $throwException($attname, $attrKey, 'nested')
					),
					default => null
				};
			}
		}
	}

	/**
	 * Validates disabled column configurations
	 *
	 * Performs the following validations:
	 * 1. Initializes disabled configuration if not set
	 * 2. When join is false:
	 *    - Validates that disabled columns exist in table columns
	 * 3. Validates that all disabled values are boolean
	 *
	 * Configuration structure:
	 * disabled: {
	 *    columnName1: true|false,
	 *    columnName2: true|false
	 * }
	 *
	 * @throws ControllerException When:
	 *      - Disabled column doesn't exist in table columns (when join is false)
	 *      - Disabled value is not a valid boolean
	 * @return void
	 */
	private function validateDisabled(): void
	{
		// Initialize buttons if not set
		$this->config->table->disabled ??= false;

		// Unconditional exit if no configuration
		if (!$this->config->table->disabled || !is_object($this->config->table->disabled) || empty($this->config->table->disabled)) {
			return;
		}

		$keys = array_keys(get_object_vars($this->config->table->disabled));

		// Validate disabled columns exist in table columns when join is false
		if ($this->config->table->join === false) {
			$columnsValue = array_map(
				function ($value) {
					return $value->db;
				},
				$this->config->table->columns
			);

			foreach ($keys as $key) {
				in_array($key, $columnsValue) ?: throw new ControllerException(
					'controller\tableEvent\validateConfig\param',
					[
						'message' => ['disabled', $key],
						'details' => [$this->config->app, $this->config->event]
					],
					ControllerException::TYPE_API_EXCEPTION
				);
			}
		}

		// Validate all disabled values are boolean
		foreach ($this->config->table->disabled as $value) {
			filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
				?: throw new ControllerException(
					'controller\tableEvent\validateConfig\param',
					[
						'message' => ['disabled', $value],
						'details' => [$this->config->app, $this->config->event]
					],
					ControllerException::TYPE_API_EXCEPTION
				);
		}
	}

	/**
	 * Validates search by column configuration
	 *
	 * Validates that:
	 * 1. Search by column is initialized
	 * 2. Search by column exists in table columns
	 *
	 * Configuration structure:
	 * searchby: {
	 *    columnName: "column_db_name"
	 * }
	 *
	 * Note: There appears to be a typo in the condition 'dissearchbyabled'
	 * which should probably be 'disabled' or similar
	 *
	 * @throws ControllerException When:
	 *      - Specified search by column doesn't exist in table columns
	 * @return void
	 */
	private function validateSearchBy(): void
	{
		// Initialize searchby if not set
		$this->config->table->searchby ??= false;

		// Unconditional exit if no configuration
		if (!$this->config->table->searchby || !is_object($this->config->table->searchby) || empty($this->config->table->searchby)) {
			return;
		}

		// Get all column DB names
		$columnsValue = array_map(
			function ($value) {
				return $value->db;
			},
			$this->config->table->columns
		);

		// Validate searchby column exists in table columns
		in_array($this->config->table->searchby, $columnsValue)
			?: throw new ControllerException(
				'controller\tableEvent\validateConfig\param',
				[
					'message' => ['searchby', $this->config->table->searchby],
					'details' => [$this->config->app, $this->config->event]
				],
				ControllerException::TYPE_API_EXCEPTION
			);
	}

	/**
	 * Validates access configuration
	 *
	 * Validates that:
	 * 1. Access configuration is initialized
	 * 2. Access configuration keys are valid
	 * 3. Access configuration values are valid
	 *
	 * Configuration structure:
	 * access: {
	 *    show: 1,
	 *    edit: 1,
	 *    add: 2,
	 *    delete: 3
	 * }
	 *
	 * @throws ControllerException When:
	 *      - Access configuration is not initialized
	 *      - Access configuration key is invalid
	 *      - Access configuration value is invalid
	 * @return void
	 *
	 * @see self::VALID_EDITOR_STRATEGY Valid editor strategy keys
	 * @see self::VALID_ACCESS_LEVEL Valid access level pattern
	 */
	private function validateAccess(): void
	{

		foreach (self::VALID_EDITOR_STRATEGY as $key) {
			// Set default access level if not set
			$this->config->table->access->{$key} ??= self::VALID_DEFAULT_ACCESS_LEVEL;

			if (filter_var($this->config->table->access->{$key}, FILTER_VALIDATE_REGEXP, self::VALID_ACCESS_LEVEL) === false) {
				throw new ControllerException(
					'controller\tableEvent\validateConfig\param',
					[
						'message' => ['access->' . $key, $this->config->table->access->{$key}],
						'details' => [$this->config->app, $this->config->event]
					],
					ControllerException::TYPE_API_EXCEPTION
				);
			}
		}
	}
}
