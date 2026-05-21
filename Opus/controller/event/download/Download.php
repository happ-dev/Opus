<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-16 08:47:51
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-16 14:46:57
 **/

namespace Opus\controller\event\download;

use Opus\config\Config;
use Opus\controller\request\Request;
use Opus\controller\exception\ControllerException;

/**
 * Handles file download requests
 *
 * Validates GET parameters (app, file, type), resolves the file path
 * based on application scope and access type, then streams the file to the client.
 *
 * URL: index.php?page=download&app={app_name}&file={file_name}&type={public|private}
 *
 * GET parameters:
 * - app  {string} Application name (2-50 chars: a-zA-Z0-9_)
 * - file {string} File name with extension (a-zA-Z0-9_.- followed by .extension)
 * - type {string} Access type, defaults to 'public':
 *   - public:  file available for download for all users with access to the application
 *   - private: file generated on request or sent as private, scoped to user session
 *
 * @package Opus\controller\event\download
 */
class Download
{
	/**
	 * Retrieves and validates the 'app' parameter from the GET request
	 *
	 * @return string Validated application name
	 * @throws ControllerException If parameter is missing or contains invalid characters
	 */
	private function getAppParameter(): string
	{
		$app = Request::get(
			'app',
			FILTER_VALIDATE_REGEXP,
			['options' => ['regexp' => '/^[a-zA-Z0-9_]{2,50}$/']]
		);

		match (true) {
			// lack app parametr
			is_null($app) => throw new ControllerException(
				'controller\event\download\param',
				['message' => 'app']
			),
			// parameter has a false value
			$app === false => throw new ControllerException(
				'controller\event\download\paramInvalid',
				['message' => Request::get('app')]
			)
		};

		return $app;
	}

	/**
	 * Retrieves and validates the 'file' parameter from the GET request
	 *
	 * @return string Validated file name with extension
	 * @throws ControllerException If parameter is missing or contains invalid characters
	 */
	private function getFileNameParameter(): string
	{
		$file = Request::get(
			'file',
			FILTER_VALIDATE_REGEXP,
			[
				'options' => ['regexp' => '/^[a-zA-Z0-9_\.\-]+\.[a-zA-Z0-9]+$/']
			]
		);

		match (true) {
			// lack name parametr
			is_null($file) => throw new ControllerException(
				'controller\event\download\param',
				['message' => 'file']
			),
			// parameter has a false value
			$file === false => throw new ControllerException(
				'controller\event\download\paramInvalid',
				['message' => Request::get('file')]
			)
		};

		return $file;
	}

	/**
	 * Retrieves and validates the 'type' parameter from the GET request
	 *
	 * @return string File access type: 'public' or 'private', defaults to 'public'
	 */
	private function getTypeParameter(): string
	{
		return Request::get(
			'type',
			FILTER_VALIDATE_REGEXP,
			['options' => ['regexp' => '/^(public|private)$/']]
		) ?: 'public';
	}

	/**
	 * Builds the full filesystem path to the requested file
	 *
	 * Resolves path based on whether the app is internal (vendor/Opus/apps/)
	 * or external (apps/), and whether the file is public or private (session-scoped).
	 *
	 * @return string Absolute path to the file
	 * @throws ControllerException If app or file parameters are invalid
	 */
	private function selectFullPathFileName(): string
	{
		$app = $this->getAppParameter();
		$internalApp = (in_array($app, array_column(Config::OPUS_APPS, 'app')))
			? '/vendor/Opus/apps/'
			: '/apps/';
		$type = $this->getTypeParameter();
		$fileName = $this->getFileNameParameter();

		return getcwd() . $internalApp . $app . '/files/' . match ($type) {
			'public' => 'public/',
			'private' => 'private/' . $_SESSION['id'] . '/',
		} . $fileName;
	}

	/**
	 * Sends the requested file to the client as a download
	 *
	 * Validates parameters, checks file existence and readability,
	 * then streams the file with appropriate headers.
	 *
	 * @return void
	 * @throws ControllerException If parameters are invalid, file does not exist,
	 *                             is not readable, or cannot be read
	 */
	public static function downloadFile(): void
	{
		$download = new self();
		$fullPathFileName = $download->selectFullPathFileName();

		file_exists($fullPathFileName) ?: throw new ControllerException(
			'controller\event\download\fileExists',
			['details' => $fullPathFileName]
		);

		is_readable($fullPathFileName) ?: throw new ControllerException(
			'controller\event\download\fileReadable',
			['details' => $fullPathFileName]
		);

		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . urlencode(basename($fullPathFileName)));
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($fullPathFileName));
		ob_clean();
		flush();
		exit;
	}
}
