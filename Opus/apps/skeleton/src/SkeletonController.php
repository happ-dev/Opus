<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-05-24 11:37:33
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-24 13:09:15
 **/

namespace Opus\apps\skeleton\src;

use Opus\controller\InterfaceIndexController;
use Opus\view\view\View;

class SkeletonController implements InterfaceIndexController
{
	public function indexAction()
	{
		return new View();
	}
}
