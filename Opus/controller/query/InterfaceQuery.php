<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-02 19:56:19
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-02 19:58:08
 **/

namespace Opus\controller\query;

interface InterfaceQuery
{
	/**
	 * Creates a database query based on the model configuration
	 *
	 * This method generates SQL queries or prepares database operations based on
	 * the provided model configuration. It supports various modes and query types.
	 *
	 * @return string|array|object SQL query string, array of queries, or PDO object depending on mode
	 *
	 * Model configuration structure:
	 * - 'mode': Operation mode
	 *   - MODE_TRANSACTION: For database transactions
	 *   - MODE_EXECUTE: For direct execution (returns PDO object)
	 *   - MODE_PREPARE: For prepared statements without data
	 *   - MODE_QUERY: Creates array of queries (for SELECT/table operations)
	 *
	 * - 'db_config': Database configuration (required for MODE_EXECUTE)
	 *
	 * - 'type': Query type
	 *   - 'insert': INSERT INTO operations
	 *   - 'select': SELECT operations
	 *   - 'update': UPDATE operations
	 *   - 'table': CREATE TABLE operations
	 *   - 'delete': DELETE operations
	 *
	 * - 'chunk': Integer or NULL - Returns array with specified number of elements
	 *            (works only with MODE_QUERY except 'table' type)
	 *
	 * - 'table': 'scheme.table' - Table name with schema
	 *
	 * - 'columns': Array of column definitions
	 *   - 'name': Column name (first is always ID with CONSTRAINT pk_)
	 *   - 'type': Column data type
	 *   - 'pdo_param': PDO parameter type (for update/insert)
	 *
	 * - 'where': Array of conditions
	 *   - 'left': Table column name
	 *   - 'right': Value (not used in MODE_PREPARE)
	 *   - 'param': Operator (=, >, <, >=, <=, <>, BETWEEN, LIKE, IN)
	 *
	 * - 'foreign_key': Array of foreign key definitions (for 'table' type)
	 *   - 'key': Foreign key name
	 *   - 'table': Referenced table
	 *   - 'id': Referenced column
	 *
	 * - 'drop_table': Boolean - Whether to drop table if exists
	 *
	 * - 'grant': Array of permissions (for 'table' type)
	 *   - 'user': Database user
	 *   - 'table': Array of table permissions
	 *   - 'sequence': Array of sequence permissions
	 *
	 * - 'distinct_on': Boolean|string DISTINCT ON, - Whether to use DISTINCT ON (for 'select' type)
	 *
	 * - 'other_columns_name': Array of additional columns (for 'select' type)
	 *
	 * - 'left_join': Array of join definitions (for 'select' type)
	 *   - 'table': Joined table name
	 *   - 'column': Columns to select from joined table
	 *   - 'on': Join condition
	 *
	 * - 'order_by': Array of sorting definitions
	 *   - 'column': Column to sort by
	 *   - 'sort': Sort direction ('asc' or 'desc')
	 *
	 * - 'limit': Integer - Maximum number of rows to return
	 *
	 * - 'offset': Integer - Number of rows to skip
	 *
	 * - 'group_by': Array of columns to group by
	 *
	 * - 'exception': Exception type to throw on error
	 *   - ControllerException::TYPE_PAGE_EXCEPTION
	 *   - ControllerException::TYPE_API_EXCEPTION
	 *   - ControllerException::TYPE_API_STRONG_EXCEPTION
	 *   - ControllerException::TYPE_CLI_EXCEPTION
	 */
	public function createQuery(): string|array|object;
}
