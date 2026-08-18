<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-08-18 13:43:34
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-08-18 14:48:33
 **/

namespace Opus\apps\settings\src;

use Opus\controller\InterfaceApiController;
use Opus\controller\lang\Lang;
use Opus\html\table\Table;
use Opus\storage\db\Db;

/**
 * Handles API response for user groups modal.
 * Returns header config and HTML table body with group data.
 *
 * @implements InterfaceApiController
 */
class GroupsApi implements InterfaceApiController
{
	private object $lang;
	private object $table;

	public function __construct()
	{
		$this->table = new Table();
		$this->lang = Lang::getInstance();
	}

	/**
	 * Outputs JSON response with modal header and groups table body.
	 *
	 * @return void
	 */
	public function apiAction(): void
	{
		echo json_encode([
			'header' => $this->header(),
			'body' => $this->body()
		]);
	}

	/**
	 * Returns modal header configuration.
	 *
	 * @return array{text: string, icon: string}
	 */
	private function header(): array
	{
		return [
			'text' => $this->lang->get('settings.modal.groups.button'),
			'icon' => 'bi-people',
		];
	}

	/**
	 * Builds HTML table with groups data (name, level) from database.
	 *
	 * @return string Rendered HTML table
	 */
	private function body(): string
	{
		$tableDetails = Db::dbGetTableDetails('groups', 'groups', ['gname', 'glevel']);
		$data = Db::dbArrayResult('SELECT glevel, gname FROM groups.groups ORDER BY glevel DESC');
		$thead = $cname = [];

		foreach ($tableDetails as $value) {
			$cname[] = $value['attname'];
			$thead[] =  $this->lang->get($value['comment']);
		}

		$this->table->addTable([
			'attributes' => [
				'class' => 'table table-sm table-bordered border-success',
				'id' => 'id_opus-settings-groups-table'
			],
			'cname' => $cname,
			'thead' => $thead,
			'tbody' => $data,
			'tfoot' => false
		]);

		return $this->table->getTableById('id_opus-settings-groups-table');
	}
}
