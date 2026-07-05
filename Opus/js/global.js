/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-03-27 18:51:03
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-06-28 00:17:03
 **/

"use strict";

const http500 = `
	<strong>500 – Internal Server Error</strong><br><br>
	Internal server error – the server encountered unexpected difficulties
	that prevented the request from being fulfilled
`;

$.ajaxSetup({
	headers: { "X-CSRF-Token": $("meta[name='csrf-token']").attr("content") },
});

document.addEventListener("DOMContentLoaded", () => {
	"use strict";

	// Bootstrap form validation
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

	// Bootstrap theme switch
	const getStoredTheme = () => localStorage.getItem("theme");
	const setStoredTheme = (theme) => localStorage.setItem("theme", theme);

	const getPreferredTheme = () => {
		const storedTheme = getStoredTheme();

		if (storedTheme) {
			return storedTheme;
		}

		return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
	};

	const setTheme = (theme) => {
		if (theme === "auto") {
			document.documentElement.setAttribute(
				"data-bs-theme",
				window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light",
			);
		} else {
			document.documentElement.setAttribute("data-bs-theme", theme);
		}
	};

	setTheme(getPreferredTheme());

	const showActiveTheme = (theme) => {
		const btnToActive = document.querySelector(`[data-bs-theme-value="${theme}"]`);
		document.querySelectorAll("[data-bs-theme-value]").forEach((element) => {
			element.classList.remove("active");
			element.setAttribute("aria-pressed", "false");
		});
		btnToActive.classList.add("active");
		btnToActive.setAttribute("aria-pressed", "true");
	};

	window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", () => {
		const storedTheme = getStoredTheme();

		if (storedTheme !== "light" && storedTheme !== "dark") {
			setTheme(getPreferredTheme());
		}
	});

	document.querySelectorAll("[data-bs-theme-value]").forEach((toggle) => {
		toggle.addEventListener("click", () => {
			const theme = toggle.getAttribute("data-bs-theme-value");
			setStoredTheme(theme);
			setTheme(theme);
			showActiveTheme(theme);
		});
	});

	// Sidebar dropdown with fixed positioning
	document.querySelectorAll(".sidebar-body-opus .dropdown-toggle-opus").forEach((toggle) => {
		new bootstrap.Dropdown(toggle, {
			popperConfig: {
				strategy: "fixed",
				placement: "right-start",
				modifiers: [
					{
						name: "preventOverflow",
						options: { boundary: "viewport" },
					},
				],
			},
		});
	});

	// Async page loading (event delegation, active link)
	new OpusAsyncPage();
});
