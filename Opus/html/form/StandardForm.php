<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-07-27 18:44:45
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-27 21:50:14
 **/

namespace Opus\html\form;

use stdClass;
use Opus\storage\db\Db;
use Opus\controller\exception\ControllerException;

/**
 * Builds a complete HTML form from database table metadata or a manually provided data array.
 *
 * Automatically selects the appropriate StandardFormElements method for each column
 * based on its PostgreSQL type and template attribute. Returns concatenated HTML via get()
 * or provides access to all elements in the $data array (each row contains 'el' key with rendered HTML).
 *
 * Constructor parameters:
 *
 * @param array &$data When DATA_SOURCE_ARRAY, each element must contain at minimum:
 *   - 'attname'    (string) Column/field name — used for input name/id generation
 *   - 'comment'    (string) Lang key or 'English|lang.key' format for label
 *   - 'type'       (string) PostgreSQL type: smallint|integer|bigint|numeric|decimal|real|
 *                           double|float|serial|bigserial|boolean|date|timestamp|character|text
 *   - 'value'      (mixed)  Optional. Current field value for edit operations
 *   - 'attnotnull' (bool)   Optional. If true, adds HTML required attribute
 *   When DATA_SOURCE_DB, this array is populated automatically from Db::dbGetTableDetails().
 *
 * @param array $template Column-specific rendering configuration. Keys are column names.
 *   Each entry must contain 'attribute' => 'readonly'|'select'|'default'.
 *   A 'default' key is always added automatically if not provided.
 *
 *   For 'readonly':
 *     ['attribute' => 'readonly', 'value' => 'SELECT id, text FROM table WHERE id = {id}']
 *     ['attribute' => 'readonly', 'value' => 'static text']
 *
 *   For 'select':
 *     SQL mode:          ['attribute' => 'select', 'text' => 'SELECT value, text FROM table']
 *     Static mode:       ['attribute' => 'select', 'text' => [...], 'value' => [...]]
 *
 *   For 'select-opus':
 *     ['attribute' => 'select-opus', 'app' => 'appName', 'event' => 'eventName', 'limit' => 20]
 *
 *   For 'default': ['attribute' => 'default'] — type-based auto-detection
 *
 * @param object $options Configuration object with two sub-objects:
 *   $options->db (database source configuration):
 *     - scheme  (string|null) Database schema name, null = DATA_SOURCE_ARRAY
 *     - table   (string|null) Table name, null = DATA_SOURCE_ARRAY
 *     - columns (string|null) Comma-separated column names or null for all
 *     - execute (array)       Parameters for Db::dbExecute() to retrieve row values
 *
 *   $options->elements (passed to every StandardFormElements method):
 *     - width        (string)  CSS width, default '100%'
 *     - type         (string)  Input type attribute, default 'text'
 *     - shadow       (string)  Shadow CSS class, default 'bs-opus-black-3d'
 *     - margin       (string)  Margin CSS class, default 'mb-3'
 *     - required     (bool)    HTML required attribute, default true
 *     - readonly     (bool)    HTML readonly attribute, default false
 *     - floating     (bool)    Wrap in Bootstrap form-floating with icon, default true
 *     - standardName (bool)    Prefix name/id with 'input_'/'id_input_', default true
 *     - size         (string)  Bootstrap size class [default|lg|sm], default 'default'
 *     - style        (string)  Inline style for wrapper, default 'border-radius: var(--bs-border-radius)'
 *     - icon         (string)  Bootstrap icon class, default 'bi-input-cursor-text'
 *
 * Usage:
 *   $form = new StandardForm(data: $data, template: $template, options: $options);
 *   $html = $form->buildForm()->get();
 *
 *   // Or iterate over elements:
 *   $form->buildForm();
 *   foreach ($form->data as $row) { echo $row['el']; }
 */
class StandardForm
{
	const DATA_SOURCE_DB = 'db';
	const DATA_SOURCE_ARRAY = 'array';

	public function __construct(
		public array &$data = [],
		public array $template = [],
		public object $options = new stdClass(),
	) {
		$this->options->db ??= new stdClass();
		$this->options->elements ??= new stdClass();
	}

	/**
	 * Builds the form by resolving data source, validating inputs, and generating HTML elements.
	 *
	 * Determines data source (DB or array), applies template to each column,
	 * then dispatches to the appropriate StandardFormElements method based on type/template.
	 * After calling, each $this->data[$key]['el'] contains rendered HTML.
	 *
	 * @return self
	 */
	final public function buildForm()
	{
		$this->standardOptions();
		$this->setTemplate();

		$dataSource = match (true) {
			// Data is retrieved from the database
			$this->options->db->scheme !== null && $this->options->db->table !== null => (function () {
				$this->validateTemplate();
				$this->dataSourceDb();
				return self::DATA_SOURCE_DB;
			})(),
			// Data is passed from the outside
			default => (function () {
				$this->validateTemplate();
				$this->validateData();
				$this->dataSourceArray();
				return self::DATA_SOURCE_ARRAY;
			})()
		};

		foreach ($this->data as $key => $value) {
			// Extract base data type
			list($type) = explode(' ', $value['type'], 2);

			match (true) {
				// Serial value, first column if data from db
				$key === 0 && $dataSource === self::DATA_SOURCE_DB =>
				StandardFormElements::serialValue($this->data[$key], $this->options->elements),

				// Readonly value
				($value['template']['attribute'] ?? null) === 'readonly' =>
				StandardFormElements::readonlyValue($this->data[$key], $this->options->elements),

				// Password value
				($value['template']['type'] ?? null) === 'password' =>
				StandardFormElements::passwordValue($this->data[$key], $this->options->elements),

				// Select value
				($value['template']['attribute'] ?? null) === 'select'
				|| ($value['template']['attribute'] ?? null) === 'select-opus' =>
				StandardFormElements::selectValue($this->data[$key], $this->options->elements),

				// Boolean value
				$type == 'boolean' =>
				StandardFormElements::booleanValue($this->data[$key], $this->options->elements),

				// Numeric type
				in_array(
					(function () use ($type) {
						if (preg_match('/^\s*(smallint|integer|bigint|numeric|decimal|real|double|float|serial|bigserial)\b/i', $type, $matches)) {
							return strtolower($matches[1]);
						}

						return 'default';
					})(),
					['smallint', 'integer', 'bigint', 'numeric', 'decimal', 'real', 'double', 'float', 'serial', 'bigserial']
				) => StandardFormElements::numericValue($this->data[$key], $this->options->elements),

				// Date value
				$type == 'date' => StandardFormElements::dateValue($this->data[$key], $this->options->elements),

				// Timestamp
				$type == 'timestamp' => StandardFormElements::timestampValue($this->data[$key], $this->options->elements),

				// Default case (fallback for other types)
				default => StandardFormElements::standardTypeValue($this->data[$key], $this->options->elements)
			};
		}

		return $this;
	}

	/**
	 * Returns concatenated HTML of all rendered form elements.
	 *
	 * Iterates over $this->data and joins all 'el' values into a single string.
	 *
	 * @return string Complete form HTML
	 */
	final public function get()
	{
		$html = '';

		foreach ($this->data as $value) {
			$html .= $value['el'] ?? null;
		}

		return $html;
	}

	/**
	 * Applies default values to $this->options->db and $this->options->elements
	 *
	 * @return void
	 */
	private function standardOptions(): void
	{
		// If the `schema` and `table` options are not null,
		// it means the form data is being retrieved from a database table.
		$this->options->db->scheme ??= null;
		$this->options->db->table ??= null;
		$this->options->db->columns ??= null;

		// parameter for Db::dbExecute function to retrieve a value
		// see Opus\storage\db;
		$this->options->db->execute ??= [];

		// Options will affect all HTML elements.
		$this->options->elements->floating ??= true;
	}

	/**
	 * Retrieves table metadata and row values from database, assigns template per column
	 *
	 * @return void
	 */
	private function dataSourceDb()
	{
		$this->data = Db::dbGetTableDetails(
			$this->options->db->scheme,
			$this->options->db->table,
			$this->options->db->columns
		);
		$values = Db::dbExecute($this->options->db->execute);

		foreach ($this->data as $key => $value) {
			$column = $value['attname'];
			$this->data[$key]['value'] = $values[0][$column] ?? null;
			$this->data[$key]['template'] = $this->getTemplate($column);
		}
	}

	/**
	 * Assigns template to each element in manually provided $this->data array
	 *
	 * @return void
	 */
	private function dataSourceArray(): void
	{
		foreach ($this->data as $key => $value) {
			$this->data[$key]['template'] = $this->getTemplate($value['attname']);
		}
	}

	/**
	 * Merges user-provided template with default fallback ['default' => ['attribute' => 'default']]
	 *
	 * @return void
	 */
	private function setTemplate(): void
	{
		$this->template = array_merge_recursive(
			['default' => ['attribute' => 'default']],
			$this->template
		);
	}

	/**
	 * Returns template configuration for a given column, falls back to 'default'
	 *
	 * @param string $column Column name or array key
	 * @return array Template array with 'attribute' key
	 */
	private function getTemplate(string $column): array
	{
		return $this->template[$column] ?? $this->template['default'];
	}

	/**
	 * Validates $this->data keys (attname, comment, type) and column type against allowed PostgreSQL types.
	 * Used only for DATA_SOURCE_ARRAY — DB data is already validated by PostgreSQL.
	 *
	 * @throws ControllerException When required key is empty/not string or type is invalid
	 * @return void
	 */
	private function validateData(): void
	{
		foreach ($this->data as $row) {
			array_map(
				fn($key) => empty($row[$key]) || !is_string($row[$key])
					? throw new ControllerException(
						'html\form\standardForm',
						['message' => $key],
						ControllerException::TYPE_API_EXCEPTION
					)
					: null,
				['attname', 'comment', 'type']
			);

			$type = strtolower(explode(' ', $row['type'], 2)[0]);
			in_array($type, [
				'smallint',
				'integer',
				'bigint',
				'numeric',
				'decimal',
				'real',
				'double',
				'float',
				'serial',
				'bigserial',
				'boolean',
				'date',
				'timestamp',
				'character',
				'text'
			]) ?: throw new ControllerException(
				'html\form\standardForm\type',
				['message' => $type],
				ControllerException::TYPE_API_EXCEPTION
			);
		}
	}

	/**
	 * Validates 'attribute' value in each $this->template entry against allowed values: readonly|select|default
	 *
	 * @throws ControllerException When template attribute is invalid
	 * @return void
	 */
	private function validateTemplate(): void
	{
		array_map(
			fn($key, $value) => in_array($value['attribute'] ?? $value['type'] ?? null, ['readonly', 'select', 'select-opus', 'password', 'default'])
				?: throw new ControllerException(
					'html\form\standardForm\template',
					['message' => "$key: " . ($value['attribute'] ?? 'null')],
					ControllerException::TYPE_API_EXCEPTION
				),
			array_keys($this->template),
			$this->template
		);
	}
}
