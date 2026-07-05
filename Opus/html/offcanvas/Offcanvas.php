<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-04-01 18:04:49
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-05 16:51:08
 **/

namespace Opus\html\offcanvas;

use stdClass;
use Opus\html\TraitHTML;
use Opus\controller\lang\Lang;
use Opus\controller\exception\ControllerException;
use Opus\html\form\Form;
use Opus\html\buttons\Buttons;

class Offcanvas
{
	use TraitHTML;

	private array $offcanvas;

	/**
	 * Adds an offcanvas component with specified configuration options
	 *
	 * @param string $name Unique identifier for the offcanvas
	 * @param object $options Configuration object with properties:
	 *                        - size: Offcanvas size ('sm', 'md', 'lg', 'xl', 'xxl', null)
	 *                        - scrollable: Enable body scrolling (bool|array)
	 *                        - static: Static backdrop configuration (bool|array)
	 *                        - placement: Position ('start', 'end', 'top', 'bottom')
	 *                        - shadow: CSS shadow class ('bs-opus-green-3d', string|bool)
	 *                        - keyboard: Keyboard interaction (['data-bs-keyboard' => 'true'], bool|array)
	 *                        - ajax: Enable AJAX loader/alerts (bool)
	 *                        - form: Enable form wrapper (bool)
	 *                        - formId: Form element ID (string)
	 *                        - method: Form method (string)
	 *                        - action: Form action URL (string)
	 *                        - csrf: Enable CSRF token (bool)
	 *                        - id: Offcanvas element ID (string)
	 *                        - header: Additional header HTML (string)
	 *                        - headerClass: Header CSS classes ('offcanvas-header-opus-green bs-opus-green', string)
	 *                        - headerIcon: Bootstrap icon class (string)
	 *                        - headerText: Header title text (string)
	 *                        - body: Body content HTML (string)
	 *                        - footer: Footer content HTML (string)
	 * @return object Returns $this for method chaining
	 * @throws ControllerException If offcanvas name already exists or invalid size/placement
	 */
	public function addOffcanvas(
		string $name,
		object $options = new stdClass()
	): object {
		// Check if the offcanvas name is unique
		if (isset($this->offcanvas[$name])) {
			throw new ControllerException(
				'html\offcanvas\duplicate',
				['message' => $name]
			);
		}

		// Set default values for options
		$options->size ??= null;
		$options->scrollable ??= false;
		$options->static ??= ['data-bs-backdrop' => 'static'];
		$options->placement ??= 'end';
		$options->shadow ??= 'bs-opus-green-3d';
		$options->keyboard ??= ['data-bs-keyboard' => 'true'];
		$options->ajax ??= true;
		$options->form ??= false;
		$options->formId ??= 'id__' . $name . '-form';
		$options->method ??= 'post';
		$options->action ??= null;
		$options->csrf ??= false;
		$options->id ??= 'id__' . $name;
		$options->header ??= null;
		$options->headerClass ??= 'offcanvas-header-opus-green bs-opus-green';
		$options->headerIcon ??= null;
		$options->headerText ??= null;
		$options->headerCloseX ??= (function () use ($name, $options) {
			$form = new Form();
			$form->addElement(Buttons::closeButtonX(
				'offcanvas-' . $name,
				[
					'data-bs-dismiss' => 'offcanvas',
					'aria-label' => 'Close',
					'data-bs-target' => '#' . $options->id
				]
			));
			return $form->getElement('close-btn-offcanvas-' . $name);
		})();
		$options->body ??= null;
		$options->footer ??= null;
		$options->footerClasses ??= ($options->ajax === true) ? 'p-3' : '';

		// Create base modal structure
		$this->offcanvas[$name] = [
			'id' => $options->id,
			'class' => 'offcanvas' . match ($options->size) {
				null => null,
				'sm' => '-sm',
				'md' => '-md',
				'lg' => '-lg',
				'xl' => '-xl',
				'xxl' => '-xxl',
				default => throw new ControllerException(
					'html\offcanvas\size',
					['message' => $options->size]
				)
			} . match ($options->placement) {
				'start' => ' offcanvas-start',
				'end' => ' offcanvas-end',
				'top' => ' offcanvas-top',
				'bottom' => ' offcanvas-bottom',
				default => throw new ControllerException(
					'html\offcanvas\placement',
					['message' => $options->placement]
				)
			} . match (true) {
				is_string($options->shadow) => ' ' . $options->shadow,
				default => ''
			},
			'tabindex' => '-1',
			'aria-labelledby' => $options->id . '-label'
		];

		// Add body scrolling
		if ($options->scrollable !== false) {
			$this->offcanvas[$name] = array_merge_recursive(
				$this->offcanvas[$name],
				[
					'data-bs-scroll' => 'true',
					'data-bs-backdrop' => 'false'
				]
			);
		}

		// Add static backdrop if enabled
		// remove body scrolling if enabled
		if ($options->static !== false) {
			$this->offcanvas[$name] = array_merge_recursive(
				$this->offcanvas[$name],
				match (true) {
					$options->scrollable !== false => (function () use ($name, $options) {
						unset(
							$this->offcanvas[$name]['data-bs-scroll'],
							$this->offcanvas[$name]['data-bs-backdrop']
						);
						return $options->static;
					})(),
					default => $options->static
				}
			);
		}

		// Add keyboard if enabled
		if ($options->keyboard !== false) {
			$this->offcanvas[$name] = array_merge_recursive(
				$this->offcanvas[$name],
				$options->keyboard
			);
		}

		// Add form if enabled
		if ($options->form === true) {
			$this->offcanvas[$name]['form'] = [
				'class' => 'needs-validation',
				'id' => $options->formId,
				'method' => $options->method,
				'action' => $options->action
			];
		}

		// Create csrf token
		if ($options->csrf === true) {

			// Generate a unique token for this specific form
			$_SESSION['csrf'] = bin2hex(random_bytes(32));

			$this->offcanvas[$name]['csrf'] = [
				'value' => $_SESSION['csrf']
			];
		}

		// Header Text
		$options->headerText = match (true) {
			is_null($options->headerText) => null,
			filter_var($options->headerText, FILTER_VALIDATE_REGEXP, self::VALID_LANG_KEY) !== false => Lang::getInstance()->get($options->headerText),
			default => $options->headerText
		};

		// Create offcanvas header
		$this->offcanvas[$name]['header'] = <<<HTML
		<div class="offcanvas-header justify-content-between {$name}-header {$options->headerClass}">
			<h5 class="offcanvas-title" id="{$options->id}-label">
				<span class="me-1 ms-2 badge bg-opus-black bs-opus-black fs-5">
					<i id="id_{$name}-icon-header" class="bi {$options->headerIcon}"></i>
				</span>
				<span id="id_{$name}-text-header" class="me-2">{$options->headerText}</span>
				<span id="id_{$name}-post-header"></span>
				{$options->header}
			</h5>
			{$options->headerCloseX}
		</div>
		HTML;

		// Check if the offcanvas is ready for ajax
		$this->offcanvas[$name]['ajax'] = match ($options->ajax) {
			false => '',
			true => <<<HTML
			<!-- alerts -->
			<div class="row {$name}-alerts">
				<div class="col">
					<div class="alert alert-danger bs-opus-red-3d" style="word-break: normal"></div>
					<div class="alert alert-success bs-opus-lime-3d" style="word-break: normal"></div>
				</div>
			</div>

			<!-- loader animation -->
			<div class="row {$name}-loader">
				<div class="col d-flex justify-content-center">
					<div class="spinner-border" role="status"></div>
				</div>
			</div>
			HTML
		};

		// Create offcanvas body
		$this->offcanvas[$name]['body'] = <<<HTML
		<div class="offcanvas-body offcanvas-body-opus {$name}-body d-flex flex-align-start flex-column h-100">
			<div class="container">

				{$this->offcanvas[$name]['ajax']}

				<!-- offcanvas body row -->
				<div class="row {$name}-body-row">
					<div class="col">
						{$options->body}
					</div>
				</div>

			</div>
		</div>
		HTML;

		// Create offcanvas footer
		$this->offcanvas[$name]['footer'] = <<<HTML
		<div class="offcanvas-footer offcanvas-footer-opus text-center gap-2 {$options->footerClasses} {$name}-footer">{$options->footer}</div>
		HTML;

		// Store options for later reference
		$this->offcanvas[$name]['options'] = $options;

		return $this;
	}

	/**
	 * Generates HTML markup for the offcanvas component
	 *
	 * @param string $name Offcanvas identifier
	 * @return string Complete HTML markup for the offcanvas
	 * @throws ControllerException If offcanvas name doesn't exist
	 */
	private function createOffcanvas(string $name): string
	{
		// Check if the offcanvas name exists
		if (!isset($this->offcanvas[$name])) {
			throw new ControllerException(
				'html\offcanvas\lack',
				['message' => $name]
			);
		}

		// Create outer offcanvas container
		$html = '<div ';

		foreach ($this->offcanvas[$name] as $key => $value) {
			// Skip nested elements that will be processed separately
			if (in_array($key, ['form', 'header', 'body', 'footer', 'options', 'ajax', 'csrf']) || !is_scalar($value)) {
				continue;
			}

			$html .= $key . '="' . $value . '" ';
		}

		$html .= '>';

		// Add header
		$html .= $this->offcanvas[$name]['header'];

		// Add form opening tag if needed
		if ($this->offcanvas[$name]['options']->form !== false) {
			$form = '<form ';

			foreach ($this->offcanvas[$name]['form'] as $key => $value) {
				$form .= $key . '="' . $value . '" ';
			}

			$form .= 'novalidate>';
			$html .= $form;
		}

		// Add input hidden csrf
		if ($this->offcanvas[$name]['options']->csrf !== false) {
			$html .= '<input type="hidden" name="csrf" value="' . $this->offcanvas[$name]['csrf']['value'] . '">';
		}

		// Add body
		$html .= $this->offcanvas[$name]['body'];

		// Add footer
		$html .= $this->offcanvas[$name]['footer'];

		// Add form closing tag if needed
		if ($this->offcanvas[$name]['options']->form !== false) {
			$html .= '</form>';
		}

		// Close all containers
		return $html . '</div>';
	}

	/**
	 * Retrieves generated HTML for a specific offcanvas by name
	 *
	 * @param string $name Offcanvas identifier
	 * @return string Complete HTML markup for the offcanvas
	 * @throws ControllerException If offcanvas name doesn't exist
	 */
	public function getOffcanvasByName(string $name): string
	{
		return $this->createOffcanvas($name);
	}
}
