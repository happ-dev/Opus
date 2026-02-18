<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-02-09 13:24:42
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-02-09 13:29:15
 **/

namespace Opus\controller\event;

use Opus\config\Config;
use Opus\controller\request\Request;

class Event
{

	const TYPE_PAGE = Request::TYPE_PAGE;
	const TYPE_ASYNC_PAGE = Request::TYPE_ASYNC_PAGE;
	const TYPE_API = Request::TYPE_API;
	const TYPE_CLI = Request::TYPE_CLI;

	const TYPE_APPS = [
		self::TYPE_PAGE => ['login', 'logout', 'download', 'upload'],
		self::TYPE_ASYNC_PAGE => [],
		self::TYPE_API => ['asyncevent', 'tableevent', 'injectevent'],
		self::TYPE_CLI => []
	];

	/**
	 * Creates an application object instance based on configuration
	 *
	 * Retrieves the class name from the application's configuration
	 * and instantiates a new object of that class.
	 *
	 * @param string $app Application identifier/name
	 * @return object Instantiated application object
	 */
	private function createAppObject(string $app): object
	{
		$class = Config::getConfig($app)->app->class;
		return new $class();
	}

	/**
	 * Creates and processes a new event with type-specific validation
	 *
	 * Factory method that handles three types of events:
	 * - PAGE and API events: validates the request parameter against TYPE_APPS
	 * - CLI events: validates the app parameter against TYPE_APPS
	 *
	 * @param string $type Event type (TYPE_PAGE, TYPE_API, or TYPE_CLI)
	 * @param string|null $app Application identifier to be validated for CLI events
	 * @param string|null $request Request identifier to be validated for PAGE/API events
	 *
	 * @throws EventException When:
	 *      - PAGE/API: request matches restricted types in TYPE_APPS
	 *      - CLI: app matches restricted types in TYPE_APPS
	 * @return mixed Returns the result of:
	 *      - Normal flow: indexAction(), apiAction(), or cliAction() from app object
	 *      - Exception flow: corresponding action from EventException handler
	 *
	 * @see Event::TYPE_APPS
	 * @see Event::createAppObject()
	 */
	public static function newEvent(string $type, ?string $app, ?string $request): mixed
	{
		$event = new self();
		return null;
	}
}
