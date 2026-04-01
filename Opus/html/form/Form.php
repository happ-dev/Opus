<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-04-01 12:27:48
 * @Last Modified by:   Tomasz Ulazowski
 * @Last Modified time: 2026-04-01 12:37:39
 **/

namespace Opus\html\form;

use Opus\libs\Common;
use Opus\controller\request\Request;
use Opus\controller\exception\ControllerException;

class Form extends AbstractForm
{
	final public function addElement(array $element): object
	{
		// Validate the element configuration
		FormElementValidate::validateElement($element);

		// Generate a unique key for this element
		$uniqueKey = Common::windowsUniqId();

		// Store element metadata in keys array
		$key = [
			'id_key' => $uniqueKey,
			'id' => $element['id'],
			'name' => $element['name']
		];

		// Initialize or append to keys array
		array_push($this->keys, $key);

		// Store the element in elements array
		$this->elements[$uniqueKey] = $element;

		return $this;
	}

	final public function setAttribute(string $key, ?string $value = null): object
	{
		// Check if attribute is valid or starts with 'data-'
		if (!in_array($key, self::HTML_VALID_FORM_ATTRIBUTES) && strpos($key, 'data-') !== 0) {
			throw new ControllerException(
				'html\form\setAttribute',
				['message' => $key]
			);
		}

		$this->attributes[$key] = $value;
		return $this;
	}

	final public function beginForm(): string
	{
		$html = '<form ';

		foreach ($this->attributes as $key => $value) {
			$html .= match (is_null($value)) {
				true => $key . ' ',
				false => $key . '="' . $value . '" ',
			};
		}

		$html .= '>';
		return preg_replace('/\s+>/', '>', $html);
	}

	final public function endForm(): string
	{
		return '</form>';
	}

	public function getElement(string $value): string
	{
		// Find the element by ID or name
		$idKey = $this->findIdKey($value);

		// Generate HTML based on element type
		return match ($this->elements[$idKey]['tag']) {
			'select' => $this->selectType($this->elements[$idKey]),
			default => $this->defaultType($this->elements[$idKey])
		};
	}

	public function getListElements(): array
	{
		$list = [];

		foreach ($this->elements as $element) {
			$list[] = $element['name'];
		}

		return $list;
	}

	/**
	 * Validate csrf token
	 *
	 * @throws ControllerException When validation fails
	 */
	final public static function csrfToken(): void
	{
		$csrf = Request::validateCsrfToken();

		if ($csrf !== true) {
			throw new ControllerException(
				'html\form\csrf',
				['message' => $csrf],
				ControllerException::TYPE_API_EXCEPTION
			);
		};
	}
}
