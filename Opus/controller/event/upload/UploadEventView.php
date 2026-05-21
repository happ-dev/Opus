<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-18 19:54:36
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-19 15:39:25
 **/

namespace Opus\controller\event\upload;

use stdClass;
use ArrayObject;
use Opus\html\modal\Modal;
use Opus\html\buttons\Buttons;
use Opus\html\form\Form;

/**
 * @extends ArrayObject Provides array-like access to view variables
 * @property string $id The ID property from variables array
 */
class UploadEventView extends ArrayObject
{
	private ?string $indexAction = null;

	public function __construct(array $variables = [])
	{
		parent::__construct($variables, ArrayObject::ARRAY_AS_PROPS);

		$form = new Form();
		$form->addElement(Buttons::cancelButton('upload-event', 'modal'));		// id_cancel-btn-upload-event
		$form->addElement(Buttons::closeButton(									// id_close-btn-upload-event
			'upload-event',
			['data-bs-dismiss' => 'modal']
		));
		$form->addElement(Buttons::submitButton('upload-event'));				// id_submit-btn-upload-event

		$options = new stdClass();
		$options->id = $this->id;
		$options->scrollable = false;		// if form is true, value must be false because bootstrap issue
		$options->form = true;
	}

	public function __toString()
	{
		return $this->indexAction;
	}
}





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
