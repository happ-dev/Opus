<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-04-01 17:38:26
 * @Last Modified by:   Tomasz Ulazowski
 * @Last Modified time: 2026-04-01 17:55:45
 **/

namespace Opus\html\collapse;

use Opus\html\form\Form;
use Opus\controller\exception\ControllerException;

class Collapse		// all CSS class are to be improved as soon as they are created!!!
{
	private array $collapses;

	/**
	 * Adds a new collapse component to the collection
	 *
	 * This method creates a new Bootstrap collapse component with customizable options including
	 * styling, AJAX functionality, header configuration, and button controls. Each collapse includes
	 * standard sections for header, body, and footer with built-in support for
	 * alerts and loading indicators when AJAX is enabled.
	 *
	 * @param string $name A unique identifier for the collapse component
	 * @param object $options Configuration options for the collapse:
	 *        - shadow: (string) CSS class for shadow effects (default: 'bs-happ-green-3d')
	 *        - additionalClasses: (string|null) Additional CSS classes for the collapse container
	 *        - ajax: (bool) Whether to include AJAX support with alerts and loader (default: true)
	 *        - id: (string) HTML ID attribute (default: 'id__' . $name)
	 *        - header: (string|null) Additional HTML content for the header section
	 *        - headerClasses: (string) CSS classes for header styling (default: 'collapse-header-happ-green')
	 *        - headerIcon: (string|null) Bootstrap icon class for header icon
	 *        - headerText: (string|null) Text content for header title
	 *        - additionalHeaderTextClasses: (string|null) Additional CSS classes for header text
	 *        - body: (string|null) HTML content for the collapse body
	 *        - footer: (string|null) HTML content for the collapse footer
	 *        - footerClasses: (string) CSS classes for footer styling (default: 'd-flex justify-content-center p-2')
	 *        - additionalFooterClasses: (string|null) Additional CSS classes for footer
	 *        - buttonText: (string|null) Text content for the toggle button
	 *        - buttonColor: (string) Bootstrap button color class (default: 'btn-success')
	 *        - buttonIcon: (string) Bootstrap icon class for toggle button (default: 'bi-arrows-expand')
	 *
	 * @return object Returns $this for method chaining
	 * @throws ControllerException If a collapse with the given name already exists
	 */
	public function addCollapse(string $name, object $options): object
	{
		// Check if the collapse name is unique
		if (isset($this->collapses[$name])) {
			throw new ControllerException(
				'html\collapses\duplicate',
				['message' => $name]
			);
		}

		// Set default values for options
		$options->additionalClasses ??= null;
		$options->ajax ??= true;
		$options->id ??= 'id__' . $name;
		$options->header ??= null;
		$options->headerClasses ??= 'collapse-header-happ-green';
		$options->headerIcon ??= null;
		$options->headerText ??= null;
		$options->additionalHeaderTextClasses ??= null;
		$options->body ??= null;
		$options->footer ??= null;
		$options->footerClasses ??= 'justify-content-center p-2';
		$options->additionalFooterClasses ??= null;
		$options->buttonText ??= null;
		$options->buttonColor ??= 'btn-primary';
		$options->buttonIcon ??= 'bi-arrows-expand';

		// Create base collapse structure
		$this->collapses[$name] = [
			'id' => $options->id,
			'class' => match (is_null($options->additionalClasses)) {
				true => 'collapse bg-happ-box-theme bs-happ-green-3d',
				false => 'collapse bg-happ-box-theme bs-happ-green-3d ' . $options->additionalClasses
			}
		];

		// Header classes
		$options->headerTextClasses = (is_null($options->additionalHeaderTextClasses))
			? 'me-2'
			: 'me-2 ' . $options->additionalHeaderTextClasses;

		// Footer additional classes
		if (!is_null($options->additionalFooterClasses)) {
			$options->footerClasses .= ' ' . $options->additionalFooterClasses;
		}

		// Button
		$this->collapses[$name]['button'] = [
			'name' => 'collapse-btn-' . $name,
			'id' => 'id_collapse-btn-' . $name,
			'tag' => 'button',
			'text' => '<i class="me-1 bi ' . $options->buttonIcon . '"></i><em>' . $options->buttonText . '</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'btn btn-sm ' . $options->buttonColor,
				'data-bs-toggle' => 'collapse',
				'data-bs-target' => '#' . $options->id
			]
		];

		// Create collapse header
		$this->collapses[$name]['header'] = <<<HTML
		<div class="collapse-header {$name}-header {$options->headerClasses}">
			<div class="container">
				<div class="row">
					<div class="col">
						<span class="me-1 ms-2 badge bg-happ-black bs-happ-black fs-5">
							<i class="bi {$options->headerIcon}"></i>
						</span>
						<span class="{$options->headerTextClasses}">{$options->headerText}</span>
					</div>
				</div>
			</div>
		</div>
		HTML;

		// Check if the collapse is ready for ajax
		$this->collapses[$name]['ajax'] = match ($options->ajax) {
			false => '',
			true => <<<HTML
				<!-- alerts -->
				<div class="row {$name}-alerts">
					<div class="col">
						<div class="alert alert-danger bs-happ-red-3d" style="word-break: normal"></div>
						<div class="alert alert-success bs-happ-lime-3d" style="word-break: normal"></div>
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

		// Create collapse body
		$this->collapses[$name]['body'] = <<<HTML
			<div class="collapse-body p-2">
				<div class="container">

					{$this->collapses[$name]['ajax']}

					<!-- collapse body row -->
					<div class="row {$name}-body-row">
						<div class="col">
							{$options->body}
						</div>
					</div>
				</div>
			</div>
		HTML;

		// Create collapse footer
		$this->collapses[$name]['footer'] = <<<HTML
			<div class="collapse-footer {$name}-footer {$options->footerClasses}">{$options->footer}</div>
		HTML;

		// Store options for later reference
		$this->collapses[$name]['options'] = $options;

		return $this;
	}

	/**
	 * Generates HTML for a collapse component
	 *
	 * This method builds the complete HTML structure for a Bootstrap collapse based on
	 * the configuration stored in the collapses collection. It includes the outer collapse
	 * container with all defined sections (header, body, footer) assembled into a
	 * complete collapsible component.
	 *
	 * @param string $name The identifier of the collapse to create
	 * @return string Complete HTML markup for the collapse component
	 * @throws ControllerException If the specified collapse name doesn't exist
	 */
	private function createCollapse(string $name): string
	{
		// Check if collapse name exists
		if (!isset($this->collapses[$name])) {
			throw new ControllerException(
				'html\collapses\lack',
				['message' => $name]
			);
		}

		$html = '<div ';

		foreach ($this->collapses[$name] as $key => $value) {
			// Skip nested elements that will be processed separately
			if (in_array($key, ['header', 'body', 'footer', 'options', 'ajax', 'button']) || !is_scalar($value)) {
				continue;
			}

			$html .= $key . '="' . $value . '" ';
		}

		$html .= '>';

		// Add header
		$html .= $this->collapses[$name]['header'];

		// Add body
		$html .= $this->collapses[$name]['body'];

		// Add footer
		$html .= $this->collapses[$name]['footer'];

		return $html . '</div>';
	}

	/**
	 * Retrieves the toggle button HTML for a collapse component
	 *
	 * This method generates the HTML markup for the toggle button that controls
	 * the specified collapse component. The button is created using the Form class
	 * and includes all Bootstrap attributes necessary for collapse functionality.
	 *
	 * @param string $name The identifier of the collapse component
	 * @return string HTML markup for the collapse toggle button
	 * @throws ControllerException If the specified collapse name doesn't exist
	 */
	public function getCollapseButton(string $name): string
	{
		// Check if collapse name exists
		if (!isset($this->collapses[$name])) {
			throw new ControllerException(
				'html\collapses\lack',
				['message' => $name]
			);
		}

		$form = new Form();
		$form->addElement($this->collapses[$name]['button']);
		return $form->getElement('collapse-btn-' . $name);
	}

	/**
	 * Retrieves the complete collapse component HTML
	 *
	 * This method returns the full HTML structure for the specified collapse component,
	 * including the container, header, body, and footer sections. This is the complete
	 * collapsible content that will be shown/hidden when the toggle button is clicked.
	 *
	 * @param string $name The identifier of the collapse component
	 * @return string Complete HTML markup for the entire collapse component
	 */
	public function getCollapse(string $name): string
	{
		// Check if collapse name exists
		if (!isset($this->collapses[$name])) {
			throw new ControllerException(
				'html\collapses\lack',
				['message' => $name]
			);
		}

		return $this->createCollapse($name);
	}
}
