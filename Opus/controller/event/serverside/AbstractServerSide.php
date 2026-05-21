<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-19 20:17:35
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-19 20:29:11
 **/

namespace Opus\controller\event\serverside;

use Opus\storage\exception\StorageException;

abstract class AbstractServerSide
{
	protected object $config;

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
	abstract public function serverSide(): void;
}
