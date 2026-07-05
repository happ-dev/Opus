<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-24 16:24:39
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-06-06 15:38:32
 **/

namespace Opus\apps\demo\src;

use Opus\controller\InterfaceIndexController;
use Opus\view\view\View;

class DemoController implements InterfaceIndexController
{
	public function indexAction()
	{
		return new View([
			'sidebar' => DemoSidebar::getSidebar(),
			'staticModal' => modal\DemoStaticModal::demoModal(),
			'dynamicModal' => modal\DemoDynamicModal::demoModal(),
		]);
	}
}
