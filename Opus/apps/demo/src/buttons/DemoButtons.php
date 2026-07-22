<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-07-10 09:12:06
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-13 12:28:35
 **/

namespace Opus\apps\demo\src\buttons;

use stdClass;
use Opus\controller\InterfacePageController;
use Opus\controller\lang\Lang;
use Opus\html\form\Form;
use Opus\html\table\Table;
use Opus\html\asyncpage\AsyncPage;
use Opus\html\buttons\Buttons;

/**
 * Async page controller for the Buttons demo section
 *
 * Renders a demo page presenting all available Opus button types with live previews.
 * Includes tabbed viewer (Info, PHP, JS, Config) with a table of methods and button previews.
 */
class DemoButtons implements InterfacePageController
{
	private object $form;
	private object $lang;

	/**
	 * Initializes Form and Lang instances
	 */
	public function __construct()
	{
		$this->form = new Form();
		$this->lang = Lang::getInstance();
	}

	/**
	 * Renders the buttons demo async page and outputs its HTML
	 *
	 * @return void
	 */
	public function asyncAction(): void
	{
		$options = new stdClass();
		$options->headerText = 'demo.sidebar.buttons';
		$options->headerIcon = 'bi-type-bold';
		$options->body = $this->body();
		$apageTemplate = new AsyncPage();
		echo $apageTemplate->addAsyncPage('demo-buttons', $options)->get();
	}

	/**
	 * Builds the full body HTML for the buttons demo page
	 *
	 * Combines all tabs into a nav-tabs layout.
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
				<ul class="nav nav-tabs nav-tabs-opus" id="id_opus-demo-buttons-tab" role="tablist">
					{$buttons}
				</ul>
				<div class="tab-content" id="id_opus-demo-buttons-tab-content">
					{$contents}
				</div>
			</div>
		</div>
		HTML;
	}

	/**
	 * Builds the Info tab with buttons methods table and live previews
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabInfo(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-buttons-tab-info',
			'id' => 'id_opus-btn-demo-buttons-tab-info',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-card-text"></i><em>' . $this->lang->get('demo.buttons.tab.info') . '</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus active',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-buttons-options-tab-info',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-buttons-options-tab-info',
				'aria-selected' => 'true'
			]
		]);

		$agenda = $this->lang->get('demo.buttons.tab.agenda');
		$note = $this->lang->get('demo.buttons.tab.note');

		// Generate button previews
		$preview = new Form();
		$preview->addElement(Buttons::cancelButton('demo-preview'));
		$preview->addElement(Buttons::closeButton('demo-preview'));
		$preview->addElement(Buttons::closeButtonX('demo-preview-x'));
		$preview->addElement(Buttons::submitButton('demo-preview'));
		$preview->addElement(Buttons::saveButton('demo-preview'));
		$preview->addElement(Buttons::loginButton('demo-preview'));

		$modalBtns = Buttons::modalButtons('demo-preview');
		$offcanvasBtns = Buttons::offcanvasButtons('demo-preview-oc');

		$table = new Table();
		$table->addTable([
			'attributes' => [
				'class' => 'table table-sm table-bordered border-success',
				'id' => 'id_demo-buttons-options-table'
			],
			'cname' => ['method', 'params', 'desc', 'preview'],
			'thead' => [
				$this->lang->get('demo.buttons.tab.method'),
				$this->lang->get('demo.buttons.tab.params'),
				$this->lang->get('demo.modal.static.tab.description'),
				$this->lang->get('demo.buttons.tab.preview')
			],
			'tfoot' => false,
			'tbody' => [
				[
					'method' => 'cancelButton',
					'params' => '$name, $dataBsDismiss',
					'desc' => $this->lang->get('demo.buttons.tab.cancelButton.desc'),
					'preview' => <<<HTML
					<div class="d-grid justify-content-center p-2">{$preview->getElement('cancel-btn-demo-preview')}</div>
					HTML
				],
				[
					'method' => 'closeButton',
					'params' => '$name, $options',
					'desc' => $this->lang->get('demo.buttons.tab.closeButton.desc'),
					'preview' => <<<HTML
					<div class="d-grid justify-content-center p-2">{$preview->getElement('close-btn-demo-preview')}</div>
					HTML
				],
				[
					'method' => 'closeButtonX',
					'params' => '$name, $options',
					'desc' => $this->lang->get('demo.buttons.tab.closeButtonX.desc'),
					'preview' => <<<HTML
					<div class="d-grid justify-content-center p-2">{$preview->getElement('close-btn-demo-preview-x')}</div>
					HTML
				],
				[
					'method' => 'submitButton',
					'params' => '$name',
					'desc' => $this->lang->get('demo.buttons.tab.submitButton.desc'),
					'preview' => <<<HTML
					<div class="d-grid justify-content-center p-2">{$preview->getElement('submit-btn-demo-preview')}</div>
					HTML
				],
				[
					'method' => 'saveButton',
					'params' => '$name',
					'desc' => $this->lang->get('demo.buttons.tab.saveButton.desc'),
					'preview' => <<<HTML
					<div class="d-grid justify-content-center p-2">{$preview->getElement('save-btn-demo-preview')}</div>
					HTML
				],
				[
					'method' => 'loginButton',
					'params' => '$name',
					'desc' => $this->lang->get('demo.buttons.tab.loginButton.desc'),
					'preview' => <<<HTML
					<div class="d-grid justify-content-center p-2">{$preview->getElement('login-btn-demo-preview')}</div>
					HTML
				],
				[
					'method' => 'modalButtons',
					'params' => '$name, $options',
					'desc' => $this->lang->get('demo.buttons.tab.modalButtons.desc'),
					'preview' => <<<HTML
					<div class="d-flex justify-content-evenly p-2 me-2">
						{$modalBtns->getElement('cancel-btn-demo-preview')}
						{$modalBtns->getElement('submit-btn-demo-preview')}
						{$modalBtns->getElement('close-btn-demo-preview')}
					</div>
					HTML
				],
				[
					'method' => 'offcanvasButtons',
					'params' => '$name, $options',
					'desc' => $this->lang->get('demo.buttons.tab.offcanvasButtons.desc'),
					'preview' => <<<HTML
					<div class="d-flex justify-content-evenly p-2 me-2">
						{$offcanvasBtns->getElement('cancel-btn-demo-preview-oc')}
						{$offcanvasBtns->getElement('submit-btn-demo-preview-oc')}
						{$offcanvasBtns->getElement('close-btn-demo-preview-oc')}
					</div>
					HTML
				],
			]
		]);

		$obj->button = $this->form->getElement('opus-btn-demo-buttons-tab-info');
		$obj->content = <<<HTML
		<div class="tab-pane fade show active" id="id_opus-demo-buttons-options-tab-info" role="tabpanel" aria-labelledby="id_opus-btn-demo-buttons-tab-info" tabindex="0">
			<div class="container mt-3">
				<h6 class="fw-bold mb-3">Opus\html\buttons\Buttons</h6>
				<p>{$agenda}</p>
				{$table->getTableById('id_demo-buttons-options-table')}
				<p class="text-muted small">{$note}</p>
			</div>
		</div>
		HTML;

		return $obj;
	}

	/**
	 * Builds the PHP tab with DemoButtons source code
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabPHP(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-buttons-tab-php',
			'id' => 'id_opus-btn-demo-buttons-tab-php',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-php"></i><em>PHP</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-buttons-tab-php',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-buttons-tab-php',
				'aria-selected' => 'false'
			]
		]);

		$content = htmlspecialchars(
			file_get_contents('vendor/Opus/apps/demo/src/buttons/DemoButtons.php'),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-buttons-tab-php');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-buttons-tab-php" role="tabpanel" aria-labelledby="id_opus-btn-demo-buttons-tab-php" tabindex="0">
			<pre><code class="mt-3 language-php">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}

	/**
	 * Builds the JS tab with buttons JavaScript info
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabJS(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-buttons-tab-js',
			'id' => 'id_opus-btn-demo-buttons-tab-js',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-js"></i><em>JS</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-buttons-tab-js',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-buttons-tab-js',
				'aria-selected' => 'false'
			]
		]);

		$content = $this->lang->get('demo.buttons.tab.js.code');

		$obj->button = $this->form->getElement('opus-btn-demo-buttons-tab-js');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-buttons-tab-js" role="tabpanel" aria-labelledby="id_opus-btn-demo-buttons-tab-js" tabindex="0">
			<div class="alert alert-info mt-3" role="alert">{$content}</div>
		</div>
		HTML;

		return $obj;
	}

	/**
	 * Builds the Config tab with demoButtons entry from demo.config.json
	 *
	 * @return object{button: string, content: string}
	 */
	private function bodyTabConfig(): object
	{
		$obj = new stdClass();
		$this->form->addElement([
			'name' => 'opus-btn-demo-buttons-tab-config',
			'id' => 'id_opus-btn-demo-buttons-tab-config',
			'tag' => 'button',
			'text' => '<i class="me-1 bi bi-filetype-json"></i><em>Config</em>',
			'attributes' => [
				'type' => 'button',
				'class' => 'nav-link nav-link-opus',
				'data-bs-toggle ' => 'tab',
				'data-bs-target' => '#id_opus-demo-buttons-tab-config',
				'role' => 'tab',
				'aria-controls' => 'id_opus-demo-buttons-tab-config',
				'aria-selected' => 'false'
			]
		]);

		$config = json_decode(file_get_contents('vendor/Opus/apps/demo/config/demo.config.json'));

		$content = htmlspecialchars(
			json_encode($config->asyncPage->demoButtons, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$obj->button = $this->form->getElement('opus-btn-demo-buttons-tab-config');
		$obj->content = <<<HTML
		<div class="tab-pane fade" id="id_opus-demo-buttons-tab-config" role="tabpanel" aria-labelledby="id_opus-btn-demo-buttons-tab-config" tabindex="0">
			<pre><code class="mt-3 language-json">{$content}</code></pre>
		</div>
		HTML;

		return $obj;
	}
}
