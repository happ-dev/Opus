<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-07-03 19:34:23
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-04 10:23:37
 **/

namespace Opus\apps\demo\src\offcanvas;

use Opus\controller\InterfaceApiController;
use Opus\controller\lang\Lang;

/**
 * API controller for the dynamic offcanvas component in the Demo application
 *
 * Returns a JSON response containing header and body data
 * used by OpusOffcanvas to dynamically populate the offcanvas panel.
 */
class DemoDynamicOffcanvasApi implements InterfaceApiController
{
	/**
	 * Outputs JSON response with header and body data for the dynamic offcanvas
	 *
	 * @return void
	 */
	public function apiAction(): void
	{
		echo json_encode([
			'header' => $this->header(),
			'body' => $this->body(),
		]);
	}

	/**
	 * Builds the offcanvas header data
	 *
	 * @return array{text: string, icon: string}
	 */
	private function header(): array
	{
		return [
			'text' => Lang::getInstance()->get('demo.offcanvas.button.dynamic'),
			'icon' => 'bi-layout-sidebar-inset-reverse'
		];
	}

	/**
	 * Builds the offcanvas body HTML content
	 *
	 * @return string HTML markup with info and success alert messages
	 */
	private function body(): string
	{
		$lang = Lang::getInstance();
		return <<<HTML
		<div class="alert alert-info mt-3" role="alert">
			{$lang->get('demo.offcanvas.dynamic.content.info')}
		</div>
		<div class="alert alert-success mt-3" role="alert">
			{$lang->get('demo.offcanvas.dynamic.content.js')}
		</div>
		HTML;
	}
}
