<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-04-01 18:13:37
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-04-02 12:51:06
 **/

namespace Opus\html\sidebar;

use stdClass;
use Opus\libs\Common;
use Opus\html\form\Form;
use Opus\controller\request\Request;
use Opus\controller\lang\Lang;
use Opus\controller\exception\ControllerException;

/**
 * Adds a new sidebar navigation to the collection
 *
 * This method creates a new Bootstrap sidebar with customizable navigation items,
 * dropdown menus, styling options, and localization support. Each sidebar includes
 * support for regular navigation links, dropdown submenus, custom HTML attributes,
 * and responsive design with Bootstrap classes.
 *
 * @param string $name A unique identifier for the sidebar
 * @param array $items Array of sidebar item configurations, each containing:
 *        - href: (string) Link URL or internal page parameter (e.g., 'page=homefinances&spage=portfolio')
 *        - icon: (string) Bootstrap Icons class name (e.g., 'bi-piggy-bank')
 *        - text: (text) The translation key (e.g., 'controller.login.user')
 *        - dropdown: (array, optional) Array of dropdown items with same structure as main items
 *        - Custom HTML attributes can be added (e.g., 'data-bs-toggle', 'data-bs-target')
 * @param object $options Configuration options for the sidebar:
 *        - asidePadding: (string) CSS classes for aside element padding (default: 'pe-0 pt-3 pb-3')
 *        - id: (string) Unique ID for the sidebar (auto-generated as 'id_{name}-sidebar')
 *        - additionalClasses: (string) Additional CSS classes for sidebar container (default: 'sidebar-body-happ')
 *        - additionalHtmlTags: (array|false) Custom HTML attributes for aside element (default: false)
 *        - itemPadding: (string) CSS classes for navigation item padding (default: 'pt-2')
 *        - itemAdditionalClasses: (string) Additional CSS classes for nav links (default: 'nav-link-happ')
 *        - itemIconSize: (string) Bootstrap size class for icons (default: 'fs-1')
 *        - itemTextSize: (string) CSS font-size value for text (default: 'var(--bs-body-font-size-sm)')
 *
 * @return object Returns $this for method chaining
 * @throws ControllerException If a sidebar with the given name already exists
 */
class Sidebar		// all CSS class are to be improved as soon as they are created!!!
{
	const VALID_LANG_KEY = ['options' => ['regexp' => '/^[a-zA-Z]+(\.[a-zA-Z]+)*$/']];
	const VALID_HREF = ['options' => ['regexp' => '/^page=(\w+)(?:&spage=(\w+))?$/']];
	const EXCLUDED_KEYS = ['href', 'icon', 'text', 'dropdown'];

	private array $sidebars;

	/**
	 * Retrieves and generates HTML markup for a specific sidebar by name
	 *
	 * Public interface method that returns the complete HTML structure for a previously
	 * configured sidebar. Acts as a wrapper for the internal createSidebar method.
	 *
	 * @param string $name The unique identifier of the sidebar to retrieve
	 * @return string Complete HTML markup for the requested sidebar
	 * @throws ControllerException When the specified sidebar name doesn't exist
	 */
	public function getSidebarByName(string $name): string
	{
		return $this->createSidebar($name);
	}

	/**
	 * Processes a link string for use in href attributes
	 *
	 * Validates the link against the VALID_HREF pattern and converts valid internal
	 * links to full URLs using Request::url(). Invalid or external links are returned unchanged.
	 *
	 * @param string $link The link to process (e.g., "page=homefinances&spage=portfolio")
	 * @return string The processed URL (e.g., "index.php?page=homefinances")
	 */
	private function href(string $link): string
	{
		return (filter_var($link, FILTER_VALIDATE_REGEXP, self::VALID_HREF) !== false)
			? Request::url('index.php?' . $link)
			: $link;
	}

	/**
	 * Generates a localized text span element with custom font size
	 *
	 * Creates an HTML span element containing localized text based on the user's
	 * current session language with the specified font size styling.
	 *
	 * @param string $key The translation key (e.g., 'controller.login.user')
	 * @param string $size CSS font-size value (e.g., "14px", "1.2rem", "var(--bs-font-size)")
	 * @return string HTML span element with localized text and font size styling
	 */
	private function span(string $key, string $size): string
	{
		$text = Lang::getInstance()->get($key);
		return <<<HTML
		<span style="font-size: {$size};">{$text}</span>
		HTML;
	}

	/**
	 * Generates icon HTML for sidebar items with optional dropdown indicator
	 *
	 * Creates either a single Bootstrap icon or a container with the main icon plus
	 * a chevron indicator for dropdown items. Uses Bootstrap Icons and custom styling.
	 *
	 * @param array $item Sidebar item configuration containing 'icon' key and optional 'dropdown' key
	 * @param string $size CSS class for icon size (e.g., "fs-1", "fs-2", "bi-lg")
	 * @return string HTML containing either a single icon or icon container with chevron
	 */
	private function i(array $item, string $size): string
	{
		return (!isset($item['dropdown']))
			? <<<HTML
			<i class="is-happ-black {$size} bi {$item['icon']}"></i>
			HTML
			: <<<HTML
			<div class="d-flex align-items-center justify-content-end">
				<i class="is-happ-black {$size} bi {$item['icon']}"></i>
				<i class="is-happ-black bi bi-chevron-compact-right"></i>
			</div>
			HTML;
	}

	/**
	 * Merges additional custom attributes into an element configuration
	 *
	 * Extracts any custom attributes from the item configuration (excluding standard
	 * sidebar keys) and merges them into the element's attributes array. This allows
	 * for custom HTML attributes to be added to sidebar elements.
	 *
	 * @param array $item The sidebar item configuration containing potential custom attributes
	 * @param array &$element Reference to the element array where attributes will be merged
	 * @return void The element array is modified directly by reference
	 */
	private function mergeAdditionalAttributes(array $item, array &$element): void
	{
		// Extract attributes not in the excluded keys list
		$additionalAttributes = array_diff_key($item, array_flip(self::EXCLUDED_KEYS));

		// Merge additional attributes if any exist
		if (!empty($additionalAttributes)) {
			$element['attributes'] = array_merge_recursive($element['attributes'], $additionalAttributes);
		}
	}

	/**
	 * Creates a sidebar navigation element and adds it to the form
	 *
	 * Generates a complete sidebar navigation link element with icon, text, and appropriate
	 * attributes. Handles both regular navigation items and dropdown toggles. The element
	 * is added to the provided Form object and returns the unique element name for reference.
	 *
	 * @param Form &$form Reference to the Form object where the element will be added
	 * @param array $item Sidebar item configuration containing 'href', 'icon', 'text', and optional 'dropdown'
	 * @param object $options Sidebar options containing styling and size properties
	 * @return string The unique element name generated for this sidebar item
	 */
	private function sidebarElement(Form &$form, array $item, object $options): string
	{
		// Generate unique element name
		$name = Common::windowsUniqId();

		// Build base element configuration
		$element = [
			'name' => $name,
			'id' => 'id_' . $name,
			'tag' => 'a',
			'text' => $this->i($item, $options->itemIconSize) . $this->span($item['text'], $options->itemTextSize),
			'attributes' => [
				'href' => $this->href($item['href']),
				'class' => 'nav-link py-0 d-flex flex-column ' . $options->itemAdditionalClasses
			]
		];

		// Add dropdown or regular navigation attributes
		$element['attributes'] = match (isset($item['dropdown'])) {
			true => array_merge_recursive(
				$element['attributes'],
				[
					'data-bs-toggle' => 'dropdown',
					'aria-expanded' => 'false',
					'role' => 'button'
				]
			),
			false => array_merge_recursive(
				$element['attributes'],
				[
					'aria-current' => 'page'
				]
			)
		};

		// Merge any additional custom attributes
		$this->mergeAdditionalAttributes($item, $element);

		// Add element to form
		$form->addElement($element);

		return $name;
	}

	/**
	 * Creates a dropdown menu element for sidebar dropdowns
	 *
	 * Generates either a dropdown divider (when item is a string) or a dropdown menu item
	 * (when item is an array). For menu items, creates a link with icon and localized text.
	 * The element is added to the provided Form object.
	 *
	 * @param Form &$form Reference to the Form object where the element will be added
	 * @param array|string $item Either a string for divider tag or array with 'icon', 'text', 'href' keys
	 * @return string|null The unique element name if successful, null if item type is invalid
	 */
	private function dropdownMenuElement(Form &$form, array|string $item): ?string
	{
		// Generate unique element name
		$name = Common::windowsUniqId();

		// Build base element configuration
		$element = match (true) {
			is_string($item) => [
				'name' => $name,
				'id' => 'id_' . $name,
				'tag' => $item,
				'attributes' => [
					'class' => 'dropdown-divider'
				]
			],

			is_array($item) => [
				'name' => $name,
				'id' => 'id_' . $name,
				'tag' => 'a',
				'text' => '<i class="me-1 bi ' . $item['icon'] . '"></i>' . Lang::getInstance()->get($item['text']),
				'attributes' => [
					'href' => $this->href($item['href']),
					'class' => 'dropdown-item'
				]
			],

			default => null
		};

		if (is_null($element)) {
			return null;
		}

		// Merge any additional custom attributes
		if (is_array($item)) {
			$this->mergeAdditionalAttributes($item, $element);
		}

		// Add element to form
		$form->addElement($element);

		return $name;
	}

	/**
	 * Generates HTML for dropdown menu items
	 *
	 * Iterates through an array of dropdown items and creates HTML list items
	 * for each valid dropdown element. Skips invalid items and wraps each
	 * element in a <li> tag for proper Bootstrap dropdown structure.
	 *
	 * @param Form &$form Reference to the Form object containing the dropdown elements
	 * @param array $dropdownItems Array of dropdown item configurations (arrays or strings)
	 * @return string HTML string containing <li> wrapped dropdown elements
	 */
	private function dropdownMenuHtml(Form &$form, array $dropdownItems): string
	{
		$html = '';

		// Process each dropdown item
		foreach ($dropdownItems as $item) {
			// Create dropdown element and get its name
			$el = $this->dropdownMenuElement($form, $item);

			// Skip invalid items that couldn't be created
			if (is_null($el)) {
				continue;
			}

			// Wrap element in list item for dropdown structure
			$html .= <<<HTML
			<li>{$form->getElement($el)}</li>
			HTML;
		}

		return $html;
	}

	/**
	 * Adds a new sidebar configuration to the sidebar collection
	 *
	 * Creates a complete sidebar structure with navigation items, dropdown menus, and custom styling.
	 * Each sidebar must have a unique name and will be configured with the provided items and options.
	 * Supports both regular navigation links and dropdown menus with custom HTML attributes.
	 *
	 * @param string $name Unique identifier for the sidebar (e.g., 'homefinances')
	 * @param array $items Array of sidebar item configurations, each containing 'href', 'icon', 'text' keys
	 * @param object $options Optional configuration object with styling and behavior settings
	 * @return object Returns $this for method chaining
	 * @throws ControllerException When sidebar name already exists
	 */
	public function addSidebar(
		string $name,
		array $items,
		object $options = new stdClass
	): object {
		// Check if the sidebar name is unique
		if (isset($this->sidebars[$name])) {
			throw new ControllerException(
				'html\sidebar\duplicate',
				['message' => $name]
			);
		}

		// Validate items
		ValidateSidebarItems::validateItems($items);

		// Set default values for options
		$options->asidePadding ??= 'pe-0 pt-3 pb-3';
		$options->id = 'id_' . $name . '-sidebar';
		$options->additionalClasses ??= 'sidebar-body-happ';
		$options->additionalHtmlTags ??= false;		// [key => value]
		$options->itemPadding ??= 'pt-2';
		$options->itemAdditionalClasses ??= 'nav-link-happ';
		$options->itemIconSize ??= 'fs-1';
		$options->itemTextSize ??= 'var(--bs-body-font-size-sm)';

		$form = new Form();

		// Create base sidebar structure
		$this->sidebars[$name] = [
			'class' => 'col-auto ' . $options->asidePadding
		];

		if ($options->additionalHtmlTags !== false) {
			$this->sidebars[$name] = array_merge_recursive(
				$this->sidebars[$name],
				$options->additionalHtmlTags
			);
		}

		// Configure child sidebar dialog classes
		$this->sidebars[$name]['child-div'] = [
			'id' => $options->id,
			'class' => 'nav nav-pills text-center ' . $options->additionalClasses
		];

		// Items
		foreach ($items as $key => $item) {
			$el = $this->sidebarElement($form, $item, $options);

			$this->sidebars[$name]['items'][$key] = match (true) {
				isset($item['dropdown']) => (function () use (&$form, $item, $el, $options) {
					return <<<HTML
					<li class="nav-item {$options->itemPadding}">
						<div class="dropend">
							{$form->getElement($el)}
							<ul class="dropdown-menu">
								{$this->dropdownMenuHtml($form,$item['dropdown'])}
							</ul>
						</div>
					</li>
					HTML;
				})(),

				default => (function () use (&$form, $el, $options) {
					return <<<HTML
					<li class="nav-item {$options->itemPadding}">
						{$form->getElement($el)}
					</li>
					HTML;
				})()
			};
		}

		// Store options for later reference
		$this->sidebars[$name]['options'] = $options;

		return $this;
	}

	/**
	 * Generates complete HTML markup for a configured sidebar
	 *
	 * Creates the full sidebar structure including the outer <aside> container,
	 * inner navigation wrapper <div>, and all sidebar items. Processes stored
	 * sidebar configuration to build Bootstrap-compatible navigation HTML.
	 *
	 * @param string $name The unique identifier of the sidebar to generate
	 * @return string Complete HTML markup for the sidebar including <aside> and <div> containers
	 * @throws ControllerException When the specified sidebar name doesn't exist
	 */
	private function createSidebar(string $name): string
	{
		// Check if the sidebar name exists
		if (!isset($this->sidebars[$name])) {
			throw new ControllerException(
				'html\sidebar\lack',
				['message' => $name]
			);
		}

		// Add after the first existence check:
		if (!isset($this->sidebars[$name]['child-div']) || !isset($this->sidebars[$name]['items'])) {
			throw new ControllerException(
				'html\sidebar\incomplete',
				['message' => $name]
			);
		}

		// Create outer sidebar container
		$html = '<aside ';

		foreach ($this->sidebars[$name] as $key => $value) {
			// Skip nested elements that will be processed separately
			if (in_array($key, ['child-div', 'items']) || !is_scalar($value)) {
				continue;
			}

			$html .= $key . '="' . $value . '" ';
		}

		$html .= '>';

		// Create dialog wrapper
		$html .= '<div ';

		foreach ($this->sidebars[$name]['child-div'] as $key => $value) {
			$html .= $key . '="' . $value . '" ';
		}

		$html .= '>';

		// Items
		foreach ($this->sidebars[$name]['items'] as $value) {
			$html .= $value;
		}

		return $html . '</div></aside>';
	}
}
