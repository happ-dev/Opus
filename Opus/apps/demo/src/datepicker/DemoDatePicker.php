<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-07-22 11:59:29
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-22 18:00:28
 **/

namespace Opus\apps\demo\src\datepicker;

use stdClass;
use Opus\controller\InterfacePageController;
use Opus\html\asyncpage\AsyncPage;
use Opus\controller\lang\Lang;
use Opus\html\form\Form;
use Opus\html\table\Table;

/**
 * Async page controller for the DatePicker demo section
 *
 * Renders a demo page presenting OpusDatePicker usage with options table,
 * live input previews, and tabbed viewer (Info, PHP, JS, Config).
 */
class DemoDatePicker implements InterfacePageController
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
	 * Renders the datepicker demo async page and outputs its HTML
	 *
	 * @return void
	 */
	public function asyncAction(): void
	{
		$options = new stdClass();
		$options->headerText = 'demo.sidebar.datepicker';
		$options->headerIcon = 'bi-calendar-event';
		$options->body = $this->body();
		$apageTemplate = new AsyncPage();
		echo $apageTemplate->addAsyncPage('demo-datepicker', $options)->get();
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
	 * Builds the Info tab with options table and live picker previews
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabInfo(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-datepicker-tab-info',
			'id' => 'id_opus-btn-demo-datepicker-tab-info',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-card-text"></i><em>' . $this->lang->get('demo.datepicker.tab.info') . '</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus active',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-datepicker-options-tab-info',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-datepicker-options-tab-info',
				'aria-selected' => 'true'
			]
		]);

		$agenda = $this->lang->get('demo.datepicker.tab.agenda');
		$note = $this->lang->get('demo.datepicker.tab.note');

		// Generate date inputs previews
		$this->form->addElement([
			'name' => 'opus-input-demo-datepicker-date',
			'id' => 'id_opus-input-demo-datepicker-date',
			'tag' => 'input',
			'attributes' => [
				'style' => 'width: 300px',
				'type' => 'text',
				'class' => 'form-control date-opus-picker',
			]
		]);

		// Generate timestamp inputs previews
		$this->form->addElement([
			'name' => 'opus-input-demo-datepicker-timestamp',
			'id' => 'id_opus-input-demo-datepicker-timestamp',
			'tag' => 'input',
			'attributes' => [
				'style' => 'width: 300px',
				'type' => 'text',
				'class' => 'form-control timestamp-opus-picker',
			]
		]);

		// Generate date inputs with min date constraints previews
		$this->form->addElement([
			'name' => 'opus-input-demo-datepicker-date-min',
			'id' => 'id_opus-input-demo-datepicker-date-min',
			'tag' => 'input',
			'attributes' => [
				'style' => 'width: 300px',
				'type' => 'text',
				'class' => 'form-control date-opus-picker',
				'data-opus-picker-min' => '-3'
			]
		]);

		// Generate timestamp inputs min/max date constraints previews
		$this->form->addElement([
			'name' => 'opus-input-demo-datepicker-date-min-max',
			'id' => 'id_opus-input-demo-datepicker-date-min-max',
			'tag' => 'input',
			'attributes' => [
				'style' => 'width: 300px',
				'type' => 'text',
				'class' => 'form-control timestamp-opus-picker',
				'data-opus-picker-min' => '-3',
				'data-opus-picker-max' => '7'
			]
		]);

		$dateInput = $this->form->getElement('opus-input-demo-datepicker-date');
		$timestampInput = $this->form->getElement('opus-input-demo-datepicker-timestamp');
		$dateMinInput = $this->form->getElement('opus-input-demo-datepicker-date-min');
		$dateMinMaxInput = $this->form->getElement('opus-input-demo-datepicker-date-min-max');

		$this->table->addTable([
			'attributes' => [
				'class' => 'table table-sm table-bordered border-success',
				'id' => 'id_demo-datepicker-options-table'
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
					'option' => '.date-opus-picker',
					'type' => 'CSS class',
					'default' => '—',
					'desc' => $this->lang->get('demo.datepicker.tab.class.date.desc'),
					'preview' => '<div class="p-2">' . $dateInput . '</div>'
				],
				[
					'option' => '.timestamp-opus-picker',
					'type' => 'CSS class',
					'default' => '—',
					'desc' => $this->lang->get('demo.datepicker.tab.class.timestamp.desc'),
					'preview' => '<div class="p-2">' . $timestampInput . '</div>'
				],
				[
					'option' => 'data-opus-picker-min',
					'type' => 'number|null',
					'default' => 'null',
					'desc' => $this->lang->get('demo.datepicker.tab.min.desc'),
					'preview' => '<div class="p-2">' . $dateMinInput . '</div>'
				],
				[
					'option' => 'data-opus-picker-max',
					'type' => 'number|null',
					'default' => 'null',
					'desc' => $this->lang->get('demo.datepicker.tab.max.desc'),
					'preview' => '<div class="p-2">' . $dateMinMaxInput . '</div>'
				],
				[
					'option' => 'container',
					'type' => 'string',
					'default' => '—',
					'desc' => $this->lang->get('demo.datepicker.tab.container.desc'),
					'preview' => ''
				],
				[
					'option' => 'options.onSelect',
					'type' => 'Function|null',
					'default' => 'null',
					'desc' => $this->lang->get('demo.datepicker.tab.onSelect.desc'),
					'preview' => ''
				],
				[
					'option' => $this->lang->get('demo.datepicker.tab.format.option'),
					'type' => 'string',
					'default' => 'yyyy-MM-dd',
					'desc' => $this->lang->get('demo.datepicker.tab.format.desc'),
					'preview' => ''
				],
				[
					'option' => $this->lang->get('demo.datepicker.tab.formatTs.option'),
					'type' => 'string',
					'default' => 'yyyy-MM-dd HH:mm:ss',
					'desc' => $this->lang->get('demo.datepicker.tab.formatTs.desc'),
					'preview' => ''
				],
				[
					'option' => $this->lang->get('demo.datepicker.tab.navigation.option'),
					'type' => '—',
					'default' => '—',
					'desc' => $this->lang->get('demo.datepicker.tab.navigation.desc'),
					'preview' => ''
				],
				[
					'option' => $this->lang->get('demo.datepicker.tab.today.option'),
					'type' => 'button',
					'default' => '—',
					'desc' => $this->lang->get('demo.datepicker.tab.today.desc'),
					'preview' => ''
				],
				[
					'option' => $this->lang->get('demo.datepicker.tab.clear.option'),
					'type' => 'button',
					'default' => '—',
					'desc' => $this->lang->get('demo.datepicker.tab.clear.desc'),
					'preview' => ''
				],
				[
					'option' => $this->lang->get('demo.datepicker.tab.time.option'),
					'type' => 'HH:mm:ss',
					'default' => $this->lang->get('demo.datepicker.tab.time.default'),
					'desc' => $this->lang->get('demo.datepicker.tab.time.desc'),
					'preview' => ''
				],
			]
		]);

		$obj->button = $this->form->getElement('opus-btn-demo-datepicker-tab-info');
		$obj->content = <<<HTML
		<div class="tab-pane fade show active" id="id_opus-demo-datepicker-options-tab-info" role="tabpanel" aria-labelledby="id_opus-btn-demo-datepicker-tab-info" tabindex="0">
			<div class="container mt-3">
				<h6 class="fw-bold mb-3">js OpusDatePicker class</h6>
				<p>{$agenda}</p>
				{$this->table->getTableById('id_demo-datepicker-options-table')}
				<p class="text-muted small">{$note}</p>
			</div>
		</div>
		HTML;

		return $obj;
	}

	/**
	 * Builds the PHP tab with DemoDatePicker source code
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabPHP(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-datepicker-tab-php',
			'id' => 'id_opus-btn-demo-datepicker-tab-php',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-php"></i><em>PHP</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-datepicker-tab-php',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-datepicker-tab-php',
				'aria-selected' => 'false'
			]
		]);

		$content = htmlspecialchars(
			file_get_contents('vendor/Opus/apps/demo/src/datepicker/DemoDatePicker.php'),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-datepicker-tab-php');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-datepicker-tab-php" role="tabpanel" aria-labelledby="id_opus-btn-demo-buttons-tab-php" tabindex="0">
			<pre><code class="mt-3 language-php">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}

	/**
	 * Builds the JS tab with OpusDatePicker region from demo.js
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabJS(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-datepicker-tab-js',
			'id' => 'id_opus-btn-demo-datepicker-tab-js',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-js"></i><em>JS</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-datepicker-tab-js',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-datepicker-tab-js',
				'aria-selected' => 'false'
			]
		]);

		$file = file_get_contents('vendor/Opus/apps/demo/js/demo.js');

		// Header file
		preg_match('/^(\/\*\*.*?\*\*\/)/s', $file, $header);

		// Fragment between markers
		preg_match('/\/\/ #region OpusDatePicker\n(.*?)\/\/ #endregion OpusDatePicker/s', $file, $block);

		$content = htmlspecialchars(
			trim(($header[1] ?? '') . "\n\n" . ($block[1] ?? '')),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-datepicker-tab-js');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-datepicker-tab-js" role="tabpanel" aria-labelledby="id_opus-btn-demo-datepicker-tab-js" tabindex="0">
			<pre><code class="mt-3 language-javascript">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}

	/**
	 * Builds the Config tab with demoDatePicker entry from demo.config.json
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabConfig(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-datepicker-tab-config',
			'id' => 'id_opus-btn-demo-datepicker-tab-config',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-json"></i><em>Config</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-datepicker-tab-config',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-butdatepickertons-tab-config',
				'aria-selected' => 'false'
			]
		]);

		$config = json_decode(file_get_contents('vendor/Opus/apps/demo/config/demo.config.json'));

		$content = htmlspecialchars(
			json_encode($config->asyncPage->demoDatePicker, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-datepicker-tab-config');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-datepicker-tab-config" role="tabpanel" aria-labelledby="id_opus-btn-demo-datepicker-tab-config" tabindex="0">
			<pre><code class="mt-3 language-json">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}
}
