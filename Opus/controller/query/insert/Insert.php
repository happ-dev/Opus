<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 14:49:18
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 14:51:43
 **/

namespace Opus\controller\query\insert;

use stdClass;
use Opus\controller\query\AbstractQueryValidate;
use Opus\controller\query\InterfaceQuery;
use Opus\controller\exception\ControllerException;

class Insert extends AbstractQueryValidate implements InterfaceQuery
{
	public function __construct(array $conf, public array $data)
	{
		$this->config = $conf;

		// Check if mode is valid
		in_array($this->config['mode'], self::QVALID_INSERT_MODE)
			?: throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['Insert::mode', $this->config['mode']]],
				$this->config['exception']
			);

		$this->validateTable();
		$this->validateColumns('insert');
		$this->validateChunk();
		$this->query = new stdClass();
		$this->query->columnToInsert = $this->config['columns'];
		array_shift($this->query->columnToInsert);
		$this->query->countColumnToInsert = count($this->query->columnToInsert);
	}

	public function createQuery(): string|array|object
	{
		return match ($this->config['mode']) {
			self::MODE_PREPARE => InsertPrepare::create($this->config, $this->data, $this->query),
			self::MODE_QUERY => InsertQuery::create($this->config, $this->data, $this->query),
			self::MODE_TRANSACTION => InsertTransaction::create($this->config, $this->data, $this->query)
		};
	}
}
