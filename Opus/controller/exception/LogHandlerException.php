<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-02-07 14:21:00
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-02-14 08:22:31
 **/

namespace Opus\controller\exception;

use Opus\storage\db\Db;

class LogHandlerException
{
	const LOG_TYPE_ERROR = 'ERROR';
	const LOG_TYPE_WARNING = 'WARNING';
	const LOG_TYPE_APP = 'APP-INFO';
	const LOG_TYPE_API = 'API-INFO';
	const LOG_TYPE_CLI = 'CLI-INFO';

	/**
	 * Creates transaction parameters array for log insertion
	 *
	 * @param string $errorPath Path to the error location
	 * @param string $exMessage Exception message
	 * @param string|null $exDetails Exception details
	 * @param string $logType Log type constant (LOG_TYPE_ERROR, LOG_TYPE_WARNING, etc.)
	 * @return array Transaction parameters for database insertion
	 */
	final public static function createLogTransactionParams(
		string $errorPath,
		string $exMessage,
		?string $exDetails,
		string $logType
	): array {
		return [
			'prepare' => <<<SQL
				INSERT INTO
					logs.logs(logTime, logType, logPath, logMessage, logDetails)
				VALUES ((SELECT now()), :logType, :logPath, :logMessage, :logDetails)
			SQL,
			'params' => [':logType', ':logPath', ':logMessage', ':logDetails'],
			':logType' => [$logType],
			':logPath' => [$errorPath],
			':logMessage' => [self::cleanLog($exMessage)],
			':logDetails' => [(!is_null($exDetails)) ? $exDetails : null]
		];
	}

	/**
	 * Function saves program logs to the database
	 * table logs.logs
	 *
	 * @param string $errorPath - path to the error
	 * @param string $exMessage - exception message
	 * @param string $exDetails - exception details
	 * @param string $logType:
	 * 		- LogHandlerException::LOG_TYPE_ERROR
	 * 		- LogHandlerException::LOG_TYPE_WARNING
	 * 		- LogHandlerException::LOG_TYPE_APP
	 * 		- LogHandlerException::LOG_TYPE_API
	 * 		- LogHandlerException::LOG_TYPE_CLI
	 * @param string $exceptionType:
	 * 		- StorageException::TYPE_PAGE_EXCEPTION
	 * 		- StorageException::TYPE_ASYNC_PAGE_EXCEPTION
	 * 		- StorageException::TYPE_API_EXCEPTION
	 * 		- StorageException::TYPE_CLI_EXCEPTION
	 * @param string $exceptionType - type of the exception
	 * @return void
	 * @throws Exception from Db
	 */
	final public static function insertLog(
		string $errorPath,
		string $exMessage,
		?string $exDetails,
		string $logType,
		string $exceptionType
	): void {
		Db::dbTransactions(
			[
				self::createLogTransactionParams($errorPath, $exMessage, $exDetails, $logType),
				null,
				$exceptionType
			]
		);
	}

	/**
	 * Function cleans the log from the tags
	 *
	 * @param string $text - log text
	 * @return string - cleaned log text
	 */
	private static function cleanLog(string $text): string
	{
		$varText = strip_tags($text);
		return preg_replace(
			'/\e[[][A-Za-z0-9];?[0-9]*m?/',
			'',
			$varText
		);
	}
}
