<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-04-01 11:31:43
 * @Last Modified by:   Tomasz Ulazowski
 * @Last Modified time: 2026-04-01 12:12:34
 **/

namespace Opus\html\form;

use Opus\controller\exception\ControllerException;

class FormElementValidate
{
	use TraitValidHtmlTags;

	/**
	 * Validates a form element configuration
	 *
	 * This method validates the structure and content of a form element configuration,
	 * ensuring that all required fields are present and valid. It also sets default
	 * values for optional fields when they are not provided.
	 *
	 * @param array &$element The element configuration to validate (passed by reference)
	 *                       The element should contain:
	 *                       - 'id': string (required) - Must start with 'id_', followed by lowercase letters, numbers, and hyphens
	 *                       - 'tag': string (required) - HTML tag name like 'input', 'select', 'button'
	 *                       - 'attributes': array (required) - HTML attributes for the element
	 *                       - 'name': string (optional) - Element name, generated from ID if not provided
	 *                       - 'closing-tag': string (optional) - Generated automatically based on tag type
	 *                       - 'option': array (optional/required for select) - Options for select elements
	 *                       - 'optgroups': array (optional) - Option groups for select elements
	 * @return void
	 * @throws ControllerException When the element configuration is invalid or missing required fields
	 */
	public static function validateElement(array &$element): void
	{
		// New class instance
		$formElement = new self();

		// Define validation rules
		$rules = [
			'required' => ['tag', 'id', 'attributes'],
			'optional' => ['name', 'closing-tag', 'option', 'optgroups'],
			'defaults' => [
				'name' => function ($data) {
					return isset($data['name']) && is_string($data['name'])
						? $data['name']
						: str_replace('id_', '', $data['id']);
				},
				'closing-tag' => function ($data) {
					$requiresClosing = in_array($data['tag'], self::HTML_TAGS_REQUIRING_CLOSING);
					return $requiresClosing ? '</' . $data['tag'] . '>' : false;
				}
			],
			'validators' => [
				'id' => fn($value) => preg_match('/^id_[a-z0-9\-\_]+$/', $value) ? $value : false,
				'tag' => fn($value) => in_array(
					$value,
					array_merge(self::HTML_TAGS_REQUIRING_CLOSING, self::HTML_TAGS_NOT_REQUIRING_CLOSING)
				) ? true : false,
				'attributes' => fn(&$value, $tag) => $formElement->validateAttributes($value, $tag),
				'option' => fn(&$value, $tag, $optGroups) => $formElement->validateOption($value, $tag, $optGroups),
				'optgroups' => fn(&$value, $tag) => $formElement->validateOptGroups($value, $tag)
			],
			'messages' => [
				'id' => "ID must start with 'id_', contain only lowercase letters, numbers, and hyphens",
				'tag' => "Tag must be a valid HTML tag",
				'name' => "Name must be a string or null",
				'attributes' => "Attributes must be not empty array!",
				'option' => "Option must be not empty array!",
				'optgroups' => "OptGroups must be not empty array!"
			]
		];

		// Check required fields
		foreach ($rules['required'] as $field) {
			$element[$field] ?? throw new ControllerException(
				'html\form\validateElement\isset',
				['message' => $field]
			);
		}

		// Apply validators and set defaults
		foreach (array_merge($rules['required'], $rules['optional']) as $field) {
			// Set default for optional fields if empty
			if (in_array($field, $rules['optional']) && empty($element[$field]) && isset($rules['defaults'][$field])) {
				$element[$field] = $rules['defaults'][$field]($element);
				continue;
			}

			// Apply validator if exists
			if (isset($rules['validators'][$field]) && isset($element[$field])) {

				// Exception for function input
				$validatedValue = match ($field) {
					'option' => $rules['validators'][$field]($element[$field], $element['tag'], isset($element['optgroups'])),
					'attributes',
					'optgroups' => $rules['validators'][$field]($element[$field], $element['tag']),
					default => $rules['validators'][$field]($element[$field])
				};

				if ($validatedValue === false) {
					throw new ControllerException(
						'html\form\validateElement\parametr',
						['message' => $rules['messages'][$field]]
					);
				}
			}
		}
	}

	/**
	 * Validates HTML element attributes based on tag type
	 *
	 * This method checks that required attributes are present and valid,
	 * and that all other attributes follow HTML standards.
	 *
	 * @param array &$attributes The attributes to validate (passed by reference)
	 * @param string $tag The HTML tag these attributes belong to
	 * @return bool True if validation passes
	 * @throws ControllerException When attributes don't meet requirements
	 */
	protected function validateAttributes(array &$attributes, string $tag): bool
	{
		$rules = [
			'required' => match ($tag) {
				'input' => ['class', 'type'],
				default => ['class']
			},
			'validators' => [
				'class' => fn($value) => is_string($value) && !empty($value) ? $value : false,
				'type' => fn($value) => in_array($value, self::HTML_INPUT_TYPES) ? $value : false
			],
			'messages' => [
				'class' => "Class must be a string!",
				'type' => "Type must be one of: " . implode(', ', self::HTML_INPUT_TYPES),
				'bool-attribute' => fn($value) => "Attribute {$value} must be one of: " . implode(', ', self::HTML_BOOLEAN_ATTRIBUTES),
				'non-bool-attribute' => fn($value) => "Attribute {$value} must have a value!"
			]
		];

		// Check required fields
		foreach ($rules['required'] as $field) {
			$attributes[$field] ?? throw new ControllerException(
				'html\form\validateElement\isset',
				['message' => $field]
			);
		}

		// Apply validators using match to avoid if statements
		foreach ($attributes as $key => $value) {
			match (true) {
				isset($rules['validators'][$key]) =>
				$rules['validators'][$key]($value) ?? throw new ControllerException(
					'html\form\validateElement\parametr',
					['message' => $rules['messages'][$key]]
				),

				is_numeric($key) && !in_array($value, self::HTML_BOOLEAN_ATTRIBUTES) =>
				throw new ControllerException(
					'html\form\validateElement\parametr',
					['message' => $rules['messages']['bool-attribute']($value)]
				),

				$key === 'value' => null,

				is_string($key) && (is_null($value) || empty($value)) =>
				throw new ControllerException(
					'html\form\validateElement\parametr',
					['message' => $rules['messages']['non-bool-attribute']($key)]
				),

				default => null
			};
		}

		return true;
	}

	/**
	 * Validates select option parameters
	 *
	 * This method validates the option configuration for select HTML elements,
	 * ensuring that required fields are present and all values are valid.
	 *
	 * @param array &$option The option parameters to validate (passed by reference)
	 * @param string $tag The HTML tag these options belong to
	 * @param bool $optGroups Whether option groups are present
	 * @return bool True if validation passes
	 * @throws ControllerException When option parameters don't meet requirements
	 */
	protected function validateOption(array &$option, string $tag, bool $optGroups = false): bool
	{
		// If tag is not equal select, option will not be validated!
		if ($tag !== 'select') {
			return true;
		}

		$rules = [
			'required' => match ($optGroups) {
				true => [],
				false => ['text']
			},
			'optional' => ['all', 'ftext', 'value', 'selected', 'disabled'],
			'defaults' => [
				'all' => false,			// boolean, if not provided it defaults to false
				'ftext' => null			// string|null, if not provided it defaults to null
			],
			'validators' => [
				'all' => fn($value) => is_bool($value) ? $value : false,
				'ftext' => fn($value) => is_string($value) || is_null($value) ? $value : false,
				'text' => fn($value) => is_array($value) && !empty($value) ? $value : false,
				'value' => function ($value, $data) {
					return is_array($value) && count($value) === count($data['text']) ? $value : false;
				},
				'selected' => fn($value, $data) => is_int($value) && $value >= 0 && $value < count($data['text']) ? $value : false,
				'disabled' => function ($value, $data) {

					if (!is_array($value)) {
						return false;
					}

					foreach ($value as $element) {

						if (!is_int($element) || $element < 0 || $element >= count($data['text'])) {
							return $element;
						}
					}

					return true;
				}
			],
			'messages' => [
				'text' => "Text must be a non-empty array",
				'all' => "All must be a boolean value",
				'ftext' => "Ftext must be a string or null",
				'value' => "Value must be an array with the same number of elements as value",
				'selected' => "Selected must be a valid index in the value array",
				'disabled' => "Disabled must be an array of valid indices in the value array",
				'disabled-index' => fn($value) => "Disabled item: {$value} is greater than the value array!"
			]
		];

		// Check required fields
		foreach ($rules['required'] as $field) {
			$option[$field] ?? throw new ControllerException(
				'html\form\validateElement\isset',
				['message' => $field]
			);
		}

		// Process each field (required and optional)
		foreach (array_merge($rules['required'], $rules['optional']) as $field) {
			match (true) {
				// Set defaults for optional fields if empty
				in_array($field, $rules['optional'])
					&& empty($option[$field])
					&& array_key_exists($field, $rules['defaults']) => $option[$field] = $rules['defaults'][$field],

				// Skip fields that don't need validation
				!isset($rules['validators'][$field]) || !isset($option[$field]) => null,

				// Validate fields that need validation
				default => $this->validateOptionField($field, $option, $rules)
			};
		}

		return true;
	}

	/**
	 * Helper method to validate a specific option field
	 *
	 * @param string $field The field name to validate
	 * @param array &$option The option parameters
	 * @param array $rules The validation rules
	 * @throws ControllerException When validation fails
	 */
	private function validateOptionField(string $field, array &$option, array $rules): void
	{
		$validatedValue = match ($field) {
			'value',
			'selected',
			'disabled' => $rules['validators'][$field]($option[$field], $option),
			default => $rules['validators'][$field]($option[$field])
		};

		if ($validatedValue === false || (is_numeric($validatedValue) && $field === 'disabled')) {
			throw new ControllerException(
				'html\form\validateElement\parametr',
				['message' => match ($field) {
					'disabled' => is_numeric($validatedValue)
						? $rules['messages']['disabled-index']($validatedValue)
						: $rules['messages'][$field],
					default => $rules['messages'][$field]
				}]
			);
		}
	}

	/**
	 * Validates option groups for select HTML elements
	 *
	 * This method validates the optgroup configuration for select elements,
	 * ensuring that required fields are present and all values are valid.
	 *
	 * @param array &$optgroups The option groups to validate (passed by reference)
	 * @param string $tag The HTML tag these option groups belong to
	 * @return bool True if validation passes
	 * @throws ControllerException When option group parameters don't meet requirements
	 */
	private function validateOptGroups(array &$optgroups, string $tag): bool
	{
		// Skip validation if not a select tag
		if ($tag !== 'select') {
			return true;
		}

		$rules = [
			'required' => ['label', 'option'],
			'required.option' => ['text'],
			'optional.option' => ['value'],
			'validators' => [
				'label' => fn($value) => is_string($value) ? $value : false,
				'option' => fn($value) => is_array($value) && !empty($value) ? $value : false
			],
			'validators.option' => [
				'text' => fn($value) => is_array($value) && !empty($value) ? $value : false,
				'value' => fn($value, $data) => is_array($value) && count($value) === count($data['text']) ? $value : false
			],
			'messages' => [
				'label' => fn($value) => "Label must be a string at index: {$value}",
				'option' => fn($value) => "Option must be a non-empty array at index: {$value}",
				'text' => fn($value) => "Text must be an non-empty array at index: {$value}",
				'value' => fn($value) => "Value must be an array with the same number of elements as text at index: {$value} in optGroups."
			]
		];

		// Main optgroups loop
		foreach ($optgroups as $index => $group) {
			$this->validateOptGroup($group, $rules, $index);
		}

		return true;
	}

	/**
	 * Validates a single option group
	 *
	 * @param array $group The option group to validate
	 * @param array $rules The validation rules
	 * @param int $index The index of the group in the optgroups array
	 * @throws ControllerException When validation fails
	 */
	private function validateOptGroup(array $group, array $rules, int $index): void
	{
		// Check required fields and their sub-fields
		foreach ($rules['required'] as $field) {
			// Check if field exists
			$group[$field] ?? throw new ControllerException(
				'html\form\validateElement\isset',
				['message' => $field]
			);

			// Check sub-fields if needed
			$this->validateSubFields($group, $field, $rules, $index);

			// Validate the field
			$validatedValue = $rules['validators'][$field]($group[$field]);
			if ($validatedValue === false) {
				throw new ControllerException(
					'html\form\validateElement\parametr',
					['message' => $rules['messages'][$field]($index)]
				);
			}
		}
	}

	/**
	 * Validates sub-fields of an option group field
	 *
	 * @param array $group The option group
	 * @param string $field The parent field name
	 * @param array $rules The validation rules
	 * @param int $index The index of the group
	 * @throws ControllerException When validation fails
	 */
	private function validateSubFields(array $group, string $field, array $rules, int $index): void
	{
		// Check required sub-fields
		$requiredKey = 'required.' . $field;
		if (isset($rules[$requiredKey])) {
			foreach ($rules[$requiredKey] as $subField) {
				$group[$field][$subField] ?? throw new ControllerException(
					'html\form\validateElement\isset',
					['message' => $subField]
				);
			}
		}

		// Validate sub-fields if they exist
		$optionalKey = 'optional.' . $field;
		$validatorsKey = 'validators.' . $field;

		if ((isset($rules[$requiredKey]) || isset($rules[$optionalKey])) && isset($rules[$validatorsKey])) {
			$subFields = array_merge(
				$rules[$requiredKey] ?? [],
				$rules[$optionalKey] ?? []
			);

			foreach ($subFields as $subField) {
				if (!isset($group[$field][$subField])) {
					continue;
				}

				$validatedSubValue = match ($subField) {
					'value' => $rules[$validatorsKey][$subField]($group[$field][$subField], $group[$field]),
					default => $rules[$validatorsKey][$subField]($group[$field][$subField])
				};

				if ($validatedSubValue === false) {
					throw new ControllerException(
						'html\form\validateElement\parametr',
						['message' => $rules['messages'][$subField]($index)]
					);
				}
			}
		}
	}
}
