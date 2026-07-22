<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-20 18:50:48
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-22 10:43:42
 **/

namespace Opus\controller\event\editor;

use Opus\storage\db\Db;
use Opus\html\form\Form;
use Opus\controller\lang\Lang;
use Opus\controller\event\TraitValidEditorStrategy;
use Opus\storage\exception\StorageException;

class TableEditorFieldValues
{
	use TraitValidEditorStrategy;

	/**
	 * Creates a disabled text input field for serial/identity columns
	 *
	 * This method handles serial/identity columns (auto-incrementing IDs) by:
	 * 1. Creating a disabled text input field to display the current value
	 * 2. Setting the dataId reference to the current value
	 * 3. Updating the tableDetails array with input field information
	 *
	 * Serial columns are typically primary keys that shouldn't be editable,
	 * so this method ensures they're displayed but not modifiable.
	 *
	 * @param array &$tableDetails Reference to the table details array to be updated
	 * @param int $index The index of the column in the tableDetails array
	 * @param array $value The column value and metadata
	 * @param string|null &$dataId Reference to store the ID value for record identification
	 * @return void
	 */
	final public static function serialValue(
		array &$tableDetails,
		int $index,
		?array $value,
		?string &$dataId
	): void {
		$form = new Form();
		$element = [
			'name' => 'input_' . $index,
			'id' => 'id_input_' . $index,
			'tag' => 'input',
			'attributes' => [
				'style' => 'width: 400px; display: inline',
				'class' => 'form-control form-control-sm',
				'type' => 'text',
				'disabled'
			]
		];

		if (isset($value['value']) && !is_null($value['value'])) {
			$element['attributes']['value'] = $value['value'];
		}

		$form->addElement($element);
		$dataId = $value['value'] ??= null;
		$tableDetails[$index]['input_name'] = 'input_' . $index;
		$tableDetails[$index]['value'] = $form->getElement('input_' . $index);
		unset($form);
	}

	/**
	 * Creates a disabled field for select values with lookup query
	 *
	 * This method handles fields that use a SELECT query for value lookup by:
	 * 1. Modifying the configured SELECT query to filter for the current value
	 * 2. Executing the query to retrieve the display value
	 * 3. Updating the tableDetails array with the display value
	 *
	 * This is used for foreign key fields where we want to display a meaningful
	 * value (like a name) instead of just the ID.
	 *
	 * @param array &$tableDetails Reference to the table details array to be updated
	 * @param object $config The configuration object containing SELECT queries
	 * @param int $index The index of the column in the tableDetails array
	 * @param array $value The column value and metadata
	 * @return void
	 */
	final public static function disabledSelectValue(
		array &$tableDetails,
		object $config,
		int $index,
		?array $value
	): void {
		match (true) {
			// Case: SQL query in config
			is_string($config->table->select->{$value['attname']}) &&
				!empty($config->table->select->{$value['attname']}) => (function () use (&$tableDetails, $config, $index, $value) {
				// Determine if we need to add WHERE or AND based on existing query
				$where = (strpos($config->table->select->{$value['attname']}, 'WHERE') !== false) ? ' AND ' : ' WHERE ';

				// Prepare the query by trimming semicolon
				$query = trim($config->table->select->{$value['attname']}, ';');

				// Extract column name from the query (assumes second word is the column)
				$queryArray = explode(' ', $query);

				// Execute the query with added filter for the current value
				$selectResult = Db::dbArrayResult(
					$query . $where . trim($queryArray[1], ',') . ' = ' . $value['value'],
					$config->table->db,
					StorageException::TYPE_API_EXCEPTION
				);

				// Get the result keys and store the display value
				$keys = array_keys($selectResult[0]);
				$tableDetails[$index]['input_name'] = 'input_' . $index;
				$tableDetails[$index]['value'] = (isset($keys[1])) ? $selectResult[0][$keys[1]] : $selectResult[0][$keys[0]];
			})(),

			// Default case: Direct arrays or raw value
			default => (function () use (&$tableDetails, $index, $value) {
				$tableDetails[$index]['input_name'] = 'input_' . $index;
				$tableDetails[$index]['value'] = $value['value'];
			})()
		};
	}

	/**
	 * Creates a select dropdown field for foreign key columns
	 *
	 * This method handles fields that use a dropdown for value selection by:
	 * 1. Determining the source of options (direct values or database query)
	 * 2. Creating a select element with appropriate options
	 * 3. Setting the selected value for edit operations
	 * 4. Adding required attribute for NOT NULL columns
	 * 5. Updating the tableDetails array with the form element
	 *
	 * @param array &$tableDetails Reference to the table details array to be updated
	 * @param object $config The configuration object containing select options or queries
	 * @param int $index The index of the column in the tableDetails array
	 * @param array|null $value The column value and metadata
	 * @return void
	 */
	final public static function selectValue(
		array &$tableDetails,
		object &$config,
		int $index,
		?array $value
	): void {
		// Initialize form and select options
		$form = new Form();
		$selectValue = [];
		$selectText = [];

		// Determine source of select options
		match (true) {
			// Case: Direct value and text arrays in config
			isset($config->table->select->{$value['attname']}->value)
				|| isset($config->table->select->{$value['attname']}->text) => (function () use (&$selectValue, &$selectText, $config, $value) {
				$selectValue = $config->table->select->{$value['attname']}->value;
				$selectText = $config->table->select->{$value['attname']}->text;
			})(),

			// Case: SQL query in config
			is_string($config->table->select->{$value['attname']})
				&& !empty($config->table->select->{$value['attname']}) => (function () use (&$selectValue, &$selectText, $config, $value) {
				$selectResult = Db::dbArrayResult(
					$config->table->select->{$value['attname']},
					$config->table->db,
					StorageException::TYPE_API_EXCEPTION
				);
				$keys = array_keys($selectResult[0]);

				foreach ($selectResult as $i => $valueSelect) {
					$selectValue[$i] = $valueSelect[$keys[0]];
					$selectText[$i] = isset($keys[1]) ? $valueSelect[$keys[1]] : $valueSelect[$keys[0]];
				}

				$selectValue = array_merge([null], $selectValue);
				$selectText = array_merge([null], $selectText);
			})(),

			// Default case: do nothing
			default => null
		};

		// Create form element configuration
		$element = [
			'name' => 'input_' . $index,
			'id' => 'id_input_' . $index,
			'tag' => 'select',
			'attributes' => [
				'style' => 'width: 400px',
				'class' => 'form-select form-select-sm'
			],
			'option' => [
				'all' => false,
				'value' => $selectValue,
				'text' => $selectText
			]
		];

		// Set selected value for edit operations
		if ($config->strategy === self::EDITOR_STRATEGY_EDIT) {
			$element['option']['selected'] = array_search($value['value'], $selectValue);
		}

		// Add required attribute for NOT NULL columns
		if ((bool) $value['attnotnull'] === true) {
			array_push($element['attributes'], 'required');
		}

		// Create and store the form element
		$form->addElement($element);
		$tableDetails[$index]['input_name'] = 'input_' . $index;
		$tableDetails[$index]['value'] = $form->getElement('input_' . $index);
		unset($form, $selectValue, $selectText, $element);
	}

	/**
	 * Creates a button element with associated text value
	 *
	 * This method handles fields that need an action button by:
	 * 1. Determining the display text based on field type and value
	 * 2. Adding a float-end class to the button for proper alignment
	 * 3. Creating a button element with configured attributes
	 * 4. Adding the record ID as a data attribute for JavaScript interaction
	 * 5. Updating the tableDetails array with the text and button element
	 *
	 * @param array &$tableDetails Reference to the table details array to be updated
	 * @param object &$config The configuration object containing button definitions
	 * @param int $index The index of the column in the tableDetails array
	 * @param array|null $value The column value and metadata
	 * @param string|null &$dataId Reference to the record ID for button data attribute
	 * @param string $type The field type (e.g., 'boolean') for text formatting
	 * @return void
	 */
	final public static function buttonElement(
		array &$tableDetails,
		object &$config,
		int $index,
		?array $value,
		?string &$dataId,
		string $type
	): void {
		$form = new Form();

		// Determine display text based on field type
		$text = self::isBoolValue($value['value'], $type);

		$buttonIcon = $config->table->buttons->{$value['attname']}->icon;
		$buttonText = Lang::getInstance()->get($config->table->buttons->{$value['attname']}->text);

		// Create button element
		$form->addElement([
			'name' => 'input_' . $index,
			'id' => 'id_input_' . $index,
			'tag' => 'button',
			'text' => <<<HTML
			<i class="me-1 bi {$buttonIcon}"></i><em>{$buttonText}</em>
			HTML,
			'attributes' => array_merge_recursive(
				(array) $config->table->buttons->{$value['attname']}->attributes,
				[
					'type' => 'button',
					'class' => 'mr-sm-1 btn btn-sm btn-dark bs-opus-black-3d float-end',
					'data-id' => $dataId
				]
			)
		]);

		// Store text and button in tableDetails
		$tableDetails[$index]['input_name'] = 'input_' . $index;
		$tableDetails[$index]['value'] = <<<HTML
		<div style="max-width: 400px;">
			<span class="d-inline-block text-truncate align-middle" style="max-width: 300px;">{$text}</span>
			{$form->getElement('input_' .$index)}
		</div>
		HTML;
		unset($form);
	}

	/**
	 * Creates a simple disabled text display for a field
	 *
	 * This method handles fields that should be displayed as plain text without
	 * any form controls. It formats boolean values appropriately using the
	 * isBoolValue helper method and updates the tableDetails array with the
	 * formatted value.
	 *
	 * This is typically used for read-only fields in view mode or for fields
	 * that don't need any special input controls.
	 *
	 * @param array &$tableDetails Reference to the table details array to be updated
	 * @param int $index The index of the column in the tableDetails array
	 * @param array|null $value The column value and metadata
	 * @param string $type The data type of the field
	 * @return void
	 */
	final public static function disabledElement(
		array &$tableDetails,
		int $index,
		?array $value,
		string $type
	): void {
		$tableDetails[$index]['input_name'] = 'input_' . $index;
		$tableDetails[$index]['value'] = self::isBoolValue($value['value'], $type);
	}

	/**
	 * Creates a standard text input field for basic data types
	 *
	 * This method handles fields that use a standard text input by:
	 * 1. Creating a text input element with appropriate styling
	 * 2. Setting the current value from the database
	 * 3. Adding required attribute for NOT NULL columns
	 * 4. Updating the tableDetails array with the form element
	 *
	 * This is used for most basic data types like varchar, integer, etc.
	 * that don't need special input handling.
	 *
	 * @param array &$tableDetails Reference to the table details array to be updated
	 * @param int $index The index of the column in the tableDetails array
	 * @param array|null $value The column value and metadata
	 * @return void
	 */
	final public static function standardTypeValue(array &$tableDetails, int $index, ?array $value): void
	{
		$form = new Form();
		$element = [
			'name' => 'input_' . $index,
			'id' => 'id_input_' . $index,
			'tag' => 'input',
			'attributes' => [
				'style' => 'width: 400px; display: inline',
				'class' => 'form-control form-control-sm',
				'type' => 'text'
			]
		];

		// Add required attribute for NOT NULL columns
		if ((bool) $value['attnotnull'] === true) {
			array_push($element['attributes'], 'required');
		}

		if (isset($value['value']) && !is_null($value['value'])) {
			$element['attributes']['value'] = $value['value'];
		}

		$form->addElement($element);
		$tableDetails[$index]['input_name'] = 'input_' . $index;
		$tableDetails[$index]['value'] = $form->getElement('input_' . $index);
		unset($form);
	}

	/**
	 * Creates a select dropdown for boolean values
	 *
	 * This method handles boolean fields by:
	 * 1. Creating a select element with true/false options
	 * 2. Using localized text for the options (True/False in English, Prawda/Fałsz in Polish)
	 * 3. Setting the selected value for edit operations
	 * 4. Adding required attribute for NOT NULL columns
	 * 5. Updating the tableDetails array with the form element
	 *
	 * @param array &$tableDetails Reference to the table details array to be updated
	 * @param object &$config The configuration object containing editor settings
	 * @param int $index The index of the column in the tableDetails array
	 * @param array|null $value The column value and metadata
	 * @return void
	 */
	final public static function booleanValue(
		array &$tableDetails,
		object &$config,
		int $index,
		?array $value
	): void {
		$form = new Form();
		$element = [
			'name' => 'input_' . $index,
			'id' => 'id_input_' . $index,
			'tag' => 'select',
			'attributes' => [
				'style' => 'width: 400px',
				'class' => 'form-select form-select-sm'
			],
			'option' => [
				'all' => false,
				'value' => [null, 'true', 'false'],
				'text' => [
					null,
					self::isBoolValue(true, 'boolean'),
					self::isBoolValue(false, 'boolean')
				]
			]

		];

		// Set selected value for edit operations
		if ($config->strategy === self::EDITOR_STRATEGY_EDIT) {
			$element['option']['selected'] = ((bool) $value['value'] === true) ? 1 : 2;
		}

		// Add required attribute for NOT NULL columns
		if ((bool) $value['attnotnull'] === true) {
			array_push($element['attributes'], 'required');
		}

		$form->addElement($element);
		$tableDetails[$index]['input_name'] = 'input_' . $index;
		$tableDetails[$index]['value'] = $form->getElement('input_' . $index);
		unset($form);
	}

	/**
	 * Creates a date picker input field
	 *
	 * This method handles date fields by:
	 * 1. Creating a text input with date picker functionality
	 * 2. Setting appropriate styling and data attributes for the date picker
	 * 3. Adding a placeholder to show the expected date format
	 * 4. Setting the current value from the database
	 * 5. Adding required attribute for NOT NULL columns
	 * 6. Updating the tableDetails array with the form element
	 *
	 * @param array &$tableDetails Reference to the table details array to be updated
	 * @param int $index The index of the column in the tableDetails array
	 * @param array|null $value The column value and metadata
	 * @return void
	 */
	final public static function dateValue(array &$tableDetails, int $index, ?array $value): void
	{
		$form = new Form();
		$element = [
			'name' => 'input_' . $index,
			'id' => 'id_input_' . $index,
			'tag' => 'input',
			'attributes' => [
				'class' => 'form-control form-control-sm date-opus-picker',
				'style' => 'width: 400px; display: inline; position: relative',
				'type' => 'text',
				'placeholder' => 'YYYY-MM-DD'
			]
		];

		// Add required attribute for NOT NULL columns
		if ((bool) $value['attnotnull'] === true) {
			array_push($element['attributes'], 'required');
		}

		if (isset($value['value']) && !is_null($value['value'])) {
			$element['attributes']['value'] = $value['value'];
		}

		$form->addElement($element);
		$tableDetails[$index]['input_name'] = 'input_' . $index;
		$tableDetails[$index]['value'] = $form->getElement('input_' . $index);
		unset($form);
	}

	/**
	 * Creates a datetime picker input field
	 *
	 * This method handles timestamp fields by:
	 * 1. Creating a text input with datetime picker functionality
	 * 2. Setting appropriate styling and data attributes for the datetime picker
	 * 3. Adding a placeholder to show the expected datetime format
	 * 4. Setting the current value from the database
	 * 5. Adding required attribute for NOT NULL columns
	 * 6. Updating the tableDetails array with the form element
	 *
	 * @param array &$tableDetails Reference to the table details array to be updated
	 * @param int $index The index of the column in the tableDetails array
	 * @param array|null $value The column value and metadata
	 * @return void
	 */
	final public static function timestampValue(array &$tableDetails, int $index, ?array $value): void
	{
		$form = new Form();
		$element = [
			'name' => 'input_' . $index,
			'id' => 'id_input_' . $index,
			'tag' => 'input',
			'attributes' => [
				'class' => 'form-control form-control-sm timestamp-opus-picker',
				'style' => 'width: 400px; display: inline; position: relative',
				'type' => 'text',
				'placeholder' => 'YYYY-MM-DD HH:mm:ss',
			]
		];

		// Add required attribute for NOT NULL columns
		if ((bool) $value['attnotnull'] === true) {
			array_push($element['attributes'], 'required');
		}

		if (isset($value['value']) && !is_null($value['value'])) {
			$element['attributes']['value'] = $value['value'];
		}

		$form->addElement($element);
		$tableDetails[$index]['input_name'] = 'input_' . $index;
		$tableDetails[$index]['value'] = $form->getElement('input_' . $index);
		unset($form);
	}

	/**
	 * Formats boolean values as localized text
	 *
	 * This helper method converts boolean values to localized text strings
	 * based on the user's language setting. For boolean type fields, it returns
	 * "True"/"False" in English or "Prawda"/"Fałsz" in Polish. For other types,
	 * it returns the original value unchanged.
	 *
	 * @param mixed $value The value to format
	 * @param string $type The data type of the value
	 * @return mixed Formatted value for boolean type or original value for other types
	 */
	private static function isBoolValue(mixed $value, string $type): mixed
	{
		return match ($type) {
			'boolean' => ((bool) $value === true)
				? Lang::getInstance()->get('event.message.true')
				: Lang::getInstance()->get('event.message.false'),
			default => $value
		};
	}
}
