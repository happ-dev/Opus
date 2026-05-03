<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-03 15:52:44
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 15:56:52
 **/

namespace Opus\controller\query\update;

use stdClass;
use Opus\controller\query\AbstractQueryValidate;
use Opus\controller\query\InterfaceQuery;
use Opus\controller\exception\ControllerException;

class Update extends AbstractQueryValidate implements InterfaceQuery
{
	public function __construct(array $conf, public array $data)
	{
		$this->config = $conf;

		// Check if mode is valid
		in_array($this->config['mode'], self::QVALID_UPDATE_MODE)
			?: throw new ControllerException(
				'controller\query\validate\param',
				['message' => ['Update::mode', $this->config['mode']]],
				$this->config['exception']
			);

		$this->validateTable();
		$this->validateColumns('update');
		$this->validateChunk();
		$this->query = new stdClass();
		$this->query->columnToUpdate = $this->config['columns'];
		array_shift($this->query->columnToUpdate);
		$this->query->countColumnToUpdate = count($this->query->columnToUpdate);
	}

	public function createQuery(): string|array|object
	{
		return match ($this->config['mode']) {
			self::MODE_PREPARE => UpdatePrepare::create($this->config, $this->data, $this->query),
			self::MODE_QUERY => UpdateQuery::create($this->config, $this->data, $this->query),
			self::MODE_TRANSACTION => UpdateTransaction::create($this->config, $this->data, $this->query)
		};
	}
}
