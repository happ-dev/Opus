<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-07-20 10:37:49
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-22 11:26:18
 **/

namespace Opus\apps\demo\src\table;

use stdClass;
use Opus\controller\request\Request;
use Opus\controller\InterfaceApiController;
use Opus\controller\exception\ControllerException;
use Opus\controller\lang\Lang;
use Opus\storage\db\Db;
use Opus\storage\exception\StorageException;
use Opus\html\form\StandardFormElements;

class DemoTableBonusesApi implements InterfaceApiController
{
	const REQUEST_NEW_BONUS = 'new-bonus';
	const REQUEST_AWARD_BONUS = 'award-bonus';

	private object $requestData;
	private object $postData;

	public function __construct()
	{
		$this->requestData = new stdClass();
		$this->requestData->request = Request::get('request');
		$this->requestData->accountId = Request::get('id');
	}

	public function apiAction(): void
	{
		echo match ($this->requestData->request) {
			self::REQUEST_AWARD_BONUS => null,
			self::REQUEST_NEW_BONUS => json_encode([
				'header' => $this->header(),
				'body' => $this->body()
			]),
			default => throw new ControllerException(
				'controller\asyncEvent\validateConfig\param',
				[
					'message' => ['request', $this->requestData->request],
					'details' => ['demo', 'demoTableBonuses']
				],
				ControllerException::TYPE_API_EXCEPTION
			)
		};
	}

	private function header(): array
	{
		return [
			'text' => Lang::getInstance()->get('demo.table.event.bonuses.header.text'),
			'icon' => 'bi-bank'
		];
	}

	private function body(): string
	{
		$this->getBonusDataById();
		$this->buildHtmlForm();
		$html = '';

		foreach ($this->requestData->data as $value) {
			$html .= $value['el'] ?? null;
		}

		//var_dump($this->requestData->data);

		return <<<HTML
		{$html}
		HTML;
	}

	private function getBonusDataById()
	{
		$this->requestData->data = Db::dbGetTableDetails('demo', 'bonuses', null);
		$values = Db::dbExecute([
			'prepare' => <<<SQL
			SELECT * FROM demo.bonuses
				WHERE id_to_payroll = :id_to_payroll
					AND DATE_TRUNC('month', pay_date) = DATE_TRUNC('month', CURRENT_DATE);
			SQL,
			':id_to_payroll' => $this->requestData->accountId
		]);

		foreach ($this->requestData->data as $index => $value) {
			$column = $value['attname'];
			$this->requestData->data[$index]['value'] = $values[0][$column] ?? null;
			$this->requestData->data[$index]['template'] = $this->inputsTemplate($column);
		}
	}

	private function inputsTemplate(string $column)
	{
		$template = [
			'id_to_payroll' => [
				'attribute' => 'readonly',
				'value' => <<<SQL
					SELECT id__payroll, CONCAT(lastname, ' ', firstname) AS associate
					FROM demo.payroll WHERE id__payroll = {$this->requestData->accountId};
					SQL
			],
			'default' => ['attribute' => 'default']
		];

		return $template[$column] ?? $template['default'];
	}

	private function buildHtmlForm()
	{
		foreach ($this->requestData->data as $index => $value) {
			// Extract base data type
			list($type) = explode(' ', $value['type'], 2);

			//var_dump($type, $value['template']['tag']);

			match (true) {
				// Serial value (first column)
				$index === 0 =>
				StandardFormElements::serialValue($this->requestData->data[$index]),

				// Readonly value
				$value['template']['attribute'] === 'readonly' =>
				StandardFormElements::readonlyValue($this->requestData->data[$index]),

				// Boolean value
				$type == 'boolean' =>
				StandardFormElements::booleanValue($this->requestData->data[$index]),

				// Numeric type
				in_array(
					(function () use ($type) {
						if (preg_match('/^\s*(smallint|integer|bigint|numeric|decimal|real|double|float|serial|bigserial)\b/i', $type, $matches)) {
							return strtolower($matches[1]);
						}

						return 'default';
					})(),
					['smallint', 'integer', 'bigint', 'numeric', 'decimal', 'real', 'double', 'float', 'serial', 'bigserial']
				) => StandardFormElements::numericValue($this->requestData->data[$index]),

				// Date value
				$type == 'date' => StandardFormElements::dateValue($this->requestData->data[$index]),

				// Timestamp
				$type == 'timestamp' => StandardFormElements::timestampValue($this->requestData->data[$index]),

				// Default case (fallback for other types)
				default => StandardFormElements::standardTypeValue($this->requestData->data[$index])
			};
		}
	}
}
