<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-24 12:04:04
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-15 19:51:24
 **/

namespace Opus\view\view;

use ArrayObject;
use Opus\config\Config;
use Opus\controller\Controller;
use Opus\controller\event\TableEventView;

class View extends ArrayObject
{
	private ?string $indexAction = null;
	private ?string $indexModals = null;
	private ?string $indexOffcanvas = null;

	public function __construct(array $variables = [])
	{
		// load event variables
		$this->viewTableEvent($variables);

		parent::__construct($variables, ArrayObject::ARRAY_AS_PROPS);

		// load main page view
		$this->viewPage();
	}

	public function __toString()
	{
		$content = $this->indexAction . $this->indexModals . $this->indexOffcanvas;

		// Preserve content inside <code> blocks from comment removal
		$preserved = [];
		$content = preg_replace_callback('/<code[^>]*>[\s\S]*?<\/code>/i', function ($match) use (&$preserved) {
			$key = '<!--PRESERVED_' . count($preserved) . '-->';
			$preserved[$key] = $match[0];
			return $key;
		}, $content);

		// Remove comments from remaining content
		$content = preg_replace(
			'/\/\*[\s\S]*?\*\/|([^:]|^)\/\/.*$/',
			'',
			$content
		);

		// Restore preserved <code> blocks
		return str_replace(array_keys($preserved), array_values($preserved), $content);
	}

	/**
	 * Loads and processes the main page view
	 *
	 * This method includes the main page view file if one exists for the current request.
	 * It captures the output of the included file using output buffering and stores it
	 * in the indexAction property, making it available for rendering in the final output.
	 *
	 * @return void
	 */
	private function viewPage(): void
	{
		// Skip if no page view is available
		if (is_null(Controller::getAppIndex()->index)) {
			return;
		}

		// Capture the page view content
		ob_start();
		require_once Controller::getAppIndex()->index;
		$this->indexAction = ob_get_clean();
	}

	/**
	 * Loads and processes table event view component
	 *
	 * This method checks if a table event is configured for the current application
	 * and creates a TableEventView instance if available. The view is added to the
	 * variables array under the 'dteview' key for use in templates.
	 *
	 * @param array &$variables Reference to the variables array that will be passed to the view
	 * @return void
	 */
	private function viewTableEvent(&$variables): void
	{
		if (!isset(Config::getConfig(Controller::getApp())->tableEvent)) {
			return;
		}

		$variables['dtEvent'] = new TableEventView([
			'id' => Config::getConfig(Controller::getApp())->idTableEvent
		]);
	}
}
