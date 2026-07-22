<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-20 17:05:05
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-16 15:48:24
 **/

namespace Opus\controller\event\query;

use PDO;
use Opus\controller\request\Request;
use Opus\controller\query\Query;
use Opus\controller\exception\ControllerException;
use Opus\controller\event\TraitValidQueryTypes;
use Opus\controller\event\TraitValidEditorStrategy;
use Opus\controller\lang\Lang;
use Opus\storage\db\Db;
use Opus\storage\exception\StorageException;

class TableQuery
{
	use TraitValidQueryTypes;
	use TraitValidEditorStrategy;

	private static string|array $queryType;

	/**
	 * Processes form data and executes the appropriate database query
	 *
	 * This method handles the complete query workflow:
	 * 1. Determines the query type based on the editor strategy
	 * 2. Processes form data, handling NULL values and special input transformations
	 * 3. Preserves the ID value for the primary key field
	 * 4. Executes the database transaction with the appropriate query
	 * 5. Returns a JSON response with success message
	 *
	 * @return void
	 * @throws StorageException If the database transaction fails
	 */
	public static function tableQuery(): void
	{
		$csrf = Request::validateCsrfToken();

		if ($csrf !== true) {
			throw new ControllerException(
				'controller\tableEvent\query\csrf',
				['message' => $csrf],
				ControllerException::TYPE_API_EXCEPTION
			);
		};

		self::setQueryType();
		$data = [];

		// Process each column from the form data
		foreach ($_SESSION['tableEditor']['column'] as $index => $value) {
			// Check if NULL checkbox is checked
			$nullValue = (bool) Request::post($value['ch_name']);

			// Process field value based on NULL status and field position
			if ($nullValue === true) {
				$data[0][$value['name']] = ($index === 0) ? $_SESSION['tableEditor']['config']['id'] : null;
				$_SESSION['tableEditor']['column'][$index]['pdoTypes'] = PDO::PARAM_NULL;
			} else {
				$data[0][$value['name']] = ($index === 0)
					? $_SESSION['tableEditor']['config']['id']
					: self::queryInputExeptions(Request::post($value['in_name']));
			}
		}

		// Execute database transaction
		Db::dbTransactions(
			Query::createQuery(
				[
					'mode' => Query::MODE_TRANSACTION,
					'type' => self::$queryType,
					'table' => $_SESSION['tableEditor']['config']['table'],
					'columns' => $_SESSION['tableEditor']['column']
				],
				$data
			),
			$_SESSION['tableEditor']['config']['db'],
			StorageException::TYPE_API_STRONG_EXCEPTION
		);

		// Return success response
		echo json_encode([
			'success' => true,
			'message' => Lang::getInstance()->get('event.message.save')
		]);
	}

	/**
	 * Determines the query type based on the editor strategy
	 *
	 * This method maps editor strategies (show, edit, add, delete) to their
	 * corresponding query types (select, update, insert, delete) by replacing
	 * the strategy name with the appropriate query type.
	 *
	 * It validates that the mapping resulted in a valid query type and throws
	 * an exception if the conversion fails.
	 *
	 * @throws ControllerException If the query type cannot be determined
	 * @return void
	 */
	private static function setQueryType(): void
	{
		// Map editor strategy to query type
		self::$queryType = str_replace(
			self::VALID_EDITOR_STRATEGY,
			self::VALID_QUERY_TYPES,
			$_SESSION['tableEditor']['config']['strategy']
		);

		// Validate that a valid query type was determined
		if (is_null(self::$queryType)) {
			throw new ControllerException(
				'controller\tableEvent\query\type',
				[
					'message' => ['query types', print_r(self::$queryType, true)]
				],
				ControllerException::TYPE_API_EXCEPTION
			);
		}
	}

	/**
	 * Handles special input values that need transformation
	 *
	 * This method processes input values that have special meanings in HTML forms
	 * but need to be transformed to their actual values for database operations.
	 * For example, form inputs might use placeholders like 'page_token' that need
	 * to be converted to actual characters like 'page'.
	 *
	 * @param mixed $input The input value to process
	 * @return string|null The processed input value
	 */
	private static function queryInputExeptions(mixed $input): ?string
	{
		return match (true) {
			in_array($input, self::VALID_INPUTS_EXEPTIONS) => str_replace(
				self::VALID_INPUTS_EXEPTIONS,
				['page', '#', '+'],
				$input
			),
			default => $input
		};
	}
}
