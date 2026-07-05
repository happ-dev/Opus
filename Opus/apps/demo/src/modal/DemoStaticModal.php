<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-06-06 15:18:03
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-06-26 01:03:25
 **/

namespace Opus\apps\demo\src\modal;

use stdClass;
use Opus\controller\lang\Lang;
use Opus\html\modal\Modal;
use Opus\html\table\Table;
use Opus\html\buttons\Buttons;

/**
 * Demonstrates how to create a static (server-rendered) modal using Opus\html\modal\Modal
 *
 * This class serves as a showcase for the Demo application, illustrating
 * the creation of a modal that is fully rendered server-side and loaded
 * with the page without any JavaScript code required.
 *
 * @package Opus\apps\demo\src\modal
 */
class DemoStaticModal
{
	private static ?self $instance = null;

	/**
	 * Returns a singleton instance of the demo static modal
	 *
	 * Uses singleton pattern to prevent multiple instantiations
	 * of the same modal within a single request.
	 *
	 * @return object Modal instance configured for the demo application
	 */
	public static function demoModal(): object
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance->createModal();
	}

	/**
	 * Builds the static modal with tabbed content: options info, PHP source, and JS note
	 *
	 * Creates an XL modal containing:
	 * - Info tab: table with all available $options parameters (using Opus\html\table\Table)
	 * - PHP tab: syntax-highlighted source code of this file (using highlight.js)
	 * - JS tab: informational message that no JS is needed for static modals
	 *
	 * @return Modal Configured Modal instance ready to be rendered
	 */
	private function createModal(): Modal
	{
		$buttons = Buttons::modalButtons('opus-demo-static-modal');
		$options = new stdClass();
		$options->ajax = false;
		$options->size = 'xl';
		$options->headerIcon = 'bi-window';
		$options->headerText = 'demo.modal.static.header';
		$additionalHeaderText = Lang::getInstance()->get('demo.modal.static.header.text');
		$firstTabText = Lang::getInstance()->get('demo.modal.static.tab.info');
		$firstTabNote = Lang::getInstance()->get('demo.modal.static.tab.note');
		$firstTabAgenda = Lang::getInstance()->get('demo.modal.static.tab.agenda');

		$l = Lang::getInstance();
		$table = new Table();
		$table->addTable([
			'attributes' => [
				'class' => 'table table-sm table-bordered',
				'id' => 'id_demo-static-modal-options'
			],
			'cname' => ['option', 'type', 'default', 'desc'],
			'thead' => [
				$l->get('demo.modal.static.tab.option'),
				$l->get('demo.modal.static.tab.type'),
				$l->get('demo.modal.static.tab.default'),
				$l->get('demo.modal.static.tab.description')
			],
			'tfoot' => false,
			'tbody' => [
				['option' => 'size', 'type' => 'string|null', 'default' => 'null', 'desc' => $l->get('demo.modal.tab.size.desc')],
				['option' => 'centered', 'type' => 'bool', 'default' => 'false', 'desc' => $l->get('demo.modal.tab.centered.desc')],
				['option' => 'scrollable', 'type' => 'bool', 'default' => 'false', 'desc' => $l->get('demo.modal.tab.scrollable.desc')],
				['option' => 'shadow', 'type' => 'string', 'default' => 'bs-opus-green-3d', 'desc' => $l->get('demo.modal.tab.shadow.desc')],
				['option' => 'ajax', 'type' => 'bool', 'default' => 'true', 'desc' => $l->get('demo.modal.tab.ajax.desc')],
				['option' => 'form', 'type' => 'bool', 'default' => 'false', 'desc' => $l->get('demo.modal.tab.form.desc')],
				['option' => 'method', 'type' => 'string', 'default' => 'post', 'desc' => $l->get('demo.modal.tab.method.desc')],
				['option' => 'action', 'type' => 'string|null', 'default' => 'null', 'desc' => $l->get('demo.modal.tab.action.desc')],
				['option' => 'csrf', 'type' => 'bool', 'default' => 'false', 'desc' => $l->get('demo.modal.tab.csrf.desc')],
				['option' => 'static', 'type' => 'bool|array', 'default' => 'static', 'desc' => $l->get('demo.modal.tab.static.desc')],
				['option' => 'keyboard', 'type' => 'bool|array', 'default' => 'true', 'desc' => $l->get('demo.modal.tab.keyboard.desc')],
				['option' => 'headerClass', 'type' => 'string', 'default' => 'modal-header-opus-green', 'desc' => $l->get('demo.modal.tab.headerClass.desc')],
				['option' => 'headerIcon', 'type' => 'string|null', 'default' => 'null', 'desc' => $l->get('demo.modal.tab.headerIcon.desc')],
				['option' => 'headerText', 'type' => 'string|null', 'default' => 'null', 'desc' => $l->get('demo.modal.tab.headerText.desc')],
				['option' => 'body', 'type' => 'string|null', 'default' => 'null', 'desc' => $l->get('demo.modal.tab.body.desc')],
				['option' => 'footer', 'type' => 'string|null', 'default' => 'null', 'desc' => $l->get('demo.modal.tab.footer.desc')]
			]
		]);
		$tableHtml = $table->getTableById('id_demo-static-modal-options');

		$phpCode = htmlspecialchars(
			file_get_contents('vendor/Opus/apps/demo/src/modal/DemoStaticModal.php'),
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8'
		);

		$jsCode = Lang::getInstance()->get('demo.modal.static.tab.js.code');

		$options->header = <<<HTML
		<span class="modal-header-opus-additional-text">{$additionalHeaderText}</span>
		HTML;
		$options->body = <<<HTML
		<ul class="nav nav-tabs nav-tabs-opus" id="id_opus-demo-static-modal-tab" role="tablist">
			<li class="nav-item" role="presentation">
				<button class="nav-link nav-link-opus active" id="id_opus-btn-demo-static-modal-tab-info" data-bs-toggle="tab" data-bs-target="#id_opus-demo-static-modal-tab-info" type="button" role="tab" aria-controls="id_opus-demo-static-modal-tab-info" aria-selected="true">
					<i class="me-1 bi bi-info-circle"></i>{$firstTabText}
				</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link nav-link-opus" id="id_opus-btn-demo-static-modal-tab-php" data-bs-toggle="tab" data-bs-target="#id_opus-demo-static-modal-tab-php" type="button" role="tab" aria-controls="id_opus-demo-static-modal-tab-php" aria-selected="true">
					<i class="me-1 bi bi-filetype-php"></i>PHP
				</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link nav-link-opus" id="id_opus-btn-demo-static-modal-tab-js" data-bs-toggle="tab" data-bs-target="#id_opus-demo-static-modal-tab-js" type="button" role="tab" aria-controls="id_opus-demo-static-modal-tab-js" aria-selected="false">
					<i class="me-1 bi bi-filetype-js"></i>JS
				</button>
			</li>
		</ul>
		<div class="tab-content" id="id_opus-demo-static-modal-tab-content">
			<div class="tab-pane fade show active" id="id_opus-demo-static-modal-tab-info" role="tabpanel" aria-labelledby="id_opus-btn-demo-static-modal-tab-info" tabindex="0">
				<div class="container mt-3">
					<h6 class="fw-bold mb-3">Opus\html\modal\Modal</h6>
					<p>{$firstTabAgenda} <code>$</code><code>options</code>:</p>
					{$tableHtml}
					<p class="text-muted small">{$firstTabNote}</p>
				</div>
			</div>
			<div class="tab-pane fade" id="id_opus-demo-static-modal-tab-php" role="tabpanel" aria-labelledby="id_opus-btn-demo-static-modal-tab-php" tabindex="0">
				<pre><code class="mt-3 language-php">{$phpCode}</code></pre>
			</div>
			<div class="tab-pane fade" id="id_opus-demo-static-modal-tab-js" role="tabpanel" aria-labelledby="id_opus-btn-demo-static-modal-tab-js" tabindex="0">
				<div class="alert alert-success mt-3" role="alert">{$jsCode}</div>
			</div>
		</div>
		HTML;
		$options->footer = $buttons->getElement('close-btn-opus-demo-static-modal');

		$modal = new Modal();
		$modal->addModal('opus-demo-static-modal', $options);
		return $modal;
	}
}
