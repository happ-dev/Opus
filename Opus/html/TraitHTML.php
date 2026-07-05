<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-07-05 15:19:12
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-05 15:24:51
 **/

namespace Opus\html;

trait TraitHTML
{
	const VALID_LANG_KEY = ['options' => ['regexp' => '/^[a-zA-Z]+(\.[a-zA-Z]+)*$/']];
	const VALID_HREF = ['options' => ['regexp' => '/^page=(\w+)(?:&spage=(\w+))?$/']];
	const EXCLUDED_KEYS = ['href', 'icon', 'text', 'dropdown'];
}
