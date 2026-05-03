<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 15:12:12
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 15:12:26
 **/

namespace Opus\controller\query\select;

class SelectQuery extends AbstractSelect
{

	public function __construct(array $config, object $select)
	{
		$this->config = $config;
		$this->select = $select;
	}

	public static function create(array $conf, ?object $objQuery): array|string|object
	{
		$selectQuery = new SelectQuery($conf, $objQuery);
		return $selectQuery->select()
			. $selectQuery->otherColumnsName()
			. $selectQuery->leftJoinColumnName()
			. $selectQuery->from()
			. $selectQuery->leftJoin()
			. $selectQuery->where()
			. $selectQuery->groupBy()
			. $selectQuery->orderBy()
			. $selectQuery->limit()
			. $selectQuery->offset()
			. ';';
	}
}
