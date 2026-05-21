<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-21 19:53:05
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-21 21:46:42
 **/

namespace Opus\controller\event;

use Opus\controller\exception\ControllerException;

class AsyncPageValidate
{
	const VALID_ACCESS_LEVEL = ['options' => ['min_range' => 0, 'max_range' => 9]];

	public function __construct(public object &$config)
	{
		$this->config->async->type ??= ControllerException::TYPE_ASYNC_PAGE_EXCEPTION;
		$this->validateAccessLevel();
		$this->validateFile();
		$this->validateClass();
	}

	/**
	 * Validates the access level configuration
	 *
	 * Ensures that the access level is an integer between 0 and 9.
	 *
	 * @return void
	 * @throws ControllerException If access level is invalid
	 */
	private function validateAccessLevel(): void
	{
		$this->config->async->access ??= 9;

		filter_var($this->config->async->access, FILTER_VALIDATE_INT, self::VALID_ACCESS_LEVEL)
			?: throw new ControllerException(
				'controller\asyncPage\validateConfig\param',
				[
					'message' => ['access', $this->config->async->access],
					'details' => [$this->config->app, $this->config->event]
				],
				ControllerException::TYPE_ASYNC_PAGE_EXCEPTION
			);
	}

	/**
	 * Validates the file configuration parameter
	 *
	 * This method:
	 * 1. Ensures the file parameter is defined in configuration
	 * 2. Verifies that the specified file exists on the filesystem
	 *
	 * @return void
	 * @throws ControllerException If file parameter is missing or file doesn't exist
	 */
	private function validateFile(): void
	{
		$this->config->async->file ??= false;

		if ($this->config->async->file === false) {
			throw new ControllerException(
				'controller\asyncPage\validateConfig\param',
				[
					'message' => ['file', $this->config->async->file],
					'details' => [$this->config->app, $this->config->event]
				],
				ControllerException::TYPE_ASYNC_PAGE_EXCEPTION
			);
		}

		file_exists($this->config->async->file)
			?: throw new ControllerException(
				'controller\asyncPage\validateConfig\file',
				['message' => $this->config->async->file],
				ControllerException::TYPE_ASYNC_PAGE_EXCEPTION
			);
	}

	/**
	 * Validates the class configuration parameter
	 *
	 * This method:
	 * 1. Ensures the class parameter is defined in configuration
	 * 2. Verifies that the specified class is declared in the file
	 *
	 * @return void
	 * @throws ControllerException If class parameter is missing or class isn't declared in the file
	 */
	private function validateClass(): void
	{
		$this->config->async->class ??= false;

		if ($this->config->async->file === false) {
			throw new ControllerException(
				'controller\asyncPage\validateConfig\param',
				[
					'message' => ['class', $this->config->async->class],
					'details' => [$this->config->app, $this->config->event]
				],
				ControllerException::TYPE_ASYNC_PAGE_EXCEPTION
			);
		}

		// Extract the class name without namespace
		$className = substr(strrchr($this->config->async->class, '\\'), 1);

		// Read the file content
		$fileContent = file_get_contents($this->config->async->file);

		// Check if the class is declared in the file
		if (!preg_match('/class\s+' . preg_quote($className) . '\s+/i', $fileContent)) {
			throw new ControllerException(
				'controller\asyncPage\validateConfig\param',
				[
					'message' => $this->config->async->file,
					'details' => [$this->config->app, $this->config->event]
				],
				ControllerException::TYPE_ASYNC_PAGE_EXCEPTION
			);
		}

		unset($fileContent);
	}
}
