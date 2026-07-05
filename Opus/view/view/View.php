<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-24 12:04:04
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-24 13:26:59
 **/

namespace Opus\view\view;

use ArrayObject;
use Opus\controller\Controller;

class View extends ArrayObject
{
	private ?string $indexAction = null;
	private ?string $indexModals = null;
	private ?string $indexOffcanvas = null;

	public function __construct(array $variables = [])
	{
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
}
