<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-16 15:07:26
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-19 14:38:38
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
	public function __construct(public readonly ?object $upload) {}

	private function getParameters(): array
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

		return $result;
	}
}
















///**
// * Handles file upload requests
// *
// * Validates GET parameters (app, type) and uploaded file,
// * then moves the file to the appropriate directory.
// *
// * URL: index.php?page=upload&app={app_name}&type={public|private}
// *
// * GET parameters:
// * - app  {string} Application name (2-50 chars: a-zA-Z0-9_)
// * - type {string} Access type, defaults to 'private':
// *   - public:  file available for all users with access to the application
// *   - private: file scoped to user session
// *
// * POST parameters (multipart/form-data):
// * - file {file} Uploaded file
// *
// * @package Opus\controller\event\upload
// */
//class Upload
//{
//	/**
//	 * Retrieves and validates the 'app' parameter from the GET request
//	 *
//	 * @return string Validated application name
//	 * @throws ControllerException If parameter is missing or contains invalid characters
//	 */
//	private function getAppParameter(): string
//	{
//		$app = Request::get(
//			'app',
//			FILTER_VALIDATE_REGEXP,
//			['options' => ['regexp' => '/^[a-zA-Z0-9_]{2,50}$/']]
//		);
//
//		match (true) {
//			is_null($app) => throw new ControllerException(
//				'controller\event\upload\param',
//				['message' => 'app']
//			),
//			$app === false => throw new ControllerException(
//				'controller\event\upload\paramInvalid',
//				['message' => Request::get('app')]
//			)
//		};
//
//		return $app;
//	}
//
//	/**
//	 * Retrieves and validates the 'type' parameter from the GET request
//	 *
//	 * @return string File access type: 'public' or 'private', defaults to 'private'
//	 */
//	private function getTypeParameter(): string
//	{
//		return Request::get(
//			'type',
//			FILTER_VALIDATE_REGEXP,
//			['options' => ['regexp' => '/^(public|private)$/']]
//		) ?: 'private';
//	}
//
//	/**
//	 * Validates the uploaded file from $_FILES
//	 *
//	 * Checks upload error code, file name format, and file size.
//	 *
//	 * @return array Validated file data from $_FILES['file']
//	 * @throws ControllerException If no file uploaded, upload error, invalid name, or size exceeded
//	 */
//	private function validateUploadedFile(): array
//	{
//		if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
//			throw new ControllerException(
//				'controller\event\upload\noFile',
//				null
//			);
//		}
//
//		if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
//			throw new ControllerException(
//				'controller\event\upload\uploadError',
//				['message' => $_FILES['file']['error']]
//			);
//		}
//
//		$fileName = $_FILES['file']['name'];
//
//		if (!preg_match('/^[a-zA-Z0-9_\.\-]+\.[a-zA-Z0-9]+$/', $fileName)) {
//			throw new ControllerException(
//				'controller\event\upload\paramInvalid',
//				['message' => $fileName]
//			);
//		}
//
//		return $_FILES['file'];
//	}
//
//	/**
//	 * Builds the target directory path for the uploaded file
//	 *
//	 * Resolves path based on whether the app is internal (vendor/Opus/apps/)
//	 * or external (apps/), and whether the file is public or private (session-scoped).
//	 *
//	 * @return string Absolute path to the target directory
//	 * @throws ControllerException If app parameter is invalid
//	 */
//	private function selectTargetDirectory(): string
//	{
//		$app = $this->getAppParameter();
//		$internalApp = (in_array($app, array_column(Config::OPUS_APPS, 'app')))
//			? '/vendor/Opus/apps/'
//			: '/apps/';
//		$type = $this->getTypeParameter();
//
//		return getcwd() . $internalApp . $app . '/files/' . match ($type) {
//			'public' => 'public/',
//			'private' => 'private/' . $_SESSION['id'] . '/',
//		};
//	}
//
//	/**
//	 * Handles the file upload process
//	 *
//	 * Validates parameters and uploaded file, ensures target directory
//	 * exists and is writable, then moves the file to the destination.
//	 *
//	 * @return void
//	 * @throws ControllerException If validation fails, directory is not writable,
//	 *                             or file cannot be moved
//	 */
//	public static function uploadFile(): void
//	{
//		$upload = new self();
//		$file = $upload->validateUploadedFile();
//		$targetDir = $upload->selectTargetDirectory();
//
//		if (!is_dir($targetDir)) {
//			mkdir($targetDir, 0755, true) ?: throw new ControllerException(
//				'controller\event\upload\mkdir',
//				['details' => $targetDir]
//			);
//		}
//
//		is_writable($targetDir) ?: throw new ControllerException(
//			'controller\event\upload\dirWritable',
//			['details' => $targetDir]
//		);
//
//		$targetPath = $targetDir . $file['name'];
//
//		move_uploaded_file($file['tmp_name'], $targetPath) ?: throw new ControllerException(
//			'controller\event\upload\moveFailed',
//			['details' => $targetPath]
//		);
//	}
//}










//### html
//<div id="drop-zone" class="drop-zone">
//  <p>Przeciągnij plik tutaj lub kliknij, aby wybrać</p>
//  <input type="file" id="file-input" hidden>
//</div>
//<div id="result"></div>
//
//
//### css
//.drop-zone {
//  width: 400px;
//  height: 200px;
//  border: 2px dashed #007bff;
//  border-radius: 10px;
//  display: flex;
//  align-items: center;
//  justify-content: center;
//  color: #555;
//  cursor: pointer;
//  transition: all 0.3s ease;
//}
//
//.drop-zone.drag-over {
//  background-color: #e9f7ff;
//  border-color: #0056b3;
//}
//
//### javascript
//const dropZone = document.getElementById('drop-zone');
//const fileInput = document.getElementById('file-input');
//const resultDiv = document.getElementById('result');
//
//// 1. Kliknięcie otwiera standardowy wybór pliku
//dropZone.addEventListener('click', () => fileInput.click());
//
//fileInput.addEventListener('change', (e) => {
//  const files = e.target.files;
//  if (files.length > 0) uploadFiles(files);
//});
//
//// 2. Zapobieganie domyślnym akcjom przeglądarki dla drag and drop
//['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
//  dropZone.addEventListener(eventName, preventDefaults, false);
//});
//
//function preventDefaults(e) {
//  e.preventDefault();
//  e.stopPropagation();
//}
//
//// 3. Dodanie/usunięcie podświetlenia strefy przy przeciąganiu
//['dragenter', 'dragover'].forEach(eventName => {
//  dropZone.addEventListener(eventName, highlight, false);
//});
//
//['dragleave', 'drop'].forEach(eventName => {
//  dropZone.addEventListener(eventName, unhighlight, false);
//});
//
//function highlight() {
//  dropZone.classList.add('drag-over');
//}
//
//function unhighlight() {
//  dropZone.classList.remove('drag-over');
//}
//
//// 4. Obsługa upuszczenia pliku
//dropZone.addEventListener('drop', handleDrop, false);
//
//function handleDrop(e) {
//  const dt = e.dataTransfer;
//  const files = dt.files;
//
//  if (files.length > 0) {
//    uploadFiles(files);
//  }
//}
//
//// 5. Wysyłanie plików na serwer
//function uploadFiles(files) {
//  const formData = new FormData();
//
//  for (let i = 0; i < files.length; i++) {
//    formData.append('file', files[i]);
//  }
//
//  resultDiv.innerHTML = 'Wysyłanie...';
//
//  // Zamień '/upload-endpoint' na adres swojego serwera
//  fetch('/upload-endpoint', {
//    method: 'POST',
//    body: formData
//  })
//  .then(response => response.json())
//  .then(data => {
//    resultDiv.innerHTML = 'Plik przesłany pomyślnie!';
//    console.log(data);
//  })
//  .catch(error => {
//    resultDiv.innerHTML = 'Błąd podczas przesyłania pliku.';
//    console.error(error);
//  });
//}
