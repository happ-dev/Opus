<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-04-25 12:58:29
 * @Last Modified by:   Tomasz Ulazowski
 * @Last Modified time: 2026-04-28 10:42:50
 **/

namespace Opus\view\login;

use stdClass;
use Opus\config\Config;
use Opus\controller\request\Request;
use Opus\controller\lang\Lang;
use Opus\html\modal\Modal;
use Opus\html\buttons\Buttons;
use Opus\html\form\Form;

class Login
{
	public ?object $modal;

	public function __construct()
	{
		$form = new Form();
		$form->addElement(Buttons::loginButton('opus-login'));
		$form->addElement(Buttons::cancelButton('opus-login', 'modal'));
		$opusLoginInputLabel = Lang::getInstance()->get('controller.login.user');
		$opusPasswordInputLabel = Lang::getInstance()->get('controller.login.password');
		$form->addElement([
			'name' => 'opus-login-input',
			'id' => 'id_opus-login-input',
			'tag' => 'input',
			'attributes' => [
				'type' => 'text',
				'class' => 'form-control',
				'required',
				'autofocus',
				'placeholder' => $opusLoginInputLabel
			]
		]);

		$form->addElement([
			'name' => 'opus-login-password',
			'id' => 'id_opus-login-password',
			'tag' => 'input',
			'attributes' => [
				'type' => 'password',
				'class' => 'form-control',
				'required',
				'placeholder' => $opusPasswordInputLabel
			]
		]);
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
		<div class="input-group mb-4 bs-opus-black-3d" style="border-radius: var(--bs-border-radius)">
			<span class="input-group-text">
				<i class="bi bi-person-badge"></i>
			</span>
			<div class="form-floating">
				{$form->getElement('opus-login-input')}
				<label for="id_opus-login-input">{$opusLoginInputLabel}</label>
			</div>
		</div>
		<div class="input-group mb-4 bs-opus-black-3d" style="border-radius: var(--bs-border-radius)">
			<span class="input-group-text">
				<i class="bi bi-key"></i>
			</span>
			<div class="form-floating">
				{$form->getElement('opus-login-password')}
				<label for="id_opus-login-password">{$opusPasswordInputLabel}</label>
			</div>
		</div>
		HTML;
		$options->footer = $form->getElement('login-btn-opus-login');
		$this->modal = new Modal();
		$this->modal->addModal('nav-opus-login', $options);
	}
}
