<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 15:10:02
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 15:11:12
 **/

namespace Opus\controller\query\select;

class SelectPrepare extends AbstractSelect
{
	public function __construct(array $config, object $select)
	{
		$this->config = $config;
		$this->select = $select;
	}

	public static function create(array $conf, ?object $objQuery): array|string|object
	{
		$selectPrepare = new SelectPrepare($conf, $objQuery);
		return $selectPrepare->select()
			. $selectPrepare->otherColumnsName()
			. $selectPrepare->leftJoinColumnName()
			. $selectPrepare->from()
			. $selectPrepare->leftJoin()
			. $selectPrepare->where()
			. $selectPrepare->groupBy()
			. $selectPrepare->orderBy()
			. $selectPrepare->limit()
			. $selectPrepare->offset()
			. ';';
	}

	/**
	 * Generates the WHERE clause for a prepared SELECT statement
	 *
	 * This method creates a WHERE clause with parameter placeholders for a prepared
	 * statement. It extracts the column name from the table.column format to use
	 * as the parameter name. The first condition is prefixed with 'WHERE' and
	 * subsequent conditions are prefixed with 'AND'.
	 *
	 * @return string|null SQL WHERE clause with parameter placeholders
	 */
	protected function where(): ?string
	{
		// Return null if no WHERE conditions are defined
		if ($this->config['where'] === false) {
			return null;
		}

		$conditions = [];

		foreach ($this->config['where'] as $index => $value) {
			// Extract table and column from table.column format
			list($table, $columnName) = explode('.', $value['left']);

			// Create condition with parameter placeholder
			$conditions[] = $value['left'] . ' ' . $value['param'] . ' :' . $columnName;
		}

		// Join conditions with AND operators and prefix with WHERE
		return empty($conditions) ? '' : ' WHERE ' . implode(' AND ', $conditions);
	}
}
