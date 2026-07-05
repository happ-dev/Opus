/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-06-05 20:52:18
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-06-22 14:16:29
 **/

/**
 * Highlights code blocks with syntax highlighting
 * Handles both standard code and HTML inside heredoc strings
 *
 * @param {Element|Document} container - Container to search for code blocks
 */
function highlightCode(container = document) {
	// First pass highlight
	container.querySelectorAll('pre code[class*="language-"]:not([data-highlighted])').forEach((el) => {
		hljs.highlightElement(el);
	});

	// Second pass - highlight HTML inside heredoc strings
	container.querySelectorAll("pre code .hljs-string").forEach((span) => {
		const text = span.textContent;

		// Check if it looks like HTML (contains tags)
		if (/<[a-z][\s\S]*>/i.test(text)) {
			const result = hljs.highlight(text, { language: "xml" });
			span.innerHTML = result.value;
			span.classList.remove("hljs-string");
			span.classList.add("hljs-heredoc");
		}
	});
}

document.addEventListener("DOMContentLoaded", () => {
	"use strict";
	highlightCode();
});
