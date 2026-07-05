<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-21 19:52:37
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-06-28 17:51:51
 **/

namespace Opus\controller\event;

use stdClass;
use Opus\config\Config;
use Opus\controller\request\Request;
use Opus\controller\exception\ControllerException;

/**
 * Handles asynchronous page loading requests
 *
 * Loads and executes a page fragment (HTML + optional JS) based on
 * the asyncPage configuration defined in the application config file.
 *
 * URL: index.php?apage=asyncpage&app={app_name}&event={event_name}
 *
 * @package Opus\controller\event
 */
class AsyncPage
{
	private static object $config;

	/**
	 * Loads and validates asyncPage configuration from URL parameters
	 *
	 * @return void
	 * @throws ControllerException If configuration parameters are invalid
	 */
	private static function selectConfig(): void
	{
		self::$config = new stdClass();
		self::$config->app = Request::fromUrl('app');
		self::$config->event = Request::fromUrl('event');
		self::$config->async = Config::getConfig(self::$config->app)->asyncPage->{self::$config->event};
		new AsyncPageValidate(self::$config);
	}

	/**
	 * Executes the async page controller and returns its output
	 *
	 * Validates CSRF token, loads the configuration, includes the page file,
	 * instantiates the controller and calls asyncAction().
	 *
	 * @return mixed HTML content returned by asyncAction()
	 * @throws ControllerException If CSRF validation fails, or configuration or file is invalid
	 */
	public static function doAsyncPage(): mixed
	{
		$csrf = Request::validateCsrfToken();

		if ($csrf !== true) {
			throw new ControllerException(
				'controller\asyncPage\csrf',
				['message' => $csrf],
				ControllerException::TYPE_ASYNC_PAGE_EXCEPTION
			);
		}

		self::selectConfig();
		require_once self::$config->async->view;
		$objEvent = new self::$config->async->class;
		return $objEvent->asyncAction();
	}
}
