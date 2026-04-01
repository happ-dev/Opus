<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-04-01 12:48:38
 * @Last Modified by:   Tomasz Ulazowski
 * @Last Modified time: 2026-04-01 12:55:16
 **/

namespace Opus\html\table;

use Opus\controller\exception\ControllerException;

class TableParamsValidate
{
	/**
	 * Validates the complete table configuration
	 *
	 * This method serves as the main entry point for table validation.
	 * It validates both the table attributes and structure by calling
	 * the appropriate validation methods.
	 *
	 * @param array &$table The complete table configuration to validate (passed by reference)
	 * @throws ControllerException When any part of the table configuration is invalid
	 * @return void No return value as the array is modified by reference
	 */
	public function validateInputTable(array &$table): void
	{
		$this->validateAttributes($table['attributes']);
		$this->validateStructure($table);
	}

	/**
	 * Validates a percentage value
	 *
	 * @param string $value The value to validate
	 * @param int $min Minimum percentage allowed
	 * @param int $max Maximum percentage allowed
	 * @return int|false The percentage value or false if invalid
	 */
	private function validatePercentage(string $value, int $min, int $max)
	{

		if (!preg_match('/^(\d{1,3})%$/', $value, $matches)) {
			return false;
		}

		$percentage = (int) $matches[1];
		return filter_var(
			$percentage,
			FILTER_VALIDATE_INT,
			[
				'options' => ['min_range' => $min, 'max_range' => $max]
			]
		) ? $percentage . '%' : false;
	}

	/**
	 * Validates and normalizes table attribute parameters
	 *
	 * This method checks required fields, applies validators, and sets default values
	 * for optional parameters. It modifies the attributes array directly.
	 *
	 * @param array &$attributes The attribute parameters to validate and normalize (passed by reference)
	 * @throws ControllerException When attributes don't match expected format or required fields are missing
	 * @return void No return value as the array is modified by reference
	 */
	private function validateAttributes(array &$attributes): void
	{
		// Define validation rules
		$attributesRules = [
			'required' => ['class', 'id'],
			'optional' => ['width', 'cellspacing'],
			'defaults' => [
				'width' => '100%',
				'cellspacing' => '0'
			],

			'validators' => [
				'id' => fn($value) => preg_match('/^id_[a-z0-9\-]+$/', $value) ? $value : false,
				'width' => fn($value) => $this->validatePercentage($value, 10, 100),
				'cellspacing' => fn($value) => filter_var($value, FILTER_VALIDATE_INT, [
					'options' => ['min_range' => 0, 'max_range' => 100]
				])
			],

			'messages' => [
				'id' => "Invalid id format. Must start with 'id_', contain only lowercase letters, numbers, and hyphens",
				'width' => "Width must be a percentage value between 10% and 100%",
				'cellspacing' => "Cellspacing must be an integer between 0 and 100"
			]
		];

		// Check required fields
		foreach ($attributesRules['required'] as $field) {

			if (empty($attributes[$field])) {
				throw new ControllerException(
					'html\table\addTable\validateAttributes',
					['message' => $field]
				);
			}
		}

		// Apply validators and set defaults
		foreach (array_merge($attributesRules['required'], $attributesRules['optional']) as $field) {

			// Set default for optional fields if empty
			if (in_array($field, $attributesRules['optional']) && empty($attributes[$field])) {
				$attributes[$field] = $attributesRules['defaults'][$field];
				continue;
			}

			// Apply validator if exists
			if (isset($attributesRules['validators'][$field])) {
				$validatedValue = $attributesRules['validators'][$field]($attributes[$field]);

				if ($validatedValue === false) {
					throw new ControllerException(
						'html\table\addTable\validateAttributes\parametr',
						['message' => $attributesRules['messages'][$field]]
					);
				}

				$attributes[$field] = (string) $validatedValue;
			}
		}
	}

	/**
	 * Validates tbody structure against column names
	 *
	 * @param array $tbody The tbody data to validate
	 * @param array $columnNames The column names that should exist in each row
	 * @throws ControllerException When tbody structure is invalid
	 * @return void
	 */
	private function validateTbodyStructure(array $tbody, array $columnNames): void
	{
		foreach ($tbody as $rowIndex => $row) {
			if (!is_array($row)) {
				throw new ControllerException(
					'html\table\addTable\validateTbodyStructure',
					['message' => $rowIndex]
				);
			}

			// Check that each row has all required columns
			foreach ($columnNames as $column) {

				if (!array_key_exists($column, $row)) {
					throw new ControllerException(
						'html\table\addTable\validateTbodyStructure\row',
						['message' => [$column, $rowIndex]]
					);
				}
			}
		}
	}

	/**
	 * Validates table structure parameters
	 *
	 * @param array &$params The table structure parameters to validate
	 * @throws ControllerException When parameters don't match expected format
	 * @return void No return value as the array is modified by reference
	 */
	private function validateStructure(array &$params): void
	{
		// Define validation rules
		$rules = [
			'types' => [
				'cname' => function ($value, $params) {
					return ($params['tbody'] === false && ($value === null || empty($value))) ||
						($params['tbody'] !== false && is_array($value));
				},
				'thead' => fn($value) => is_array($value),
				'tfoot' => fn($value) => is_array($value) || $value === false,
				'tbody' => fn($value) => is_array($value) || $value === false
			],
			'messages' => [
				'cname' => "cname must be an array when tbody is not false, or null when tbody is false",
				'thead' => "thead must be an array",
				'tfoot' => "tfoot must be an array or false",
				'tbody' => "tbody must be an array or false"
			]
		];

		// Validate each parameter against its rule
		foreach ($rules['types'] as $param => $validator) {
			// Special case for cname when tbody is false
			if ($param === 'cname' && $params['tbody'] === false) {
				$params[$param] ??= null;
				// Skip validation for cname when tbody is false
				continue;
			}

			// Check if parameter exists before validating it
			if (!isset($params[$param]) || !$validator($params[$param], $params)) {
				throw new ControllerException(
					'html\table\addTable\validateStructure',
					['message' => $rules['messages'][$param]]
				);
			}
		}

		// Validate tbody structure if it's an array
		if (is_array($params['tbody']) && !empty($params['tbody']) && is_array($params['cname'])) {
			$this->validateTbodyStructure($params['tbody'], $params['cname']);
		}
	}
}
