<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-06-06 18:46:53
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-06-30 10:30:53
 **/

namespace Opus\apps\demo\src\modal;

use stdClass;
use Opus\controller\InterfaceApiController;
use Opus\controller\lang\Lang;
use Opus\html\table\Table;

/**
 * API handler for the demo dynamic modal
 *
 * Provides AJAX response with header configuration and body content
 * including tabbed view with info, PHP source, JS source, and config examples.
 *
 * @package Opus\apps\demo\src\modal
 */
class DemoDynamicModalApi implements InterfaceApiController
{
	/**
	 * Outputs JSON response with modal header and body content
	 *
	 * @return void
	 */
	public function apiAction(): void
	{
		echo json_encode([
			'header' => $this->header(),
			'body' => $this->body(),
		]);
	}

	/**
	 * Builds header configuration array
	 *
	 * @return array Header options with text, icon, and additional text
	 */
	private function header(): array
	{
		$additionaText = Lang::getInstance()->get('demo.modal.dynamic.header.text');
		return [
			'text' => Lang::getInstance()->get('demo.modal.dynamic.header'),
			'icon' => 'bi-lightning',
			'additionalText' => <<<HTML
			<span class="modal-header-opus-additional-text">{$additionaText}</span>
			HTML,
		];
	}

	/**
	 * Builds the modal body with tabbed navigation
	 *
	 * @return string HTML content with tabs: info, PHP, PHP-Api, JS, Config
	 */
	private function body(): string
	{
		$info = $this->bodyTabInfo();
		$php = $this->bodyTabPHP();
		$phpApi = $this->bodyTabPHPApi();
		$js = $this->bodyTabJS();
		$config = $this->bodyTabConfig();
		return <<<HTML
		<ul class="nav nav-tabs nav-tabs-opus" id="id_opus-demo-dynamic-modal-tab" role="tablist">
			<!-- bodyTabInfo -->
			<li class="nav-item" role="presentation">
				<button class="nav-link nav-link-opus active" id="id_opus-btn-demo-dynamic-modal-tab-info" data-bs-toggle="tab" data-bs-target="#id_opus-demo-dynamic-modal-tab-info" type="button" role="tab" aria-controls="id_opus-demo-dynamic-modal-tab-info" aria-selected="true">
					<i class="me-1 bi {$info->icon}"></i>{$info->text}
				</button>
			</li>

			<!-- bodyTabPHP -->
			<li class="nav-item" role="presentation">
				<button class="nav-link nav-link-opus" id="id_opus-btn-demo-dynamic-modal-tab-php" data-bs-toggle="tab" data-bs-target="#id_opus-demo-dynamic-modal-tab-php" type="button" role="tab" aria-controls="id_opus-demo-dynamic-modal-tab-php" aria-selected="false">
					<i class="me-1 bi {$php->icon}"></i>{$php->text}
				</button>
			</li>

			<!-- bodyTabPHPApi -->
			<li class="nav-item" role="presentation">
				<button class="nav-link nav-link-opus" id="id_opus-btn-demo-dynamic-modal-tab-php-api" data-bs-toggle="tab" data-bs-target="#id_opus-demo-dynamic-modal-tab-php-api" type="button" role="tab" aria-controls="id_opus-demo-dynamic-modal-tab-php-api" aria-selected="false">
					<i class="me-1 bi {$phpApi->icon}"></i>{$phpApi->text}
				</button>
			</li>

			<!-- bodyTabJS -->
			<li class="nav-item" role="presentation">
				<button class="nav-link nav-link-opus" id="id_opus-btn-demo-dynamic-modal-tab-js" data-bs-toggle="tab" data-bs-target="#id_opus-demo-dynamic-modal-tab-js" type="button" role="tab" aria-controls="id_opus-demo-dynamic-modal-tab-js" aria-selected="false">
					<i class="me-1 bi {$js->icon}"></i>{$js->text}
				</button>
			</li>

			<!-- bodyTabConfig -->
			<li class="nav-item" role="presentation">
				<button class="nav-link nav-link-opus" id="id_opus-btn-demo-dynamic-modal-tab-config" data-bs-toggle="tab" data-bs-target="#id_opus-demo-dynamic-modal-tab-config" type="button" role="tab" aria-controls="id_opus-demo-dynamic-modal-tab-config" aria-selected="false">
					<i class="me-1 bi {$config->icon}"></i>{$config->text}
				</button>
			</li>
		</ul>
		<div class="tab-content" id="id_opus-demo-dynamic-modal-tab-content">
			<!-- bodyTabInfo -->
			<div class="tab-pane fade show active" id="id_opus-demo-dynamic-modal-tab-info" role="tabpanel" aria-labelledby="id_opus-btn-demo-dynamic-modal-tab-info" tabindex="0">{$info->content}</div>

			<!-- bodyTabPHP -->
			<div class="tab-pane fade" id="id_opus-demo-dynamic-modal-tab-php" role="tabpanel" aria-labelledby="id_opus-btn-demo-dynamic-modal-tab-php" tabindex="0">
				<pre><code class="mt-3 language-php">{$php->content}</code></pre>
			</div>

			<!-- bodyTabPHPApi -->
			<div class="tab-pane fade" id="id_opus-demo-dynamic-modal-tab-php-api" role="tabpanel" aria-labelledby="id_opus-btn-demo-dynamic-modal-tab-php-api" tabindex="0">
				<pre><code class="mt-3 language-php">{$phpApi->content}</code></pre>
			</div>

			<!-- bodyTabJS -->
			<div class="tab-pane fade" id="id_opus-demo-dynamic-modal-tab-js" role="tabpanel" aria-labelledby="id_opus-btn-demo-dynamic-modal-tab-js" tabindex="0">
				<pre><code class="mt-3 language-javascript">{$js->content}</code></pre>
			</div>

			<!-- bodyTabConfig -->
			<div class="tab-pane fade" id="id_opus-demo-dynamic-modal-tab-config" role="tabpanel" aria-labelledby="id_opus-btn-demo-dynamic-modal-tab-config" tabindex="0">
				<pre><code class="mt-3 language-json ">{$config->content}</code></pre>
			</div>
		</div>
		HTML;
	}

	/**
	 * Builds the info tab with modal options table
	 *
	 * @return object Object with text, icon, and content properties
	 */
	private function bodyTabInfo(): object
	{
		$obj = new stdClass();
		$obj->text = Lang::getInstance()->get('demo.modal.static.tab.info');
		$obj->icon = 'bi-info-circle';
		$agenda = Lang::getInstance()->get('demo.modal.static.tab.agenda');
		$note = Lang::getInstance()->get('demo.modal.static.tab.note');
		$lang = Lang::getInstance();
		$table = new Table();
		$table->addTable([
			'attributes' => [
				'class' => 'table table-sm table-bordered',
				'id' => 'id_demo-dynamic-modal-options'
			],
			'cname' => ['option', 'type', 'default', 'desc'],
			'thead' => [
				$lang->get('demo.modal.static.tab.option'),
				$lang->get('demo.modal.static.tab.type'),
				$lang->get('demo.modal.static.tab.default'),
				$lang->get('demo.modal.static.tab.description')
			],
			'tfoot' => false,
			'tbody' => [
				['option' => 'size', 'type' => 'string|null', 'default' => 'null', 'desc' => $lang->get('demo.modal.tab.size.desc')],
				['option' => 'centered', 'type' => 'bool', 'default' => 'false', 'desc' => $lang->get('demo.modal.tab.centered.desc')],
				['option' => 'scrollable', 'type' => 'bool', 'default' => 'false', 'desc' => $lang->get('demo.modal.tab.scrollable.desc')],
				['option' => 'shadow', 'type' => 'string', 'default' => 'bs-opus-green-3d', 'desc' => $lang->get('demo.modal.tab.shadow.desc')],
				['option' => 'ajax', 'type' => 'bool', 'default' => 'true', 'desc' => $lang->get('demo.modal.tab.ajax.desc')],
				['option' => 'form', 'type' => 'bool', 'default' => 'false', 'desc' => $lang->get('demo.modal.tab.form.desc')],
				['option' => 'method', 'type' => 'string', 'default' => 'post', 'desc' => $lang->get('demo.modal.tab.method.desc')],
				['option' => 'action', 'type' => 'string|null', 'default' => 'null', 'desc' => $lang->get('demo.modal.tab.action.desc')],
				['option' => 'csrf', 'type' => 'bool', 'default' => 'false', 'desc' => $lang->get('demo.modal.tab.csrf.desc')],
				['option' => 'static', 'type' => 'bool|array', 'default' => 'static', 'desc' => $lang->get('demo.modal.tab.static.desc')],
				['option' => 'keyboard', 'type' => 'bool|array', 'default' => 'true', 'desc' => $lang->get('demo.modal.tab.keyboard.desc')],
				['option' => 'headerClass', 'type' => 'string', 'default' => 'modal-header-opus-green', 'desc' => $lang->get('demo.modal.tab.headerClass.desc')],
				['option' => 'headerIcon', 'type' => 'string|null', 'default' => 'null', 'desc' => $lang->get('demo.modal.tab.headerIcon.desc')],
				['option' => 'headerText', 'type' => 'string|null', 'default' => 'null', 'desc' => $lang->get('demo.modal.tab.headerText.desc')],
				['option' => 'body', 'type' => 'string|null', 'default' => 'null', 'desc' => $lang->get('demo.modal.tab.body.desc')],
				['option' => 'footer', 'type' => 'string|null', 'default' => 'null', 'desc' => $lang->get('demo.modal.tab.footer.desc')]
			]
		]);
		$tableHtml = $table->getTableById('id_demo-dynamic-modal-options');
		$obj->content = <<<HTML
		<div class="container mt-3">
			<h6 class="fw-bold mb-3">Opus\html\modal\Modal</h6>
			<p>{$agenda} <code>$</code><code>options</code>:</p>
			{$tableHtml}
			<p class="text-muted small">{$note}</p>
		</div>
		HTML;
		return $obj;
	}

	/**
	 * Builds the PHP tab with DemoDynamicModal source code
	 *
	 * @return object Object with text, icon, and content properties
	 */
	private function bodyTabPHP(): object
	{
		$obj = new stdClass();
		$obj->text = 'PHP';
		$obj->icon = 'bi-filetype-php';
		$obj->content = htmlspecialchars(
			file_get_contents('vendor/Opus/apps/demo/src/modal/DemoDynamicModal.php'),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);
		return $obj;
	}

	/**
	 * Builds the PHP-Api tab with DemoDynamicModalApi source code
	 *
	 * @return object Object with text, icon, and content properties
	 */
	private function bodyTabPHPApi(): object
	{
		$obj = new stdClass();
		$obj->text = 'PHP-Api';
		$obj->icon = 'bi-filetype-php';
		$obj->content = htmlspecialchars(
			file_get_contents('vendor/Opus/apps/demo/src/modal/DemoDynamicModalApi.php'),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);
		return $obj;
	}

	/**
	 * Builds the JS tab with demo.js source code
	 *
	 * @return object Object with text, icon, and content properties
	 */
	private function bodyTabJS(): object
	{
		$obj = new stdClass();
		$obj->text = 'JS';
		$obj->icon = 'bi-filetype-js';
		$file = file_get_contents('vendor/Opus/apps/demo/js/demo.js');

		// Header file
		preg_match('/^(\/\*\*.*?\*\*\/)/s', $file, $header);

		// Fragment between markers
		preg_match('/\/\/ #region objDynamicModal\n(.*?)\/\/ #endregion objDynamicModal/s', $file, $block);

		$obj->content = htmlspecialchars(
			trim(($header[1] ?? '') . "\n\n" . ($block[1] ?? '')),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		return $obj;
	}

	/**
	 * Builds the Config tab with asyncEvent configuration excerpt
	 *
	 * @return object Object with text, icon, and content properties
	 */
	private function bodyTabConfig(): object
	{
		$obj = new stdClass();
		$obj->text = 'Config';
		$obj->icon = 'bi-filetype-json';
		$config = json_decode(file_get_contents('vendor/Opus/apps/demo/config/demo.config.json'));
		$obj->content = htmlspecialchars(
			json_encode($config->asyncEvent->demoDynamicModal, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);
		return $obj;
	}
}
