<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-07-05 18:21:45
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-05 19:08:00
 **/

namespace Opus\apps\demo\src\collapse;

use Opus\controller\InterfaceApiController;
use Opus\controller\lang\Lang;

/**
 * API controller for the dynamic collapse demo
 *
 * Returns JSON response with header and body content for the dynamic collapse component,
 * loaded via AJAX by the OpusCollapse JavaScript class.
 */
class DemoDynamicCollapseApi implements InterfaceApiController
{
	/**
	 * Outputs JSON response with header and body for the dynamic collapse
	 *
	 * @return void
	 */
	public function apiAction(): void
	{
		echo json_encode([
			'body' => $this->body(),
		]);
	}

	/**
	 * Builds the collapse body HTML
	 *
	 * @return string HTML with info and JS description alerts
	 */
	private function body(): string
	{
		$lang = Lang::getInstance();
		return <<<HTML
		<div class="alert alert-info mt-3" role="alert">
			{$lang->get('demo.collapse.dynamic.content.info')}
		</div>
		<div class="alert alert-success mt-3" role="alert">
			{$lang->get('demo.collapse.dynamic.content.js')}
		</div>
		HTML;
	}
}
