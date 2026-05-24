<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-21 19:27:54
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-22 20:39:01
 **/

namespace Opus\controller\event;

use stdClass;
use ArrayObject;
use Opus\html\modal\Modal;
use Opus\html\buttons\Buttons;
use Opus\html\form\Form;
use Opus\controller\query\TraitQuery;

/**
 * Handles the view generation for table event operations
 *
 * This class extends ArrayObject to provide a flexible container for view variables
 * while generating HTML output for table event operations. It's used to render
 * the response for table event API calls.
 *
 * API Endpoint Format:
 * https://14.6.83.14/index.php
 * 		?api=tableevent
 * 		&app={app}
 * 		&event={event}
 * 		&process=editor
 * 		&strategy={strategy}
 *
 * @param string app Application name, must be defined in config.json
 * @param string event Table event name from the application's config file
 * @param string process Processing mode, typically "editor"
 * @param string strategy Operation type: "add", "edit", "show", or "delete"
 *
 * @example
 * https://14.6.83.14/index.php?api=tableevent&app=users&event=manage_users&process=editor&strategy=edit
 *
 * @extends ArrayObject Provides array-like access to view variables
 * @property string $id The ID property from variables array
 */
class TableEventView extends ArrayObject
{
	use TraitQuery;

	private ?string $indexAction = null;
	private string $page = self::QINPUT_EXEPTIONS_PAGE;
	private string $hashtag = self::QINPUT_EXEPTIONS_HASHTAG;
	private string $plus = self::QINPUT_EXEPTIONS_PLUS;

	public function __construct(array $variables = [])
	{
		parent::__construct($variables, ArrayObject::ARRAY_AS_PROPS);

		$form = new Form();
		$form->addElement(Buttons::cancelButton('table-event', 'modal'));							// id_cancel-btn-table-event
		$form->addElement(Buttons::closeButton('table-event', ['data-bs-dismiss' => 'modal']));		// id_close-btn-table-event
		$form->addElement(Buttons::saveButton('table-event'));										// id_save-btn-table-event

		$options = new stdClass();
		$options->id = $this->id;
		$options->size = 'xl';
		$options->scrollable = false;		// if form is true, value must be false because bootstrap issue
		$options->form = true;
		$options->body = <<<HTML
			<div class="table-responsive pt-2 pb-2" id="id_body-table-event"></div>
		HTML;
		$options->footer = $form->getElement('close-btn-table-event')
			. $form->getElement('save-btn-table-event')
			. $form->getElement('cancel-btn-table-event');

		$modal = new Modal();
		$modal->addModal('table-event', $options);

		ob_start();
		echo <<<HTML
		{$modal->getModalByName('table-event')}
		<script type="text/javascript">
			$(document).ready(function () {

			});
		</script>
		HTML;

		$this->indexAction = ob_get_clean();
	}

	public function __toString()
	{
		return $this->indexAction;
	}
}
