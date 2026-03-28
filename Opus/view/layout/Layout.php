<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-02-18 11:33:40
 * @Last Modified by:   Tomasz Ulazowski
 * @Last Modified time: 2026-03-28 17:09:32
 **/

namespace Opus\view\layout;

use Opus\config\Config;
use Opus\controller\TraitController;

class Layout
{
	use TraitController;

	public ?string $opusMainAppScript;
	public ?string $appScript;

	public function __construct(protected mixed $content = null, protected object $layout)
	{
		$this->setHeadLibs();
		$this->opusMainAppScript = $this->buildScript(
			$this->layout->js,
			fn($c) => "\nwindow.APP_LANGUAGE = '" . $_SESSION['lang'] . "';{$c}\n\t\t"
		);
		$this->appScript = $this->buildScript(
			$this->layout->appJs,
			fn($c) => "$(document).ready(function () {\n{$c}\n\t\t});"
		);
		return require_once $layout->index;
	}

	/**
	 * Generates HTML tags for vendor stylesheets, scripts, and Opus library files
	 *
	 * Builds <link> tags for CSS files and <script> tags for JS files defined in
	 * global config vendor array, appending Opus CSS, opus.js and {app}.lib.js if available.
	 * Results are stored in $this->layout->stylesheets and $this->layout->scripts.
	 *
	 * @return void
	 */
	private function setHeadLibs()
	{
		$this->layout->stylesheets = (function () {
			$html = '';
			$css = preg_grep('/\.css$/i', Config::getConfig()->vendor);

			foreach ($css as $file) {
				$html .= <<<HTML
				<link rel="stylesheet" href="vendor/{$file}"/>
				HTML;
			}

			if (!is_null($this->layout->opusCss)) {
				$html .= <<<HTML
				<link rel="stylesheet" href="{$this->layout->opusCss}"/>
				HTML;
			}

			return $html . PHP_EOL;
		})();

		$this->layout->scripts = (function () {
			$html = '';
			$js = preg_grep('/\.js$/i', Config::getConfig()->vendor);

			foreach ($js as $file) {
				$html .= <<<HTML
				<script type="text/javascript" src="vendor/{$file}"></script>
				HTML;
			}

			if (!is_null($this->layout->opusLib)) {
				$html .= <<<HTML
				<script type="text/javascript" src="{$this->layout->opusLib}"></script>
				HTML;
			}

			if (!is_null($this->layout->appLib)) {
				$html .= <<<HTML
				<script type="text/javascript" src="{$this->layout->appLib}"></script>
				HTML;
			}

			return $html . PHP_EOL;
		})();
	}

	/**
	 * Includes a JS file, strips comments, and optionally wraps the content
	 *
	 * Captures the output of the included file via output buffering,
	 * removes comments using JS_PATTERN_COMMENTS, and applies the wrapper
	 * callable if provided.
	 *
	 * @param string|null $file Path to the JavaScript file to include
	 * @param callable|null $wrapper Optional callback that receives the cleaned content and returns wrapped string
	 * @return string|null The processed (and optionally wrapped) JS content, or null if empty
	 */
	private function buildScript(?string $file, ?callable $wrapper = null): ?string
	{
		// Start output buffering to capture included content
		ob_start();

		// Include main app js file
		require_once $file;

		// Get captured content and clean up
		$content = trim(ob_get_clean());

		if (empty($content)) {
			return null;
		}

		$content = trim(preg_replace(
			self::JS_PATTERN_COMMENTS,
			self::JS_REPLACEMENT_COMMENTS,
			$content
		));

		return $wrapper ? $wrapper($content) : $content;
	}

}
