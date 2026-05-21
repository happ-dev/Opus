<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-19 16:40:29
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-19 16:43:56
 **/

namespace Opus\controller\event;

use stdClass;
use Opus\config\Config;
use Opus\controller\request\Request;
use Opus\controller\exception\ControllerException;

/**
 * Handles asynchronous event processing
 *
 * This class provides functionality to:
 * - Load and validate configuration for asynchronous events
 * - Dynamically include event handler files
 * - Instantiate event handler classes
 * - Execute API actions defined in those handlers
 *
 * It uses URL parameters to determine which event to process
 * and configuration settings to locate the appropriate handler.
 *
 * @example
 * index.php?api=asyncevent&app=skeleton&event=hello
 */
class AsyncEvent
{
	private static object $config;

	/**
	 * Initializes and validates configuration for the async event
	 *
	 * This method:
	 * 1. Creates a configuration object
	 * 2. Retrieves app and event parameters from the URL
	 * 3. Loads the corresponding configuration
	 * 4. Validates the configuration using AsyncEventValidate
	 *
	 * @return void
	 * @throws ControllerException If configuration validation fails
	 */
	private static function selectConfig(): void
	{
		self::$config = new stdClass();
		self::$config->app = Request::fromUrl('app');
		self::$config->event = Request::fromUrl('event');
		self::$config->async = Config::getConfig(self::$config->app)->asyncEvent->{self::$config->event};
		new AsyncEventValidate(self::$config);
	}

	/**
	 * Processes and executes the async event
	 *
	 * This method:
	 * 1. Initializes and validates the configuration
	 * 2. Includes the specified file
	 * 3. Instantiates the event class
	 * 4. Executes the apiAction method
	 *
	 * @return mixed Result from the apiAction method
	 * @throws ControllerException If configuration validation fails
	 */
	public static function doAsyncEvent(): mixed
	{
		self::selectConfig();
		require_once self::$config->async->file;
		$objEvent = new self::$config->async->class;
		return $objEvent->apiAction();
	}
}
