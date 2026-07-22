<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-07-20 16:00:43
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-22 12:44:14
 **/

namespace Opus\html\form;

use stdClass;
use Opus\html\form\Form;
use Opus\controller\lang\Lang;
use Opus\storage\db\Db;

class StandardFormElements
{
	final public static function serialValue(array &$data, object $options = new stdClass()): void
	{
		$options->width ??= '100%';
		$options->shadow ??= 'bs-opus-black-3d';
		$options->margin ??= 'mb-3';

		$form = new Form();
		$element = [
			'name' => 'input_' . $data['attname'],
			'id' => 'id_input_' . $data['attname'],
			'tag' => 'input',
			'attributes' => [
				'style' => 'width: ' . $options->width . '; box-sizing: border-box',
				'class' => 'form-control-plaintext',
				'type' => 'text',
				'readonly',
				'value' => $data['value'] ?? ''
			]
		];
		$form->addElement($element);

		$label = Lang::getInstance()->get($data['comment']);

		$data['el'] = <<<HTML
		<div class="form-floating {$options->margin} {$options->shadow}" style="border-radius: var(--bs-border-radius)">
			{$form->getElement($element['name'])}
			<label for="{$element['id']}">{$label}</label>
		</div>
		HTML;

		unset($form);
	}

	final public static function standardTypeValue(array &$data, object $options = new stdClass()): void
	{
		$options->width ??= '100%';
		$options->shadow ??= 'bs-opus-black-3d';
		$options->margin ??= 'mb-3';
		$options->required ??= true;
		$label = Lang::getInstance()->get($data['comment']);

		$form = new Form();
		$element = [
			'name' => 'input_' . $data['attname'],
			'id' => 'id_input_' . $data['attname'],
			'tag' => 'input',
			'attributes' => [
				'style' => 'width: ' . $options->width . '; box-sizing: border-box',
				'class' => 'form-control',
				'type' => 'text',
				'value' => $data['value'] ?? '',
				'placeholder' => $label
			]
		];

		// Add required attribute for NOT NULL columns
		if (($options->required ?? false) || (isset($data['attnotnull']) && $data['attnotnull'] === true)) {
			array_push($element['attributes'], 'required');
		}

		$form->addElement($element);

		$data['el'] = <<<HTML
		<div class="form-floating {$options->margin} {$options->shadow}" style="border-radius: var(--bs-border-radius)">
			{$form->getElement($element['name'])}
			<label for="{$element['id']}">{$label}</label>
		</div>
		HTML;

		unset($form);
	}

	final public static function readonlyValue(array &$data, object $options = new stdClass()): void
	{
		$options->width ??= '100%';
		$options->shadow ??= 'bs-opus-black-3d';
		$options->margin ??= 'mb-3';
		$label = Lang::getInstance()->get($data['comment']);

		$form = new Form();
		$text = null;
		$value = null;

		// Determine source of text and value
		match (true) {
			// Case: SQL query in config
			self::isQuery($data['template']['value']) === true => (function () use (&$data, &$text, &$value) {
				$result = Db::dbArrayResult($data['template']['value']);
				$keys = array_keys($result[0]);
				$text = $result[0][$keys[1]];
				$value = $result[0][$keys[0]];
			})(),

			// Default case: value and text
			default => (function () use (&$data, &$text, &$value) {
				$text = $value = $data['template']['value'];
			})()
		};


		$elText = [
			'name' => 'input_' . $data['attname'] . '-text',
			'id' => 'id_input_' . $data['attname'] . '-text',
			'tag' => 'input',
			'attributes' => [
				'style' => 'width: ' . $options->width . '; box-sizing: border-box',
				'class' => 'form-control-plaintext',
				'type' => 'text',
				'readonly',
				'value' => $text,
				'placeholder' => $label
			]
		];
		$elValue = [
			'name' => 'input_' . $data['attname'],
			'id' => 'id_input_' . $data['attname'],
			'tag' => 'input',
			'attributes' => [
				'type' => 'hidden',
				'class' => 'form-control',
				'value' => $value
			]
		];

		$form->addElement($elText);
		$form->addElement($elValue);

		$data['el'] = <<<HTML
		<div class="form-floating {$options->margin} {$options->shadow}" style="border-radius: var(--bs-border-radius)">
			{$form->getElement($elValue['name'])}
			{$form->getElement($elText['name'])}
			<label for="{$elText['id']}">{$label}</label>
		</div>
		HTML;

		unset($form);
	}

	final public static function booleanValue(array &$data, object $options = new stdClass()): void
	{
		$options->width ??= '100%';
		$options->shadow ??= 'bs-opus-black-3d';
		$options->margin ??= 'mb-3';
		$options->required ??= true;
		$label = Lang::getInstance()->get($data['comment']);

		$form = new Form();
		$element = [
			'name' => 'input_' . $data['attname'],
			'id' => 'id_input_' . $data['attname'],
			'tag' => 'select',
			'attributes' => [
				'style' => 'width: ' . $options->width . '; box-sizing: border-box',
				'class' => 'form-select',
				'aria-label' => $label
			],
			'option' => [
				'all' => false,
				'value' => [null, 'true', 'false'],
				'text' => [
					null,
					Lang::getInstance()->get('event.message.true'),
					Lang::getInstance()->get('event.message.false')
				]
			]
		];

		// Set selected value for edit operations
		if (isset($data['value']) && !is_null($data['value'])) {
			$element['option']['selected'] = ((bool) $data['value'] === true) ? 1 : 2;
		}

		// Add required attribute for NOT NULL columns
		if (($options->required ?? false) || (isset($data['attnotnull']) && $data['attnotnull'] === true)) {
			array_push($element['attributes'], 'required');
		}

		$form->addElement($element);

		$data['el'] = <<<HTML
		<div class="form-floating {$options->margin} {$options->shadow}" style="border-radius: var(--bs-border-radius)">
			{$form->getElement($element['name'])}
			<label for="{$element['id']}">{$label}</label>
		</div>
		HTML;

		unset($form);
	}

	final public static function numericValue(array &$data, object $options = new stdClass()): void
	{
		$options->width ??= '100%';
		$options->shadow ??= 'bs-opus-black-3d';
		$options->margin ??= 'mb-3';
		$options->required ??= true;
		$label = Lang::getInstance()->get($data['comment']);

		$form = new Form();

		$element = [
			'name' => 'input_' . $data['attname'],
			'id' => 'id_input_' . $data['attname'],
			'tag' => 'input',
			'attributes' => [
				'style' => 'width: ' . $options->width . '; box-sizing: border-box',
				'class' => 'form-control form-control-opus-mask-fiat',
				'type' => 'text',
				'inputmode' => 'decimal',
				'value' => $data['value'] ?? '',
				'placeholder' => $label
			]
		];

		// Add required attribute for NOT NULL columns
		if (($options->required ?? false) || (isset($data['attnotnull']) && $data['attnotnull'] === true)) {
			array_push($element['attributes'], 'required');
		}

		$form->addElement($element);

		$data['el'] = <<<HTML
		<div class="form-floating {$options->margin} {$options->shadow}" style="border-radius: var(--bs-border-radius)">
			{$form->getElement($element['name'])}
			<label for="{$element['id']}">{$label}</label>
		</div>
		HTML;

		unset($form);
	}

	final public static function dateValue(array &$data, object $options = new stdClass()): void
	{
		$options->width ??= '100%';
		$options->shadow ??= 'bs-opus-black-3d';
		$options->margin ??= 'mb-3';
		$options->required ??= true;
		$label = Lang::getInstance()->get($data['comment']);

		$form = new Form();
		$element = [
			'name' => 'input_' . $data['attname'],
			'id' => 'id_input_' . $data['attname'],
			'tag' => 'input',
			'attributes' => [
				'style' => 'width: ' . $options->width . '; box-sizing: border-box',
				'class' => 'form-control date-opus-picker',
				'type' => 'text',
				'value' => $data['value'] ?? '',
				'placeholder' => $label
			]
		];

		// Add required attribute for NOT NULL columns
		if (($options->required ?? false) || (isset($data['attnotnull']) && $data['attnotnull'] === true)) {
			array_push($element['attributes'], 'required');
		}

		$form->addElement($element);

		$data['el'] = <<<HTML
		<div class="form-floating {$options->margin} {$options->shadow}" style="border-radius: var(--bs-border-radius)">
			{$form->getElement($element['name'])}
			<label for="{$element['id']}">{$label}</label>
		</div>
		HTML;

		unset($form);
	}

	final public static function timestampValue(array &$data, object $options = new stdClass()): void
	{
		$options->width ??= '100%';
		$options->shadow ??= 'bs-opus-black-3d';
		$options->margin ??= 'mb-3';
		$options->required ??= true;
		$label = Lang::getInstance()->get($data['comment']);

		$form = new Form();
		$element = [
			'name' => 'input_' . $data['attname'],
			'id' => 'id_input_' . $data['attname'],
			'tag' => 'input',
			'attributes' => [
				'style' => 'width: ' . $options->width . '; box-sizing: border-box',
				'class' => 'form-control timestamp-opus-picker',
				'type' => 'text',
				'value' => $data['value'] ?? '',
				'placeholder' => $label
			]
		];

		// Add required attribute for NOT NULL columns
		if (($options->required ?? false) || (isset($data['attnotnull']) && $data['attnotnull'] === true)) {
			array_push($element['attributes'], 'required');
		}

		$form->addElement($element);

		$data['el'] = <<<HTML
		<div class="form-floating {$options->margin} {$options->shadow}" style="border-radius: var(--bs-border-radius)">
			{$form->getElement($element['name'])}
			<label for="{$element['id']}">{$label}</label>
		</div>
		HTML;

		unset($form);
	}

	private static function isQuery(string $sql): bool
	{
		return preg_match(
			'/^\s*(WITH\b[\s\S]+?\bSELECT\b|SELECT\b)/i',
			trim($sql)
		) === 1;
	}
}
