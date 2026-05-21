<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-20 22:00:23
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-21 19:14:43
 **/

namespace Opus\controller\event\editor;

use Opus\controller\event\TraitValidEditorStrategy;
use opus\controller\auth\Authorization;
use opus\controller\request\Request;
use opus\controller\exception\ControllerException;

class TableEditor
{
	use TraitValidEditorStrategy;

	private static object $config;

	public function __construct(public readonly InterfaceEditor $strategy) {}

	/**
	 * Validates editor strategy and ID parameters
	 *
	 * This method extracts the strategy parameter from the URL and validates that it's supported.
	 * For non-add strategies, it also extracts and validates the ID parameter.
	 *
	 * @throws ControllerException If the strategy is invalid or if ID is missing when required
	 * @return void
	 */
	private static function validateConfig(): void
	{
		self::$config->strategy = Request::fromUrl('strategy');

		if (!in_array(self::$config->strategy, self::VALID_EDITOR_STRATEGY)) {
			throw new ControllerException(
				'controller\tableEvent\selectConfig\param',
				[
					'message' => 'strategy',
					'details' => self::$config->strategy
				],
				ControllerException::TYPE_API_EXCEPTION
			);
		}

		if (self::$config->strategy != self::EDITOR_STRATEGY_ADD) {
			self::$config->table->id = Request::fromUrl('id');

			if (self::$config->table->id === '/' || self::$config->table->id === false) {
				throw new ControllerException(
					'controller\tableEvent\selectConfig\param',
					[
						'message' => 'id',
						'details' => self::$config->table->id
					],
					ControllerException::TYPE_API_EXCEPTION
				);
			}
		}
	}

	/**
	 * Selects and instantiates the appropriate editor strategy based on the request
	 *
	 * This method:
	 * 1. Validates the strategy and ID parameters from the request
	 * 2. Creates the appropriate strategy object (Show, Edit, Add, or Delete)
	 * 3. Returns a TableEditor instance with the selected strategy
	 *
	 * @return object TableEditor instance with the appropriate strategy
	 * @throws ControllerException If the strategy is invalid or required parameters are missing
	 */
	private static function selectStrategy(): object
	{
		self::validateConfig();

		return match (self::$config->strategy) {
			self::EDITOR_STRATEGY_SHOW => new TableEditor(new TableEditorShow(self::$config)),
			self::EDITOR_STRATEGY_EDIT => new TableEditor(new TableEditorEdit(self::$config)),
			self::EDITOR_STRATEGY_ADD => new TableEditor(new TableEditorAdd(self::$config)),
			self::EDITOR_STRATEGY_DELETE => new TableEditor(new TableEditorDelete(self::$config))
		};
	}

	/**
	 * Main entry point for table editor operations
	 *
	 * This static method handles the complete table editor workflow:
	 * 1. Stores the configuration
	 * 2. Selects the appropriate strategy based on the request
	 * 3. Verifies user authorization for the requested operation
	 * 4. Executes the selected strategy
	 *
	 * @param object $config The table event configuration object
	 * @return mixed Result of the table editor operation
	 * @throws ControllerException If validation fails or user is not authorized
	 */
	public static function tableEditor(object $config): mixed
	{
		self::$config = $config;
		$selectedStrategy = self::selectStrategy();
		Authorization::accessTableEditorStrategy(self::$config);
		return $selectedStrategy->strategy->doTableEdit();
	}
}
