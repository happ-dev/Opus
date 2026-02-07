<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-02-06 10:00:04
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-02-07 13:37:16
 **/

namespace Opus\storage\db;

use PDOStatement;
use stdClass;

interface InterfaceDb
{
	/**
	 * Function tests the connection to the database.
	 *
	 * @return bool
	 */
	public function dbConnectTest(): bool;

	/**
	 * Function sets fetch mode in the PDO object
	 *
	 * @param PDOStatement $objPDO PDO instance
	 * @param int $mode One of the PDO::FETCH_* constants
	 * @link https://www.php.net/manual/en/pdo.constants.php#pdo.constants.fetch-default
	 * @return bool
	 * @throws StorageException
	 */
	public function dbSetFetchMode(PDOStatement $objPDO, int $mode): bool;

	/**
	 * Execute an SQL statement and return the number of affected rows
	 *
	 * @param string $query
	 * @return int|false
	 * @throws StorageException
	 */
	public function dbExec(string $query): int|false;

	/**
	 * Execute a query
	 *
	 * @param string $query
	 * @return PDOStatement|false
	 * @throws StorageException
	 */
	public function dbQuery(string $query): PDOStatement|false;

	/**
	 * Creates a prepared statement and sends a request to execute
	 * with given parameters, waits for the result.
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
	 * @link https://www.php.net/manual/en/pdo.constants.php
	 * @link https://www.php.net/manual/en/pdo.constants.php#pdo.constants.fetch-default
	 * @return array
	 * @throws StorageException
	 */
	public function dbExecute(array $params): array;

	/**
	 * Creates and commit transactions
	 *
	 * @param array $params
	 * 	- [
	 * 			[
	 * 				'query' => 'sql query',									// all other parameters will be ignored
	 * 				'prepare' => 'sql prepare query',
	 * 				'params' => [':value_1', ':value_2', ':value_n'],		// parameter is not required, if not specified,
	 * 																		// they will be searched for in the query
	 * 				'pdoTypes' => [PDO::PARAM_STR, PDO::PARAM_STR, ...],	// parameter is not required by default
	 * 				':value_1' => [],
	 * 				':value_2' => [],
	 * 				...
	 * 				':value_n' => []
	 * 			],
	 * 			[
	 * 				'query' => 'sql query',
	 * 				'prepare' => 'sql prepare query',
	 * 				'params' => [':value_1', ':value_2', ':value_n'],
	 * 				'pdoTypes' => [PDO::PARAM_STR, PDO::PARAM_STR, ...],
	 * 				':value_1' => [],
	 * 				':value_2' => [],
	 * 				...
	 * 				':value_n' => []
	 * 			],
	 * 			...
	 * 	  ]
	 * @return stdClass
	 * @throws StorageException
	 */
	public function dbTransactions(array $params): stdClass;

	/**
	 * Executes a query using a cursor for memory-efficient processing of large result sets
	 *
	 * @param string $query The SQL query to execute
	 * @param string $cursor The name of the cursor
	 * @param int $batchSize The number of rows to fetch in each batch, default is 1000
	 * @return array The fetched results
	 * @throws StorageException If cursor operations fail
	 */
	public function dbCursor(string $query, string $cursor, int $batchSize): array;

	/**
	 * Fetches the remaining rows from a result set
	 *
	 * @param PDOStatement $objPDO
	 * @return array
	 */
	public function dbResult(PDOStatement $objPDO): array;

	/**
	 * Fetches the remaining rows from a result as associative array
	 *
	 * @param string $query
	 * @return array
	 * @throws StorageException
	 */
	public function dbArrayResult(string $query): array;

	/**
	 * Fetches the remaining rows from a result as numeric array
	 *
	 * @param string $query
	 * @return array
	 * @throws StorageException
	 */
	public function dbArrayNumResult(string $query): array;

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
	public function dbGetTableDetails(
		string $scheme,
		string $table,
		?array $columns,
		?string $conf = null,
		string $storageException
	): array;

	/**
	 * Quotes a string for use in a query
	 *
	 * @param string $string The string to be quoted
	 * @return string The quoted string
	 * @throws StorageException If quoting fails
	 */
	public function dbQuote(string $string): string;
}
