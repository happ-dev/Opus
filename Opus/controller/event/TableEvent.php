<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-21 19:17:16
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-21 19:22:27
 **/

namespace Opus\controller\event;

use stdClass;
use Opus\config\Config;
use Opus\controller\exception\ControllerException;
use Opus\controller\request\Request;
use Opus\controller\event\serverside\ServerSide;
use Opus\controller\event\query\TableQuery;
use Opus\controller\event\editor\TableEditor;

/**
 * Handles table-related API events for data manipulation and display
 *
 * This class processes table event API requests that allow applications to:
 * - Fetch data for tables (serverside processing)
 * - Execute database queries (query processing)
 * - Handle CRUD operations on table data (editor processing)
 *
 * API Endpoint Format:
 * index.php?api=tableevent&app={app}&event={event}&process={process}
 *
 * @param string app Application name, must be defined in config.json
 * @param string event Event name, must be defined in app-name.config.json
 * @param string process Processing mode: "serverside", "query", or "editor"
 *                      - serverside: Handles server-side processing for DataTables
 *                      - query: Executes database queries defined in configuration
 *                      - editor: Handles CRUD operations (add/edit/delete/view)
 *
 * @example
 * index.php?api=tableevent&app=skeleton&event=hello&process=editor
 * index.php?api=tableevent&app=skeleton&event=hello&process=serverside
 */
class TableEvent
{
	const VALID_PROCESS = ['serverside', 'query', 'editor'];
	private static object $config;

	/**
	 * Sets up and validates the configuration for a table event
	 *
	 * This method:
	 * 1. Extracts request parameters (app, event, process) from the URL
	 * 2. Validates that the process type is supported
	 * 3. Loads the table event configuration from the application config
	 * 4. Validates the complete configuration
	 *
	 * @throws ControllerException If the process parameter is invalid or missing
	 * @return void
	 */
	private static function selectConfig(): void
	{
		// Create configuration object and extract request parameters
		self::$config = new stdClass();
		self::$config->app = Request::fromUrl('app');
		self::$config->event = Request::fromUrl('event');
		self::$config->process = Request::fromUrl('process');

		// Validate process parameter
		if (
			self::$config->process === '/'
			|| self::$config->process === false
			|| !in_array(self::$config->process, self::VALID_PROCESS)
		) {
			throw new ControllerException(
				'controller\tableEvent\selectConfig\param',
				[
					'message' => 'process',
					'details' => Request::uri()
				],
				ControllerException::TYPE_API_EXCEPTION
			);
		}

		// Load table event configuration and validate
		self::$config->table = Config::getConfig(self::$config->app)->tableEvent->{self::$config->event};
		new TableEventValidate(self::$config);
	}

	/**
	 * Main entry point for processing table events
	 *
	 * This method handles all table event requests by:
	 * 1. Setting up and validating the configuration
	 * 2. Dispatching the request to the appropriate handler based on process type:
	 *    - serverside: For DataTables server-side processing
	 *    - query: For executing database queries (insert, update, delete)
	 *    - editor: For generating form interfaces (add, edit, show, delete)
	 *
	 * @return mixed The result from the appropriate handler
	 * @throws ControllerException If configuration validation fails
	 */
	public static function doTableEvent(): mixed
	{
		self::selectConfig();

		return match (self::$config->process) {
			'serverside' => ServerSide::serverSide(self::$config),
			'query' => TableQuery::tableQuery(self::$config),
			'editor' => TableEditor::tableEditor(self::$config)
		};
	}
}
