<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-20 17:48:45
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-20 19:00:27
 **/

namespace Opus\controller\event\editor;

use PDO;
use Opus\html\form\Form;
use Opus\html\table\Table;
use Opus\storage\db\Db;
use Opus\storage\db\AbstractDb;
use Opus\storage\exception\StorageException;
use Opus\controller\event\TraitValidEditorStrategy;

abstract class AbstractTableEditor implements InterfaceEditor
{
	use TraitValidEditorStrategy;

	protected ?array $tableDetails;
	protected ?string $dataId = null;
	protected object $config;

	/**
	 * Retrieves detailed information about the table structure
	 *
	 * This method:
	 * 1. Extracts the schema and table name from the configuration
	 * 2. Queries the database for detailed information about all columns in the table
	 * 3. Stores the column details in the tableDetails property for later use
	 *
	 * The retrieved information includes column names, data types, constraints,
	 * and other metadata needed for form generation and data validation.
	 *
	 * @return void
	 * @throws StorageException If database access fails
	 */
	protected function selectTableDetails(): void
	{
		list($scheme, $table) = explode('.', $this->config->table->table);
		$this->tableDetails = Db::dbGetTableDetails(
			$scheme,
			$table,
			null,
			$this->config->table->db,
			StorageException::TYPE_API_EXCEPTION
		);
	}

	/**
	 * Sets up NULL handling checkboxes for nullable columns
	 *
	 * This method processes each column in the table and:
	 * 1. For NOT NULL columns, sets placeholder values
	 * 2. For nullable columns, creates checkboxes to allow setting NULL values
	 * 3. Handles special cases for disabled fields and existing NULL values
	 *
	 * The checkboxes are stored in the tableDetails array for each column,
	 * making them available for form rendering.
	 *
	 * @return void
	 */
	protected function getFieldNulls(): void
	{
		$form = new Form();

		foreach ($this->tableDetails as $key => $value) {
			// Process based on column's NULL constraint
			match ((bool) $value['attnotnull']) {
				// NOT NULL columns don't need NULL checkboxes
				true => (function () use ($key) {
					$this->tableDetails[$key]['chname'] = null;
					$this->tableDetails[$key]['chattnotnull'] = null;
				})(),

				// Nullable columns get checkboxes
				false => (function () use ($form, $key, $value) {
					$element = [
						'name' => 'ch_' . $key,
						'id' => 'id_ch_' . $key,
						'tag' => 'input',
						'attributes' => [
							'class' => 'form-check-input',
							'type' => 'checkbox',
							'value' => true
						]
					];

					// Determine checkbox state based on value and strategy
					if ($this->config->strategy !== self::EDITOR_STRATEGY_ADD && is_null($value['value'])) {
						array_push($element['attributes'], 'checked');
					}

					// Handle disabled fields
					if (
						isset($this->config->table->disabled->{$value['attname']})
						&& (bool) $this->config->table->disabled->{$value['attname']} === true
					) {
						array_push($element['attributes'], 'disabled');
					}

					// Create checkbox element
					$form->addElement($element);

					// Store checkbox references in tableDetails
					$this->tableDetails[$key]['chname'] = 'ch_' . $key;
					$this->tableDetails[$key]['chattnotnull'] = $form->getElement('ch_' . $key);
				})()
			};
		}

		unset($form);
	}

	/**
	 * Retrieves field values for an existing record
	 *
	 * This method:
	 * 1. Determines which column to use for searching (from searchby configuration)
	 * 2. Prepares and executes a database query to fetch the record
	 * 3. Populates the 'value' property for each column in tableDetails
	 *
	 * The retrieved values are used to populate form fields when editing
	 * or viewing an existing record.
	 *
	 * @return void
	 * @throws StorageException If database access fails
	 */
	protected function getFieldValues(): void
	{
		// Skip for add strategy or when ID is not set
		if ($this->config->strategy === self::EDITOR_STRATEGY_ADD || !isset($this->config->table->id)) {
			// Initialize empty values for all columns
			foreach ($this->tableDetails as $key => $value) {
				$this->tableDetails[$key]['value'] = null;
			}

			return;
		}

		// Rest of the method remains unchanged
		$index = 0;

		if ($this->config->table->searchby !== false) {
			// Use match with an IIFE to find the search column index
			$index = (function () {
				foreach ($this->tableDetails as $key => $value) {

					if ($this->config->table->searchby === $value['attname']) {
						return $key;
					}
				}

				return 0; // Default to first column if not found
			})();
		}

		// Prepare and execute query to fetch record
		$param = (string) ':' . $this->tableDetails[$index]['attname'];
		$result = Db::dbExecute(
			[
				'prepare' => 'SELECT * FROM ' . $this->config->table->table . ' WHERE ' . $this->tableDetails[$index]['attname'] . ' = ' . $param,
				$param => $this->config->table->id
			],
			$this->config->table->db,
			StorageException::TYPE_API_EXCEPTION
		);

		// Populate values for all columns
		foreach ($this->tableDetails as $key => $value) {
			$this->tableDetails[$key]['value'] = $result[0][$this->tableDetails[$key]['attname']];
		}
	}

	/**
	 * Creates appropriate form elements for each field based on data type
	 *
	 * This method processes each column in the table and creates the appropriate
	 * form element based on:
	 * 1. Column position (first column is treated as a serial/ID field)
	 * 2. Field configuration (disabled, select, buttons)
	 * 3. Data type (integer, boolean, date, timestamp, etc.)
	 * 4. Current strategy (add, edit, show, delete)
	 *
	 * It delegates the actual form element creation to specialized methods
	 * in the TableEditorFieldValues class.
	 *
	 * @return void
	 */
	protected function setFieldValues(): void
	{
		foreach ($this->tableDetails as $index => $value) {
			// Extract base data type
			list($type) = explode(' ', $value['type'], 2);

			match (true) {
				// Serial value (first column)
				$index === 0 =>
				TableEditorFieldValues::serialValue($this->tableDetails, $index, $value, $this->dataId),

				// Disabled select value
				$this->config->table->disabled !== false &&
					$this->config->table->select !== false &&
					isset($this->config->table->disabled->{$value['attname']}) &&
					isset($this->config->table->select->{$value['attname']}) &&
					($type == 'integer' || $type == 'character') &&
					$this->config->strategy === self::EDITOR_STRATEGY_EDIT =>
				TableEditorFieldValues::disabledSelectValue($this->tableDetails, $this->config, $index, $value),

				// Select value
				$this->config->table->select !== false &&
					(isset($this->config->table->select->{$value['attname']}->value) ||
						is_string($this->config->table->select->{$value['attname']})) =>
				TableEditorFieldValues::selectValue($this->tableDetails, $this->config, $index, $value),

				// Button element
				isset($this->config->table->disabled->{$value['attname']}) &&
					(bool) $this->config->table->disabled->{$value['attname']} === true &&
					isset($this->config->table->buttons->{$value['attname']}) &&
					$this->config->strategy === self::EDITOR_STRATEGY_EDIT =>
				TableEditorFieldValues::buttonElement($this->tableDetails, $this->config, $index, $value, $this->dataId, $type),

				// Disabled element
				(bool) $this->config->table->disabled !== false &&
					isset($this->config->table->disabled->{$value['attname']}) &&
					(bool) $this->config->table->disabled->{$value['attname']} === true &&
					$this->config->strategy === self::EDITOR_STRATEGY_EDIT =>
				TableEditorFieldValues::disabledElement($this->tableDetails, $index, $value, $type),

				// Standard numeric/string types
				in_array($type, ['integer', 'bigint', 'character', 'real', 'double', 'numeric']) =>
				TableEditorFieldValues::standardTypeValue($this->tableDetails, $index, $value),

				// Boolean value
				$type == 'boolean' =>
				TableEditorFieldValues::booleanValue($this->tableDetails, $this->config, $index, $value),

				// Date value
				$type == 'date' =>
				TableEditorFieldValues::dateValue($this->tableDetails, $index, $value),

				// Timestamp value
				$type == 'timestamp' =>
				TableEditorFieldValues::timestampValue($this->tableDetails, $index, $value),

				// Default case (fallback for other types)
				default =>
				TableEditorFieldValues::standardTypeValue($this->tableDetails, $index, $value)
			};
		}
	}

	/**
	 * Generates the table body for displaying record data
	 *
	 * This method creates a table display for the record data with:
	 * 1. Different column sets based on user permission level
	 * 2. Appropriate headers for the displayed columns
	 * 3. All field values from the tableDetails array
	 *
	 * Admin users (level > 6) see additional technical columns like
	 * column names and data types, while regular users see only
	 * the field labels, NULL checkboxes, and values.
	 *
	 * @return array Table configuration with HTML content and metadata
	 */
	protected function body(): array
	{
		// Determine columns to display based on user level
		$columns = match ((int) $_SESSION['level'] > 6) {
			true => [
				'cname' => ['attname', 'type', 'comment', 'chattnotnull', 'value'],
				'thead' => ['Kolumna', 'Typ', 'Pole', 'NULL', 'Wartość']
			],
			false => [
				'cname' => ['comment', 'chattnotnull', 'value'],
				'thead' => ['Pole', 'NULL', 'Wartość']
			]
		};

		// Create table with field data
		$table = new Table();
		$table->addTable([
			'attributes' => [
				'class' => 'table table-sm table-hover table-happ-black',
				'id' => 'id_table-event-dt'
			],
			'cname' => $columns['cname'],
			'thead' => $columns['thead'],
			'tfoot' => false,
			'tbody' => $this->tableDetails
		]);

		// Return table HTML and metadata
		return [
			'table' => $table->getTableById('id_table-event-dt'),
			'strategy' => $this->config->strategy,
			'root' => true
		];
	}

	/**
	 * Prepares session data for saving form values
	 *
	 * This method stores configuration and column information in the session
	 * to be used during the save operation. It:
	 * 1. Saves database connection, strategy, table name, and access settings
	 * 2. Processes each column, skipping disabled fields in edit mode
	 * 3. Stores column metadata including name, type, and form field references
	 * 4. Handles special case for delete operations by only processing the first column
	 *
	 * The session data is later used by the save handler to process form submissions.
	 *
	 * @return void
	 */
	protected function prepareSave(): void
	{
		// Store configuration in session
		$_SESSION['tableEditor']['config'] = [
			'db' => $this->config->table->db,
			'strategy' => $this->config->strategy,
			'table' => $this->config->table->table,
			'id' => $this->config->table->id ??= null,
			'access' => $this->config->table->access
		];
		$index = 0;

		// Process each column
		foreach ($this->tableDetails as $value) {
			// Skip disabled fields in edit mode
			if (
				isset($this->config->table->disabled->{$value['attname']})
				&& (bool) $this->config->table->disabled->{$value['attname']} === true
				&& $this->config->strategy === self::EDITOR_STRATEGY_EDIT
			) {
				continue;
			}

			// Store column metadata in session
			$_SESSION['tableEditor']['column'][$index] = [
				'name' => $value['attname'],
				'type' => $value['type'],
				'in_name' => (isset($value['input_name'])) ? $value['input_name'] : null,
				'ch_name' => (isset($value['chname'])) ? $value['chname'] : null,
			];

			// For delete operations, only process the first column
			if ($this->config->strategy === self::EDITOR_STRATEGY_DELETE && $index === 0) {
				break;
			}

			$index++;
		}
	}
}
