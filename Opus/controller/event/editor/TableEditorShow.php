<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-20 21:44:57
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-20 21:59:58
 **/

namespace Opus\controller\event\editor;

use Opus\storage\db\Db;
use Opus\storage\exception\StorageException;
use Opus\controller\lang\Lang;

class TableEditorShow extends AbstractTableEditor
{
	public function __construct(object $config)
	{
		$this->config = $config;
	}

	/**
	 * Generates the header configuration for the show record view
	 *
	 * This method creates a header configuration with:
	 * 1. An icon class for the header icon (book icon)
	 * 2. Localized text based on the user's language setting
	 *
	 * @return array Header configuration with icon and text
	 */
	private function header(): array
	{
		return [
			'icon' => 'bi-book',
			'text' => Lang::getInstance()->get('event.table.editor.header.show')
		];
	}

	/**
	 * Formats and validates field values for display
	 *
	 * This method processes each field value to ensure proper display formatting:
	 * 1. For integer fields with SELECT configuration, it looks up display values
	 *    from the database (e.g., showing a name instead of an ID)
	 * 2. For boolean fields, it converts true/false values to localized text
	 *
	 * This ensures that the displayed values are user-friendly and properly formatted.
	 *
	 * @return void
	 * @throws StorageException If database access fails during lookup queries
	 */
	private function validateValues(): void
	{
		foreach ($this->tableDetails as $index => $value) {
			list($type) = explode(' ', $value['type'], 2);

			// Process field based on type
			match ($type) {
				// Integer fields with SELECT configuration
				'integer' => $this->processIntegerField($index, $value),

				// Boolean fields
				'boolean' => $this->tableDetails[$index]['value'] = ((bool) $value['value'] === true)
					? Lang::getInstance()->get('event.message.true')
					: Lang::getInstance()->get('event.message.false'),

				// Other types - no special processing needed
				default => null
			};
		}
	}

	/**
	 * Processes integer fields with SELECT configuration
	 *
	 * @param int $index The index of the field in tableDetails
	 * @param array $value The field value and metadata
	 * @return void
	 */
	private function processIntegerField(int $index, array $value): void
	{
		// Skip if no SELECT configuration exists for this field
		if (
			!isset($this->config->table->select->{$value['attname']}) ||
			empty($this->config->table->select->{$value['attname']}) ||
			!is_string($this->config->table->select->{$value['attname']})
		) {
			return;
		}

		// Determine if we need to add WHERE or AND
		$where = (strpos($this->config->table->select->{$value['attname']}, 'WHERE') !== false) ? ' AND ' : ' WHERE ';

		// Prepare and execute the query
		$query = trim($this->config->table->select->{$value['attname']}, ';');
		$queryArray = explode(' ', $query);

		$selectResult = match (!is_null($value['value'])) {
			true => Db::dbArrayResult(
				$query . $where . trim($queryArray[1], ',') . ' = ' . $value['value'],
				$this->config->table->db,
				StorageException::TYPE_API_EXCEPTION
			),

			false => [[$value['attname'] => $value['value']]]
		};

		// Extract the display value
		$keys = array_keys($selectResult[0]);
		$this->tableDetails[$index]['value'] = (isset($keys[1])) ? $selectResult[0][$keys[1]] : $selectResult[0][$keys[0]];
	}

	/**
	 * Processes and outputs the show record view
	 *
	 * This method:
	 * 1. Retrieves detailed information about the table structure
	 * 2. Fetches the current values for the record being viewed
	 * 3. Generates a JSON response with header and body content
	 *
	 * The JSON response includes:
	 * - success flag (always true for show operations)
	 * - header configuration with icon and title
	 * - body content with the record data in table format
	 *
	 * @return void
	 * @throws StorageException If database access fails
	 */
	public function doTableEdit(): void
	{
		$this->selectTableDetails();
		$this->getFieldValues();
		$this->getFieldNulls();
		$this->validateValues();

		echo json_encode([
			'success' => true,
			'head' => $this->header(),
			'body' => $this->body()
		]);
	}
}
