/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-08-18 16:00:00
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-08-18 18:34:06
 **/

/**
 * Profile page — edit profile modal, change password modal.
 */

ogl.tableCSS("#id_profile-dt");

/** @type {OpusModal} Edit profile modal */
const objProfileEdit = new OpusModal({
	name: "opus-profile-edit",
	data: { app: "profile", event: "editProfile" },
});

objProfileEdit.bindAjaxModal({
	footerStrategy: "edit",
	relatedTarget: () => {
		objProfileEdit.setData({ request: "edit-profile-form" });
	},
	onRender: () => {
		OpusSelect.bindSelect(objProfileEdit.el);
	},
	onHide: () => {
		OpusSelect.destroy(objProfileEdit.el);
		window.location = window.location.pathname + "?page=profile";
	},
	onSave: () => {
		objProfileEdit.bindPostModal({
			json: true,
			buildUrl: () => {
				const url = new URLSearchParams();
				url.append("app", objProfileEdit.data.app);
				url.append("event", objProfileEdit.data.event);
				url.append("request", "edit-profile-save");
				return url;
			},
			buildData: () => {
				const postData = {};
				const $form = $(objProfileEdit.formId);

				$form.find('[id^="id_input_"], input[type=checkbox]:checked').each(function () {
					const name = $(this).attr("name");
					const value = $(this).val();
					if (value) postData[name] = value;
				});

				return postData;
			},
		});
	},
});

/** @type {OpusModal} Change password modal */
const objProfileChangePassword = new OpusModal({
	name: "opus-profile-change-password",
	data: { app: "profile", event: "changePassword" },
});

objProfileChangePassword.bindAjaxModal({
	footerStrategy: "edit",
	relatedTarget: () => {
		objProfileChangePassword.setData({ request: "change-password-form" });
	},
	onRender: () => {
		const pass = document.getElementById("id_input_password");
		const confirm = document.getElementById("id_input_confirm_password");

		pass.pattern = "(?=.*[A-Z])(?=.*\\d).{8,}";

		const feedback = document.createElement("div");
		feedback.className = "invalid-feedback";
		confirm.closest(".input-group").after(feedback);

		const validate = () => {
			const weakPass = pass.value !== "" && !pass.value.match(/(?=.*[A-Z])(?=.*\d).{8,}/);
			const mismatch = confirm.value !== "" && confirm.value !== pass.value;

			let msg = "";
			if (weakPass)
				msg = "<?= Opus\controller\lang\Lang::getInstance()->get('profile.alert.password.requirements') ?>";
			else if (mismatch)
				msg = "<?= Opus\controller\lang\Lang::getInstance()->get('profile.alert.password.not.equal') ?>";

			pass.classList.toggle("is-invalid", weakPass);
			confirm.classList.toggle("is-invalid", mismatch);
			feedback.textContent = msg;
			feedback.style.display = msg ? "block" : "";
		};

		pass.addEventListener("input", validate);
		confirm.addEventListener("input", validate);
	},
	onHide: () => {
		OpusModal.resetModalByName("opus-profile-change-password");
	},
	onSave: () => {
		objProfileChangePassword.bindPostModal({
			json: true,
			buildUrl: () => {
				const url = new URLSearchParams();
				url.append("app", objProfileChangePassword.data.app);
				url.append("event", objProfileChangePassword.data.event);
				url.append("request", "change-password-save");
				return url;
			},
			buildData: () => {
				const postData = {};
				const $form = $(objProfileChangePassword.formId);

				$form.find('[id^="id_input_"]').each(function () {
					const name = $(this).attr("name");
					const value = $(this).val();
					if (value) postData[name] = value;
				});

				return postData;
			},
		});
	},
});
