<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-02-18 11:33:40
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-02-18 12:08:48
 **/

namespace Opus\view\layout;

use Opus\config\Config;

class Layout
{
	public function __construct(protected mixed $content = null, protected object $layout)
	{
		return require_once $layout->index;
	}
}
