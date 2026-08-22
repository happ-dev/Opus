<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-07-22 19:20:31
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-08-08 13:09:08
 **/

namespace Opus\apps\demo\src\select;

use stdClass;
use Opus\controller\InterfacePageController;
use Opus\html\asyncpage\AsyncPage;
use Opus\controller\lang\Lang;
use Opus\html\form\Form;
use Opus\html\form\StandardFormElements;
use Opus\html\table\Table;

/**
 * Demo page controller for OpusSelect component
 *
 * Demonstrates all select element variants: static/AJAX, single/multiple,
 * text-only/value, flat/optgroups, with various options (width, shadow,
 * margin, required, floating, size, icon). Content loaded asynchronously
 * via AsyncPage with 16 preview rows in the options table.
 *
 * @package Opus\apps\demo\src\select
 */
class DemoSelect implements InterfacePageController
{
	private object $form;
	private object $lang;
	private object $table;

	/**
	 * Initializes Form, Lang and Table instances
	 */
	public function __construct()
	{
		$this->form = new Form();
		$this->lang = Lang::getInstance();
		$this->table = new Table();
	}

	/**
	 * Renders async page with select demo content
	 *
	 * @return void Outputs AsyncPage HTML
	 */
	public function asyncAction(): void
	{
		$options = new stdClass();
		$options->headerText = 'demo.sidebar.select';
		$options->headerIcon = 'bi-menu-button';
		$options->body = $this->body();
		$apageTemplate = new AsyncPage();
		echo $apageTemplate->addAsyncPage('demo-select', $options)->get();
	}

	/**
	 * Builds the full body HTML with nav-tabs layout
	 *
	 * @return string Complete body HTML
	 */
	private function body(): string
	{
		$tabs = [
			$this->bodyTabInfo(),
			$this->bodyTabPHP(),
			$this->bodyTabPHPElements(),
			$this->bodyTabJS(),
			$this->bodyTabConfig(),
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

	/**
	 * Builds the Info tab with 32 select elements and 16-row options table
	 * Each row shows option/type/default/description and a live preview
	 * with select + multiple pair demonstrating the given option
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabInfo(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-select-tab-info',
			'id' => 'id_opus-btn-demo-select-tab-info',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-card-text"></i><em>' . $this->lang->get('demo.select.tab.info') . '</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus active',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-select-options-tab-info',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-select-options-tab-info',
				'aria-selected' => 'true'
			]
		]);

		$agenda = $this->lang->get('demo.select.tab.agenda');
		$note = $this->lang->get('demo.select.tab.note');

		// Opus select, text from template
		$selectText = DemoSelectElements::$selectText;
		StandardFormElements::selectValue($selectText);

		// Opus multiple, text from template
		$multipleText = DemoSelectElements::$multipleText;
		StandardFormElements::selectValue($multipleText);

		// Opus grouped select text from template
		$selectOptText = DemoSelectElements::$selectOptText;
		StandardFormElements::selectValue($selectOptText);

		// Opus grouped multiple text from template
		$multipleOptText = DemoSelectElements::$multipleOptText;
		StandardFormElements::selectValue($multipleOptText);

		// Opus select text from sql
		$selectTextSql = DemoSelectElements::$selectTextSql;
		StandardFormElements::selectValue($selectTextSql);

		// Opus multiple text from sql
		$multipleTextSql = DemoSelectElements::$multipleTextSql;
		StandardFormElements::selectValue($multipleTextSql);

		// Opus grouped select text from sql
		$selectOptTextSql = DemoSelectElements::$selectOptTextSql;
		StandardFormElements::selectValue($selectOptTextSql);

		// Opus grouped multiple text from sql
		$multipleOptTextSql = DemoSelectElements::$multipleOptTextSql;
		StandardFormElements::selectValue($multipleOptTextSql);

		// Opus select text from ajax
		$selectAjaxText = DemoSelectElements::$selectAjaxText;
		StandardFormElements::selectValue($selectAjaxText);

		// Opus multiple text from ajax
		$multipleAjaxText = DemoSelectElements::$multipleAjaxText;
		StandardFormElements::selectValue($multipleAjaxText);

		// Opus grouped select text from ajax
		$selectAjaxOptText = DemoSelectElements::$selectAjaxOptText;
		StandardFormElements::selectValue($selectAjaxOptText);

		// Opus grouped multiple text from ajax
		$multipleAjaxOptText = DemoSelectElements::$multipleAjaxOptText;
		StandardFormElements::selectValue($multipleAjaxOptText);

		// Opus select text from ajax, sql
		$selectAjaxTextSql = DemoSelectElements::$selectAjaxTextSql;
		StandardFormElements::selectValue($selectAjaxTextSql);

		// Opus multiple text from ajax, sql
		$multipleAjaxTextSql = DemoSelectElements::$multipleAjaxTextSql;
		StandardFormElements::selectValue($multipleAjaxTextSql);

		// Opus grouped select text from ajax, sql
		$selectOptAjaxTextSql = DemoSelectElements::$selectOptAjaxTextSql;
		StandardFormElements::selectValue($selectOptAjaxTextSql);

		// Opus grouped multiple text from ajax, sql
		$multipleOptAjaxTextSql = DemoSelectElements::$multipleOptAjaxTextSql;
		StandardFormElements::selectValue($multipleOptAjaxTextSql);

		// Opus select, value from template
		$selectValue = DemoSelectElements::$selectValue;
		StandardFormElements::selectValue($selectValue);

		// Opus select multiple, value from template
		$multipleValue = DemoSelectElements::$multipleValue;
		StandardFormElements::selectValue($multipleValue);

		// Opus grouped select value from template
		$selectOptValue = DemoSelectElements::$selectOptValue;
		StandardFormElements::selectValue($selectOptValue);

		// Opus grouped multiple value from template
		$multipleOptValue = DemoSelectElements::$multipleOptValue;
		StandardFormElements::selectValue($multipleOptValue);

		// Opus select text, value from sql, with default value
		$selectValueSql = DemoSelectElements::$selectValueSql;
		StandardFormElements::selectValue($selectValueSql);

		// Opus select multiple text, value from sql
		$multipleValueSql = DemoSelectElements::$multipleValueSql;
		StandardFormElements::selectValue($multipleValueSql);

		// Opus grouped select text, value from sql
		$selectOptValueSql = DemoSelectElements::$selectOptValueSql;
		StandardFormElements::selectValue($selectOptValueSql);

		// Opus grouped multiple text, value from sql
		$multipleOptValueSql = DemoSelectElements::$multipleOptValueSql;
		StandardFormElements::selectValue($multipleOptValueSql);

		// Opus select text, value from ajax
		$selectAjaxValue = DemoSelectElements::$selectAjaxValue;
		StandardFormElements::selectValue($selectAjaxValue);

		// Opus multiple text, value from ajax
		$multipleAjaxValue = DemoSelectElements::$multipleAjaxValue;
		StandardFormElements::selectValue($multipleAjaxValue);

		// Opus grouped select text, value from ajax
		$selectAjaxOptValue = DemoSelectElements::$selectAjaxOptValue;
		StandardFormElements::selectValue($selectAjaxOptValue);

		// Opus grouped multiple text, value from ajax
		$multipleAjaxOptValue = DemoSelectElements::$multipleAjaxOptValue;
		StandardFormElements::selectValue($multipleAjaxOptValue);

		// Opus select text, value from ajax, sql
		$selectAjaxValueSql = DemoSelectElements::$selectAjaxValueSql;
		StandardFormElements::selectValue($selectAjaxValueSql);

		// Opus multiple text, value from ajax, sql
		$multipleAjaxValueSql = DemoSelectElements::$multipleAjaxValueSql;
		StandardFormElements::selectValue($multipleAjaxValueSql);

		// Opus grouped select text, value from ajax, sql
		$selectOptAjaxValueSql = DemoSelectElements::$selectOptAjaxValueSql;
		StandardFormElements::selectValue($selectOptAjaxValueSql);

		// Opus grouped multiple text, value from ajax, sql
		$multipleOptAjaxValueSql = DemoSelectElements::$multipleOptAjaxValueSql;
		StandardFormElements::selectValue($multipleOptAjaxValueSql);

		$this->table->addTable([
			'attributes' => [
				'class' => 'table table-sm table-bordered border-success',
				'id' => 'id_demo-select-options-table'
			],
			'cname' => ['option', 'type', 'default', 'desc', 'preview'],
			'thead' => [
				$this->lang->get('demo.modal.static.tab.option'),
				$this->lang->get('demo.modal.static.tab.type'),
				$this->lang->get('demo.modal.static.tab.default'),
				$this->lang->get('demo.modal.static.tab.description'),
				$this->lang->get('demo.datepicker.tab.preview')
			],
			'tfoot' => false,
			'tbody' => [
				[
					'option' => "template['select-opus']",
					'type' => 'bool',
					'default' => 'false',
					'desc' => $this->lang->get('demo.select.tab.selectOpus.desc'),
					'preview' => <<<HTML
					<div class="row pt-2">
						<div class="col">
							<div class="d-inline-flex ps-2 pt-2 pe-2 pb-3">
								<span class="text-lime bs-opus-black-3d px-2 py-2" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); border-radius: var(--bs-border-radius);">
									{$this->lang->get('demo.select.tab.preview.row1')}
								</span>
							</div>
						</div>
					</div>
					<div class="row align-items-center p-2">
						<div class="col">
							<div>{$selectText['el']}</div>
							<div class="pt-3">{$multipleText['el']}</div>
						</div>
					</div>
					HTML
				],
				[
					'option' => "template['text']",
					'type' => 'array|string(SQL)',
					'default' => '—',
					'desc' => $this->lang->get('demo.select.tab.text.desc'),
					'preview' => <<<HTML
					<div class="row pt-2">
						<div class="col">
							<div class="d-inline-flex ps-2 pt-2 pe-2 pb-3">
								<span class="text-lime bs-opus-black-3d px-2 py-2" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); border-radius: var(--bs-border-radius);">
									{$this->lang->get('demo.select.tab.preview.row2')}
								</span>
							</div>
						</div>
					</div>
					<div class="row align-items-center p-2">
						<div class="col">
							<div>{$selectOptText['el']}</div>
							<div class="pt-3">{$multipleOptText['el']}</div>
						</div>
					</div>
					HTML
				],
				[
					'option' => "template['value']",
					'type' => 'array|null',
					'default' => 'null',
					'desc' => $this->lang->get('demo.select.tab.templateValue.desc'),
					'preview' => <<<HTML
					<div class="row pt-2">
						<div class="col">
							<div class="d-inline-flex ps-2 pt-2 pe-2 pb-3">
								<span class="text-lime bs-opus-black-3d px-2 py-2" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); border-radius: var(--bs-border-radius);">
									{$this->lang->get('demo.select.tab.preview.row3')}
								</span>
							</div>
						</div>
					</div>
					<div class="row align-items-center p-2">
						<div class="col">
							<div>{$selectTextSql['el']}</div>
							<div class="pt-3">{$multipleTextSql['el']}</div>
						</div>
					</div>
					HTML
				],
				[
					'option' => "template['multiple']",
					'type' => 'bool',
					'default' => 'false',
					'desc' => $this->lang->get('demo.select.tab.multiple.desc'),
					'preview' => <<<HTML
					<div class="row pt-2">
						<div class="col">
							<div class="d-inline-flex ps-2 pt-2 pe-2 pb-3">
								<span class="text-lime bs-opus-black-3d px-2 py-2" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); border-radius: var(--bs-border-radius);">
									{$this->lang->get('demo.select.tab.preview.row4')}
								</span>
							</div>
						</div>
					</div>
					<div class="row align-items-center p-2">
						<div class="col">
							<div>{$selectOptTextSql['el']}</div>
							<div class="pt-3">{$multipleOptTextSql['el']}</div>
						</div>
					</div>
					HTML
				],
				[
					'option' => "template['optgroups']",
					'type' => 'array|null',
					'default' => 'null',
					'desc' => $this->lang->get('demo.select.tab.optgroups.desc'),
					'preview' => <<<HTML
					<div class="row pt-2">
						<div class="col">
							<div class="d-inline-flex ps-2 pt-2 pe-2 pb-3">
								<span class="text-lime bs-opus-black-3d px-2 py-2" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); border-radius: var(--bs-border-radius);">
									{$this->lang->get('demo.select.tab.preview.row5')}
								</span>
							</div>
						</div>
					</div>
					<div class="row align-items-center p-2">
						<div>{$selectAjaxText['el']}</div>
						<div class="pt-3">{$multipleAjaxText['el']}</div>
					</div>
					HTML
				],
				[
					'option' => "template['app']",
					'type' => 'string|null',
					'default' => 'null',
					'desc' => $this->lang->get('demo.select.tab.app.desc'),
					'preview' => <<<HTML
					<div class="row pt-2">
						<div class="col">
							<div class="d-inline-flex ps-2 pt-2 pe-2 pb-3">
								<span class="text-lime bs-opus-black-3d px-2 py-2" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); border-radius: var(--bs-border-radius);">
									{$this->lang->get('demo.select.tab.preview.row6')}
								</span>
							</div>
						</div>
					</div>
					<div class="row align-items-center p-2">
						<div>{$selectAjaxOptText['el']}</div>
						<div class="pt-3">{$multipleAjaxOptText['el']}</div>
					</div>
					HTML
				],
				[
					'option' => "template['event']",
					'type' => 'string|null',
					'default' => 'null',
					'desc' => $this->lang->get('demo.select.tab.event.desc'),
					'preview' => <<<HTML
					<div class="row pt-2">
						<div class="col">
							<div class="d-inline-flex ps-2 pt-2 pe-2 pb-3">
								<span class="text-lime bs-opus-black-3d px-2 py-2" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); border-radius: var(--bs-border-radius);">
									{$this->lang->get('demo.select.tab.preview.row7')}
								</span>
							</div>
						</div>
					</div>
					<div class="row align-items-center p-2">
						<div>{$selectAjaxTextSql['el']}</div>
						<div class="pt-3">{$multipleAjaxTextSql['el']}</div>
					</div>
					HTML
				],
				[
					'option' => "template['limit']",
					'type' => 'int',
					'default' => '20',
					'desc' => $this->lang->get('demo.select.tab.limit.desc'),
					'preview' => <<<HTML
					<div class="row pt-2">
						<div class="col">
							<div class="d-inline-flex ps-2 pt-2 pe-2 pb-3">
								<span class="text-lime bs-opus-black-3d px-2 py-2" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); border-radius: var(--bs-border-radius);">
									{$this->lang->get('demo.select.tab.preview.row8')}
								</span>
							</div>
						</div>
					</div>
					<div class="row align-items-center p-2">
						<div>{$selectOptAjaxTextSql['el']}</div>
						<div class="pt-3">{$multipleOptAjaxTextSql['el']}</div>
					</div>
					HTML
				],
				[
					'option' => "data['value']",
					'type' => 'int|string|array|null',
					'default' => 'null',
					'desc' => $this->lang->get('demo.select.tab.value.desc'),
					'preview' => <<<HTML
					<div class="row pt-2">
						<div class="col">
							<div class="d-inline-flex ps-2 pt-2 pe-2 pb-3">
								<span class="text-lime bs-opus-black-3d px-2 py-2" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); border-radius: var(--bs-border-radius);">
									{$this->lang->get('demo.select.tab.preview.row9')}
								</span>
							</div>
						</div>
					</div>
					<div class="row align-items-center p-2">
						<div>{$selectValue['el']}</div>
						<div class="pt-3">{$multipleValue['el']}</div>
					</div>
					HTML
				],
				[
					'option' => 'options->width',
					'type' => 'string',
					'default' => '100%',
					'desc' => $this->lang->get('demo.select.tab.width.desc'),
					'preview' => <<<HTML
					<div class="row pt-2">
						<div class="col">
							<div class="d-inline-flex ps-2 pt-2 pe-2 pb-3">
								<span class="text-lime bs-opus-black-3d px-2 py-2" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); border-radius: var(--bs-border-radius);">
									{$this->lang->get('demo.select.tab.preview.row10')}
								</span>
							</div>
						</div>
					</div>
					<div class="row align-items-center p-2">
						<div>{$selectOptValue['el']}</div>
						<div class="pt-3">{$multipleOptValue['el']}</div>
					</div>
					HTML
				],
				[
					'option' => 'options->shadow',
					'type' => 'string',
					'default' => 'bs-opus-black-3d',
					'desc' => $this->lang->get('demo.select.tab.shadow.desc'),
					'preview' => <<<HTML
					<div class="row pt-2">
						<div class="col">
							<div class="d-inline-flex ps-2 pt-2 pe-2 pb-3">
								<span class="text-lime bs-opus-black-3d px-2 py-2" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); border-radius: var(--bs-border-radius);">
									{$this->lang->get('demo.select.tab.preview.row11')}
								</span>
							</div>
						</div>
					</div>
					<div class="row align-items-center p-2">
						<div>{$selectValueSql['el']}</div>
						<div class="pt-3">{$multipleValueSql['el']}</div>
					</div>
					HTML
				],
				[
					'option' => 'options->margin',
					'type' => 'string',
					'default' => 'mb-3',
					'desc' => $this->lang->get('demo.select.tab.margin.desc'),
					'preview' => <<<HTML
					<div class="row pt-2">
						<div class="col">
							<div class="d-inline-flex ps-2 pt-2 pe-2 pb-3">
								<span class="text-lime bs-opus-black-3d px-2 py-2" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); border-radius: var(--bs-border-radius);">
									{$this->lang->get('demo.select.tab.preview.row12')}
								</span>
							</div>
						</div>
					</div>
					<div class="row align-items-center p-2">
						<div>{$selectOptValueSql['el']}</div>
						<div class="pt-3">{$multipleOptValueSql['el']}</div>
					</div>
					HTML
				],
				[
					'option' => 'options->required',
					'type' => 'bool',
					'default' => 'true',
					'desc' => $this->lang->get('demo.select.tab.required.desc'),
					'preview' => <<<HTML
					<div class="row pt-2">
						<div class="col">
							<div class="d-inline-flex ps-2 pt-2 pe-2 pb-3">
								<span class="text-lime bs-opus-black-3d px-2 py-2" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); border-radius: var(--bs-border-radius);">
									{$this->lang->get('demo.select.tab.preview.row13')}
								</span>
							</div>
						</div>
					</div>
					<div class="row align-items-center p-2">
						<div>{$selectAjaxValue['el']}</div>
						<div class="pt-3">{$multipleAjaxValue['el']}</div>
					</div>
					HTML
				],
				[
					'option' => 'options->floating',
					'type' => 'bool',
					'default' => 'false',
					'desc' => $this->lang->get('demo.select.tab.floating.desc'),
					'preview' => <<<HTML
					<div class="row pt-2">
						<div class="col">
							<div class="d-inline-flex ps-2 pt-2 pe-2 pb-3">
								<span class="text-lime bs-opus-black-3d px-2 py-2" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); border-radius: var(--bs-border-radius);">
									{$this->lang->get('demo.select.tab.preview.row14')}
								</span>
							</div>
						</div>
					</div>
					<div class="row align-items-center p-2">
						<div>{$selectAjaxOptValue['el']}</div>
						<div class="pt-3">{$multipleAjaxOptValue['el']}</div>
					</div>
					HTML
				],
				[
					'option' => 'options->size',
					'type' => 'string',
					'default' => 'default',
					'desc' => $this->lang->get('demo.select.tab.size.desc'),
					'preview' => <<<HTML
					<div class="row pt-2">
						<div class="col">
							<div class="d-inline-flex ps-2 pt-2 pe-2 pb-3">
								<span class="text-lime bs-opus-black-3d px-2 py-2" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); border-radius: var(--bs-border-radius);">
									{$this->lang->get('demo.select.tab.preview.row15')}
								</span>
							</div>
						</div>
					</div>
					<div class="row align-items-center p-2">
						<div>{$selectAjaxValueSql['el']}</div>
						<div class="pt-3">{$multipleAjaxValueSql['el']}</div>
					</div>
					HTML
				],
				[
					'option' => 'options->icon',
					'type' => 'string',
					'default' => 'bi-menu-button',
					'desc' => $this->lang->get('demo.select.tab.icon.desc'),
					'preview' => <<<HTML
					<div class="row pt-2">
						<div class="col">
							<div class="d-inline-flex ps-2 pt-2 pe-2 pb-3">
								<span class="text-lime bs-opus-black-3d px-2 py-2" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); border-radius: var(--bs-border-radius);">
									{$this->lang->get('demo.select.tab.preview.row16')}
								</span>
							</div>
						</div>
					</div>
					<div class="row align-items-center p-2">
						<div>{$selectOptAjaxValueSql['el']}</div>
						<div class="pt-3">{$multipleOptAjaxValueSql['el']}</div>
					</div>
					HTML
				]
			]
		]);

		$obj->button = $this->form->getElement('opus-btn-demo-select-tab-info');
		$obj->content = <<<HTML
		<div class="tab-pane fade show active" id="id_opus-demo-select-options-tab-info" role="tabpanel" aria-labelledby="id_opus-btn-demo-select-tab-info" tabindex="0">
			<div class="container mt-3">
				<h6 class="fw-bold mb-3">js OpusSelect class, php StandardFormElements::selectValue(array <code>&$</code>data, object <code>$</code>options = new stdClass())</h6>
				<p>{$agenda}</p>
				{$this->table->getTableById('id_demo-select-options-table')}
				<p class="text-muted small">{$note}</p>
			</div>
		</div>
		HTML;

		return $obj;
	}

	/**
	 * Builds the PHP tab displaying DemoSelect.php source code
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabPHP(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-select-tab-php',
			'id' => 'id_opus-btn-demo-select-tab-php',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-php"></i><em>PHP</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-select-tab-php',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-select-tab-php',
				'aria-selected' => 'false'
			]
		]);

		$content = htmlspecialchars(
			file_get_contents('vendor/Opus/apps/demo/src/select/DemoSelect.php'),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-select-tab-php');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-select-tab-php" role="tabpanel" aria-labelledby="id_opus-btn-demo-buttons-tab-php" tabindex="0">
			<pre><code class="mt-3 language-php">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}

	/**
	 * Builds the PHP-Elements tab displaying DemoSelectElements.php source code
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabPHPElements(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-select-tab-php-el',
			'id' => 'id_opus-btn-demo-select-tab-php-el',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-php"></i><em>PHP-Elements</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-select-tab-php-el',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-select-tab-php-el',
				'aria-selected' => 'false'
			]
		]);

		$content = htmlspecialchars(
			file_get_contents('vendor/Opus/apps/demo/src/select/DemoSelectElements.php'),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-select-tab-php-el');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-select-tab-php-el" role="tabpanel" aria-labelledby="id_opus-btn-demo-buttons-tab-php-el" tabindex="0">
			<pre><code class="mt-3 language-php">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}

	/**
	 * Builds the JS tab displaying OpusSelect region from demo.js
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabJS(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-select-tab-js',
			'id' => 'id_opus-btn-demo-select-tab-js',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-js"></i><em>JS</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-select-tab-js',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-select-tab-js',
				'aria-selected' => 'false'
			]
		]);

		$file = file_get_contents('vendor/Opus/apps/demo/js/demo.js');

		// Header file
		preg_match('/^(\/\*\*.*?\*\*\/)/s', $file, $header);

		// Fragment between markers
		preg_match('/\/\/ #region OpusSelect\r?\n(.*?)\/\/ #endregion OpusSelect/s', $file, $block);

		$content = htmlspecialchars(
			trim(($header[1] ?? '') . "\n\n" . ($block[1] ?? '')),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-select-tab-js');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-select-tab-js" role="tabpanel" aria-labelledby="id_opus-btn-demo-select-tab-js" tabindex="0">
			<pre><code class="mt-3 language-javascript">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}

	/**
	 * Builds the Config tab displaying asyncPage and asyncSelect config sections
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabConfig(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-select-tab-config',
			'id' => 'id_opus-btn-demo-select-tab-config',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-json"></i><em>Config</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-select-tab-config',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-select-tab-config',
				'aria-selected' => 'false'
			]
		]);

		$config = json_decode(file_get_contents('vendor/Opus/apps/demo/config/demo.config.json'));

		$content = htmlspecialchars(
			json_encode($config->asyncPage->demoSelect, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$content .= ',' . PHP_EOL . htmlspecialchars(
			json_encode($config->asyncSelect, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-select-tab-config');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-select-tab-config" role="tabpanel" aria-labelledby="id_opus-btn-demo-select-tab-config" tabindex="0">
			<pre><code class="mt-3 language-json">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}
}
