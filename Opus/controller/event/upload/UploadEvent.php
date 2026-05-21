<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-16 15:07:26
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-19 16:16:58
 **/

namespace Opus\controller\event\upload;

use stdClass;
use Opus\config\Config;
use Opus\controller\request\Request;
use Opus\controller\exception\ControllerException;

/**
 * Handles file upload requests
 *
 * Validates GET parameters (app, type) and uploaded file,
 * then moves the file to the appropriate directory.
 *
 * URL: index.php?page=uploadevent&app={app_name}&type={public|private}
 *
 * GET parameters:
 * - app  {string} Application name (2-50 chars: a-zA-Z0-9_)
 * - type {string} Access type, defaults to 'private':
 *   - public:  file available for all users with access to the application
 *   - private: file scoped to user session
 *
 * POST parameters (multipart/form-data):
 * - file {file} Uploaded file
 *
 * @package Opus\controller\event\upload
 */
class UploadEvent
{
	public ?object $upload;

	/**
	 * Retrieves and validates GET parameters (app, type) and builds target directory path
	 *
	 * @return void
	 * @throws ControllerException If parameter is missing or contains invalid characters
	 */
	private function getParameters(): void
	{
		$parameters = [
			'app' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'options' => ['regexp' => '/^[a-zA-Z0-9_]{2,50}$/'],
				'default' => null
			],
			'type' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'options' => ['regexp' => '/^(public|private)$/'],
				'default' => 'private'
			]
		];

		$result = [];

		foreach ($parameters as $name => $config) {
			$value = Request::get($name, $config['filter'], ['options' => $config['options']]);

			match (true) {
				!is_null($config['default']) && !$value => $result[$name] = $config['default'],
				is_null($value) => throw new ControllerException(
					'controller\event\upload\param',
					['message' => $name],
					ControllerException::TYPE_API_EXCEPTION
				),
				$value === false => throw new ControllerException(
					'controller\event\upload\paramInvalid',
					['message' => Request::get($name)],
					ControllerException::TYPE_API_EXCEPTION
				),
				default => $result[$name] = $value
			};
		}

		$internalApp = (in_array($result['app'], array_column(Config::OPUS_APPS, 'app')))
			? '/vendor/Opus/apps/'
			: '/apps/';
		$result['targetDir'] = getcwd() . $internalApp . $result['app'] . '/files/' . match ($result['type']) {
			'public' => 'public/',
			'private' => 'private/' . $_SESSION['id'] . '/',
		};

		$this->upload = (object) $result;
	}

	/**
	 * Validates the uploaded file from $_FILES
	 *
	 * Checks upload error code and file name format.
	 *
	 * @return void
	 * @throws ControllerException If no file uploaded, upload error, or invalid file name
	 */
	private function validateUploadedFile(): void
	{
		if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
			throw new ControllerException(
				'controller\event\upload\noFile',
				null,
				ControllerException::TYPE_API_EXCEPTION
			);
		}

		if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
			throw new ControllerException(
				'controller\event\upload\uploadError',
				['message' => $_FILES['file']['error']],
				ControllerException::TYPE_API_EXCEPTION
			);
		}

		$fileName = $_FILES['file']['name'];

		if (!preg_match('/^[a-zA-Z0-9_\.\-]+\.[a-zA-Z0-9]+$/', $fileName)) {
			throw new ControllerException(
				'controller\event\upload\paramInvalid',
				['message' => $fileName],
				ControllerException::TYPE_API_EXCEPTION
			);
		}

		$this->upload->file = $_FILES['file'];
	}

	/**
	 * Moves the uploaded file to the target directory
	 *
	 * Creates target directory if it does not exist, validates write permissions,
	 * and moves the temporary file to the destination.
	 *
	 * @return void
	 * @throws ControllerException If directory cannot be created, is not writable,
	 *                             or file cannot be moved
	 */
	private function moveUploadedFile(): void
	{
		if (!is_dir($this->upload->targetDir)) {
			mkdir($this->upload->targetDir, 0755, true) ?: throw new ControllerException(
				'controller\event\upload\mkdir',
				['details' => $this->upload->targetDir]
			);
		}

		is_writable($this->upload->targetDir) ?: throw new ControllerException(
			'controller\event\upload\dirWritable',
			['details' => $this->upload->targetDir]
		);
		$this->upload->targetPath = $this->upload->targetDir . $this->upload->file['name'];
		$this->upload->file->sizemb = number_format(($this->upload->file['size'] / 1048576), 2, '.', '');
		move_uploaded_file($this->upload->file['tmp_name'], $this->upload->targetPath) ?: throw new ControllerException(
			'controller\event\upload\moveFailed',
			['details' => $this->upload->targetPath]
		);
	}

	/**
	 * Executes the full upload process and returns JSON response
	 *
	 * @return string JSON with upload result: success, app, type, file name, size
	 * @throws ControllerException If any validation or file operation fails
	 */
	final public static function doUploadEvent(): string
	{
		$upload = new self();
		$upload->getParameters();
		$upload->validateUploadedFile();
		$upload->moveUploadedFile();

		return json_encode([
			'success' => true,
			'app' => $upload->upload->app,
			'type' => $upload->upload->type,
			'file' => basename($upload->upload->targetPath),
			'size' => $upload->upload->file->sizemb . ' MB'
		]);
	}
}
