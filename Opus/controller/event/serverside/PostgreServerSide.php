<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-20 16:03:27
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-20 16:29:29
 **/

namespace Opus\controller\event\serverside;

use PDO;
use Opus\storage\db\Db;
use Opus\storage\exception\StorageException;

class PostgreServerSide extends AbstractServerSide
{
	public function __construct(object $config)
	{
		$this->config = $config;
	}

	/**
	 * Processes server-side DataTables requests and outputs JSON response
	 *
	 * This method handles the complete server-side processing workflow:
	 * 1. Sets up column information for the table
	 * 2. Builds SQL query components (JOIN, WHERE, ORDER BY, LIMIT)
	 * 3. Executes the main data query with filtering, sorting, and pagination
	 * 4. Retrieves record counts for pagination information
	 * 5. Outputs a JSON response in the format expected by DataTables
	 *
	 * The JSON response includes:
	 * - draw: Counter to prevent XSS attacks
	 * - recordsTotal: Total number of records before filtering
	 * - recordsFiltered: Number of records after filtering
	 * - data: The actual data rows
	 *
	 * @return void
	 * @throws StorageException If database access fails
	 */
	public function serverSide(): void
	{
		// Set up column information
		$this->setColumnsName();
		$request = $_GET;

		// Build the SQL query components
		$join = $this->setJoin();
		$limit = $this->setLimit($request, $this->config->columns);
		$order = $this->setOrder($request, $this->config->columns);
		$where = $this->setWhere($request, $this->config->columns);

		// Execute queries using match to handle the different query types
		$results = match (true) {
			true => (function () use ($join, $where, $order, $limit) {
				// Main query to get the data
				$objPDOData = Db::dbQuery(
					'SELECT ' . implode(', ', $this->pluck($this->config->columns, 'db')) . ' FROM ' . $this->config->table . $join . $where . $order . $limit,
					$this->config->db,
					StorageException::TYPE_API_EXCEPTION
				);
				Db::dbSetFetchMode(
					$objPDOData,
					$this->config->db,
					PDO::FETCH_NUM,
					StorageException::TYPE_API_EXCEPTION
				);
				$data = Db::dbResult(
					$objPDOData,
					$this->config->db,
					StorageException::TYPE_API_EXCEPTION
				);

				// Query for filtered record count
				$objPDORecordsFiltered = Db::dbQuery(
					'SELECT COUNT(\'' . $this->config->primaryKey . '\') FROM ' . $this->config->table . $join . $where,
					$this->config->db,
					StorageException::TYPE_API_EXCEPTION
				);
				Db::dbSetFetchMode(
					$objPDORecordsFiltered,
					$this->config->db,
					PDO::FETCH_NUM,
					StorageException::TYPE_API_EXCEPTION
				);
				$recordsFiltered = Db::dbResult(
					$objPDORecordsFiltered,
					$this->config->db,
					StorageException::TYPE_API_EXCEPTION
				)[0][0];

				// Query for total record count
				$objPDORecordsTotal = Db::dbQuery(
					'SELECT COUNT(\'' . $this->config->primaryKey . '\') FROM ' . $this->config->table . $join,
					$this->config->db,
					StorageException::TYPE_API_EXCEPTION
				);
				Db::dbSetFetchMode(
					$objPDORecordsTotal,
					$this->config->db,
					PDO::FETCH_NUM,
					StorageException::TYPE_API_EXCEPTION
				);
				$recordsTotal = Db::dbResult(
					$objPDORecordsTotal,
					$this->config->db,
					StorageException::TYPE_API_EXCEPTION
				)[0][0];

				return [
					'data' => $data,
					'recordsFiltered' => $recordsFiltered,
					'recordsTotal' => $recordsTotal
				];
			})()
		};

		// Format and output the JSON response
		echo json_encode([
			'draw' => isset($request['draw']) ? intval($request['draw']) : 0,
			'recordsTotal' => intval($results['recordsTotal']),
			'recordsFiltered' => intval($results['recordsFiltered']),
			'data' => $results['data']
		]);
	}

	/**
	 * Escapes a string for use in PostgreSQL queries using PDO
	 *
	 * @param string $value The string to escape
	 * @return string The escaped string without surrounding quotes
	 */
	private function escapeString(string $value): string
	{
		$quoted = Db::dbQuote($value, $this->config->db, StorageException::TYPE_API_EXCEPTION);
		// Remove the surrounding quotes that PDO::quote adds
		return substr($quoted, 1, -1);
	}

	/**
	 * Retrieves and sets column information for DataTables
	 *
	 * This method determines the columns to be used in the DataTable:
	 * - If columns are already defined in the configuration, it keeps them as is
	 * - Otherwise, it queries the PostgreSQL system catalogs to get column information
	 *   and creates DataTables column definitions automatically
	 *
	 * The column definitions map database column names to DataTables column indexes.
	 *
	 * @return void
	 * @throws StorageException If database access fails
	 */
	private function setColumnsName()
	{
		// Skip if columns are already defined
		if ($this->config->columns !== false) {
			return;
		}

		// Initialize columns array
		$this->config->columns = [];

		// Query PostgreSQL system catalogs for column information
		$objPDO = Db::dbQuery(
			'SELECT attrelid::regclass, attnum, attname FROM pg_attribute '
				. 'WHERE attrelid = \'' . $this->config->table . '\'::regclass '
				. 'AND attnum > 0 AND NOT attisdropped ORDER BY attnum',
			$this->config->db,
			StorageException::TYPE_API_EXCEPTION
		);

		// Set fetch mode to associative array
		Db::dbSetFetchMode(
			$objPDO,
			$this->config->db,
			PDO::FETCH_ASSOC,
			StorageException::TYPE_API_EXCEPTION
		);

		// Execute query and get results
		$result = Db::dbResult(
			$objPDO,
			$this->config->db,
			StorageException::TYPE_API_EXCEPTION
		);

		// Create DataTables column definitions
		foreach ($result as $key => $value) {
			$this->config->columns[] = (object) [
				'db' => $value['attname'],  // Database column name
				'dt' => $key                // DataTables column index
			];
		}

		// Clean up
		unset($objPDO, $result);
	}

	/**
	 * Extracts a specific property from each object in an array
	 *
	 * This utility method pulls a particular property from each object in an array,
	 * creating a new array with just those property values. It skips any objects
	 * where the property is empty (except for zero values).
	 *
	 * @param array $columns Array of objects to extract data from
	 * @param string $prop Property name to extract
	 * @return array Array of extracted property values, preserving original keys
	 */
	private function pluck(array $columns, string $prop): array
	{
		$out = [];

		foreach ($columns as $index => $value) {
			// Skip empty values, but keep zero values
			if (empty($value->$prop) && $value->$prop !== 0) {
				continue;
			}

			$out[$index] = $value->$prop;
		}

		return $out;
	}

	/**
	 * Generates the JOIN clause for the SQL query
	 *
	 * This method checks if a JOIN clause is defined in the configuration.
	 * If a JOIN is specified, it returns the JOIN clause with surrounding spaces.
	 * If no JOIN is specified, it returns a single space.
	 *
	 * @return string JOIN clause with surrounding spaces or a single space if no JOIN
	 */
	private function setJoin(): string
	{
		return match (true) {
			$this->config->join !== false => ' ' . $this->config->join . ' ',
			default => ' '
		};
	}

	/**
	 * Generates the LIMIT and OFFSET clause for pagination
	 *
	 * This method constructs the SQL pagination clause based on DataTables request parameters:
	 * - 'start': The starting row number (used for OFFSET)
	 * - 'length': The number of rows to display (used for LIMIT)
	 *
	 * If length is -1, no limit is applied (showing all records).
	 *
	 * @param array $request Data sent to server by DataTables
	 * @param array $columns Column information array
	 * @return string|null SQL LIMIT and OFFSET clause or null if no pagination requested
	 */
	private function setLimit(array $request, array $columns): ?string
	{
		return match (true) {
			isset($request['start']) && $request['length'] != -1 =>
			' LIMIT ' . intval($request['length']) . ' OFFSET ' . intval($request['start']),
			default => null
		};
	}

	/**
	 * Extracts the column name from an aliased column expression
	 *
	 * This method handles SQL column expressions that use the AS keyword for aliasing.
	 * When ordering by such columns, we need to use the alias name rather than the
	 * full expression.
	 *
	 * For example, with "CONCAT(first_name, ' ', last_name) AS full_name",
	 * this method returns "full_name" for ordering purposes.
	 *
	 * @param string $column The column expression that may contain an AS clause
	 * @return string The column name to use for ordering (alias if present, original column otherwise)
	 */
	private function setOrderColumnAs(string $column): string
	{
		return match (true) {
			preg_match('/ AS /i', $column) === 1 => trim(explode('AS', $column)[1]),
			default => $column
		};
	}

	/**
	 * Generates the ORDER BY clause for sorting data
	 *
	 * This method constructs the SQL ORDER BY clause based on DataTables ordering parameters.
	 * It handles multiple column sorting with different directions (ASC/DESC) and
	 * properly processes aliased columns using the setOrderColumnAs helper method.
	 *
	 * @param array $request Data sent to server by DataTables
	 * @param array $columns Column information array
	 * @return string|null SQL ORDER BY clause or null if no sorting requested
	 */
	private function setOrder(array $request, array $columns): ?string
	{
		// Skip if no ordering information is provided
		if (!isset($request['order']) || empty($request['order'])) {
			return null;
		}

		$orderBy = [];

		// Process each ordered column
		foreach ($request['order'] as $value) {
			$indexColumn = $value['column'];
			$requestColumn = $request['columns'][$indexColumn];
			$column = $columns[$indexColumn];

			// Only add orderable columns
			if ($requestColumn['orderable'] === 'true') {
				// Determine sort direction
				$dir = match ($value['dir']) {
					'asc' => 'ASC',
					default => 'DESC'
				};

				// Add column to order by clause
				$orderBy[] = $this->setOrderColumnAs($column->db) . ' ' . $dir;
			}
		}

		// Return ORDER BY clause if we have any columns to sort by
		return !empty($orderBy) ? ' ORDER BY ' . implode(', ', $orderBy) : null;
	}

	/**
	 * Extracts the expression part from an aliased column for WHERE clauses
	 *
	 * This method handles SQL column expressions that use the AS keyword for aliasing.
	 * When filtering by such columns, we need to use the expression part rather than
	 * the alias name.
	 *
	 * For example, with "CONCAT(first_name, ' ', last_name) AS full_name",
	 * this method returns "CONCAT(first_name, ' ', last_name)" for filtering purposes.
	 *
	 * @param string $column The column expression that may contain an AS clause
	 * @return string The expression to use for filtering (original expression if aliased, or the column name itself)
	 */
	private function setWhereColumnAs(string $column): string
	{
		return match (true) {
			preg_match('/ AS /i', $column) === 1 => trim(explode(' AS ', $column, 2)[0]),
			default => $column
		};
	}

	/**
	 * Generates the WHERE clause for filtering data
	 *
	 * This method constructs the SQL WHERE clause based on DataTables search parameters:
	 * 1. Global search across all searchable columns
	 * 2. Individual column filtering, including support for:
	 *    - Simple text search with wildcards
	 *    - Multi-select values (comma-separated values from Select2)
	 *    - Range filtering (using a pipe separator for min|max)
	 *
	 * @param array $request Data sent to server by DataTables
	 * @param array $columns Column information array
	 * @return string SQL WHERE clause or empty string if no filtering applied
	 */
	private function setWhere(array $request, array $columns): string
	{
		$globalSearch = [];
		$columnSearch = [];
		$dtColumns = $this->pluck($columns, 'dt');

		// Handle global search
		if (isset($request['search']) && $request['search']['value'] !== '') {
			$searchValue = preg_replace(
				'/^[*]|$/im',
				'%',
				$this->escapeString($request['search']['value'])
			);

			foreach ($request['columns'] as $value) {
				$indexColumn = array_search($value['data'], $dtColumns);
				if ($indexColumn === false) continue;

				$column = $columns[$indexColumn];

				if ($value['searchable'] === 'true' && !empty($column->db)) {
					$globalSearch[] = $this->setWhereColumnAs($column->db) . '::text ILIKE \'' . $searchValue . '\'';
				}
			}
		}

		// Handle individual column filtering
		if (isset($request['columns'])) {
			foreach ($request['columns'] as $value) {
				$indexColumn = array_search($value['data'], $dtColumns);
				if ($indexColumn === false) continue;

				$column = $columns[$indexColumn];
				$searchValue = $value['search']['value'];

				// Skip empty searches or non-searchable columns
				if ($value['searchable'] !== 'true' || $searchValue === '' || empty($column->db)) {
					continue;
				}

				// Handle different filter types
				if (strpos($searchValue, ',') !== false && !isset($column->range)) {
					// Multi-select values (comma-separated from Select2)
					$values = array_map('trim', explode(',', $searchValue));
					$multiSearch = [];

					foreach ($values as $val) {
						$escapedVal = $this->escapeString($val);
						$multiSearch[] = $this->setWhereColumnAs($column->db) . '::text = \'' . $escapedVal . '\'';
					}

					$columnSearch[] = '(' . implode(' OR ', $multiSearch) . ')';
				} elseif (isset($column->range) && $column->range === true && strpos($searchValue, '|') !== false) {
					// Range filtering (min|max)
					list($min, $max) = explode('|', $searchValue);
					$min = trim($min);
					$max = trim($max);

					if ($min !== '') {
						$columnSearch[] = $this->setWhereColumnAs($column->db) . ' >= \'' . $this->escapeString($min) . '\'';
					}

					if ($max !== '') {
						$columnSearch[] = $this->setWhereColumnAs($column->db) . ' <= \'' . $this->escapeString($max) . '\'';
					}
				} else {
					// Standard text search
					$columnSearch[] = $this->setWhereColumnAs($column->db) . '::text ILIKE \''
						. preg_replace('/^[*]|$/im', '%', $this->escapeString($searchValue)) . '\'';
				}
			}
		}

		// Combine the filters into a single string
		$where = '';

		if (!empty($globalSearch)) {
			$where = '(' . implode(' OR ', $globalSearch) . ')';
		}

		if (!empty($columnSearch)) {
			$where = $where === ''
				? implode(' AND ', $columnSearch)
				: $where . ' AND ' . implode(' AND ', $columnSearch);
		}

		if ($where !== '') {
			$where = 'WHERE ' . $where;
		}

		return $where;
	}
}
