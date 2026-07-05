<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-06-28 01:21:14
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-05 15:23:54
 **/

namespace Opus\html\asyncpage;

use Opus\html\TraitHTML;
use Opus\controller\lang\Lang;
use Opus\html\form\Form;
use Opus\html\buttons\Buttons;
use Opus\controller\exception\ControllerException;

/**
 * AsyncPage class for generating asynchronously loaded page components
 *
 * Creates HTML structures for pages that are loaded dynamically via AJAX.
 * Each async page contains a header with close button and a scrollable body.
 * Pages are rendered server-side and returned as raw HTML to the OpusAsyncPage JS class.
 *
 * @class AsyncPage
 * @package Opus\html\asyncpage
 *
 * @template AsyncPage Structure
 * Each async page generates the following HTML hierarchy:
 *
 * - `.async-page-opus` (id="id__{name}")         - Root flex container (100% height)
 *   - `.async-page-header-opus`                   - Fixed header (flex-shrink: 0)
 *     - `.btn-close-x`                            - Close button container
 *       - `[data-close="#id__{name}"]`            - Close button trigger
 *     - `.container > .row > .col > h5`           - Header title
 *       - `i.bi.{headerIcon}`                     - Header icon
 *       - `span.{headerText}`                     - Header text
 *   - `.async-page-body-opus`                     - Scrollable body (flex-grow: 1)
 *
 * @template Configuration (app_name.config.json)
 * ```json
 * "asyncPage": {
 *     "eventName": {
 *         "type": "apage",
 *         "access": 3,
 *         "file": "vendor/Opus/apps/app_name/src/path/ClassName.php",
 *         "class": "Opus\\apps\\app_name\\src\\path\\ClassName"
 *     }
 * }
 * ```
 *
 * @template CSS Classes
 * - `.async-page-opus`              - Flex column container, 100% height
 * - `.async-page-header-opus`       - Base header styles (flex-shrink: 0, white text, text-shadow)
 * - `.async-page-header-opus-green` - Success state (green gradient + shadow)
 * - `.async-page-header-opus-red`   - Error state (red gradient + shadow)
 * - `.async-page-header-opus-black` - Black variant (black gradient + shadow)
 * - `.async-page-body-opus`         - Scrollable body (flex-grow: 1, overflow-y: auto)
 * - `.async-page-overlay-opus`      - Full-screen loading overlay with spinner
 */
class AsyncPage
{
	use TraitHTML;

	private array $apages;

	/**
	 * Adds a new async page to the collection
	 *
	 * This method creates a new async page with customizable header and body.
	 * The header includes a close button and supports icons and translated text.
	 * The body section is scrollable and accepts any HTML content.
	 *
	 * @param string $name A unique identifier for the async page
	 * @param object $options Configuration options for the async page:
	 *        - shadow: (string) CSS class for shadow effects (default: 'bs-opus-black-3d')
	 *        - id: (string) Element ID (default: 'id__' + name)
	 *        - headerClass: (string) CSS class for header styling (default: 'bs-opus-green')
	 *        - headerIcon: (string|null) Bootstrap icon class for header (default: null)
	 *        - headerText: (string|null) Header text or Lang key (default: null)
	 *        - headerCloseX: (string|null) Custom close button HTML (default: auto-generated)
	 *        - body: (string|null) HTML content for the body section (default: null)
	 *
	 * @return object Returns $this for method chaining
	 * @throws ControllerException If an async page with the given name already exists
	 */
	public function addAsyncPage(string $name, object $options): object
	{
		// Check if async page name is unique
		if (isset($this->apages[$name])) {
			throw new ControllerException(
				'html\asyncpage\duplicate',
				['message' => $name],
				ControllerException::TYPE_API_STRONG_EXCEPTION
			);
		}

		// Set default values for options
		$options->shadow ??= 'bs-opus-green-3d';
		$options->id ??= 'id__' . $name;
		$options->headerClass ??= 'async-page-header-opus-green';
		$options->headerIcon ??= null;
		$options->headerText ??= null;
		$options->headerCloseX ??= (function () use ($name, $options) {
			$form = new Form();
			$form->addElement(Buttons::closeButtonX(
				'apage-' . $name,
				['data-close' => '#' . $options->id]
			));
			return $form->getElement('close-btn-apage-' . $name);
		})();
		$options->body ??= null;
		$options->bodyClass ??= 'container-fluid';

		// Header Text
		$options->headerText = match (true) {
			is_null($options->headerText) => null,
			filter_var($options->headerText, FILTER_VALIDATE_REGEXP, self::VALID_LANG_KEY) !== false => Lang::getInstance()->get($options->headerText),
			default => $options->headerText
		};

		// Create base async page structure
		$this->apages[$name] = [
			'id' => $options->id,
			'class' => 'async-page-opus ' . $options->shadow
		];

		// Create async page header
		$this->apages[$name]['header'] = <<<HTML
		<div class="async-page-header-opus {$options->headerClass}">
			<div class="position-absolute btn-close-x">{$options->headerCloseX}</div>
			<div class="container">
				<div class="row">
					<div class="col">
						<h5 style="margin-bottom: 0;">
							<span class="me-1 ms-0 badge bg-opus-black bs-opus-black fs-5">
								<i class="bi {$options->headerIcon}"></i>
							</span>
							<span class="me-2">{$options->headerText}</span>
						</h5>
					</div>
				</div>
			</div>
		</div>
		HTML;

		// Create async page body
		$this->apages[$name]['body'] = <<<HTML
		<div class="async-page-body-opus">
			<div class="{$options->bodyClass}">
				{$options->body}
			</div>
		</div>
		HTML;

		// Store options for later reference
		$this->apages[$name]['options'] = $options;

		return $this;
	}

	/**
	 * Generates complete HTML markup for an async page
	 *
	 * Builds the full HTML structure including the outer container with ID,
	 * shadow class, header section, and body section.
	 *
	 * @param string $name The identifier of the async page to create
	 * @return string Complete HTML markup for the async page
	 * @throws ControllerException If the specified async page name doesn't exist
	 */
	private function create(string $name): string
	{
		// Check if the async page name exists
		if (!isset($this->apages[$name])) {
			throw new ControllerException(
				'html\asyncpage\duplicate',
				['message' => $name],
				ControllerException::TYPE_API_STRONG_EXCEPTION
			);
		}

		// Create outer async page container
		$html = '<div ';

		foreach ($this->apages[$name] as $key => $value) {
			// Skip nested elements that will be processed separately
			if (in_array($key, ['child-div', 'form', 'header', 'body', 'footer', 'options', 'ajax', 'csrf']) || !is_scalar($value)) {
				continue;
			}

			$html .= $key . '="' . $value . '" ';
		}

		$html .= '>';

		// Add header
		$html .= $this->apages[$name]['header'];

		// Add body
		$html .= $this->apages[$name]['body'];

		// Close all containers
		return $html . '</div>';
	}

	/**
	 * Retrieves the HTML markup for an async page
	 *
	 * Returns the complete HTML for a specific async page by name,
	 * or the first async page in the collection if no name is provided.
	 *
	 * @param string|null $name The identifier of the async page to retrieve, or null for first
	 * @return string Complete HTML markup for the requested async page
	 * @throws ControllerException If the specified async page name doesn't exist
	 */
	public function get(?string $name = null): string
	{
		$name ??= array_key_first($this->apages);
		return $this->create($name);
	}
}
