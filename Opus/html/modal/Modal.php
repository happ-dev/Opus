<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-04-01 17:53:49
 * @Last Modified by:   Tomasz Ulazowski
 * @Last Modified time: 2026-04-27 10:43:52
 **/

namespace Opus\html\modal;

use Opus\controller\exception\ControllerException;
use Opus\html\form\Form;
use Opus\html\buttons\Buttons;

class Modal		// all CSS class are to be improved as soon as they are created!!!
{
	private array $modals;

	/**
	 * Adds a new modal dialog to the collection
	 *
	 * This method creates a new Bootstrap modal with customizable options including
	 * size, positioning, scrollability, styling, and form integration. Each modal includes
	 * standard sections for header, body, and footer with built-in support for
	 * alerts and loading indicators.
	 *
	 * @param string $name A unique identifier for the modal
	 * @param object $options Configuration options for the modal:
	 *        - size: (string|null) Modal size - 'sm', 'lg', 'xl', 'fullscreen', or null for default
	 *        - centered: (bool) Whether to vertically center the modal (default: false)
	 *        - scrollable: (bool) Whether the modal body should be scrollable (default: false)
	 *        - shadow: (string) CSS class for shadow effects (default: 'bs-opus-lime-3d')
	 *        - ajax: (bool) Whether to include AJAX support with alerts and loader (default: true)
	 *        - form: (bool) Whether to wrap modal content in a form element (default: false)
	 *        - method: (string) HTTP method for form submission (default: 'post')
	 *        - action: (string|null) Form action URL (default: null)
	 *        - static: (bool|array) Static backdrop configuration (default: ['data-bs-backdrop' => 'static'])
	 *        - header: (string|null) Additional HTML content for the header section
	 *        - headerClass: (string) CSS classes for header styling (default: 'modal-header-opus-lime bs-opus-lime')
	 *        - headerIcon: (string|null) Bootstrap icon class for header icon
	 *        - headerText: (string|null) Text content for header title
	 *        - body: (string|null) HTML content for the modal body
	 *        - footer: (string|null) HTML content for the modal footer
	 *
	 * @return object Returns $this for method chaining
	 * @throws ControllerException If a modal with the given name already exists or if an invalid size is provided
	 */
	public function addModal(string $name, object $options): object
	{
		// Check if the modal name is unique
		if (isset($this->modals[$name])) {
			throw new ControllerException(
				'html\modals\duplicate',
				['message' => $name]
			);
		}

		// Set default values for options
		$options->size ??= null;
		$options->centered ??= false;
		$options->scrollable ??= false;
		$options->shadow ??= 'bs-opus-green-3d';
		$options->ajax ??= true;
		$options->form ??= false;
		$options->formId ??= 'id__' . $name . '-form';
		$options->method ??= 'post';
		$options->action ??= null;
		$options->csrf ??= false;
		$options->id ??= 'id__' . $name;
		$options->static ??= ['data-bs-backdrop' => 'static'];
		$options->keyboard ??= ['data-bs-keyboard' => 'true'];
		$options->header ??= null;
		$options->headerClass ??= 'modal-header-opus-green bs-opus-green';
		$options->headerIcon ??= null;
		$options->headerText ??= null;
		$options->headerCloseX ??= (function () use ($name) {
			$form = new Form();
			$form->addElement(Buttons::closeButtonX(
				'modal-' . $name,
				['data-bs-dismiss' => 'modal']
			));
			return $form->getElement('close-btn-modal-' . $name);
		})();
		$options->body ??= null;
		$options->footer ??= null;

		// Create base modal structure
		$this->modals[$name] = [
			'id' => $options->id,
			'class' => 'modal fade',
			'tabindex' => '-1',
			'aria-labelledby' => $options->id . '-label',
			'aria-hidden' => 'true'
		];

		// Add static backdrop if enabled
		if ($options->static !== false) {
			$this->modals[$name] = array_merge_recursive($this->modals[$name], $options->static);
		}

		// Add keyboard if enabled
		if ($options->keyboard !== false) {
			$this->modals[$name] = array_merge_recursive($this->modals[$name], $options->keyboard);
		}

		// Configure modal dialog classes
		$this->modals[$name]['child-div'] = [
			'class' => 'modal-dialog' . match ($options->size) {
				null => null,
				'sm' => ' modal-sm',
				'lg' => ' modal-lg',
				'xl' => ' modal-xl',
				'fullscreen' => ' modal-fullscreen',
				default => throw new ControllerException(
					'html\modals\size',
					['message' => $options->size]
				)
			} . match ($options->centered) {
				false => '',
				true => ' modal-dialog-centered'
			} . match ($options->scrollable) {
				false => '',
				true => ' modal-dialog-scrollable'
			}
		];

		// Add form if enabled
		if ($options->form === true) {
			$this->modals[$name]['form'] = [
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

			$this->modals[$name]['csrf'] = [
				'value' => $_SESSION['csrf']
			];
		}

		// Create modal header
		$this->modals[$name]['header'] = <<<HTML
		<div class="modal-header {$name}-header {$options->headerClass}">
			<div class="position-absolute btn-close-x">{$options->headerCloseX}</div>
			<div class="container">
				<div class="row">
					<div class="col">
						<h5 id="{$options->id}-label">
							<span class="me-1 ms-0 badge bg-opus-black bs-opus-black fs-5">
								<i id="id_{$name}-icon-header" class="bi {$options->headerIcon}"></i>
							</span>
							<span id="id_{$name}-text-header" class="me-2">{$options->headerText}</span>
							<span id="id_{$name}-post-header"></span>
						</h5>
						{$options->header}
					</div>
				</div>
			</div>
		</div>
		HTML;

		// Check if the modal is ready for ajax
		$this->modals[$name]['ajax'] = match ($options->ajax) {
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

		// Create modal body
		$this->modals[$name]['body'] = <<<HTML
		<div class="modal-body modal-body-opus {$name}-body">
			<div class="container">

				{$this->modals[$name]['ajax']}

				<!-- modal body row -->
				<div class="row {$name}-body-row">
					<div class="col">
						{$options->body}
					</div>
				</div>
			</div>
		</div>
		HTML;

		// Create modal footer
		$this->modals[$name]['footer'] = <<<HTML
		<div class="modal-footer modal-footer-opus justify-content-center {$name}-footer">{$options->footer}</div>
		HTML;

		// Store options for later reference
		$this->modals[$name]['options'] = $options;

		return $this;
	}

	/**
	 * Generates HTML for a modal dialog
	 *
	 * This method builds the complete HTML structure for a Bootstrap modal based on
	 * the configuration stored in the modals collection. It includes the outer modal
	 * container, dialog wrapper, content container, and all defined sections (header,
	 * body, footer). If the modal is configured with a form, form tags are added
	 * around the body and footer sections.
	 *
	 * @param string $name The identifier of the modal to create
	 * @return string Complete HTML markup for the modal
	 * @throws ControllerException If the specified modal name doesn't exist
	 */
	private function createModal(string $name): string
	{
		// Check if the modal name exists
		if (!isset($this->modals[$name])) {
			throw new ControllerException(
				'html\modals\lack',
				['message' => $name]
			);
		}

		// Create outer modal container
		$html = '<div ';

		foreach ($this->modals[$name] as $key => $value) {
			// Skip nested elements that will be processed separately
			if (in_array($key, ['child-div', 'form', 'header', 'body', 'footer', 'options', 'ajax', 'csrf']) || !is_scalar($value)) {
				continue;
			}

			$html .= $key . '="' . $value . '" ';
		}

		$html .= '>';

		// Create dialog wrapper
		$html .= '<div ';

		foreach ($this->modals[$name]['child-div'] as $key => $value) {
			$html .= $key . '="' . $value . '" ';
		}

		$html .= '>';

		// Create content container
		$html .= '<div class="modal-content ' . $this->modals[$name]['options']->shadow . '">';

		// Add header
		$html .= $this->modals[$name]['header'];

		// Add form opening tag if needed
		if ($this->modals[$name]['options']->form !== false) {
			$form = '<form ';

			foreach ($this->modals[$name]['form'] as $key => $value) {
				$form .= $key . '="' . $value . '" ';
			}

			$form .= 'novalidate>';
			$html .= $form;
		}

		// Add input hidden csrf
		if ($this->modals[$name]['options']->csrf !== false) {
			$html .= '<input type="hidden" name="csrf" value="' . $this->modals[$name]['csrf']['value'] . '">';
		}

		// Add body
		$html .= $this->modals[$name]['body'];

		// Add footer
		$html .= $this->modals[$name]['footer'];

		// Add form closing tag if needed
		if ($this->modals[$name]['options']->form !== false) {
			$html .= '</form>';
		}

		// Close all containers
		return $html . '</div></div></div>';
	}

	/**
	 * Retrieves the HTML markup for a specific modal
	 *
	 * This method is a public wrapper around the private createModal method,
	 * allowing external access to generate HTML for a specific modal by name.
	 *
	 * @param string $name The identifier of the modal to retrieve
	 * @return string Complete HTML markup for the requested modal
	 * @throws ControllerException If the specified modal name doesn't exist
	 */
	public function getModalByName(string $name): string
	{
		return $this->createModal($name);
	}
}
