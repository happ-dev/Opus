<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 15:02:37
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 15:03:47
 **/

namespace Opus\controller\query\select;

abstract class AbstractSelect
{
	/**
	 * Creates SQL SELECT statements from configuration and data
	 *
	 * This static factory method generates SQL SELECT statements by:
	 * 1. Creating an SelectQuery instance with the provided configuration and data
	 * 2. Generating the SELECT portion of the statement
	 * 3. Adding VALUES clauses for each data row
	 * 4. Chunking the resulting statements based on the configuration
	 *
	 * @param array $conf The query configuration array
	 * @param object|null $objQuery The query object containing column information
	 * @return array|string|object An array of SQL SELECT statements, possibly chunked based on configuration
	 */
	abstract public static function create(array $conf, ?object $objQuery): array|string|object;

	protected array $config;
	protected object $select;

	/**
	 * Generates the SELECT portion of an SQL SELECT statement
	 *
	 * This method creates the initial part of a SELECT statement with the column names.
	 * It joins the column names with commas to form a valid column list.
	 * If distinct_on is specified, it adds DISTINCT before the column list.
	 *
	 * @return string SQL fragment for the SELECT portion of a SELECT statement
	 */
	protected function select(): string
	{
		$query = 'SELECT ' . $this->config['distinct_on'];
		$columns = [];

		foreach ($this->select->columnToSelect as $value) {
			$columns[] = $value['name'];
		}

		return $query . implode(', ', $columns);
	}

	/**
	 * Generates additional column names for a SELECT statement
	 *
	 * This method formats additional column names from the configuration
	 * to be included in the SELECT statement. If additional columns are defined,
	 * they are joined with commas and prefixed with a comma to append to the
	 * main column list. If no additional columns are defined, it returns null.
	 *
	 * @return string|null Comma-prefixed list of additional columns or null if none defined
	 */
	protected function otherColumnsName(): ?string
	{
		return (!is_null($this->config['other_columns_name']))
			? ', ' . implode(', ', $this->config['other_columns_name'])
			: $this->config['other_columns_name'];
	}

	/**
	 * Generates column names from LEFT JOIN clauses for a SELECT statement
	 *
	 * This method extracts and formats column names from all LEFT JOIN clauses
	 * defined in the configuration. Each column list is joined with commas and
	 * prefixed with a comma to append to the main column list.
	 *
	 * @return string|null Comma-prefixed list of LEFT JOIN columns or null if no joins defined
	 */
	protected function leftJoinColumnName(): ?string
	{
		// Return null if no LEFT JOINs are defined
		if ($this->config['left_join'] === false) {
			return null;
		}

		$columns = [];

		// Collect all columns from all LEFT JOINs
		foreach ($this->config['left_join'] as $join) {
			// Merge the column array into the main columns array
			$columns = array_merge($columns, $join['column']);
		}

		return ', ' . implode(', ', $columns);
	}

	/**
	 * Generates the FROM clause of an SQL SELECT statement
	 *
	 * This method creates the FROM clause with the table name specified
	 * in the configuration. The table name includes the schema.
	 *
	 * @return string SQL fragment for the FROM clause of a SELECT statement
	 */
	protected function from(): string
	{
		return ' FROM ' . $this->config['table'];
	}

	/**
	 * Generates LEFT JOIN clauses for a SELECT statement
	 *
	 * This method creates LEFT JOIN clauses for each join defined in the configuration.
	 * Each LEFT JOIN includes the table name and ON condition with proper parentheses.
	 *
	 * @return string|null SQL fragment containing all LEFT JOIN clauses or null if no joins defined
	 */
	protected function leftJoin(): ?string
	{
		// Return null if no LEFT JOINs are defined
		if ($this->config['left_join'] === false) {
			return null;
		}

		$joins = [];

		// Build each LEFT JOIN clause
		foreach ($this->config['left_join'] as $value) {
			$joins[] = ' LEFT JOIN ' . $value['table'] . ' ON (' . $value['on'] . ')';
		}

		return implode('', $joins);
	}

	/**
	 * Generates the WHERE clause for a SELECT statement
	 *
	 * This method creates a WHERE clause based on the conditions defined in the configuration.
	 * It handles multiple conditions by joining them with AND operators. The first condition
	 * is prefixed with 'WHERE' and subsequent conditions are prefixed with 'AND'.
	 *
	 * @return string|null SQL fragment for the WHERE clause or null if no conditions defined
	 */
	protected function where(): ?string
	{
		// Return null if no WHERE conditions are defined
		if ($this->config['where'] === false) {
			return null;
		}

		$conditions = [];

		// Build each condition
		foreach ($this->config['where'] as $value) {
			$conditions[] = $value['left'] . ' ' . $value['param'] . ' ' . $value['right'];
		}

		// Join conditions with AND operators and prefix with WHERE
		return ' WHERE ' . implode(' AND ', $conditions);
	}

	/**
	 * Generates the ORDER BY clause for a SELECT statement
	 *
	 * This method creates an ORDER BY clause with the columns and sort directions
	 * specified in the configuration. The ORDER BY clause sorts the result set
	 * based on the specified columns and directions.
	 *
	 * @return string|null SQL fragment for the ORDER BY clause or null if no sorting defined
	 */
	protected function orderBy(): ?string
	{
		// Return null if no ORDER BY conditions are defined
		if ($this->config['order_by'] === false) {
			return null;
		}

		$orderClauses = [];

		// Build each ORDER BY clause
		foreach ($this->config['order_by'] as $value) {
			$orderClauses[] = $value['column'] . ' ' . $value['sort'];
		}

		// Join order clauses with commas and prefix with ORDER BY
		return ' ORDER BY ' . implode(', ', $orderClauses);
	}

	/**
	 * Generates the LIMIT clause for a SELECT statement
	 *
	 * This method creates a LIMIT clause with the value specified in the configuration.
	 * The LIMIT clause restricts the number of rows returned by the query.
	 *
	 * @return string|null SQL fragment for the LIMIT clause of a SELECT statement
	 */
	protected function limit(): ?string
	{
		return is_null($this->config['limit'])
			? null
			: ' LIMIT ' . $this->config['limit'];
	}

	/**
	 * Generates the OFFSET clause for a SELECT statement
	 *
	 * This method creates an OFFSET clause with the value specified in the configuration.
	 * The OFFSET clause specifies the number of rows to skip before starting to return rows.
	 * If offset is null, no OFFSET clause is generated.
	 *
	 * @return string|null SQL fragment for the OFFSET clause or null if no offset defined
	 */
	protected function offset(): ?string
	{
		return is_null($this->config['offset'])
			? null
			: ' OFFSET ' . $this->config['offset'];
	}

	/**
	 * Generates the GROUP BY clause for a SELECT statement
	 *
	 * This method creates a GROUP BY clause with the columns specified in the configuration.
	 * The GROUP BY clause groups rows that have the same values in the specified columns
	 * into summary rows.
	 *
	 * @return string|null SQL fragment for the GROUP BY clause or null if no grouping defined
	 */
	protected function groupBy(): ?string
	{
		return $this->config['group_by'] === false
			? null
			: ' GROUP BY ' . implode(', ', $this->config['group_by']);
	}
}
