<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-07-20 10:37:49
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-08-15 22:42:21
 **/

namespace Opus\apps\demo\src\table;

use stdClass;
use Opus\controller\request\Request;
use Opus\controller\InterfaceApiController;
use Opus\controller\exception\ControllerException;
use Opus\controller\lang\Lang;
use Opus\html\form\StandardForm;
use Opus\storage\db\Db;

/**
 * Demo API controller for managing individual bonuses
 *
 * Handles two request types:
 * - REQUEST_NEW_BONUS: returns modal header and StandardForm body for bonus creation/editing
 * - REQUEST_AWARD_BONUS: saves bonus data (INSERT or UPDATE) to demo.bonuses table
 *
 * @package Opus\apps\demo\src\table
 */
class DemoTableBonusesApi implements InterfaceApiController
{
	const REQUEST_NEW_BONUS = 'new-bonus';
	const REQUEST_AWARD_BONUS = 'award-bonus';

	private object $requestData;
	private object $postData;

	/**
	 * Initializes request parameters from GET: request type and account ID
	 */
	public function __construct()
	{
		$this->requestData = new stdClass();
		$this->requestData->request = Request::get('request');
		$this->requestData->accountId = Request::get('id');
		$this->requestData->data = [];
	}

	/**
	 * Routes the request to appropriate handler based on request type
	 *
	 * REQUEST_AWARD_BONUS flow:
	 *   1. Determines INSERT/UPDATE based on id__bonus presence in POST body
	 *   2. Identifies valid columns via Db::dbGetTableDetails and filters POST keys
	 *   3. Builds prepared statement parameters for Db::dbTransactions
	 *   4. Executes transaction (INSERT or UPDATE)
	 *   5. Returns JSON with success status, message and bonus details
	 *
	 * REQUEST_NEW_BONUS flow:
	 *   Returns JSON with modal header (text + icon) and StandardForm HTML body
	 *
	 * @return void Outputs JSON response
	 * @throws ControllerException If request type is not recognized
	 */
	public function apiAction(): void
	{
		echo match ($this->requestData->request) {
			self::REQUEST_AWARD_BONUS => (function () {
				// 1. Retrieve POST body as object and determine operation type:
				//    if id__bonus exists in postData → UPDATE (overwrite existing bonus)
				//    if id__bonus is missing → INSERT (create new bonus)
				$this->postData = Request::getBody();
				$isUpdate = isset($this->postData->input_id__bonus);

				// 2. Column identification via Db::dbGetTableDetails
				//    Retrieves all column details from demo.bonuses table,
				//    then filters postData keys by stripping 'input_' prefix
				//    and matching against actual table columns.
				//    Keys containing '-' are skipped (e.g. input_id_to_payroll-text
				//    is a display-only field from readonly template).
				//    Numeric columns are sanitized via FILTER_SANITIZE_NUMBER_FLOAT
				//    to strip JS mask characters (e.g. spaces, currency symbols)
				$tableDetails = Db::dbGetTableDetails('demo', 'bonuses', null);
				$tableColumns = array_column($tableDetails, 'attname');
				$numericColumns = array_column(
					array_filter($tableDetails, fn($col) => preg_match('/^(integer|bigint|smallint|numeric|real|double)/i', $col['type'])),
					'attname'
				);

				$filtered = [];
				foreach ($this->postData as $key => $value) {
					$column = preg_replace('/^input_/', '', $key);
					if (in_array($column, $tableColumns) && !str_contains($column, '-')) {
						$filtered[$column] = in_array($column, $numericColumns)
							? filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)
							: $value;
					}
				}

				// 3. Construction of parameters for Db::dbTransactions
				//    Based on $isUpdate flag, builds either:
				//    - UPDATE demo.bonuses SET col = :col, ... WHERE id__bonus = :id__bonus
				//    - INSERT INTO demo.bonuses (col, ...) VALUES (:col, ...)
				//    Params array maps :column placeholders to filtered values
				$columns = array_keys($filtered);
				$params = array_combine(
					array_map(fn($col) => ':' . $col, $columns),
					array_map(fn($val) => [$val], array_values($filtered))
				);

				if ($isUpdate) {
					$set = implode(', ', array_map(fn($col) => "$col = :$col", $columns));
					$prepare = "UPDATE demo.bonuses SET $set WHERE id__bonus = :id__bonus";
				} else {
					$cols = implode(', ', $columns);
					$placeholders = implode(', ', array_map(fn($col) => ':' . $col, $columns));
					$prepare = "INSERT INTO demo.bonuses ($cols) VALUES ($placeholders)";
				}

				$transaction = [[
					'prepare' => $prepare,
					'params' => array_keys($params),
					...$params
				]];

				// 4. Executing a transaction, saving data to a table
				//    Db::dbTransactions handles beginTransaction, commit and rollBack.
				//    Returns stdClass with: success, data, rowCount, lastInsertIds
				$result = Db::dbTransactions($transaction);

				// 5. Response with operation result and saved bonus details
				//    Returns success status and human-readable message with
				//    bonus amount and associate name from postData
				$lang = Lang::getInstance();
				$associate = $this->postData->{'input_id_to_payroll-text'} ?? '';
				$amount = $filtered['amount'] ?? '0.00';

				return json_encode([
					'success' => $result->success,
					'message' => $isUpdate
						? $lang->get('demo.table.event.bonuses.updated')
						: $lang->get('demo.table.event.bonuses.created'),
					'details' => "{$amount} PLN — {$associate}"
				]);
			})(),
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

	/**
	 * Returns modal header configuration for bonus form
	 *
	 * @return array{text: string, icon: string}
	 */
	private function header(): array
	{
		return [
			'text' => Lang::getInstance()->get('demo.table.event.bonuses.header.text'),
			'icon' => 'bi-bank'
		];
	}

	/**
	 * Builds bonus form HTML using StandardForm
	 *
	 * @return string Rendered form HTML
	 */
	private function body(): string
	{
		$this->formTemplate();
		$this->formOptions();
		$standardForm = new StandardForm(
			$this->requestData->data,
			$this->requestData->template,
			$this->requestData->options
		);

		return $standardForm->buildForm()->get();
	}

	/**
	 * Defines form template with readonly field for associate name
	 * Uses SQL query to fetch associate by accountId
	 *
	 * @return void Sets $this->requestData->template
	 */
	private function formTemplate(): void
	{
		$this->requestData->template = [
			'id_to_payroll' => [
				'attribute' => 'readonly',
				'value' => <<<SQL
				SELECT id__payroll, CONCAT(lastname, ' ', firstname) AS associate
				FROM demo.payroll WHERE id__payroll = {$this->requestData->accountId};
				SQL
			]
		];
	}

	/**
	 * Defines form options with database configuration
	 * Loads existing bonus for current month if available (for edit mode)
	 *
	 * @return void Sets $this->requestData->options
	 */
	private function formOptions(): void
	{
		$this->requestData->options = (object) [
			'db' => (object) [
				'scheme' => 'demo',
				'table' => 'bonuses',
				'execute' => [
					'prepare' => <<<SQL
					SELECT * FROM demo.bonuses
					WHERE id_to_payroll = :id_to_payroll
					AND DATE_TRUNC('month', pay_date) = DATE_TRUNC('month', CURRENT_DATE);
					SQL,
					':id_to_payroll' => $this->requestData->accountId
				]
			]
		];
	}
}
