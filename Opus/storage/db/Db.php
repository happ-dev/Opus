<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-02-07 13:38:43
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-02-07 14:18:43
 **/

namespace Opus\storage\db;

use PDO;
use PDOStatement;
use stdClass;
use Opus\config\Config;
use Opus\storage\exception\StorageException;

class Db
{
	/**
	 * Initializes database wrapper with specific database strategy
	 *
	 * @param InterfaceDb $strategy Database implementation strategy (PostgreSQL, MySQL, etc.)
	 */
	public function __construct(public InterfaceDb $strategy) {}

	/**
	 * Selects and instantiates appropriate database strategy based on configuration
	 *
	 * @param string|null $conf Configuration name from storage config, uses default if null
	 * @param string $storageException Exception path for error handling
	 * @return Db Database instance with selected strategy
	 * @throws StorageException If configuration not found or database type unsupported
	 */
	private static function selectStrategy(?string $conf, string $storageException)
	{
		// Get default configuration if not specified
		$config = (is_null($conf)) ? Config::getConfig('storage')->default : $conf;

		if (!isset(Config::getConfig('storage')->{$config})) {
			throw new StorageException(
				'storage\db\selectStrategy',
				['message' => $conf],
				$storageException
			);
		}

		return match (Config::getConfig('storage')->{$config}->type) {
			'pgsql' => new Db(new Postgre($config, $storageException)),
			'mysql' => new Db(new MySQL($config, $storageException)),
			default => throw new StorageException(
				'storage\db\selectStrategy\type',
				['message' => $conf . '\'' . Config::getConfig('storage')->{$config}->type],
				$storageException
			),
		};
	}

	/**
	 * Tests database connection using specified configuration
	 *
	 * @param string|null $conf Configuration name from storage config, uses default if null
	 * @param string $storageException Exception type for error handling
	 * @return bool True if connection succeeds, false otherwise
	 * @throws StorageException If configuration not found or connection fails
	 */
	public static function dbConnectTest(?string $conf = null, string $storageException = StorageException::TYPE_API_EXCEPTION): bool
	{
		$strategy = self::selectStrategy($conf, $storageException);
		return $strategy->strategy->dbConnectTest();
	}

	/**
	 * Function sets fetch mode in the PDO object
	 *
	 * @param PDOStatement $obj PDO instance
	 * @param string|null $conf Configuration name, if not specified, the default configuration is used.
	 * @param int $mode One of the PDO::FETCH_* constants
	 * @param string $storageException Exception type.
	 * @link https://www.php.net/manual/en/pdo.constants.php#pdo.constants.fetch-default
	 * @return bool
	 * @throws StorageException
	 */
	public static function dbSetFetchMode(
		PDOStatement $obj,
		?string $conf = null,
		int $mode = PDO::FETCH_ASSOC,
		string $storageException = StorageException::TYPE_API_EXCEPTION
	): bool {
		$strategy = self::selectStrategy($conf, $storageException);
		return $strategy->strategy->dbSetFetchMode($obj, $mode);
	}

	/**
	 * Function executes a query and returns the number of affected rows.
	 *
	 * @param string $query The query to execute.
	 * @param string|null $conf Configuration name, if not specified, the default configuration is used.
	 * @param string $storageException Exception type.
	 * @return int|false Returns the number of affected rows on success, false on failure.
	 * @throws StorageException
	 */
	public static function dbExec(
		string $query,
		?string $conf = null,
		string $storageException = StorageException::TYPE_API_EXCEPTION
	): int|false {
		$strategy = self::selectStrategy($conf, $storageException);
		return $strategy->strategy->dbExec($query);
	}

	/**
	 * Function executes a query and returns a PDOStatement object.
	 *
	 * @param string $query The query to execute.
	 * @param string|null $conf Configuration name, if not specified, the default configuration is used.
	 * @param string $storageException Exception type.
	 * @return PDOStatement|false Returns a PDOStatement object on success, false on failure.
	 * @throws StorageException
	 */
	public static function dbQuery(
		string $query,
		?string $conf = null,
		string $storageException = StorageException::TYPE_API_EXCEPTION
	): PDOStatement|false {
		$strategy = self::selectStrategy($conf, $storageException);
		return $strategy->strategy->dbQuery($query);
	}

	/**
	 * Creates a prepared statement and executes with given parameters
	 *
	 * @param array $params
	 * 	- [
	 * 		'prepare' => 'sql prepare query',
	 * 		'bindType' => 'bindParam | bindValue'					// parameter is not required, default bindParam
	 * 		'fetchMode' => PDO::FETCH_ASSOC							// parameter is not required, default PDO::FETCH_ASSOC
	 * 		'params' => [':value_1', ':value_2', ':value_n'],		// parameter is not required, if not specified,
	 * 																// they will be searched for in the query
	 * 		'pdoTypes' => [PDO::PARAM_STR, PDO::PARAM_STR, ...],	// parameter is not required, default PDO::PARAM_STR
	 * 		':value_1' => 'string',
	 * 		':value_2' => 'string',
	 * 		...
	 * 		':value_n' => 'string'
	 * 	  ]
	 * @param string|null $conf Configuration name from storage config, uses default if null
	 * @param string $storageException Exception type for error handling
	 * @link https://www.php.net/manual/en/pdo.constants.php
	 * @link https://www.php.net/manual/en/pdo.constants.php#pdo.constants.fetch-default
	 * @return array Query results
	 * @throws StorageException If configuration not found or query execution fails
	 */
	public static function dbExecute(
		array $params,
		?string $conf = null,
		string $storageException = StorageException::TYPE_API_EXCEPTION
	): array {
		$strategy = self::selectStrategy($conf, $storageException);
		return $strategy->strategy->dbExecute($params);
	}

	/**
	 * Creates and commit transactions
	 *
	 * @param array $params
	 * 		- [
	 * 				[
	 * 					'query' => 'sql query',									// all other parameters will be ignored
	 * 					'prepare' => 'sql prepare query',
	 * 					'params' => [':value_1', ':value_2', ':value_n'],
	 * 					'pdoTypes' => [PDO::PARAM_STR, PDO::PARAM_STR, ...],	// parameter is not required by default
	 * 					':value_1' => [],
	 * 					':value_2' => [],
	 * 					...
	 * 					':value_n' => []
	 * 				],
	 * 				[
	 * 					'query' => 'sql query',
	 * 					'prepare' => 'sql prepare query',
	 * 					'params' => [':value_1', ':value_2', ':value_n'],
	 * 					'pdoTypes' => [PDO::PARAM_STR, PDO::PARAM_STR, ...],
	 * 					':value_1' => [],
	 * 					':value_2' => [],
	 * 					...
	 * 					':value_n' => []
	 * 				],
	 * 				...
	 * 		  ]
	 * @param string|null $conf Configuration name, if not specified, the default configuration is used.
	 * @param string $storageException Exception type.
	 * @return stdClass
	 * @throws StorageException
	 */
	public static function dbTransactions(
		array $params,
		?string $conf = null,
		string $storageException = StorageException::TYPE_API_EXCEPTION
	): stdClass {
		$strategy = self::selectStrategy($conf, $storageException);
		return $strategy->strategy->dbTransactions($params);
	}

	/**
	 * Executes a query using a cursor for memory-efficient processing of large result sets
	 *
	 * @param string $query The query to execute.
	 * @param string $cursor The name of the cursor to use.
	 * @param string|null $conf Configuration name, if not specified, the default configuration is used.
	 * @param int $batchSize The number of rows to fetch in each batch.
	 * @param string $storageException Exception type.
	 * @return array
	 * @throws StorageException If cursor operations fail
	 */
	public static function dbCursor(
		string $query,
		string $cursor,
		?string $conf = null,
		int $batchSize = 1000,
		string $storageException = StorageException::TYPE_API_EXCEPTION
	): array {
		$strategy = self::selectStrategy($conf, $storageException);
		return $strategy->strategy->dbCursor($query, $cursor, $batchSize);
	}

	/**
	 * Function returns the result of a query as an array.
	 * Use dbSetFetchMode to set returns result before!!!
	 *
	 * @param PDOStatement $objPDO PDOStatement object
	 * @param string|null $conf Configuration name, if not specified, the default configuration is used.
	 * @param string $storageException Exception type.
	 * @return array
	 * @throws StorageException
	 */
	public static function dbResult(
		PDOStatement $objPDO,
		?string $conf = null,
		string $storageException = StorageException::TYPE_API_EXCEPTION
	): array {
		$strategy = self::selectStrategy($conf, $storageException);
		return $strategy->strategy->dbResult($objPDO);
	}

	/**
	 * Fetches the remaining rows from a result as associative array
	 *
	 * @param string $query The query to execute.
	 * @param string|null $conf Configuration name, if not specified, the default configuration is used.
	 * @param string $storageException Exception type.
	 * @return array
	 * @throws StorageException
	 */
	public static function dbArrayResult(
		string $query,
		?string $conf = null,
		string $storageException = StorageException::TYPE_API_EXCEPTION
	): array {
		$strategy = self::selectStrategy($conf, $storageException);
		return $strategy->strategy->dbArrayResult($query);
	}

	/**
	 * Fetches the remaining rows from a result as numeric array
	 *
	 * @param string $query The query to execute.
	 * @param string|null $conf Configuration name, if not specified, the default configuration is used.
	 * @param string $storageException Exception type.
	 * @return array
	 * @throws StorageException
	 */
	public static function dbArrayNumResult(
		string $query,
		?string $conf = null,
		string $storageException = StorageException::TYPE_API_EXCEPTION
	): array {
		$strategy = self::selectStrategy($conf, $storageException);
		return $strategy->strategy->dbArrayNumResult($query);
	}

	/**
	 * Retrieves detailed information about table columns from PostgreSQL system catalogs
	 *
	 * This method queries the PostgreSQL system catalogs to retrieve detailed information
	 * about columns in a specified table, including:
	 * - Column names and positions
	 * - Data types and type modifiers
	 * - NOT NULL constraints
	 * - Default values
	 * - Serial/identity columns
	 * - Column comments/descriptions
	 *
	 * @param string $scheme The database schema name
	 * @param string $table The table name
	 * @param array|null $columns Specific columns to retrieve (null for all columns)
	 * @param string|null $conf Database configuration name (null for default)
	 * @param string $storageException Exception type to throw on errors
	 * @return array Array of column details from the database
	 * @throws StorageException If database access fails
	 */
	public static function dbGetTableDetails(
		string $scheme,
		string $table,
		?array $columns,
		?string $conf = null,
		string $storageException = StorageException::TYPE_API_EXCEPTION
	) {
		$strategy = self::selectStrategy($conf, $storageException);
		return $strategy->strategy->dbGetTableDetails($scheme, $table, $columns, $conf, $storageException);
	}

	/**
	 * Quotes a string for use in a query
	 *
	 * @param string $string The string to be quoted
	 * @param string|null $conf Configuration name, if not specified, the default configuration is used
	 * @param string $storageException Exception type
	 * @return string The quoted string
	 * @throws StorageException If quoting fails
	 */
	public static function dbQuote(
		string $string,
		?string $conf = null,
		string $storageException = StorageException::TYPE_API_EXCEPTION
	): string {
		$strategy = self::selectStrategy($conf, $storageException);
		return $strategy->strategy->dbQuote($string);
	}
}
