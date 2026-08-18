<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-07-20 16:00:43
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-08-18 19:55:14
 **/

namespace Opus\html\form;

use stdClass;
use Opus\html\form\Form;
use Opus\controller\lang\Lang;
use Opus\controller\exception\ControllerException;
use Opus\storage\db\Db;
use Opus\libs\Common;

/**
 * Generates standard HTML form elements with Bootstrap floating labels and input-group wrappers.
 *
 * Each public method accepts a $data array (passed by reference) and writes the generated HTML to $data['el'].
 *
 * $data array structure:
 *  - 'attname'    (string) Required. Column/field name used to generate input name and id attributes.
 *  - 'comment'    (string) Required. Lang key or 'English|lang.key' format, resolved via Lang::get().
 *  - 'value'      (mixed)  Optional. Current field value for edit operations.
 *  - 'attnotnull' (bool)   Optional. If true, adds HTML required attribute (used by numeric/date/timestamp/textarea).
 *  - 'template'   (array)  Required by selectValue/readonlyValue only.
 *                           For selectValue (3 scenarios):
 *                             Scenario 1 - OpusSelect static:
 *                               ['select-opus' => true, 'text' => [...], 'value' => [...]]
 *                               ['select-opus' => true, 'optgroups' => [['label' => '...', 'text' => [...], 'value' => [...]], ...]]
 *                             Scenario 2 - OpusSelect AJAX:
 *                               ['select-opus' => true, 'app' => 'appName', 'event' => 'eventName']
 *                               Optional: 'limit' (int, default 20)
 *                             Scenario 3 - Native select:
 *                               ['text' => [...], 'value' => [...]]
 *                               ['text' => 'SELECT value, text FROM ...']
 *                               ['optgroups' => [['label' => '...', 'text' => [...], 'value' => [...]], ...]]
 *                           Common optional key: 'multiple' (bool) — adds HTML multiple attribute
 *                           Common optional key: 'visible' (bool) — adds data-visible="true" for always-open panel (multiple only, default false)
 *                           Common optional key: 'required' (bool) — overrides options->required per element (not applied to readonly/serial)
 *                           For readonlyValue:
 *                             SQL mode:          ['value' => 'SELECT value, text FROM ...']
 *                             Static mode:       ['value' => 'static text']
 *
 * $options object properties (all optional, defaults applied via standardOptions):
 *  - width        (string)  CSS width, default '100%'
 *  - type         (string)  Input type attribute, default 'text'
 *  - shadow       (string)  Shadow CSS class, default 'bs-opus-black-3d'
 *  - margin       (string)  Margin CSS class, default 'mb-3'
 *  - required     (bool)    HTML required attribute, default true
 *  - readonly     (bool)    HTML readonly attribute, default false
 *  - floating     (bool)    Wrap in Bootstrap form-floating with icon, default false
 *  - placeholder  (bool)    Show label as placeholder (when floating is false), default false
 *  - standardName (bool)    Prefix name/id with 'input_'/'id_input_', default true
 *  - size         (string)  Bootstrap size class [default|lg|sm], default 'default'
 *  - style        (string)  Inline style for wrapper, default 'border-radius: var(--bs-border-radius)'
 *  - icon         (string)  Bootstrap icon class, default 'bi-input-cursor-text'
 *  - class        (string)  Additional CSS classes appended to the input/select element, default ''
 *  - rows         (string)  Textarea rows attribute, default '3' (textareaValue only)
 */
class StandardFormElements
{
	/**
	 * Validates required keys in $data array
	 *
	 * @param array $data Input data array
	 * @param array $requiredKeys Keys that must exist in $data
	 * @throws ControllerException When a required key is missing
	 */
	private static function validateData(array $data, array $requiredKeys): void
	{
		foreach ($requiredKeys as $key) {
			if (!array_key_exists($key, $data)) {
				throw new ControllerException(
					'html\form\standardFormElements\validateData',
					['message' => $key],
					ControllerException::TYPE_API_EXCEPTION
				);
			}
		}
	}

	/**
	 * Applies default values to options and overrides required from template.
	 *
	 * @param object &$options Display options to populate with defaults.
	 * @param array $data Element data array (optional). If template['required'] is set, overrides options->required.
	 */
	private static function standardOptions(object &$options, array $data = []): void
	{
		$options->width ??= '100%';
		$options->type ??= 'text';
		$options->shadow ??= 'bs-opus-black-3d';
		$options->margin ??= 'mb-3';
		$options->required ??= true;
		$options->readonly ??= false;
		$options->floating ??= false;
		$options->placeholder ??= false;
		$options->standardName ??= true;
		$options->size ??= 'default';				// [default|lg|sm]
		$options->style ??= 'border-radius: var(--bs-border-radius)';
		$options->icon ??= 'bi-input-cursor-text';
		$options->class ??= '';
		if (isset($data['template']['required'])) $options->required = $data['template']['required'];
	}

	private static function inputAttributes(object &$options, array $attr = []): array
	{
		$standard = array_filter(
			[
				'style' => "width: {$options->width}; box-sizing: border-box",
				'type' => $options->type,
				'value' => $options->value,
				'class' => ($options->readonly ? 'form-control-plaintext' : 'form-control')
					. ($options->size !== 'default' ? " form-control-{$options->size}" : '')
					. ($options->class ? " {$options->class}" : ''),
				$options->required === true ? 'required' : null,
				$options->readonly === true ? 'readonly' : null,
				'placeholder' => ($options->floating || $options->placeholder) ? $options->label : null
			],
			fn($v) => $v !== null
		);

		foreach ($attr ?? [] as $key => $value) {
			match (true) {
				is_int($key) => $standard[] = $value,
				array_key_exists($key, $standard) && is_string($standard[$key]) => $standard[$key] .= ' ' . $value,
				default => $standard[$key] = $value
			};
		}

		return $standard;
	}

	private static function selectAttributes(object &$options, array $attr = []): array
	{
		$standard = array_filter(
			[
				'style' => "width: {$options->width}; box-sizing: border-box",
				'class' => 'form-select' . ($options->size !== 'default' ? " form-select-{$options->size}" : '')
					. ($options->class ? " {$options->class}" : ''),
				'aria-label' => $options->label,
				$options->required === true ? 'required' : null,
			],
			fn($v) => $v !== null
		);

		foreach ($attr ?? [] as $key => $value) {
			match (true) {
				is_int($key) => $standard[] = $value,
				array_key_exists($key, $standard) && is_string($standard[$key]) => $standard[$key] .= ' ' . $value,
				default => $standard[$key] = $value
			};
		}

		return $standard;
	}

	private static function createElement(Form $form, array $element, object $options): string
	{
		return match ($options->floating) {
			true => <<<HTML
			<div class="input-group {$options->margin} {$options->shadow}" style="{$options->style}">
				<span class="input-group-text"><i class="is-opus-black bi {$options->icon}"></i></span>
				<div class="form-floating">
					{$form->getElement($element['name'])}
					<label for="{$element['id']}">{$options->label}</label>
				</div>
			</div>
			HTML,
			false => $form->getElement($element['name'])
		};
	}

	/**
	 * Generates a readonly input for serial/auto-increment primary key columns
	 *
	 * @param array &$data Required keys: attname, comment. Optional: value
	 * @param object $options Display options
	 */
	final public static function serialValue(array &$data, object $options = new stdClass()): void
	{
		self::validateData($data, ['attname', 'comment']);
		$options = clone $options;
		self::standardOptions($options);
		$options->readonly = true;
		$options->icon = 'bi-database-lock';
		$options->value = $data['value'] ?? '';
		$options->label = Lang::getInstance()->get($data['comment']);

		$form = new Form();
		$element = [
			'name' => $options->standardName ? 'input_' . $data['attname'] : $data['attname'],
			'id' => $options->standardName ? 'id_input_' . $data['attname'] : 'id_' . $data['attname'],
			'tag' => 'input',
			'attributes' => self::inputAttributes($options)
		];

		$form->addElement($element);
		$data['el'] = self::createElement($form, $element, $options);
		unset($form);
	}

	/**
	 * Generates a password input element (always empty, no value)
	 *
	 * @param array &$data Required keys: attname, comment
	 * @param object $options Display options
	 */
	final public static function passwordValue(array &$data, object $options = new stdClass()): void
	{
		self::validateData($data, ['attname', 'comment']);
		$options = clone $options;
		self::standardOptions($options, $data);
		$options->type = 'password';
		$options->value = '';
		$options->icon = 'bi-key';
		$options->label = Lang::getInstance()->get($data['comment']);

		$form = new Form();
		$element = [
			'name' => $options->standardName ? 'input_' . $data['attname'] : $data['attname'],
			'id' => $options->standardName ? 'id_input_' . $data['attname'] : 'id_' . $data['attname'],
			'tag' => 'input',
			'attributes' => self::inputAttributes($options)
		];
		$form->addElement($element);

		if (($data['template']['confirm'] ?? false) === true) {
			$confirmOptions = clone $options;
			$confirmOptions->label = Lang::getInstance()->get('controller.login.confirm.password');
			$confirmOptions->icon = 'bi-key-fill';

			$confirmElement = [
				'name' => $options->standardName ? 'input_confirm_' . $data['attname'] : 'confirm_' . $data['attname'],
				'id' => $options->standardName ? 'id_input_confirm_' . $data['attname'] : 'id_confirm_' . $data['attname'],
				'tag' => 'input',
				'attributes' => self::inputAttributes($confirmOptions)
			];
			$form->addElement($confirmElement);

			$data['el'] = self::createElement($form, $element, $options)
				. self::createElement($form, $confirmElement, $confirmOptions);
		} else {
			$data['el'] = self::createElement($form, $element, $options);
		}

		unset($form);
	}

	/**
	 * Generates a standard text input element
	 *
	 * @param array &$data Required keys: attname, comment. Optional: value
	 * @param object $options Display options (type, icon, etc. can be overridden)
	 */
	final public static function standardTypeValue(array &$data, object $options = new stdClass()): void
	{
		self::validateData($data, ['attname', 'comment']);
		$options = clone $options;
		self::standardOptions($options, $data);
		$options->value = $data['value'] ?? '';
		$options->label = Lang::getInstance()->get($data['comment']);

		$form = new Form();
		$element = [
			'name' => $options->standardName ? 'input_' . $data['attname'] : $data['attname'],
			'id' => $options->standardName ? 'id_input_' . $data['attname'] : 'id_' . $data['attname'],
			'tag' => 'input',
			'attributes' => self::inputAttributes($options)
		];

		$form->addElement($element);
		$data['el'] = self::createElement($form, $element, $options);
		unset($form);
	}

	/**
	 * Generates a readonly input with hidden value field (for FK display)
	 *
	 * Renders visible text (readonly) + hidden input with actual value.
	 * Template SQL must return: SELECT value_column, text_column FROM ...
	 *
	 * @param array &$data Required keys: attname, comment, template
	 * @param object $options Display options
	 */
	final public static function readonlyValue(array &$data, object $options = new stdClass()): void
	{
		self::validateData($data, ['attname', 'comment', 'template']);
		$options = clone $options;
		self::standardOptions($options);
		$options->readonly = true;
		$options->required = false;
		$options->icon = 'bi-database-lock';
		$options->label = Lang::getInstance()->get($data['comment']);

		$form = new Form();
		$value = null;

		$data['template']['value'] ??= $data['value'] ?? '';

		if (is_bool($data['template']['value'])) {
			$data['template']['value'] = $data['template']['value']
				? Lang::getInstance()->get('event.message.true')
				: Lang::getInstance()->get('event.message.false');
		}

		// Determine source of text and value
		match (true) {
			// Case: SQL query in config: SELECT value, text FROM ...
			Common::isQuery($data['template']['value']) !== false => (function () use (&$data, &$options, &$value) {
				$result = Db::dbArrayResult($data['template']['value']);
				$keys = array_keys($result[0]);
				$value = $result[0][$keys[0]];
				$options->value = $result[0][$keys[1]];
			})(),

			// Default case: value and text
			default => (function () use (&$data, &$options, &$value) {
				$options->value = $value = $data['template']['value'];
			})()
		};

		$elText = [
			'name' => $options->standardName ? 'input_' . $data['attname'] . '-text' : $data['attname'] . '-text',
			'id' => $options->standardName ? 'id_input_' . $data['attname'] . '-text' : 'id_' . $data['attname'] . '-text',
			'tag' => 'input',
			'attributes' => self::inputAttributes($options)
		];
		$elValue = [
			'name' => $options->standardName ? 'input_' . $data['attname'] : $data['attname'],
			'id' => $options->standardName ? 'id_input_' . $data['attname'] : 'id_' . $data['attname'],
			'tag' => 'input',
			'attributes' => [
				'type' => 'hidden',
				'class' => 'form-control',
				'value' => $value,
				'required'
			]
		];

		$form->addElement($elText);
		$form->addElement($elValue);

		$data['el'] = match ($options->floating) {
			true => <<<HTML
			<div class="input-group {$options->margin} {$options->shadow}" style="{$options->style}">
				<span class="input-group-text"><i class="is-opus-black bi {$options->icon}"></i></span>
				<div class="form-floating">
					{$form->getElement($elValue['name'])}
					{$form->getElement($elText['name'])}
					<label for="{$elText['id']}">{$options->label}</label>
				</div>
			</div>
			HTML,
			false => $form->getElement($elValue['name']) . $form->getElement($elText['name'])
		};

		unset($form);
	}

	/**
	 * Generates a select element with true/false options for boolean columns
	 *
	 * @param array &$data Required keys: attname, comment. Optional: value (bool)
	 * @param object $options Display options
	 */
	final public static function booleanValue(array &$data, object $options = new stdClass()): void
	{
		self::validateData($data, ['attname', 'comment']);
		$options = clone $options;
		self::standardOptions($options, $data);
		$options->icon = 'bi-menu-button-wide';
		$options->label = Lang::getInstance()->get($data['comment']);

		$form = new Form();
		$element = [
			'name' => $options->standardName ? 'input_' . $data['attname'] : $data['attname'],
			'id' => $options->standardName ? 'id_input_' . $data['attname'] : 'id_' . $data['attname'],
			'tag' => 'select',
			'attributes' => self::selectAttributes($options),
			'option' => [
				'all' => false,
				'value' => ['true', 'false'],
				'text' => [
					Lang::getInstance()->get('event.message.true'),
					Lang::getInstance()->get('event.message.false')
				]
			]
		];

		// Set selected value for edit operations
		if (isset($data['value']) && !is_null($data['value'])) {
			$element['option']['selected'] = ((bool) $data['value'] === true) ? 0 : 1;
		}

		if ($options->floating !== true) {
			$element['option']['ftext'] = $options->label;
		}

		$form->addElement($element);
		$data['el'] = self::createElement($form, $element, $options);
		unset($form);
	}

	/**
	 * Generates a select element populated from SQL query or static arrays,
	 * or an OpusSelect input when template['select-opus'] is true.
	 *
	 * Three scenarios:
	 *  1. select-opus + text/optgroups → static OpusSelect (form-select form-select-opus + <option> in DOM)
	 *  2. select-opus + app/event     → AJAX OpusSelect (form-select form-select-opus + data-* attributes, empty select)
	 *  3. no select-opus              → native select (form-select + <option> in DOM)
	 *
	 * @param array &$data Required keys: attname, comment, template. Optional: value
	 * @param object $options Display options
	 */
	final public static function selectValue(array &$data, object $options = new stdClass()): void
	{
		self::validateData($data, ['attname', 'comment', 'template']);
		$options = clone $options;
		self::standardOptions($options, $data);
		$options->icon = 'bi-menu-button';
		$options->value = $data['value'] ?? null;
		$options->label = Lang::getInstance()->get($data['comment']);

		$form = new Form();
		$isOpusSelect = ($data['template']['select-opus'] ?? false) === true;
		$hasEvent = isset($data['template']['event']);
		$hasText = isset($data['template']['text']);
		$hasOptgroups = isset($data['template']['optgroups']);
		$isMultiple = ($data['template']['multiple'] ?? false) === true;
		$isVisible = ($data['template']['visible'] ?? false) === true;

		// Build attributes based on scenario
		$extraAttr = match (true) {
			// Scenario 2: OpusSelect AJAX
			$isOpusSelect && $hasEvent => (function () use (&$data, $isMultiple, $isVisible) {
				self::validateData($data['template'], ['app', 'event']);
				$attr = [
					'class' => 'form-select-opus',
					'data-app' => $data['template']['app'],
					'data-event' => $data['template']['event'],
					'data-limit' => $data['template']['limit'] ?? 20,
				];
				if ($isMultiple) $attr[] = 'multiple';
				if ($isVisible) $attr['data-visible'] = 'true';
				return $attr;
			})(),
			// Scenario 1: OpusSelect static
			$isOpusSelect && ($hasText || $hasOptgroups) => (function () use ($isMultiple, $isVisible) {
				$attr = ['class' => 'form-select-opus'];
				if ($isMultiple) $attr[] = 'multiple';
				if ($isVisible) $attr['data-visible'] = 'true';
				return $attr;
			})(),
			// Scenario 1: OpusSelect without text/optgroups/event — error
			$isOpusSelect => throw new ControllerException(
				'html\form\standardFormElements\selectValue',
				['message' => 'select-opus requires text, optgroups or app+event'],
				ControllerException::TYPE_API_EXCEPTION
			),
			// Scenario 3: native select
			default => array_filter([
				$isMultiple ? 'multiple' : null,
			], fn($v) => $v !== null)
		};

		// Build option array
		$elementOption = match (true) {
			$isOpusSelect && $hasEvent => ['empty' => true],
			$hasOptgroups => ['all' => false],
			$hasText || !$isOpusSelect => array_merge_recursive(['all' => false], self::resolveSelectOption($data)),
			default => ['empty' => true]
		};

		$element = [
			'name' => $options->standardName ? 'input_' . $data['attname'] : $data['attname'],
			'id' => $options->standardName ? 'id_input_' . $data['attname'] : 'id_' . $data['attname'],
			'tag' => 'select',
			'attributes' => self::selectAttributes($options, $extraAttr),
			'option' => $elementOption
		];

		// Optgroups for static/SQL (not AJAX)
		if ($hasOptgroups && !($isOpusSelect && $hasEvent)) {
			$element['optgroups'] = self::resolveSelectOptgroups($data);
		}

		if ($options->floating !== true && isset($element['option'])) {
			$element['option']['ftext'] = $options->label;
		}

		$element['option']['selected'] = match (true) {
			$options->value === null => null,
			$hasOptgroups && is_array($options->value) => $options->value,
			$hasOptgroups && (is_int($options->value) || is_string($options->value)) => [$options->value],
			is_array($options->value) && isset($element['option']['value']) => array_keys(array_intersect($element['option']['value'], $options->value)),
			is_array($options->value) && isset($element['option']['text']) => array_keys(array_intersect($element['option']['text'], $options->value)),
			is_int($options->value) && isset($element['option']['value']) => array_search($options->value, $element['option']['value']),
			is_string($options->value) && isset($element['option']['text']) => array_search($options->value, $element['option']['text']),
			default => null
		};

		$form->addElement($element);
		$data['el'] = self::createElement($form, $element, $options);
		unset($form);
	}

	private static function resolveSelectOptgroups(array &$data): array
	{
		$lang = Lang::getInstance();

		return array_map(function (array $group) use ($lang) {
			$query = Common::isQuery($group['text'] ?? '');

			if ($query === false) {
				return [
					'label' => $lang->get($group['label']),
					'option' => [
						'text' => $group['text'],
						'value' => $group['value'] ?? $group['text']
					]
				];
			}

			$result = Db::dbArrayResult($group['text']);
			$keys = array_keys($result[0]);

			return [
				'label' => $lang->get($group['label']),
				'option' => match ($query) {
					'text' => [
						'text' => array_column($result, $keys[0]),
						'value' => array_column($result, $keys[0])
					],
					'value' => [
						'text' => array_column($result, $keys[1]),
						'value' => array_column($result, $keys[0])
					]
				}
			];
		}, $data['template']['optgroups']);
	}

	private static function resolveSelectOption(array &$data): array
	{
		self::validateData($data['template'], ['text']);
		$query = Common::isQuery($data['template']['text']);

		if ($query === false) {
			return [
				'text' => $data['template']['text'],
				'value' => $data['template']['value'] ?? null
			];
		}

		$result = Db::dbArrayResult($data['template']['text']);
		$keys = array_keys($result[0]);

		return match ($query) {
			'text' => ['text' => array_column($result, $keys[0])],
			'value' => [
				'text' => array_column($result, $keys[1]),
				'value' => array_column($result, $keys[0])
			]
		};
	}

	/**
	 * Generates a numeric input with fiat mask class and decimal inputmode
	 *
	 * @param array &$data Required keys: attname, comment. Optional: value, attnotnull
	 * @param object $options Display options
	 */
	final public static function numericValue(array &$data, object $options = new stdClass()): void
	{
		self::validateData($data, ['attname', 'comment']);
		$options = clone $options;
		self::standardOptions($options, $data);
		$options->required = false;
		$options->icon = 'bi-123';
		$options->value = $data['value'] ?? '';
		$options->label = Lang::getInstance()->get($data['comment']);

		// Add required attribute for NOT NULL columns
		if (($options->required ?? false) || (isset($data['attnotnull']) && $data['attnotnull'] === true)) {
			$options->required = true;
		}

		$form = new Form();

		$element = [
			'name' => $options->standardName ? 'input_' . $data['attname'] : $data['attname'],
			'id' => $options->standardName ? 'id_input_' . $data['attname'] : 'id_' . $data['attname'],
			'tag' => 'input',
			'attributes' => self::inputAttributes(
				$options,
				[
					'class' => 'form-control-opus-mask-fiat',
					'inputmode' => 'decimal'
				]
			),
		];

		$form->addElement($element);
		$data['el'] = self::createElement($form, $element, $options);
		unset($form);
	}

	/**
	 * Generates a date input with OpusDatePicker integration
	 *
	 * @param array &$data Required keys: attname, comment. Optional: value, attnotnull
	 * @param object $options Display options
	 */
	final public static function dateValue(array &$data, object $options = new stdClass()): void
	{
		self::validateData($data, ['attname', 'comment']);
		$options = clone $options;
		self::standardOptions($options, $data);
		$options->required = false;
		$options->icon = 'bi-calendar-date';
		$options->value = $data['value'] ?? '';
		$options->label = Lang::getInstance()->get($data['comment']);

		// Add required attribute for NOT NULL columns
		if (($options->required ?? false) || (isset($data['attnotnull']) && $data['attnotnull'] === true)) {
			$options->required = true;
		}

		$form = new Form();
		$element = [
			'name' => $options->standardName ? 'input_' . $data['attname'] : $data['attname'],
			'id' => $options->standardName ? 'id_input_' . $data['attname'] : 'id_' . $data['attname'],
			'tag' => 'input',
			'attributes' => self::inputAttributes($options, ['class' => 'date-opus-picker'])
		];

		$form->addElement($element);
		$data['el'] = self::createElement($form, $element, $options);
		unset($form);
	}

	/**
	 * Generates a timestamp input with OpusDatePicker integration (date + time)
	 *
	 * @param array &$data Required keys: attname, comment. Optional: value, attnotnull
	 * @param object $options Display options
	 */
	final public static function timestampValue(array &$data, object $options = new stdClass()): void
	{
		self::validateData($data, ['attname', 'comment']);
		$options = clone $options;
		self::standardOptions($options, $data);
		$options->required = false;
		$options->icon = 'bi-calendar-day';
		$options->value = $data['value'] ?? '';
		$options->label = Lang::getInstance()->get($data['comment']);

		// Add required attribute for NOT NULL columns
		if (($options->required ?? false) || (isset($data['attnotnull']) && $data['attnotnull'] === true)) {
			$options->required = true;
		}

		$form = new Form();
		$element = [
			'name' => $options->standardName ? 'input_' . $data['attname'] : $data['attname'],
			'id' => $options->standardName ? 'id_input_' . $data['attname'] : 'id_' . $data['attname'],
			'tag' => 'input',
			'attributes' => self::inputAttributes($options, ['class' => 'timestamp-opus-picker'])
		];

		$form->addElement($element);
		$data['el'] = self::createElement($form, $element, $options);
		unset($form);
	}

	/**
	 * Generates a textarea element for text/long string columns
	 *
	 * @param array &$data Required keys: attname, comment. Optional: value, attnotnull
	 * @param object $options Display options (rows defaults to '3')
	 */
	final public static function textareaValue(array &$data, object $options = new stdClass()): void
	{
		self::validateData($data, ['attname', 'comment']);
		$options = clone $options;
		self::standardOptions($options, $data);
		$options->icon = 'bi-textarea';
		$options->value = $data['value'] ?? '';
		$options->label = Lang::getInstance()->get($data['comment']);

		if (isset($data['attnotnull']) && $data['attnotnull'] === true) {
			$options->required = true;
		}

		$attr = array_filter(
			[
				'style' => "width: {$options->width}; box-sizing: border-box",
				'class' => 'form-control' . ($options->size !== 'default' ? " form-control-{$options->size}" : ''),
				'rows' => $options->rows ?? '3',
				$options->required === true ? 'required' : null,
				'placeholder' => ($options->floating || $options->placeholder) ? $options->label : null
			],
			fn($v) => $v !== null
		);

		$form = new Form();
		$element = [
			'name' => $options->standardName ? 'input_' . $data['attname'] : $data['attname'],
			'id' => $options->standardName ? 'id_input_' . $data['attname'] : 'id_' . $data['attname'],
			'tag' => 'textarea',
			'attributes' => $attr,
			'text' => $options->value
		];

		$form->addElement($element);
		$data['el'] = self::createElement($form, $element, $options);
		unset($form);
	}
}
