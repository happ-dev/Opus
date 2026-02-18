<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-02-09 13:44:56
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-02-09 13:45:22
 **/

namespace Opus\controller\event;

trait TraitValidEditorStrategy
{
	const EDITOR_STRATEGY_ADD = 'add';
	const EDITOR_STRATEGY_EDIT = 'edit';
	const EDITOR_STRATEGY_SHOW = 'show';
	const EDITOR_STRATEGY_DELETE = 'delete';

	// do not change the order in the array, joined with
	// opus\controller\event\TraitValidQueryTypes::VALID_QUERY_TYPES;
	public const VALID_EDITOR_STRATEGY = [
		self::EDITOR_STRATEGY_ADD,		// twin type TraitValidQueryTypes::insert
		self::EDITOR_STRATEGY_DELETE,	// twin type TraitValidQueryTypes::delete
		self::EDITOR_STRATEGY_EDIT,		// twin type TraitValidQueryTypes::update
		self::EDITOR_STRATEGY_SHOW		// twin type TraitValidQueryTypes::null
	];
}
