<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-19 16:54:11
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-19 20:27:17
 **/

namespace Opus\controller\event;

use Opus\controller\query\TraitQuery;

trait TraitValidQueryTypes
{
	use TraitQuery;

	// do not change the order in the array, joined with
	// opus\controller\event\TraitValidEditorStrategy::VALID_EDITOR_STRATEGY;
	public const VALID_QUERY_TYPES = [
		'insert',	// twin type TraitValidEditorStrategy::EDITOR_STRATEGY_ADD
		'delete',	// twin type TraitValidEditorStrategy::EDITOR_STRATEGY_DELETE
		'update',	// twin type TraitValidEditorStrategy::EDITOR_STRATEGY_EDIT
		null		// twin type TraitValidEditorStrategy::EDITOR_STRATEGY_SHOW
	];

	public const VALID_INPUTS_EXEPTIONS = [
		self::QINPUT_EXEPTIONS_PAGE,
		self::QINPUT_EXEPTIONS_HASHTAG,
		self::QINPUT_EXEPTIONS_PLUS
	];
}
