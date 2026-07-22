<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-05-30 14:10:28
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-22 11:34:55
 **/

namespace Opus\apps\demo\src;

use Opus\html\sidebar\Sidebar;

class DemoSidebar
{
	const ITEMS = [
		[
			'href' => '#',
			'icon' => 'bi-window',
			'text' => 'demo.sidebar.modal',
			'dropdown' => [
				[
					'href' => '#',
					'icon' => 'bi-file-earmark-code',
					'text' => 'demo.sidebar.modal.static',
					'data-bs-toggle' => 'modal',
					'data-bs-target' => '#id__opus-demo-static-modal'
				],
				[
					'href' => '#',
					'icon' => 'bi-lightning',
					'text' => 'demo.sidebar.modal.dynamic',
					'data-bs-toggle' => 'modal',
					'data-bs-target' => '#id__opus-demo-dynamic-modal'
				]
			]
		],
		[
			'href' => '#',
			'icon' => 'bi-layout-sidebar-inset',
			'text' => 'demo.sidebar.offcanvas',
			'data-apage' => 'demo',
			'data-event' => 'demoOffcanvas'
		],
		[
			'href' => '#',
			'icon' => 'bi-arrows-collapse',
			'text' => 'demo.sidebar.collapse',
			'data-apage' => 'demo',
			'data-event' => 'demoCollapse'
		],
		[
			'href' => '#',
			'icon' => 'bi-type-bold',
			'text' => 'demo.sidebar.buttons',
			'data-apage' => 'demo',
			'data-event' => 'demoButtons'
		],
		[
			'href' => '#',
			'icon' => 'bi-calendar-event',
			'text' => 'demo.sidebar.datepicker',
			'data-apage' => 'demo',
			'data-event' => 'demoDatePicker'
		],
		[
			'href' => '#',
			'icon' => 'bi-table',
			'text' => 'demo.table.buttons',
			'data-apage' => 'demo',
			'data-event' => 'demoTable'
		]
	];

	public static function getSidebar(): Sidebar
	{
		$sidebar = new Sidebar();
		$sidebar->addSidebar('demo', self::ITEMS);
		return $sidebar;
	}
}
