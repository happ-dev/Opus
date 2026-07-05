<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-04-01 17:38:26
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-05 17:25:41
 **/

namespace Opus\html\collapse;

use Opus\html\TraitHTML;
use Opus\controller\lang\Lang;
use Opus\html\form\Form;
use Opus\controller\exception\ControllerException;

class Collapse		// all CSS class are to be improved as soon as they are created!!!
{
	use TraitHTML;

	private array $collapses;

	/**
	 * Adds a new collapse component to the collection
	 *
	 * @param string $name Unique identifier for the collapse component
	 * @param object $options Configuration options:
	 *        - additionalClasses: (string|null) Additional CSS classes for the collapse container (default: null)
	 *        - ajax: (bool) Include alerts and loader for AJAX support (default: true)
	 *        - id: (string) HTML ID attribute (default: 'id__' . $name)
	 *        - header: (string|null) Additional HTML content for the header section (default: null)
	 *        - headerClasses: (string) CSS classes for header styling (default: 'collapse-header-opus collapse-header-opus-green')
	 *        - headerIcon: (string|null) Bootstrap icon class for header (default: null)
	 *        - headerText: (string|null) Text or Lang key for header title (default: null)
	 *        - additionalHeaderTextClasses: (string|null) Additional CSS classes for header text (default: null)
	 *        - body: (string|null) HTML content for the collapse body (default: null)
	 *        - footer: (string|null) HTML content for the collapse footer (default: null)
	 *        - footerClasses: (string) CSS classes for footer (default: 'justify-content-center p-3' when ajax, 'justify-content-center' otherwise)
	 *        - additionalFooterClasses: (string|null) Additional CSS classes for footer (default: null)
	 *        - buttonText: (string|null) Text or Lang key for the toggle button (default: null)
	 *        - buttonColor: (string) Bootstrap button color class (default: 'btn-primary')
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
		$options->headerClasses ??= 'collapse-header-opus collapse-header-opus-green';
		$options->headerIcon ??= null;
		$options->headerText ??= null;
		$options->additionalHeaderTextClasses ??= null;
		$options->body ??= null;
		$options->footer ??= null;
		$options->footerClasses ??= 'justify-content-center' . ($options->ajax === true ? ' p-3' : '');
		$options->additionalFooterClasses ??= null;
		$options->buttonText ??= null;
		$options->buttonColor ??= 'btn-primary';
		$options->buttonIcon ??= 'bi-arrows-expand';

		// Create base collapse structure
		$this->collapses[$name] = [
			'id' => $options->id,
			'class' => match (is_null($options->additionalClasses)) {
				true => 'collapse bs-opus-green-3d',
				false => 'collapse bs-opus-green-3d ' . $options->additionalClasses
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

		// Button text
		$options->buttonText = match (true) {
			is_null($options->buttonText) => null,
			filter_var($options->buttonText, FILTER_VALIDATE_REGEXP, self::VALID_LANG_KEY) !== false => Lang::getInstance()->get($options->buttonText),
			default => $options->buttonText
		};

		// Button
		$this->collapses[$name]['button'] = [
			'name' => 'collapse-btn-' . $name,
			'id' => 'id_collapse-btn-' . $name,
			'tag' => 'button',
			'text' => '<i class="me-1 bi ' . $options->buttonIcon . '"></i><em>' . $options->buttonText . '</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'btn btn-sm bs-opus-black-3d ' . $options->buttonColor,
				'data-bs-toggle' => 'collapse',
				'data-bs-target' => '#' . $options->id
			]
		];

		// Header Text
		$options->headerText = match (true) {
			is_null($options->headerText) => null,
			filter_var($options->headerText, FILTER_VALIDATE_REGEXP, self::VALID_LANG_KEY) !== false => Lang::getInstance()->get($options->headerText),
			default => $options->headerText
		};

		// Create collapse header
		$this->collapses[$name]['header'] = <<<HTML
		<div class="collapse-header {$name}-header {$options->headerClasses}">
			<div class="container">
				<div class="row">
					<div class="col">
						<span class="me-1 ms-2 badge bg-opus-black bs-opus-black fs-5">
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
			<div class="collapse-footer collapse-footer-opus {$name}-footer {$options->footerClasses}">{$options->footer}</div>
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
