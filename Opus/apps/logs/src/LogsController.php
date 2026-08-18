<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-08-12 16:12:41
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-08-15 15:37:36
 **/

namespace Opus\apps\logs\src;

use stdClass;
use Opus\view\view\View;
use Opus\controller\InterfaceIndexController;
use Opus\controller\auth\Authorization;
use Opus\controller\event\TableEventValidate;
use Opus\html\form\StandardFormElements;
use Opus\controller\exception\LogHandlerException;
use Opus\html\table\Table;
use Opus\controller\lang\Lang;

/**
 * Controller for the internal Logs application.
 * Displays system/application logs in a DataTable with column filters.
 */
class LogsController implements InterfaceIndexController
{
	/**
	 * Main entry point — returns the View with the logs table.
	 *
	 * @return View
	 */
	public function indexAction()
	{
		return new View([
			'table' => $this->table()
		]);
	}

	/**
	 * Builds thead filter form elements for each log column.
	 *
	 * @return object Properties: time, type, path, message, details — each with 'el' key.
	 */
	private function theadElements(): object
	{
		$options = $elements = new stdClass();
		$options->size = 'sm';
		$options->required = false;
		$options->placeholder = true;
		$options->class = 'search-filter';

		// input_logs-time
		$elements->time = [
			'attname' => 'logs-time',
			'comment' => 'logs.table.thead.time'
		];
		StandardFormElements::standardTypeValue($elements->time, $options);

		// input_logs-type
		$elements->type = [
			'attname' => 'logs-type',
			'comment' => 'logs.table.thead.type',
			'template' => [
				'text' => [
					LogHandlerException::LOG_TYPE_API,
					LogHandlerException::LOG_TYPE_APP,
					LogHandlerException::LOG_TYPE_CLI,
					LogHandlerException::LOG_TYPE_ERROR,
					LogHandlerException::LOG_TYPE_WARNING
				],
				'select-opus' => true,
				'multiple' => true
			]
		];
		StandardFormElements::selectValue($elements->type, $options);

		// input_logs-path
		$elements->path = [
			'attname' => 'logs-path',
			'comment' => 'logs.table.thead.path'
		];
		StandardFormElements::standardTypeValue($elements->path, $options);

		// input_logs-message
		$elements->message = [
			'attname' => 'logs-message',
			'comment' => 'logs.table.thead.message'
		];
		StandardFormElements::standardTypeValue($elements->message, $options);

		// input_logs-details
		$elements->details = [
			'attname' => 'logs-details',
			'comment' => 'logs.table.thead.details'
		];
		StandardFormElements::standardTypeValue($elements->details, $options);

		return $elements;
	}

	/**
	 * Creates and configures the logs Table instance.
	 *
	 * @return Table
	 */
	private function table(): Table
	{
		$table = new Table();
		$elements = $this->theadElements();
		$lang = Lang::getInstance();
		$table->addTable([
			'attributes' => [
				'class' => 'table table-sm table-hover table-striped',
				'id' => 'id_logs-dt',
				'data-add' => Authorization::accessTableEventButtons(
					'logs',
					'logs_dt',
					TableEventValidate::EDITOR_STRATEGY_ADD
				),
				'data-show' => Authorization::accessTableEventButtons(
					'logs',
					'logs_dt',
					TableEventValidate::EDITOR_STRATEGY_SHOW
				),
				'data-edit' => Authorization::accessTableEventButtons(
					'logs',
					'logs_dt',
					TableEventValidate::EDITOR_STRATEGY_EDIT
				),
				'data-delete' => Authorization::accessTableEventButtons(
					'logs',
					'logs_dt',
					TableEventValidate::EDITOR_STRATEGY_DELETE
				)
			],
			'thead' => [
				'id__logs',					//  0 id__logs
				$elements->time['el'],		//  1 logtime
				$elements->type['el'],		//  2 logtype
				$elements->path['el'],		//  3 logpath
				$elements->message['el'],	//  4 logmessage
				$elements->details['el'],	//  5 logdetails
			],
			'tfoot' => [
				'id__logs',									//  0 id__logs
				$lang->get('logs.table.thead.time'),		//  1 logtime
				$lang->get('logs.table.thead.type'),		//  2 logtype
				$lang->get('logs.table.thead.path'),		//  3 logpath
				$lang->get('logs.table.thead.message'),		//  4 logmessage
				$lang->get('logs.table.thead.details'),		//  5 logdetails
			],
			'cname' => false,
			'tbody' => false
		]);

		return $table;
	}
}
