<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-24 16:24:39
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-24 16:25:38
 **/

namespace Opus\apps\demo\src;

use Opus\controller\InterfaceIndexController;
use Opus\view\view\View;

class DemoController implements InterfaceIndexController
{
	public function indexAction()
	{
		return new View();
	}
}
