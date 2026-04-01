<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-04-01 17:14:44
 * @Last Modified by:   Tomasz Ulazowski
 * @Last Modified time: 2026-04-01 17:35:30
 **/

namespace Opus\html\form;

use Opus\html\form\Form;
use Opus\controller\lang\Lang;

class Buttons
{

	/**
	 * Creates a cancel button configuration
	 *
	 * This method generates an array configuration for a cancel button with:
	 * - Appropriate icon and translated text
	 * - Bootstrap styling
	 * - Optional modal dismiss functionality
	 *
	 * The returned array can be used with form rendering systems to generate
	 * HTML button elements with consistent styling and behavior.
	 *
	 * @param string $name Base name for the button (used in ID and name attributes)
	 * @param string|null $dataBsDismiss Bootstrap modal dismiss attribute value (e.g., 'modal')
	 * @return array Button configuration array
	 */
	public static function cancelButton(string $name, ?string $dataBsDismiss = null): array
	{
		// Create base button configuration
		$button = [
			'name' => 'cancel-btn-' . $name,
			'id' => 'id_cancel-btn-' . $name,
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-file-earmark-x"></i><em>' . Lang::getInstance()->get('html.buttons.cancel') . '</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'btn btn-primary btn-sm'
			]
		];

		// Add modal dismiss attribute if specified
		return match ($dataBsDismiss) {
			null => $button,
			default => array_merge_recursive($button, ['attributes' => ['data-bs-dismiss' => $dataBsDismiss]])
		};
	}

	/**
	 * Creates a close button configuration
	 *
	 * This method generates an array configuration for a close button with:
	 * - Appropriate icon and translated text
	 * - Bootstrap styling
	 * - Optional additional attributes
	 *
	 * The returned array can be used with form rendering systems to generate
	 * HTML button elements with consistent styling and behavior.
	 *
	 * @param string $name Base name for the button (used in ID and name attributes)
	 * @param array|null $options Optional array of additional HTML attributes to merge with button attributes
	 * @return array Button configuration array
	 */
	public static function closeButton(string $name, ?array $options = null): array
	{
		// Create base button configuration
		$button = [
			'name' => 'close-btn-' . $name,
			'id' => 'id_close-btn-' . $name,
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-file-earmark-check"></i><em>' . Lang::getInstance()->get('html.buttons.close') . '</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'btn btn-dark btn-sm'
			]
		];

		// Add modal dismiss attribute if specified
		return match ($options) {
			null => $button,
			default => array_merge_recursive($button, ['attributes' => $options])
		};
	}

	/**
	 * Creates a submit button configuration
	 *
	 * This method generates an array configuration for a submit button with:
	 * - Appropriate icon and translated text
	 * - Bootstrap styling
	 * - Form submission functionality
	 *
	 * The returned array can be used with form rendering systems to generate
	 * HTML submit buttons with consistent styling and behavior.
	 *
	 * @param string $name Base name for the button (used in ID and name attributes)
	 * @return array Button configuration array
	 */
	public static function submitButton(string $name): array
	{
		// Create base button configuration
		return [
			'name' => 'submit-btn-' . $name,
			'id' => 'id_submit-btn-' . $name,
			'tag' => 'button',
			'text' => '<span></span><i class="me-1 bi bi-file-earmark-arrow-up"></i><em>' . Lang::getInstance()->get('html.buttons.submit') . '</em>',
			'attributes' => [
				'type' => 'submit',
				'class' => 'btn btn-danger btn-sm'
			]
		];
	}

	/**
	 * Creates a save button configuration
	 *
	 * This method generates an array configuration for a save button with:
	 * - Appropriate icon and translated text
	 * - Bootstrap styling
	 * - Form submission functionality
	 *
	 * The returned array can be used with form rendering systems to generate
	 * HTML save buttons with consistent styling and behavior.
	 *
	 * @param string $name Base name for the button (used in ID and name attributes)
	 * @return array Button configuration array
	 */
	public static function saveButton(string $name): array
	{
		// Create base button configuration
		return [
			'name' => 'save-btn-' . $name,
			'id' => 'id_save-btn-' . $name,
			'tag' => 'button',
			'text' => '<span></span><i class="me-1 bi bi-file-earmark-arrow-down"></i><em>' . Lang::getInstance()->get('html.buttons.save') . '</em>',
			'attributes' => [
				'type' => 'submit',
				'class' => 'btn btn-danger btn-sm'
			]
		];
	}

	/**
	 * Creates a login button configuration
	 *
	 * This method generates an array configuration for a login button with:
	 * - User icon and translated text based on session language
	 * - Bootstrap styling with danger color scheme
	 * - Form submission functionality
	 *
	 * The returned array can be used with form rendering systems to generate
	 * HTML login buttons with consistent styling and behavior.
	 *
	 * @param string $name Base name for the button (used in ID and name attributes)
	 * @return array Button configuration array with name, id, tag, text, and attributes
	 */
	public static function loginButton(string $name): array
	{
		// Create base button configuration
		return [
			'name' => 'login-btn-' . $name,
			'id' => 'id_login-btn-' . $name,
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-person-up"></i><em>' . Lang::getInstance()->get('html.buttons.login') . '</em>',
			'attributes' => [
				'type' => 'submit',
				'class' => 'btn btn-success btn-sm'
			]
		];
	}

	/**
	 * Creates a form with a standard set of modal dialog buttons
	 *
	 * This method generates a Form object containing three standard buttons:
	 * - Cancel button (with modal dismiss attribute)
	 * - Submit button
	 * - Close button (with modal dismiss attribute)
	 *
	 * @param string $name Base name for all buttons (used in ID and name attributes)
	 * @param array|null $options Optional array of additional configuration options
	 * @return Form Form object containing the three standard modal buttons
	 * 	- id_cancel-btn-{$name}
	 *  - id_submit-btn-{$name}
	 *  - id_close-btn-{$name}
	 */
	public static function modalButtons(string $name, ?array $options = null): Form
	{
		$form = new Form();

		// id_cancel-btn-{$name}
		$form->addElement(self::cancelButton($name, 'modal'));

		// id_submit-btn-{$name}
		$form->addElement(self::submitButton($name));

		// id_close-btn-{$name}
		$form->addElement(self::closeButton(
			$name,
			match ($options) {
				null => ['data-bs-dismiss' => 'modal'],
				default => array_merge_recursive(['data-bs-dismiss' => 'modal'], $options)
			}
		));

		return $form;
	}

	/**
	 * Creates a form with a standard set of offcanvas dialog buttons
	 *
	 * This method generates a Form object containing three standard buttons for offcanvas components:
	 * - Cancel button (with offcanvas dismiss attribute)
	 * - Submit button
	 * - Close button (with offcanvas dismiss attribute and optional additional attributes)
	 *
	 * @param string $name Base name for all buttons (used in ID and name attributes)
	 * @param array|null $options Optional array of additional HTML attributes to merge with close button
	 * @return Form Form object containing the three standard offcanvas buttons
	 * 	- id_cancel-btn-{$name}
	 *  - id_submit-btn-{$name}
	 *  - id_close-btn-{$name}
	 */
	public static function offcanvasButtons(string $name, ?array $options = null): Form
	{
		$form = new Form();

		// id_cancel-btn-{$name}
		$form->addElement(self::cancelButton($name, 'offcanvas'));

		// id_submit-btn-{$name}
		$form->addElement(self::submitButton($name));

		// id_close-btn-{$name}
		$form->addElement(self::closeButton(
			$name,
			match ($options) {
				null => ['data-bs-dismiss' => 'offcanvas'],
				default => array_merge_recursive(['data-bs-dismiss' => 'offcanvas'], $options)
			}
		));

		return $form;
	}
}
