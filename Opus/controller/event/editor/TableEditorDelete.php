<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-20 21:26:31
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-20 21:27:58
 **/

namespace Opus\controller\event\editor;

use Opus\controller\lang\Lang;
use Opus\storage\exception\StorageException;

class TableEditorDelete extends AbstractTableEditor
{
	public function __construct(object $config)
	{
		$this->config = $config;
	}

	/**
	 * Generates the header configuration for the delete record view
	 *
	 * This method creates a header configuration with:
	 * 1. An icon class for the header icon (file-earmark-x icon)
	 * 2. Localized text based on the user's language setting
	 *
	 * @return array Header configuration with icon and text
	 */
	private function header(): array
	{
		return [
			'icon' => 'bi-file-earmark-x',
			'text' => Lang::getInstance()->get('event.table.editor.header.delete')
		];
	}

	/**
	 * Processes and outputs the delete record confirmation view
	 *
	 * This method:
	 * 1. Clears any previous table editor session data
	 * 2. Retrieves detailed information about the table structure
	 * 3. Fetches the current values for the record to be deleted
	 * 4. Prepares session data for the delete operation
	 * 5. Generates a JSON response with header and body content
	 *
	 * The view shows the record data for confirmation before deletion.
	 *
	 * @return void
	 * @throws StorageException If database access fails
	 */
	public function doTableEdit(): void
	{
		unset($_SESSION['tableEditor']);
		$this->selectTableDetails();
		$this->getFieldValues();
		$this->getFieldNulls();
		$this->prepareSave();

		echo json_encode([
			'success' => true,
			'head' => $this->header(),
			'body' => $this->body()
		]);
	}
}
