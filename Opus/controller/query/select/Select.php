<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 16:23:42
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 17:18:31
 **/

namespace Opus\controller\query\select;

use stdClass;
use Opus\controller\query\AbstractQueryValidate;
use Opus\controller\query\InterfaceQuery;
use Opus\controller\exception\ControllerException;

class Select extends AbstractQueryValidate implements InterfaceQuery
{
	public function __construct(array $config)
	{
		$this->config = $config;

		// Check if mode is valid
		in_array($this->config['mode'], self::QVALID_SELECT_MODE)
			?: throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['Select::mode', $this->config['mode']]],
				$this->config['exception']
			);

		$this->validateTable();
		$this->validateColumns('select');
		$this->validateDistinctOn();
		$this->validateOtherColumnName();
		$this->validateLeftJoin();
		$this->validateWhere();
		$this->validateOrderBy();
		$this->validateLimit();
		$this->validateOffset();
		$this->validateGroupBy();
		$this->validateDbConfig();
		$this->query = new stdClass();
		$this->query->columnToSelect = $this->config['columns'];
		array_shift($this->query->columnToSelect);
		$this->query->countColumnToSelect = count($this->query->columnToSelect);
	}

	public function createQuery(): string|array|object
	{
		return match ($this->config['mode']) {
			self::MODE_EXECUTE => SelectExecute::create($this->config, $this->query),
			self::MODE_PREPARE => SelectPrepare::create($this->config, $this->query),
			self::MODE_QUERY => SelectQuery::create($this->config, $this->query)
		};
	}
}
