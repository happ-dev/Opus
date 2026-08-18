<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-08-18 16:00:00
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-08-18 20:23:14
 **/

namespace Opus\apps\profile\src;

use stdClass;
use Opus\controller\InterfaceIndexController;
use Opus\html\modal\Modal;
use Opus\html\buttons\Buttons;
use Opus\view\view\View;

/**
 * Profile page controller — user can view/edit own data and change password.
 *
 * @implements InterfaceIndexController
 */
class ProfileController implements InterfaceIndexController
{
	/**
	 * Returns View with profile table and modals.
	 *
	 * @return View
	 */
	public function indexAction()
	{
		return new View([
			'userProfile' => UserProfile::build(),
			'modals' => (object) [
				'edit' => $this->editModal(),
				'changePassword' => $this->changePasswordModal()
			]
		]);
	}

	/**
	 * Builds edit profile modal with form buttons.
	 *
	 * @return Modal
	 */
	private function editModal(): Modal
	{
		$buttons = new Buttons();
		$modalButtons = $buttons->modalButtons('opus-profile-edit');

		$options = new stdClass();
		$options->form = true;
		$options->centered = true;
		$options->footer = $modalButtons->getElement('submit-btn-opus-profile-edit')
			. $modalButtons->getElement('cancel-btn-opus-profile-edit')
			. $modalButtons->getElement('close-btn-opus-profile-edit');

		$modal = new Modal();
		$options->size = 'lg';
		$modal->addModal('opus-profile-edit', $options);
		return $modal;
	}

	/**
	 * Builds change password modal with orange header and form buttons.
	 *
	 * @return Modal
	 */
	private function changePasswordModal(): Modal
	{
		$buttons = new Buttons();
		$modalButtons = $buttons->modalButtons('opus-profile-change-password');

		$options = new stdClass();
		$options->shadow = 'bs-opus-orange-3d';
		$options->headerClass = 'modal-header-opus-orange bs-opus-orange';
		$options->form = true;
		$options->centered = true;
		$options->footer = $modalButtons->getElement('submit-btn-opus-profile-change-password')
			. $modalButtons->getElement('cancel-btn-opus-profile-change-password')
			. $modalButtons->getElement('close-btn-opus-profile-change-password');

		$modal = new Modal();
		$modal->addModal('opus-profile-change-password', $options);
		return $modal;
	}
}
