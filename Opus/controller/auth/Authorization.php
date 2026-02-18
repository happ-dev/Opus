<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-02-09 13:43:46
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-02-14 13:58:18
 **/

namespace Opus\controller\auth;

use Opus\config\Config;
use Opus\controller\exception\ControllerException;
use Opus\controller\event\TableEventValidate;

class Authorization
{
	private static $access = false;

	/**
	 * Validates and processes access permissions for different application components
	 *
	 * @param object $obj Access configuration object containing:
	 *                    - app: (required) Application identifier
	 *                    - asyncPage: (optional) Sub-application identifier or false
	 *                    - tableEvent: (optional) Table event identifier or false
	 *                    - asyncEvent: (optional) Async event identifier or false
	 *                    - except: (optional) Exception type, defaults to TYPE_PAGE_EXCEPTION
	 *
	 * @throws ControllerException When:
	 *                            - Required properties are missing
	 *                            - Access is denied for any component
	 *                            - Configuration is invalid
	 * @return void
	 */
	public static function access(object $obj): void
	{
		// Validate access object properties
		self::validateAccessObj($obj);

		// Process main application access permissions
		self::accessApp($obj);

		// Process sub-application (async page) access permissions
		self::accessAsyncPage($obj);

		// Process table event access permissions
		self::accessTableEvent($obj);

		// Process async event access permissions
		self::accessAsyncEvent($obj);

		// Throw exception if access is denied
		if (!self::$access) {
			throw new ControllerException(
				'controller\authorization\access',
				[
					'message' => print_r($obj->message, true),
					'details' => Config::getConfig('email')
				],
				$obj->except
			);
		}
	}

	/**
	 * Validates and sets default values for access object properties
	 *
	 * @param object $obj Reference to the access configuration object
	 * @throws ControllerException When app property is empty or not set
	 * @return void
	 *
	 * Properties validated/set:
	 * - except: Default to TYPE_PAGE_EXCEPTION if not set
	 * - app: Must be non-empty
	 * - asyncPage: Default to false if not set
	 * - asyncEvent: Default to false if not set
	 * - tableEvent: Default to false if not set
	 */
	private static function validateAccessObj(object &$obj): void
	{
		// set as default ControllerException::TYPE_PAGE_EXCEPTION if value does not exist
		$obj->except ??= ControllerException::TYPE_PAGE_EXCEPTION;

		// Check if app property exists and is not empty
		$obj->app ?: throw new ControllerException(
			'controller\authorization\access\param',
			['message' => 'app']
		);

		// Set default values for optional properties
		$obj->asyncPage ??= false;
		$obj->asyncEvent ??= false;
		$obj->tableEvent ??= false;
	}

	/**
	 * Validates and processes table event access permissions
	 *
	 * @param object $obj Reference to the access configuration object containing:
	 *                    - tableEvent: Event identifier or false
	 *                    - app: Application identifier
	 * @throws ControllerException When table event configuration or access level is not found
	 * @return void
	 */
	private static function accessTableEvent(object &$obj): void
	{
		// Skip processing if tableEvent is explicitly set to false
		if (is_bool($obj->tableEvent) && $obj->tableEvent === false) {
			return;
		}

		// Set message for logging/debugging
		$obj->message = $obj->app . '\\' . $obj->tableEvent;

		// Verify table event configuration exists
		$eventConfig = Config::getConfig($obj->app)->tableEvent->{$obj->tableEvent}->access->show
			?? throw new ControllerException(
				'controller\authorization\access\asyncTableParam',
				['details' => print_r($obj->message, true)],
				ControllerException::TYPE_API_EXCEPTION
			);

		// Compare user's session level with required access level
		self::$access = ((int) $eventConfig <= (int) $_SESSION['level']) ? true : false;
	}

	/**
	 * Validates and processes async event access permissions
	 *
	 * @param object $obj Reference to the access configuration object containing:
	 *                    - asyncEvent: Event identifier or false
	 *                    - app: Application identifier
	 * @throws ControllerException When async event configuration or access level is not found
	 * @return void
	 */
	private static function accessAsyncEvent(object &$obj): void
	{
		// Skip processing if tableEvent is explicitly set to false
		if (is_bool($obj->asyncEvent) && $obj->asyncEvent === false) {
			return;
		}

		// Set message for logging/debugging
		$obj->message = $obj->app . '\\' . $obj->asyncEvent;

		// Verify async event configuration exists
		$eventConfig = Config::getConfig($obj->app)->asyncEvent->{$obj->asyncEvent}->access
			?? throw new ControllerException(
				'controller\authorization\access\asyncEventParam',
				['details' => print_r($obj->message, true)],
				ControllerException::TYPE_API_EXCEPTION
			);

		// Compare user's session level with required access level
		self::$access = ((int) $eventConfig <= (int) $_SESSION['level']) ? true : false;
	}

	/**
	 * Validates and processes async page access permissions
	 *
	 * @param object $obj Reference to the access configuration object containing:
	 *                    - asyncPage: Event identifier or false
	 *                    - app: Application identifier
	 * @throws ControllerException When async page configuration or access level is not found
	 * @return void
	 */
	private static function accessAsyncPage(object &$obj): void
	{
		// Skip processing if asyncPage is explicitly set to false
		if (is_bool($obj->asyncPage) && $obj->asyncPage === false) {
			return;
		}

		// Set message for logging/debugging
		$obj->message = $obj->app . '\\' . $obj->asyncPage;

		// Verify async page configuration exists
		$eventConfig = Config::getConfig($obj->app)->asyncPage->{$obj->asyncPage}->access
			?? throw new ControllerException(
				'controller\authorization\access\asyncPageParam',
				['details' => print_r($obj->message, true)],
				ControllerException::TYPE_ASYNC_PAGE_EXCEPTION
			);

		// Compare user's session level with required access level
		self::$access = ((int) $eventConfig <= (int) $_SESSION['level']) ? true : false;
	}

	/**
	 * Validates and processes main application access permissions
	 *
	 * @param object $obj Reference to the access configuration object containing:
	 *                    - app: Application identifier
	 * @return void
	 */
	private static function accessApp(object &$obj): void
	{
		// Set message for logging/debugging
		$obj->message = $obj->app;

		// Compare user's session level with required access level
		self::$access = ((int) Config::getConfig($obj->app)->app->access <= (int) $_SESSION['level']) ? true : false;
	}

	/**
	 * Validates access permissions for table event buttons
	 *
	 * @param string $app        Application identifier
	 * @param string $tableEvent Table event identifier
	 * @param string $button     Button type (add/edit/show/delete)
	 *
	 * @return bool True if user has access to the specified button, false otherwise
	 */
	public static function accessTableEventButtons(string $app, string $tableEvent, string $button): bool
	{
		// Get configuration based on button type
		$config = match ($button) {
			TableEventValidate::VALID_TABLE_BUTTON_ADD => Config::getConfig($app)->tableEvent->{$tableEvent}->access->add ?? false,
			TableEventValidate::VALID_TABLE_BUTTON_EDIT => Config::getConfig($app)->tableEvent->{$tableEvent}->access->edit ?? false,
			TableEventValidate::VALID_TABLE_BUTTON_SHOW => Config::getConfig($app)->tableEvent->{$tableEvent}->access->show ?? false,
			TableEventValidate::VALID_TABLE_BUTTON_DELETE => Config::getConfig($app)->tableEvent->{$tableEvent}->access->delete ?? false
		};

		// Return false if no configuration found
		if ($config === false) {
			return false;
		}

		// Validate access level format and compare with user's session level
		return (filter_var($config, FILTER_VALIDATE_REGEXP, TableEventValidate::VALID_ACCESS_LEVEL) !== false)
			&& ((int) $_SESSION['level'] >= (int) $config);
	}

	/**
	 * Validates access permissions for table editor strategy
	 *
	 * @param object $config Configuration object containing:
	 *                      - table->access->{strategy}: Required access level
	 *                      - strategy: Strategy identifier
	 *                      - app: Application identifier
	 *                      - event: Event identifier
	 *
	 * @throws ControllerException When user's access level is insufficient
	 * @return void
	 */
	public static function accessTableEditorStrategy(object $config): void
	{

		if ((int) $config->table->access->{$config->strategy} > (int) $_SESSION['level']) {
			throw new ControllerException(
				'controller\authorization\access',
				[
					'message' => print_r($config->app . '\\' . $config->event, true),
					'details' => Config::getConfig('email')
				],
				ControllerException::TYPE_API_EXCEPTION
			);
		}
	}
}
