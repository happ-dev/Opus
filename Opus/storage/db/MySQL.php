<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-02-07 13:34:25
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-02-07 13:37:36
 **/

namespace Opus\storage\db;

use PDO;
use PDOException;
use Opus\config\Config;
use Opus\config\EncryptStorageConfig;
use Opus\storage\exception\StorageException;

class MySQL extends AbstractDb
{
	/**
	 * Initializes MySQL database connection parameters
	 *
	 * @param string $conf Configuration name from storage config
	 * @param string $storageException Exception path for error handling
	 */
	public function __construct($conf, string $storageException)
	{
		$this->host = EncryptStorageConfig::decrypt(Config::getConfig('storage')->{$conf}->host);
		$this->port = EncryptStorageConfig::decrypt(Config::getConfig('storage')->{$conf}->port);
		$this->user = EncryptStorageConfig::decrypt(Config::getConfig('storage')->{$conf}->user);
		$this->pass = EncryptStorageConfig::decrypt(Config::getConfig('storage')->{$conf}->pass);
		$this->name = Config::getConfig('storage')->{$conf}->name;
		$this->encoding = isset(Config::getConfig('storage')->{$conf}->encoding)
			? Config::getConfig('storage')->{$conf}->encoding
			: 'utf8mb4';
		$this->storageException = $storageException;
	}

	protected function dbConnect(bool $test = false): bool
	{
		$connectString = 'mysql:host=' . $this->host
			. ';port=' . $this->port
			. ';dbname=' . $this->name
			. ';charset=' . $this->encoding;

		try {
			$this->handle = new PDO(
				$connectString,
				$this->user,
				$this->pass,
				[
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->encoding}",
					PDO::ATTR_EMULATE_PREPARES => false,
				]
			);
			return true;
		} catch (PDOException $event) {
			return match ($test) {
				true => false,
				false => throw new StorageException(
					'storage\db\dbConnect',
					[
						'message' => $event->getMessage(),
						'details' => (is_object($this->handle)) ? print_r($this->handle->errorInfo(), true) : null
					],
					$this->storageException
				)
			};
		}
	}

	public function dbGetTableDetails(
		string $scheme,
		string $table,
		?array $columns,
		?string $conf = null,
		string $storageException
	): array {
		$columnFilter = '';

		// Connecting to database,
		// Validate PDO connection is in dbConnect function
		$this->dbConnect();

		if (is_array($columns) && !empty($columns)) {
			$escapedColumns = array_map(
				fn($col) => $this->handle->quote($col),
				$columns
			);
			$columnFilter = "AND COLUMN_NAME IN (" . implode(',', $escapedColumns) . ")";
		}

		$query = "SELECT COLUMN_NAME as attname, ORDINAL_POSITION as attnum, DATA_TYPE as type, CHARACTER_MAXIMUM_LENGTH as atttypmod,
			IS_NULLABLE = 'NO' as attnotnull, COLUMN_DEFAULT IS NOT NULL as atthasdef, COLUMN_DEFAULT as adsrc, NULL as attstattarget,
			NULL as attstorage, NULL as typstorage, EXTRA = 'auto_increment' as attisserial, COLUMN_COMMENT as comment
		FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table $columnFilter ORDER BY ORDINAL_POSITION;";

		try {
			$stmt = $this->handle->prepare($query);
			$stmt->execute([
				':schema' => $scheme,
				':table' => $table
			]);
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $event) {
			throw new StorageException(
				'storage\db\dbGetTableDetails',
				[
					'message' => $event->getMessage(),
					'details' => (is_object($this->handle)) ? print_r($this->handle->errorInfo(), true) : null
				],
				$this->storageException
			);
		}
	}
}
