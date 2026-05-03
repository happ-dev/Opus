<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 14:10:10
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 14:13:52
 **/

namespace Opus\controller\query\table;

use Opus\controller\query\AbstractQueryValidate;
use Opus\controller\query\InterfaceQuery;
use Opus\controller\exception\ControllerException;

class Table extends AbstractQueryValidate implements InterfaceQuery
{
	public function __construct(array $conf)
	{
		$this->config = $conf;

		// Check if mode is valid
		in_array($this->config['mode'], self::QVALID_TABLE_MODE)
			?: throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['Table::mode', $this->config['mode']]],
				$this->config['exception']
			);

		$this->validateForeignKey();
		$this->validateDropTable();
		$this->validateTable();
		$this->validateColumns('create', true);
		$this->validateGrantTable();
	}

	/**
	 * Creates a database query for table operations
	 *
	 * This method generates SQL statements for creating, dropping, and setting permissions
	 * on a database table based on the configuration. The output format depends on the mode:
	 * - MODE_TRANSACTION: Returns an array of query objects for transaction processing
	 * - MODE_QUERY: Returns a concatenated string of all SQL statements
	 *
	 * @return string|array|object SQL query string or array of query objects
	 *                            - string: In MODE_QUERY, concatenated SQL statements
	 *                            - array: In MODE_TRANSACTION, array of query objects with format [['query' => SQL], ...]
	 */
	public function createQuery(): string|array|object
	{
		return match ($this->config['mode']) {
			self::MODE_TRANSACTION => array_filter([
				['query' => $this->dropTable()],
				['query' => $this->createTable()],
				['query' => $this->grantTable()],
				['query' => $this->grantSequence()]
			], fn($item) => $item['query'] !== null),
			self::MODE_QUERY => $this->dropTable() . $this->createTable() . $this->grantTable() . $this->grantSequence()
		};
	}

	/**
	 * Generates SQL for foreign key constraints
	 *
	 * This method creates SQL fragments for foreign key constraints
	 * based on the configuration. If no foreign keys are defined,
	 * it returns null.
	 *
	 * @return string|null SQL fragment for foreign key constraints or null if none defined
	 */
	private function foreignKey(): ?string
	{
		// Return null if no foreign keys are defined
		if ($this->config['foreign_key'] === false) {
			return null;
		}

		$query = '';

		// Generate constraint SQL for each foreign key
		foreach ($this->config['foreign_key'] as $value) {
			$query .= 'CONSTRAINT ' . $value['key']
				. '_FK FOREIGN KEY (' . $value['key'] . ') REFERENCES '
				. $value['table'] . '(' . $value['id'] . '), ';
		}

		return $query;
	}

	/**
	 * Generates SQL for dropping a table if it exists
	 *
	 * This method creates an SQL statement to drop the table if the drop_table
	 * configuration option is set to true. The CASCADE option ensures that
	 * dependent objects (like views or foreign keys) are also dropped.
	 *
	 * @return string|null SQL statement to drop the table or null if drop_table is false
	 */
	private function dropTable(): ?string
	{
		return ($this->config['drop_table'] === true)
			? 'DROP TABLE IF EXISTS ' . $this->config['table'] . ' CASCADE;'
			: null;
	}

	/**
	 * Generates SQL for creating a new table
	 *
	 * This method builds a CREATE TABLE SQL statement based on the configuration,
	 * including column definitions, foreign key constraints, and a primary key
	 * constraint using the first column as the primary key.
	 *
	 * @return string SQL statement to create the table
	 */
	private function createTable(): ?string
	{
		// Start the CREATE TABLE statement
		$query = 'CREATE TABLE ' . $this->config['table'] . ' (';

		// Add columns definitions
		foreach ($this->config['columns'] as $value) {
			$query .= $value['name'] . ' ' . strtoupper($value['type']) . ', ';
		}

		// Add foreign key constraints if any
		$query .= $this->foreignKey();

		// Extract table name from schema.table format
		list($scheme, $table) = explode('.', $this->config['table']);

		// Add primary key constraint using the first column
		$query .= 'CONSTRAINT pk_' . $table . ' PRIMARY KEY(' . $this->config['columns'][0]['name'] . '));';

		return $query;
	}

	/**
	 * Generates SQL for granting table permissions
	 *
	 * This method creates GRANT statements for table permissions based on the configuration.
	 * For each user in the grant configuration, it generates a GRANT statement with the
	 * specified permissions for the table.
	 *
	 * @return string|null SQL statements for granting table permissions or null if no grants defined
	 */
	private function grantTable(): ?string
	{
		$table = '';

		// Generate GRANT statements for each user
		foreach ($this->config['grant'] as $value) {
			// Start GRANT statements
			$table .= 'GRANT ';

			// Count permissions to handle comma placement
			$countTable = count($value['table']);

			// Build table permissions list
			for ($i = 0; $i < $countTable; $i++) {
				$table .= ($i != $countTable - 1) ? $value['table'][$i] . ', ' : $value['table'][$i];
			}

			// Complete the GRANT statements
			$table .= ' ON TABLE ' . $this->config['table'] . ' TO ' . $value['user'] . ';';
		}

		return $table;
	}

	/**
	 * Generates SQL for granting sequence permissions
	 *
	 * This method creates GRANT statements for sequence permissions based on the configuration.
	 * For each user in the grant configuration, it generates a GRANT statement with the
	 * specified permissions for the sequence associated with the table's primary key.
	 *
	 * @return string|null SQL statements for granting sequence permissions or null if no grants defined
	 */
	private function grantSequence(): ?string
	{
		$seq = '';

		// Generate GRANT statements for each user
		foreach ($this->config['grant'] as $value) {
			// Start GRANT statements
			$seq .= 'GRANT ';

			// Count permissions to handle comma placement
			$countSequence = count($value['sequence']);

			// Build sequence permissions list
			for ($j = 0; $j < $countSequence; $j++) {
				$seq .= ($j != $countSequence - 1) ? $value['sequence'][$j] . ', ' : $value['sequence'][$j];
			}

			// Complete the GRANT statements
			$seq .= ' ON SEQUENCE ' . $this->config['table'] . '_' . $this->config['columns'][0]['name'] . '_seq TO ' . $value['user'] . ';';
		}

		return $seq;
	}
}
