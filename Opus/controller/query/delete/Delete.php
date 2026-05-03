<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 14:58:53
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 15:00:50
 **/

namespace Opus\controller\query\delete;

use stdClass;
use Opus\controller\query\AbstractQueryValidate;
use Opus\controller\query\InterfaceQuery;
use Opus\controller\exception\ControllerException;

class Delete extends AbstractQueryValidate implements InterfaceQuery
{
	public function __construct(array $config, public array $data)
	{
		$this->config = $config;

		// Check if mode is valid
		in_array($this->config['mode'], self::QVALID_DELETE_MODE)
			?: throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['Delete::mode', $this->config['mode']]],
				$this->config['exception']
			);

		$this->validateTable();
		$this->validateColumns('delete');
		$this->validateChunk();
		$this->query = new stdClass();
		$this->query->columnToDelete = $this->config['columns'];
		$this->query->countColumnToDelete = count($this->query->columnToDelete);
	}

	public function createQuery(): string|array|object
	{
		return match ($this->config['mode']) {
			self::MODE_PREPARE => DeletePrepare::create($this->config, $this->data, $this->query),
			self::MODE_QUERY => DeleteQuery::create($this->config, $this->data, $this->query),
			self::MODE_TRANSACTION => DeleteTransaction::create($this->config, $this->data, $this->query)
		};
	}
}
