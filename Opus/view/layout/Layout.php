<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-02-18 11:33:40
 * @Last Modified by:   Tomasz Ulazowski
 * @Last Modified time: 2026-02-19 17:28:47
 **/

namespace Opus\view\layout;

use Opus\config\Config;

class Layout
{
	public function __construct(protected mixed $content = null, protected object $layout)
	{
		$this->setVendorLibs();
		return require_once $layout->index;
	}

	private function setVendorLibs()
	{
		$this->layout->stylesheets = function () {
			$html = '';
			$css = preg_grep('/\.css$/i', Config::getConfig()->vendor);

			foreach ($css as $file) {
				$html .= <<<HTML
					<link rel="stylesheet" href="vendor/{$file}"/>
				HTML;
			}

			return $html;
		};

		$this->layout->scripts = function () {
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

			return $html;
		};
	}
}
