<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-04-25 12:58:29
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-26 16:04:45
 **/

namespace Opus\view\login;

use stdClass;
use Opus\controller\request\Request;
use Opus\controller\lang\Lang;
use Opus\html\modal\Modal;
use Opus\html\buttons\Buttons;
use Opus\html\form\Form;
use Opus\html\form\StandardFormElements;

class Login
{
	public ?object $modal;

	public function __construct()
	{
		$form = new Form();
		$form->addElement(Buttons::loginButton('opus-login'));
		$form->addElement(Buttons::cancelButton('opus-login', 'modal'));

		$optionsLogin = new stdClass();
		$optionsLogin->standardName = false;
		$optionsLogin->floating = true;
		$optionsLogin->required = true;
		$optionsLogin->icon = 'bi bi-person-badge';

		$optionsPassword = new stdClass();
		$optionsPassword->standardName = false;
		$optionsPassword->floating = true;
		$optionsPassword->required = true;
		$optionsPassword->type = 'password';
		$optionsPassword->icon = 'bi bi-key';

		$dataLogin = [
			'attname' => 'opus-login-input',
			'comment' => 'controller.login.user'
		];
		StandardFormElements::standardTypeValue($dataLogin, $optionsLogin);

		$dataPassword = [
			'attname' => 'opus-login-password',
			'comment' => 'controller.login.password'
		];
		StandardFormElements::standardTypeValue($dataPassword, $optionsPassword);

		$options = new stdClass();
		$options->form = true;
		$options->action = Request::url('index.php?page=login');
		$options->ajax = false;
		$options->centered = true;
		$options->headerIcon = 'bi-person-up';
		$options->headerText = Lang::getInstance()->get('html.buttons.login');
		$options->body = <<<HTML
		<div class="input-group mb-4 d-flex align-items-center bs-opus-green-3d bg-opus-green" style="border-radius: var(--bs-border-radius)">
			<img src="img/happ-body.png" alt="hApp.dev" class="img-fluid me-auto mt-3 modal-login-form-img">
			<span class="modal-login-form-text font-monospace small ms-auto me-2">powerBy Opus</span>
		</div>
		{$dataLogin['el']}
		{$dataPassword['el']}
		HTML;
		$options->footer = $form->getElement('login-btn-opus-login');
		$this->modal = new Modal();
		$this->modal->addModal('nav-opus-login', $options);
	}
}
