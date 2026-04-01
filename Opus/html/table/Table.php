<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-04-01 13:12:50
 * @Last Modified by:   Tomasz Ulazowski
 * @Last Modified time: 2026-04-01 17:01:10
 **/

namespace Opus\html\table;

use Opus\libs\Common;
use Opus\controller\exception\ControllerException;

class Table extends TableParamsValidate
{
	protected $tables = [];
	protected $key = [];

	/**
	 * Adds a validated table configuration to the collection
	 *
	 * This method validates the input table configuration, generates a unique ID,
	 * and stores the table in the internal collection. It uses a fluent interface
	 * pattern, returning the current object for method chaining.
	 *
	 * @param array $table The table configuration to add with structure:
	 *        [
	 *            'attributes' => [
	 *                'class' => string,       // Required, CSS class
	 *                'id' => string,          // Required, must start with "id_", lowercase only, allows hyphens
	 *                'width' => string,       // Optional, percentage value (10%-100%), default 100%
	 *                'cellspacing' => string  // Optional, integer (0-100), default 0
	 *            ],
	 *            'cname' => array|false,      // Column names array or false if tbody is false
	 *            'thead' => array,            // Table header array
	 *            'tfoot' => array|false,      // Table footer array or false
	 *            'tbody' => array|false       // Table body with structure tbody[][column_name] or false if data is retrieved via ajax
	 *        ]
	 * @throws ControllerException When the table configuration is invalid
	 * @return self Returns the current object instance for method chaining
	 */
	final public function addTable(array $table): self
	{
		// Validate input
		$this->validateInputTable($table);

		$key = [
			'id_key' => Common::windowsUniqId(),
			'id' => $table['attributes']['id']
		];

		$this->key = (empty($this->key)) ? $key : array_merge_recursive($this->key, $key);
		$keyTable = $key['id_key'];
		$input[$keyTable] = $table;
		$this->tables = (empty($this->tables)) ? $input : array_merge_recursive($this->tables, $input);
		return $this;
	}

	/**
	 * Generates HTML for a table by its ID
	 *
	 * This method finds a table by its ID and generates the corresponding HTML markup.
	 * It handles table attributes, headers, footers, and body content.
	 *
	 * @param string $id The ID of the table to retrieve
	 * @throws ControllerException When the table ID doesn't exist or is ambiguous
	 * @return string The generated HTML table markup
	 */
	final public function getTableById($id): string
	{
		// Normalize key arrays
		$keyId = (is_array($this->key['id']))
			? $this->key['id']
			: [$this->key['id']];

		$keyIdKey = (is_array($this->key['id_key']))
			? $this->key['id_key']
			: [$this->key['id_key']];

		// Find the index of the requested ID
		$indexKey = array_keys($keyId, $id);

		// Check if ID exists
		if (empty($indexKey)) {
			throw new ControllerException(
				'html\table\getTableById',
				['message' => $id]
			);
		}

		// Check for duplicate IDs
		if (count($indexKey) > 1) {
			$duplicateIds = array_map(fn($idx) => $keyId[$idx], $indexKey);
			throw new ControllerException(
				'html\table\getTableById\indexKey',
				['message' => [$id, implode(', ', $duplicateIds)]]
			);
		}

		// Get the table configuration
		$tableKey = $keyIdKey[$indexKey[0]];
		$table = $this->tables[$tableKey];

		// Start building HTML
		return $this->generateTableHtml($table);
	}

	/**
	 * Generates HTML for table header
	 *
	 * @param array $thead The table header data
	 * @return string The generated HTML for the table header
	 */
	private function generateTheadHtml(array $thead): string
	{
		$html = '<thead>' . PHP_EOL . '<tr>' . PHP_EOL;

		foreach ($thead as $cell) {
			$html .= '<th>' . $cell . '</th>' . PHP_EOL;
		}

		return $html . '</tr>' . PHP_EOL . '</thead>' . PHP_EOL;
	}

	/**
	 * Generates HTML for table footer
	 *
	 * @param array $tfoot The table footer data
	 * @return string The generated HTML for the table footer
	 */
	private function generateTfootHtml(array $tfoot): string
	{
		$html = '<tfoot>' . PHP_EOL . '<tr>' . PHP_EOL;

		foreach ($tfoot as $cell) {
			$html .= '<th>' . $cell . '</th>' . PHP_EOL;
		}

		return $html . '</tr>' . PHP_EOL . '</tfoot>' . PHP_EOL;
	}

	/**
	 * Generates HTML for table body
	 *
	 * @param array $tbody The table body data
	 * @param array $columnNames The column names
	 * @throws ControllerException When column names are missing
	 * @return string The generated HTML for the table body
	 */
	private function generateTbodyHtml(array $tbody, array $columnNames): string
	{
		$html = '<tbody>' . PHP_EOL;

		foreach ($tbody as $row) {
			$html .= '<tr>' . PHP_EOL;

			foreach ($columnNames as $column) {
				$value = $row[$column] ?? '';
				$html .= '<td>' . $value . '</td>' . PHP_EOL;
			}

			$html .= '</tr>' . PHP_EOL;
		}

		return $html . '</tbody>' . PHP_EOL;
	}

	/**
	 * Generates HTML markup for a table configuration
	 *
	 * @param array $table The table configuration
	 * @throws ControllerException When the table configuration is invalid
	 * @return string The generated HTML table markup
	 */
	private function generateTableHtml(array $table): string
	{
		// Generate opening table tag with attributes
		$html = '<table ';

		foreach ($table['attributes'] as $name => $value) {
			if (!empty($value)) {
				$html .= "{$name}=\"{$value}\" ";
			}
		}
		$html .= '>' . PHP_EOL;

		// Generate thead if present
		if (!empty($table['thead'])) {
			$html .= $this->generateTheadHtml($table['thead']);
		}

		// Generate tfoot if present and not false
		if (!empty($table['tfoot']) && $table['tfoot'] !== false) {
			$html .= $this->generateTfootHtml($table['tfoot']);
		}

		// Generate tbody if present and not false
		if (!empty($table['tbody']) && $table['tbody'] !== false) {
			$html .= $this->generateTbodyHtml($table['tbody'], $table['cname']);
		} else {
			$html .= '<tbody></tbody>' . PHP_EOL;
		}

		// Close table tag
		return $html . '</table>' . PHP_EOL;
	}
}
