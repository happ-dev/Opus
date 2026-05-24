/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-03-27 18:51:03
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-24 11:29:23
 **/

"use strict";

const http500 = `
	<strong>500 – Internal Server Error</strong><br><br>
	Internal server error – the server encountered unexpected difficulties
	that prevented the request from being fulfilled
`;

document.addEventListener("DOMContentLoaded", () => {
	"use strict";
	const forms = document.querySelectorAll(".needs-validation");
	Array.from(forms).forEach((form) => {
		form.addEventListener(
			"submit",
			(event) => {
				if (!form.checkValidity()) {
					event.preventDefault();
					event.stopPropagation();
				}

				form.classList.add("was-validated");
			},
			false,
		);
	});
});
