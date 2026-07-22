<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-20 21:29:30
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-16 19:30:06
 **/

namespace Opus\controller\event\editor;

use Opus\controller\lang\Lang;
use Opus\storage\exception\StorageException;

class TableEditorEdit extends AbstractTableEditor
{
	public function __construct(object $config)
	{
		$this->config = $config;
	}

	/**
	 * Generates the header configuration for the edit record view
	 *
	 * This method creates a header configuration with:
	 * 1. An icon class for the header icon (pencil-square icon)
	 * 2. Localized text based on the user's language setting
	 *
	 * @return array Header configuration with icon and text
	 */
	private function header(): array
	{
		return [
			'icon' => 'bi-pencil-square',
			'text' => Lang::getInstance()->get('event.table.editor.header.edit')
		];
	}

	/**
	 * Processes and outputs the edit record view
	 *
	 * This method:
	 * 1. Clears any previous table editor session data
	 * 2. Retrieves detailed information about the table structure
	 * 3. Fetches the current values for the record being edited
	 * 4. Sets up NULL handling checkboxes for nullable fields
	 * 5. Creates appropriate form elements for each field
	 * 6. Prepares session data for the save operation
	 * 7. Generates a JSON response with header and body content
	 *
	 * @return void
	 * @throws StorageException If database access fails
	 */
	public function doTableEdit(): void
	{
		unset($_SESSION['tableEditor']);
		$this->selectTableDetails();
		$this->translateComments();
		$this->getFieldValues();
		$this->getFieldNulls();
		$this->setFieldValues();
		$this->prepareSave();

		echo json_encode([
			'success' => true,
			'header' => $this->header(),
			'body' => $this->body(),
			'debug' => $this->tableDetails
		]);
	}
}
