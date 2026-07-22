<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-07-10 16:02:19
 * @Last Modified by:   Tomasz Ulazowski
 * @Last Modified time: 2026-07-20 17:08:39
 **/

namespace Opus\apps\demo\src\table;

use stdClass;
use Opus\controller\InterfacePageController;
use Opus\controller\auth\Authorization;
use Opus\controller\event\TableEventValidate;
use Opus\html\asyncpage\AsyncPage;
use Opus\controller\lang\Lang;
use Opus\html\form\Form;
use Opus\html\modal\Modal;
use Opus\html\buttons\Buttons;
use Opus\html\table\Table;

class DemoTable implements InterfacePageController
{
	private object $form;
	private object $lang;
	private object $table;

	public function __construct()
	{
		$this->form = new Form();
		$this->lang = Lang::getInstance();
		$this->table = new Table();
	}

	public function asyncAction(): void
	{
		$options = new stdClass();
		$options->headerText = 'demo.table.buttons';
		$options->headerIcon = 'bi-table';
		$options->body = $this->body();
		$apageTemplate = new AsyncPage();
		echo $apageTemplate->addAsyncPage('demo-table', $options)->get();
	}

	private function body(): string
	{
		$tabs = [
			$this->bodyTabTableDemo(),
			$this->bodyTabInfo(),
			$this->bodyTabPHP(),
			$this->bodyTabJS(),
			$this->bodyTabSQL(),
			$this->bodyTabConfig()
		];

		$buttons = '';
		$contents = '';

		foreach ($tabs as $tab) {
			$buttons .= "<li class=\"nav-item\" role=\"presentation\">{$tab->button}</li>";
			$contents .= $tab->content;
		}

		return <<<HTML
		<div class="row">
			<div class="col">
				<ul class="nav nav-tabs nav-tabs-opus" id="id_opus-demo-table-tab" role="tablist">
					{$buttons}
				</ul>
				<div class="tab-content" id="id_opus-demo-table-tab-content">
					{$contents}
				</div>
			</div>
		</div>
		HTML;
	}

	private function bodyTabTableDemo(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-table-tab-table-demo',
			'id' => 'id_opus-btn-demo-table-tab-table-demo',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-table"></i><em>' . $this->lang->get('demo.table.buttons') . '</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus active',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-table-options-tab-table-demo',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-table-options-tab-table-demo',
				'aria-selected' => 'true'
			]
		]);

		$this->table->addTable([
			'attributes' => [
				'class' => 'table table-sm table-hover table-striped',
				'id' => 'id_demo-table-dt',
				'data-add' => Authorization::accessTableEventButtons(
					'demo',
					'apTableDemo_dt',
					TableEventValidate::EDITOR_STRATEGY_ADD
				),
				'data-show' => Authorization::accessTableEventButtons(
					'demo',
					'apTableDemo_dt',
					TableEventValidate::EDITOR_STRATEGY_SHOW
				),
				'data-edit' => Authorization::accessTableEventButtons(
					'demo',
					'apTableDemo_dt',
					TableEventValidate::EDITOR_STRATEGY_EDIT
				),
				'data-delete' => Authorization::accessTableEventButtons(
					'demo',
					'apTableDemo_dt',
					TableEventValidate::EDITOR_STRATEGY_DELETE
				)
			],
			'thead' => [
				'id__payroll',													//  0 id__payroll
				$this->lang->get('demo.table.db.payroll.firstname'),			//  1 firstname
				$this->lang->get('demo.table.db.payroll.lastname'),				//  2 lastname
				$this->lang->get('demo.table.db.payroll.active'),				//  3 active
				'dept_id',														//  4 dept_id
				$this->lang->get('demo.table.db.payroll.department'),			//  5 dept
				$this->lang->get('demo.table.db.payroll.position'),				//  6 position
				'contract',														//  7 contract
				$this->lang->get('demo.table.db.payroll.hire_date'),			//  8 hire_date
				$this->lang->get('demo.table.db.payroll.salary'),				//  9 salary
				$this->lang->get('demo.table.db.bonuses.granted'),				// 10 granted
				$this->lang->get('demo.table.db.bonuses.reason'),				// 11 reason
				$this->lang->get('demo.table.db.bonuses.amount'),				// 12 amount
				$this->lang->get('demo.table.db.bonuses.percent'),				// 13 percent
				$this->lang->get('demo.table.db.bonuses.total'),				// 14 total
				$this->lang->get('demo.table.db.bonuses.pay_date')				// 15 pay_date
			],
			'tfoot' => [
				'',		//  0 id__payroll
				'',		//  1 firstname
				'',		//  2 lastname
				'',		//  3 active
				'',		//  4 dept_id
				'',		//  5 dept
				'',		//  6 position
				'',		//  7 contract
				'',		//  8 hire_date
				'',		//  9 salary
				'',		// 10 granted
				'',		// 11 reason
				'',		// 12 amount
				'',		// 13 percent
				'',		// 14 total
				''		// 15 pay_date
			],
			'cname' => false,
			'tbody' => false
		]);

		$obj->button = $this->form->getElement('opus-btn-demo-table-tab-table-demo');
		$obj->content = <<<HTML
		<div class="tab-pane fade show active" id="id_opus-demo-table-options-tab-table-demo" role="tabpanel" aria-labelledby="id_opus-demo-table-options-tab-table-demo" tabindex="0">
			<div class="container mt-3">{$this->table->getTableById('id_demo-table-dt')}</div>
		</div>
		HTML;

		return $obj;
	}

	private function bodyTabInfo(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-table-tab-info',
			'id' => 'id_opus-btn-demo-table-tab-info',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-card-text"></i><em>' . $this->lang->get('demo.table.tab.info') . '</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-table-options-tab-info',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-table-options-tab-info',
				'aria-selected' => 'false'
			]
		]);

		$agenda = $this->lang->get('demo.table.tab.agenda');
		$note = $this->lang->get('demo.table.tab.note');

		$this->table->addTable([
			'attributes' => [
				'class' => 'table table-sm table-bordered border-success',
				'id' => 'id_demo-table-options-table'
			],
			'cname' => ['option', 'type', 'default', 'desc'],
			'thead' => [
				$this->lang->get('demo.modal.static.tab.option'),
				$this->lang->get('demo.modal.static.tab.type'),
				$this->lang->get('demo.modal.static.tab.default'),
				$this->lang->get('demo.modal.static.tab.description')
			],
			'tfoot' => false,
			'tbody' => [
				['option' => 'attributes.class', 'type' => 'string', 'default' => '—', 'desc' => $this->lang->get('demo.table.tab.class.desc')],
				['option' => 'attributes.id', 'type' => 'string', 'default' => '—', 'desc' => $this->lang->get('demo.table.tab.id.desc')],
				['option' => 'attributes.width', 'type' => 'string', 'default' => '100%', 'desc' => $this->lang->get('demo.table.tab.width.desc')],
				['option' => 'attributes.cellspacing', 'type' => 'string', 'default' => '0', 'desc' => $this->lang->get('demo.table.tab.cellspacing.desc')],
				['option' => 'attributes.data-add', 'type' => 'bool', 'default' => 'false', 'desc' => $this->lang->get('demo.table.tab.data-add.desc')],
				['option' => 'attributes.data-edit', 'type' => 'bool', 'default' => 'false', 'desc' => $this->lang->get('demo.table.tab.data-edit.desc')],
				['option' => 'attributes.data-show', 'type' => 'bool', 'default' => 'false', 'desc' => $this->lang->get('demo.table.tab.data-show.desc')],
				['option' => 'attributes.data-delete', 'type' => 'bool', 'default' => 'false', 'desc' => $this->lang->get('demo.table.tab.data-delete.desc')],
				['option' => 'cname', 'type' => 'array|false', 'default' => '—', 'desc' => $this->lang->get('demo.table.tab.cname.desc')],
				['option' => 'thead', 'type' => 'array', 'default' => '[]', 'desc' => $this->lang->get('demo.table.tab.thead.desc')],
				['option' => 'tfoot', 'type' => 'array|false', 'default' => 'false', 'desc' => $this->lang->get('demo.table.tab.tfoot.desc')],
				['option' => 'tbody', 'type' => 'array|false', 'default' => '—', 'desc' => $this->lang->get('demo.table.tab.tbody.desc')],
			]
		]);

		$obj->button = $this->form->getElement('opus-btn-demo-table-tab-info');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-table-options-tab-info" role="tabpanel" aria-labelledby="id_opus-btn-demo-table-tab-info" tabindex="0">
			<div class="container mt-3">
				<h6 class="fw-bold mb-3">Opus\html\table\Table</h6>
				<p>{$agenda} <code>$</code><code>options</code>:</p>
				{$this->table->getTableById('id_demo-table-options-table')}
				<p class="text-muted small">{$note}</p>
			</div>
		</div>
		HTML;

		return $obj;
	}

	private function bodyTabPHP(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-table-tab-php',
			'id' => 'id_opus-btn-demo-table-tab-php',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-php"></i><em>PHP</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-table-tab-php',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-table-tab-php',
				'aria-selected' => 'false'
			]
		]);

		$content = htmlspecialchars(
			file_get_contents('vendor/Opus/apps/demo/src/table/DemoTable.php'),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-table-tab-php');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-table-tab-php" role="tabpanel" aria-labelledby="id_opus-btn-demo-table-tab-php" tabindex="0">
			<pre><code class="mt-3 language-php">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}

	private function bodyTabJS(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-table-tab-js',
			'id' => 'id_opus-btn-demo-table-tab-js',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-js"></i><em>JS</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-table-tab-js',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-table-tab-js',
				'aria-selected' => 'false'
			]
		]);

		$file = file_get_contents('vendor/Opus/apps/demo/js/demo.js');

		// Header file
		preg_match('/^(\/\*\*.*?\*\*\/)/s', $file, $header);

		// Fragment between markers
		preg_match('/\/\/ #region objTable\n(.*?)\/\/ #endregion objTable/s', $file, $block);

		$content = htmlspecialchars(
			trim(($header[1] ?? '') . "\n\n" . ($block[1] ?? '')),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-table-tab-js');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-table-tab-js" role="tabpanel" aria-labelledby="id_opus-btn-demo-table-tab-js" tabindex="0">
			<pre><code class="mt-3 language-javascript">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}

	private function bodyTabSQL(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-table-tab-sql',
			'id' => 'id_opus-btn-demo-table-tab-sql',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-sql"></i><em>SQL</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-table-tab-sql',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-table-tab-sql',
				'aria-selected' => 'false'
			]
		]);

		$content = htmlspecialchars(
			file_get_contents('vendor/Opus/sql/scheme/demo.sql'),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-table-tab-sql');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-table-tab-sql" role="tabpanel" aria-labelledby="id_opus-btn-demo-table-tab-sql" tabindex="0">
			<pre><code class="mt-3 language-sql">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}

	private function bodyTabConfig(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-table-tab-config',
			'id' => 'id_opus-btn-demo-table-tab-config',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-json"></i><em>Config</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-table-tab-config',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-table-tab-config',
				'aria-selected' => 'false'
			]
		]);

		$config = json_decode(file_get_contents('vendor/Opus/apps/demo/config/demo.config.json'));

		$content = '"idTableEvent": ' . htmlspecialchars(
			json_encode($config->idTableEvent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		) . ',' . PHP_EOL;

		$content .= '"tableEvent.apTableDemo_dt": ' . htmlspecialchars(
			json_encode($config->tableEvent->apTableDemo_dt, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		) . ',' . PHP_EOL;

		$content .= '"asyncPage.demoTable": ' . htmlspecialchars(
			json_encode($config->asyncPage->demoTable, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-table-tab-config');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-table-tab-config" role="tabpanel" aria-labelledby="id_opus-btn-demo-table-tab-config" tabindex="0">
			<pre><code class="mt-3 language-json">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}

	public static function tableEditModal(): Modal
	{
		$buttons = new Buttons();
		$modalButtons = $buttons->modalButtons('opus-demo-bonuses-table-edit-modal');

		$options = new stdClass();
		$options->form = true;
		$options->size = 'lg';
		$options->footer = $modalButtons->getElement('submit-btn-opus-demo-bonuses-table-edit-modal')
			. $modalButtons->getElement('cancel-btn-opus-demo-bonuses-table-edit-modal')
			. $modalButtons->getElement('close-btn-opus-demo-bonuses-table-edit-modal');

		$modal = new Modal();
		$modal->addModal('opus-demo-bonuses-table-edit-modal', $options);
		return $modal;
	}
}
