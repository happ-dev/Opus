<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-20 16:36:03
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-20 16:45:22
 **/

namespace Opus\controller\event\serverside;

use Opus\config\Config;

/**
 * Handles server-side DataTables processing using strategy pattern
 *
 * Selects the appropriate database strategy based on the storage type
 * defined in global configuration and delegates query execution to it.
 *
 * @package Opus\controller\event\serverside
 */
class ServerSide
{
	private static object $config;

	public function __construct(public readonly AbstractServerSide $strategy) {}

	/**
	 * Entry point for server-side DataTables processing
	 *
	 * Selects the appropriate database strategy and delegates
	 * the server-side processing to it.
	 *
	 * @param object $config Table event configuration containing table and db settings
	 * @return mixed Result from the strategy's serverSide() method
	 */
	public static function serverSide(object $config): mixed
	{
		self::$config = $config;
		$strategy = self::selectStrategy();
		return $strategy->strategy->serverSide();
	}

	/**
	 * Selects the database strategy based on storage type from global configuration
	 *
	 * @return object ServerSide instance with the appropriate database strategy
	 */
	private static function selectStrategy(): object
	{
		return match (Config::getConfig('storage')->{self::$config->table->db}->type) {
			'pgsql' => new ServerSide(new PostgreServerSide(self::$config->table))
		};
	}
}
