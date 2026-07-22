<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-07-04 10:47:26
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-13 12:41:54
 **/

namespace Opus\apps\demo\src\collapse;

use stdClass;
use Opus\controller\InterfacePageController;
use Opus\controller\lang\Lang;
use Opus\html\form\Form;
use Opus\html\table\Table;
use Opus\html\asyncpage\AsyncPage;
use Opus\html\collapse\Collapse;

/**
 * Async page controller for the Collapse demo section
 *
 * Renders a demo page presenting both static and dynamic Bootstrap collapse usage.
 * Includes tabbed source code viewer (PHP, PHP-Api, JS, Config) and two collapse instances.
 */
class DemoCollapse implements InterfacePageController
{
	private object $form;
	private object $lang;
	private object $collapse;

	/**
	 * Initializes Form, Lang and Collapse instances
	 */
	public function __construct()
	{
		$this->form = new Form();
		$this->lang = Lang::getInstance();
		$this->collapse = new Collapse();
	}

	/**
	 * Renders the collapse demo async page and outputs its HTML
	 *
	 * @return void
	 */
	public function asyncAction(): void
	{
		$options = new stdClass();
		$options->headerText = 'demo.sidebar.collapse';
		$options->headerIcon = 'bi-arrows-collapse';
		$options->body = $this->body();
		$apageTemplate = new AsyncPage();
		echo $apageTemplate->addAsyncPage('demo-collapse', $options)->get();
	}

	/**
	 * Builds the full body HTML for the collapse demo page
	 *
	 * Combines toggle buttons, static and dynamic collapse components, and tabbed code viewer.
	 *
	 * @return string Complete body HTML
	 */
	private function body(): string
	{
		$this->collapseStatic();
		$this->collapseDynamic();

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

		return <<<HTML
		<div class="row mb-3">
			<div class="col">
			<!-- collapse-btn-opus-demo-static-collapse -->
			{$this->collapse->getCollapseButton('opus-demo-static-collapse')}
			<!-- collapse-btn-opus-demo-dynamic-collapse -->
			{$this->collapse->getCollapseButton('opus-demo-dynamic-collapse')}
			</div>
		</div>
		<div class="row">
			<div class="col-6">{$this->collapse->getCollapse('opus-demo-static-collapse')}</div>
			<div class="col-6">{$this->collapse->getCollapse('opus-demo-dynamic-collapse')}</div>
		</div>
		<div class="row">
			<div class="col">
				<ul class="nav nav-tabs nav-tabs-opus" id="id_opus-demo-collapse-tab" role="tablist">
					{$buttons}
				</ul>
				<div class="tab-content" id="id_opus-demo-collapse-tab-content">
					{$contents}
				</div>
			</div>
		</div>
		HTML;
	}

	/**
	 * Builds and registers the static collapse component
	 *
	 * No AJAX support — content is fully rendered server-side.
	 *
	 * @return void
	 */
	private function collapseStatic(): void
	{
		$options = new stdClass();
		$options->buttonText = 'demo.collapse.button.static';
		$options->buttonColor = 'btn-dark';
		$options->headerIcon = 'bi-arrows-collapse';
		$options->headerText = 'demo.collapse.button.static';
		$options->additionalClasses = 'mb-4';
		$options->body = <<<HTML
		<div class="alert alert-info mt-3" role="alert">
			{$this->lang->get('demo.collapse.static.content.info')}
		</div>
		<div class="alert alert-success mt-3" role="alert">
			{$this->lang->get('demo.collapse.static.content.js')}
		</div>
		HTML;
		$options->ajax = false;
		$this->collapse->addCollapse('opus-demo-static-collapse', $options);
	}

	/**
	 * Builds and registers the dynamic collapse component
	 *
	 * Content is loaded via AJAX using DemoDynamicCollapseApi.
	 *
	 * @return void
	 */
	private function collapseDynamic(): void
	{
		$options = new stdClass();
		$options->buttonText = 'demo.collapse.button.dynamic';
		$options->buttonColor = 'btn-dark';
		$options->headerIcon = 'bi-arrows-collapse';
		$options->headerText = 'demo.collapse.button.dynamic';
		$options->additionalClasses = 'mb-4';
		$this->collapse->addCollapse('opus-demo-dynamic-collapse', $options);
	}

	/**
	 * Builds the Info tab with collapse options table
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabInfo(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-collapse-tab-info',
			'id' => 'id_opus-btn-demo-collapse-tab-info',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-card-text"></i><em>' . $this->lang->get('demo.collapse.tab.info') . '</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus active',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-collapse-options-tab-info',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-collapse-options-tab-info',
				'aria-selected' => 'true'
			]
		]);

		$agenda = $this->lang->get('demo.collapse.tab.agenda');
		$note = $this->lang->get('demo.collapse.tab.note');

		$table = new Table();
		$table->addTable([
			'attributes' => [
				'class' => 'table table-sm table-bordered border-success',
				'id' => 'id_demo-collapse-options-table'
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
				['option' => 'additionalClasses', 'type' => 'string|null', 'default' => 'null', 'desc' => $this->lang->get('demo.collapse.tab.additionalClasses.desc')],
				['option' => 'ajax', 'type' => 'bool', 'default' => 'true', 'desc' => $this->lang->get('demo.collapse.tab.ajax.desc')],
				['option' => 'id', 'type' => 'string', 'default' => "'id__' . \$name", 'desc' => $this->lang->get('demo.offcanvas.tab.formId.desc')],
				['option' => 'headerClasses', 'type' => 'string', 'default' => 'collapse-header-opus collapse-header-opus-green', 'desc' => $this->lang->get('demo.collapse.tab.headerClasses.desc')],
				['option' => 'headerIcon', 'type' => 'string|null', 'default' => 'null', 'desc' => $this->lang->get('demo.collapse.tab.headerIcon.desc')],
				['option' => 'headerText', 'type' => 'string|null', 'default' => 'null', 'desc' => $this->lang->get('demo.collapse.tab.headerText.desc')],
				['option' => 'additionalHeaderTextClasses', 'type' => 'string|null', 'default' => 'null', 'desc' => $this->lang->get('demo.collapse.tab.additionalHeaderTextClasses.desc')],
				['option' => 'body', 'type' => 'string|null', 'default' => 'null', 'desc' => $this->lang->get('demo.collapse.tab.body.desc')],
				['option' => 'footer', 'type' => 'string|null', 'default' => 'null', 'desc' => $this->lang->get('demo.collapse.tab.footer.desc')],
				['option' => 'footerClasses', 'type' => 'string', 'default' => 'justify-content-center p-3', 'desc' => $this->lang->get('demo.collapse.tab.footerClasses.desc')],
				['option' => 'additionalFooterClasses', 'type' => 'string|null', 'default' => 'null', 'desc' => $this->lang->get('demo.collapse.tab.additionalFooterClasses.desc')],
				['option' => 'buttonText', 'type' => 'string|null', 'default' => 'null', 'desc' => $this->lang->get('demo.collapse.tab.buttonText.desc')],
				['option' => 'buttonColor', 'type' => 'string', 'default' => 'btn-primary', 'desc' => $this->lang->get('demo.collapse.tab.buttonColor.desc')],
				['option' => 'buttonIcon', 'type' => 'string', 'default' => 'bi-arrows-expand', 'desc' => $this->lang->get('demo.collapse.tab.buttonIcon.desc')],
			]
		]);

		$obj->button = $this->form->getElement('opus-btn-demo-collapse-tab-info');
		$obj->content = <<<HTML
		<div class="tab-pane fade show active" id="id_opus-demo-collapse-options-tab-info" role="tabpanel" aria-labelledby="id_opus-btn-demo-collapse-tab-info" tabindex="0">
			<div class="container mt-3">
				<h6 class="fw-bold mb-3">Opus\html\collapse\Collapse</h6>
				<p>{$agenda} <code>$</code><code>options</code>:</p>
				{$table->getTableById('id_demo-collapse-options-table')}
				<p class="text-muted small">{$note}</p>
			</div>
		</div>
		HTML;
		return $obj;
	}

	/**
	 * Builds the PHP tab with DemoCollapse source code
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabPHP(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-collapse-tab-php',
			'id' => 'id_opus-btn-demo-collapse-tab-php',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-php"></i><em>PHP</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-collapse-tab-php',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-collapse-tab-php',
				'aria-selected' => 'false'
			]
		]);

		$content = htmlspecialchars(
			file_get_contents('vendor/Opus/apps/demo/src/collapse/DemoCollapse.php'),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-collapse-tab-php');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-collapse-tab-php" role="tabpanel" aria-labelledby="id_opus-btn-demo-collapse-tab-php" tabindex="0">
			<pre><code class="mt-3 language-php">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}

	/**
	 * Builds the PHP-Api tab with DemoDynamicCollapseApi source code
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabPHPApi(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-collapse-tab-php-api',
			'id' => 'id_opus-btn-demo-collapse-tab-php-api',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-php"></i><em>PHP-Api</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-collapse-tab-php-api',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-collapse-tab-php-api',
				'aria-selected' => 'false'
			]
		]);

		$content = htmlspecialchars(
			file_get_contents('vendor/Opus/apps/demo/src/collapse/DemoDynamicCollapseApi.php'),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-collapse-tab-php-api');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-collapse-tab-php-api" role="tabpanel" aria-labelledby="id_opus-btn-demo-collapse-tab-php-api" tabindex="0">
			<pre><code class="mt-3 language-php">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}

	/**
	 * Builds the JS tab with the demoCollapse code block extracted from demo.js
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabJS(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-collapse-tab-js',
			'id' => 'id_opus-btn-demo-collapse-tab-js',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-js"></i><em>JS</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-collapse-tab-js',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-collapse-tab-js',
				'aria-selected' => 'false'
			]
		]);

		$file = file_get_contents('vendor/Opus/apps/demo/js/demo.js');

		// Header file
		preg_match('/^(\/\*\*.*?\*\*\/)/s', $file, $header);

		// Fragment between markers
		preg_match('/\/\/ #region objDynamicCollapse\n(.*?)\/\/ #endregion objDynamicCollapse/s', $file, $block);

		$content = htmlspecialchars(
			trim(($header[1] ?? '') . "\n\n" . ($block[1] ?? '')),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-collapse-tab-js');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-collapse-tab-js" role="tabpanel" aria-labelledby="id_opus-btn-demo-collapse-tab-js" tabindex="0">
			<pre><code class="mt-3 language-javascript">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}

	/**
	 * Builds the Config tab with demoDynamicCollapse entry from demo.config.json
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabConfig(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-collapse-tab-config',
			'id' => 'id_opus-btn-demo-collapse-tab-config',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-json"></i><em>Config</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-collapse-tab-config',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-collapse-tab-config',
				'aria-selected' => 'false'
			]
		]);

		$config = json_decode(file_get_contents('vendor/Opus/apps/demo/config/demo.config.json'));

		$content = htmlspecialchars(
			json_encode($config->asyncEvent->demoDynamicCollapse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-collapse-tab-config');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-collapse-tab-config" role="tabpanel" aria-labelledby="id_opus-btn-demo-collapse-tab-config" tabindex="0">
			<pre><code class="mt-3 language-json">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}
}
