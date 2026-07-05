<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-06-06 15:18:31
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-06-06 18:26:53
 **/

namespace Opus\apps\demo\src\modal;

use stdClass;
use Opus\html\modal\Modal;
use Opus\html\buttons\Buttons;
use Opus\html\form\Form;

/**
 * Demo dynamic modal component
 *
 * Creates and configures a Bootstrap modal dialog for the demo application.
 * Uses singleton pattern to ensure only one instance is created.
 *
 * @package Opus\apps\demo\src\modal
 */
class DemoDynamicModal
{
	private static ?self $instance = null;

	/**
	 * Returns the configured modal instance
	 *
	 * @return object The Modal instance configured for demo dynamic modal
	 */
	public static function demoModal(): object
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance->createModal();
	}

	/**
	 * Creates and configures the modal with close button and XL size
	 *
	 * @return Modal The configured Modal instance
	 */
	private function createModal(): Modal
	{
		$form = new Form();
		$options = new stdClass();

		$button = Buttons::closeButton(
			'opus-demo-dynamic-modal',
			['data-bs-dismiss' => 'modal']
		);
		$form->addElement($button);

		$options->size = 'xl';
		$options->footer = $form->getElement('close-btn-opus-demo-dynamic-modal');

		$modal = new Modal();
		$modal->addModal('opus-demo-dynamic-modal', $options);
		return $modal;
	}
}
