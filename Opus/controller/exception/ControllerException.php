<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-02-07 15:46:44
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-02-07 15:58:36
 **/

namespace Opus\controller\exception;

use Exception;

class ControllerException extends AbstractException
{
	/**
	 * Class throws an exception with errors, additionally writes logs to db
	 *
	 * @param public readonly string $path to error in {lang}_error.json file
	 * @param ?array $value additional error parameters
	 * 		[
	 * 			'message' => string $value$ or array [$value$, $value$, ...],
	 * 			'details' => string $value$ or array [$value$, $value$, ...]
	 * 		]
	 * @param string $type page|api|sapi|cli
	 * @param string $file with possible application errors
	 * @throws Exception
	 */
	public function __construct(
		public readonly string $path,
		?array $value,
		public readonly string $type = self::TYPE_PAGE_EXCEPTION,
		?string $file = null
	) {
		$this->loadErrorsMessage($file);
		$objError = $this->createObjectError($path, $file);

		// set message
		$this->setStrError(
			$this->exMessage,
			$objError,
			(isset($value['message'])) ? $value['message'] : null,
			$type,
			'message'
		);

		// set details
		$this->setStrError(
			$this->exDetails,
			$objError,
			(isset($value['details'])) ? $value['details'] : null,
			$type,
			'details'
		);

		parent::__construct($this->exMessage . PHP_EOL . $this->exDetails);
	}

	public function indexAction(): mixed
	{
		LogHandlerException::insertLog(
			$this->path,
			$this->exMessage,
			$this->exDetails,
			LogHandlerException::LOG_TYPE_ERROR,
			self::TYPE_PAGE_EXCEPTION
		);
		return null;
	}

	public function asyncAction(): mixed
	{
		LogHandlerException::insertLog(
			$this->path,
			$this->exMessage,
			$this->exDetails,
			LogHandlerException::LOG_TYPE_ERROR,
			self::TYPE_ASYNC_PAGE_EXCEPTION
		);

		return json_encode([
			'success' => false,
			'message' => $this->exMessage,
			'details' => $this->exDetails
		]);
	}

	public function apiAction(): mixed
	{
		LogHandlerException::insertLog(
			$this->path,
			$this->exMessage,
			$this->exDetails,
			LogHandlerException::LOG_TYPE_ERROR,
			self::TYPE_API_EXCEPTION
		);

		return json_encode([
			'success' => false,
			'message' => $this->exMessage,
			'details' => $this->exDetails
		]);
	}

	public function cliAction(): mixed
	{
		LogHandlerException::insertLog(
			$this->path,
			$this->exMessage,
			$this->exDetails,
			LogHandlerException::LOG_TYPE_ERROR,
			self::TYPE_CLI_EXCEPTION
		);
		$this->exDetails = (is_null($this->exDetails)) ? null : $this->exDetails . PHP_EOL;
		return $this->exMessage . PHP_EOL . $this->exDetails;
	}
}
