<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-02-01 20:38:30
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-02-07 17:05:53
 **/

namespace Opus\controller;

interface InterfaceController
{
	/**
	 * Handles standard page requests
	 *
	 * @return mixed
	 */
	public function indexAction(): mixed;

	/**
	 * Handles API requests
	 *
	 * @return mixed
	 */
	public function apiAction(): mixed;

	/**
	 * Handles CLI (command line interface) requests
	 *
	 * @return mixed
	 */
	public function cliAction(): mixed;

	/**
	 * Handles asynchronous page requests
	 *
	 * @return mixed
	 */
	public function asyncAction(): mixed;
}
