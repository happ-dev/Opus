<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-06-27 03:33:22
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-13 12:42:59
 **/

namespace Opus\apps\demo\src\offcanvas;

use stdClass;
use Opus\controller\InterfacePageController;
use Opus\html\asyncpage\AsyncPage;
use Opus\controller\lang\Lang;
use Opus\html\form\Form;
use Opus\html\table\Table;
use Opus\html\offcanvas\Offcanvas;
use Opus\html\buttons\Buttons;

/**
 * Async page controller for the Offcanvas demo section
 *
 * Renders a demo page presenting both static and dynamic Bootstrap offcanvas usage.
 * Includes tabbed source code viewer (PHP, PHP-Api, JS, Config) and two offcanvas instances.
 */
class DemoOffcanvas implements InterfacePageController
{
	private object $form;
	private object $lang;
	private object $offcanvas;

	/**
	 * Initializes Form, Lang and Offcanvas instances
	 */
	public function __construct()
	{
		$this->form = new Form();
		$this->lang = Lang::getInstance();
		$this->offcanvas = new Offcanvas();
	}

	/**
	 * Renders the offcanvas demo async page and outputs its HTML
	 *
	 * @return void
	 */
	public function asyncAction(): void
	{
		$options = new stdClass();
		$options->headerText = 'demo.offcanvas.page.header';
		$options->headerIcon = 'bi-layout-sidebar-inset';
		$options->body = $this->body();
		$apageTemplate = new AsyncPage();
		echo $apageTemplate->addAsyncPage('demo-offcanvas', $options)->get();
	}

	/**
	 * Builds the full body HTML for the offcanvas demo page
	 *
	 * Combines trigger buttons, tabbed code viewer, static offcanvas and dynamic offcanvas.
	 *
	 * @return string Complete body HTML
	 */
	private function body(): string
	{
		$tabs = [
			$this->bodyTabInfo(),
			$this->bodyTabPHP(),
			$this->bodyTabPHPApi(),
			$this->bodyTabJS(),
			$this->bodyTabConfig(),
		];

		$buttons = '';
		$contents = '';

		foreach ($tabs as $tab) {
			$buttons .= "<li class=\"nav-item\" role=\"presentation\">{$tab->button}</li>";
			$contents .= $tab->content;
		}

		$triggerButtons = $this->offcanvasButtons();

		return <<<HTML
		{$triggerButtons}
		<div class="row">
			<div class="col">
				<ul class="nav nav-tabs nav-tabs-opus" id="id_opus-demo-offcanvas-tab" role="tablist">
					{$buttons}
				</ul>
				<div class="tab-content" id="id_opus-demo-offcanvas-tab-content">
					{$contents}
				</div>
			</div>
		</div>
		{$this->offcanvasStatic()}
		{$this->offcanvasDynamic()}
		HTML;
	}

	/**
	 * Builds trigger buttons HTML for static and dynamic offcanvas
	 *
	 * @return string HTML with two Bootstrap offcanvas trigger buttons
	 */
	private function offcanvasButtons(): string
	{
		// static-offcanvas
		$this->form->addElement([
			'name' => 'opus-btn-demo-static-offcanvas',
			'id' => 'id_opus-btn-demo-static-offcanvas',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-layout-sidebar-inset"></i><em>' . $this->lang->get('demo.offcanvas.button.static') . '</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'btn btn-dark btn-sm bs-opus-black-3d',
				'data-bs-toggle' => 'offcanvas',
				'data-bs-target' => '#id__opus-demo-static-offcanvas',
				'aria-controls' => 'id__opus-demo-static-offcanvas'
			]
		]);

		// dynamic-offcanvas
		$this->form->addElement([
			'name' => 'opus-btn-demo-dynamic-offcanvas',
			'id' => 'id_opus-btn-demo-dynamic-offcanvas',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-layout-sidebar-inset-reverse"></i><em>' . $this->lang->get('demo.offcanvas.button.dynamic') . '</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'btn btn-dark btn-sm bs-opus-black-3d',
				'data-bs-toggle' => 'offcanvas',
				'data-bs-target' => '#id__opus-demo-dynamic-offcanvas',
				'aria-controls' => 'id__opus-demo-dynamic-offcanvas'
			]
		]);

		return <<<HTML
		<div class="row mb-3">
			<div class="col">
				<!-- id__opus-demo-static-offcanvas -->
				{$this->form->getElement('opus-btn-demo-static-offcanvas')}
				<!-- id__opus-demo-dynamic-offcanvas -->
				{$this->form->getElement('opus-btn-demo-dynamic-offcanvas')}
			</div>
		</div>
		HTML;
	}

	/**
	 * Builds and returns the static offcanvas HTML
	 *
	 * Placement start, scrollable body, no AJAX loader.
	 *
	 * @return string Complete static offcanvas HTML
	 */
	private function offcanvasStatic(): string
	{
		$options = new stdClass();
		$options->placement ??= 'start';
		$options->scrollable = true;
		$options->ajax = false;
		$options->headerIcon = 'bi-layout-sidebar-inset';
		$options->headerText = 'demo.offcanvas.button.static';
		$options->body = <<<HTML
		<div class="alert alert-info mt-3" role="alert">
			{$this->lang->get('demo.offcanvas.static.content.info')}
		</div>
		<div class="alert alert-success mt-3" role="alert">
			{$this->lang->get('demo.offcanvas.static.content.js')}
		</div>
		HTML;

		$this->offcanvas->addOffcanvas('opus-demo-static-offcanvas', $options);
		return $this->offcanvas->getOffcanvasByName('opus-demo-static-offcanvas');
	}

	/**
	 * Builds and returns the dynamic offcanvas HTML
	 *
	 * Content is loaded via AJAX using OpusOffcanvas and DemoDynamicOffcanvasApi.
	 *
	 * @return string Complete dynamic offcanvas HTML
	 */
	private function offcanvasDynamic(): string
	{
		$options = new stdClass();
		$button = Buttons::closeButton(
			'opus-demo-dynamic-offcanvas',
			['data-bs-dismiss' => 'offcanvas']
		);
		$this->form->addElement($button);
		$options->footer = $this->form->getElement('close-btn-opus-demo-dynamic-offcanvas');
		$this->offcanvas->addOffcanvas('opus-demo-dynamic-offcanvas', $options);
		return $this->offcanvas->getOffcanvasByName('opus-demo-dynamic-offcanvas');
	}

	/**
	 * Builds the Info tab with offcanvas options table
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabInfo(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-offcanvas-tab-info',
			'id' => 'id_opus-btn-demo-offcanvas-tab-info',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-card-text"></i><em>' . $this->lang->get('demo.offcanvas.tab.info') . '</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus active',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-offcanvas-options-tab-info',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-offcanvas-options-tab-info',
				'aria-selected' => 'true'
			]
		]);

		$agenda = $this->lang->get('demo.offcanvas.tab.agenda');
		$note = $this->lang->get('demo.offcanvas.tab.note');

		$table = new Table();
		$table->addTable([
			'attributes' => [
				'class' => 'table table-sm table-bordered border-success',
				'id' => 'id_demo-offcanvas-options-table'
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
				['option' => 'size', 'type' => 'string|null', 'default' => 'null', 'desc' => $this->lang->get('demo.offcanvas.tab.size.desc')],
				['option' => 'scrollable', 'type' => 'bool|array', 'default' => 'false', 'desc' => $this->lang->get('demo.offcanvas.tab.scrollable.desc')],
				['option' => 'static', 'type' => 'bool|array', 'default' => "['data-bs-backdrop' => 'static']", 'desc' => $this->lang->get('demo.offcanvas.tab.static.desc')],
				['option' => 'placement', 'type' => 'string', 'default' => 'end', 'desc' => $this->lang->get('demo.offcanvas.tab.placement.desc')],
				['option' => 'shadow', 'type' => 'string|bool', 'default' => 'bs-opus-green-3d', 'desc' => $this->lang->get('demo.offcanvas.tab.shadow.desc')],
				['option' => 'keyboard', 'type' => 'bool|array', 'default' => "['data-bs-keyboard' => 'true']", 'desc' => $this->lang->get('demo.offcanvas.tab.keyboard.desc')],
				['option' => 'ajax', 'type' => 'bool', 'default' => 'true', 'desc' => $this->lang->get('demo.offcanvas.tab.ajax.desc')],
				['option' => 'form', 'type' => 'bool', 'default' => 'false', 'desc' => $this->lang->get('demo.offcanvas.tab.form.desc')],
				['option' => 'formId', 'type' => 'string', 'default' => 'id__name-form', 'desc' => $this->lang->get('demo.offcanvas.tab.formId.desc')],
				['option' => 'method', 'type' => 'string', 'default' => 'post', 'desc' => $this->lang->get('demo.offcanvas.tab.method.desc')],
				['option' => 'action', 'type' => 'string|null', 'default' => 'null', 'desc' => $this->lang->get('demo.offcanvas.tab.action.desc')],
				['option' => 'csrf', 'type' => 'bool', 'default' => 'false', 'desc' => $this->lang->get('demo.offcanvas.tab.csrf.desc')],
				['option' => 'headerClass', 'type' => 'string', 'default' => 'offcanvas-header-opus-green bs-opus-green', 'desc' => $this->lang->get('demo.offcanvas.tab.headerClass.desc')],
				['option' => 'headerIcon', 'type' => 'string|null', 'default' => 'null', 'desc' => $this->lang->get('demo.offcanvas.tab.headerIcon.desc')],
				['option' => 'headerText', 'type' => 'string|null', 'default' => 'null', 'desc' => $this->lang->get('demo.offcanvas.tab.headerText.desc')],
				['option' => 'body', 'type' => 'string|null', 'default' => 'null', 'desc' => $this->lang->get('demo.offcanvas.tab.body.desc')],
				['option' => 'footer', 'type' => 'string|null', 'default' => 'null', 'desc' => $this->lang->get('demo.offcanvas.tab.footer.desc')],
			]
		]);

		$obj->button = $this->form->getElement('opus-btn-demo-offcanvas-tab-info');
		$obj->content = <<<HTML
		<div class="tab-pane fade show active" id="id_opus-demo-offcanvas-options-tab-info" role="tabpanel" aria-labelledby="id_opus-btn-demo-offcanvas-tab-info" tabindex="0">
			<div class="container mt-3">
				<h6 class="fw-bold mb-3">Opus\html\offcanvas\Offcanvas</h6>
				<p>{$agenda} <code>$</code><code>options</code>:</p>
				{$table->getTableById('id_demo-offcanvas-options-table')}
				<p class="text-muted small">{$note}</p>
			</div>
		</div>
		HTML;

		return $obj;
	}

	/**
	 * Builds the PHP tab with DemoOffcanvas source code
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabPHP(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-offcanvas-tab-php',
			'id' => 'id_opus-btn-demo-offcanvas-tab-php',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-php"></i><em>PHP</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-offcanvas-tab-php',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-offcanvas-tab-php',
				'aria-selected' => 'false'
			]
		]);

		$content = htmlspecialchars(
			file_get_contents('vendor/Opus/apps/demo/src/offcanvas/DemoOffcanvas.php'),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-offcanvas-tab-php');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-offcanvas-tab-php" role="tabpanel" aria-labelledby="id_opus-btn-demo-offcanvas-tab-php" tabindex="0">
			<pre><code class="mt-3 language-php">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}

	/**
	 * Builds the PHP-Api tab with DemoDynamicOffcanvasApi source code
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabPHPApi(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-offcanvas-tab-php-api',
			'id' => 'id_opus-btn-demo-offcanvas-tab-php-api',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-php"></i><em>PHP-Api</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-offcanvas-tab-php-api',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-offcanvas-tab-php-api',
				'aria-selected' => 'false'
			]
		]);

		$content = htmlspecialchars(
			file_get_contents('vendor/Opus/apps/demo/src/offcanvas/DemoDynamicOffcanvasApi.php'),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-offcanvas-tab-php-api');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-offcanvas-tab-php-api" role="tabpanel" aria-labelledby="id_opus-btn-demo-offcanvas-tab-php-api" tabindex="0">
			<pre><code class="mt-3 language-php">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}

	/**
	 * Builds the JS tab with the objDynamicOffcanvas code block extracted from demo.js
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabJS(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-offcanvas-tab-js',
			'id' => 'id_opus-btn-demo-offcanvas-tab-js',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-js"></i><em>JS</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-offcanvas-tab-js',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-offcanvas-tab-js',
				'aria-selected' => 'false'
			]
		]);

		$file = file_get_contents('vendor/Opus/apps/demo/js/demo.js');

		// Header file
		preg_match('/^(\/\*\*.*?\*\*\/)/s', $file, $header);

		// Fragment between markers
		preg_match('/\/\/ #region objDynamicOffcanvas\n(.*?)\/\/ #endregion objDynamicOffcanvas/s', $file, $block);

		$content = htmlspecialchars(
			trim(($header[1] ?? '') . "\n\n" . ($block[1] ?? '')),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-offcanvas-tab-js');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-offcanvas-tab-js" role="tabpanel" aria-labelledby="id_opus-btn-demo-offcanvas-tab-js" tabindex="0">
			<pre><code class="mt-3 language-javascript">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}

	/**
	 * Builds the Config tab with demoDynamicOffcanvas entry from demo.config.json
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabConfig(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-offcanvas-tab-config',
			'id' => 'id_opus-btn-demo-offcanvas-tab-config',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-json"></i><em>Config</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-offcanvas-tab-config',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-offcanvas-tab-config',
				'aria-selected' => 'false'
			]
		]);

		$config = json_decode(file_get_contents('vendor/Opus/apps/demo/config/demo.config.json'));

		$content = htmlspecialchars(
			json_encode($config->asyncEvent->demoDynamicOffcanvas, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-offcanvas-tab-config');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-offcanvas-tab-config" role="tabpanel" aria-labelledby="id_opus-btn-demo-offcanvas-tab-config" tabindex="0">
			<pre><code class="mt-3 language-javascript">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}
}
