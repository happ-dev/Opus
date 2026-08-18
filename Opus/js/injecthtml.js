/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-08-18 10:35:24
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-08-18 11:31:51
 **/

/**
 * Lightweight HTML injection utility supporting AJAX and variable sources.
 * Operates as a singleton.
 *
 * @class InjectHtml
 */
class InjectHtml {
	/** @type {InjectHtml|null} */
	static #instance = null;

	/** @type {Set<HTMLElement>} */
	#pending = new Set();

	/**
	 * Returns the singleton instance, creating it if necessary.
	 *
	 * @returns {InjectHtml}
	 */
	static getInstance() {
		return (this.#instance ??= new this());
	}

	/**
	 * Entry point. Injects HTML into a container from AJAX or variable.
	 *
	 * @param {HTMLElement|Object} containerOrOptions - DOM element or options object with onPrepare.
	 * @param {Object} [options={}] - Configuration when first arg is DOM element.
	 * @param {string} [options.html] - HTML string to inject directly (html mode).
	 * @param {string} [options.app] - Application name for API routing (ajax mode, overrides data-app).
	 * @param {string} [options.event] - Event name for content identification (ajax mode, overrides data-event).
	 * @param {function(): HTMLElement} [options.onPrepare] - Creates and returns container DOM element (replaces first arg).
	 * @param {function(): void} [options.onDone] - Callback invoked after successful injection.
	 * @param {function(string): void} [options.onError] - Callback invoked on error with message.
	 * @returns {void}
	 */
	static inject(containerOrOptions, options = {}) {
		const instance = this.getInstance();
		let container, config;

		if (containerOrOptions instanceof HTMLElement) {
			container = containerOrOptions;
			config = instance.#readConfig(container, options);
		} else {
			config = containerOrOptions;
			container = config.onPrepare?.();
			if (!(container instanceof HTMLElement)) return;
			config = instance.#readConfig(container, config);
		}

		if (instance.#pending.has(container)) return;

		if (config.html !== null) {
			instance.#injectFromHtml(container, config.html);
			config.onDone?.();
			return;
		}

		if (config.app && config.event) {
			instance.#pending.add(container);
			instance.#injectFromAjax(container, config);
		}
	}

	/**
	 * Merges options with container data-attributes.
	 *
	 * @param {HTMLElement} container - The target container element.
	 * @param {Object} options - Passed options.
	 * @returns {{app: string|null, event: string|null, html: string|null, onDone: function|null, onError: function|null}}
	 */
	#readConfig(container, options) {
		const data = container.dataset;
		return {
			app: options.app ?? data.app ?? null,
			event: options.event ?? data.event ?? null,
			html: options.html ?? null,
			onDone: options.onDone ?? null,
			onError: options.onError ?? null,
		};
	}

	/**
	 * Injects HTML string directly into container.
	 *
	 * @param {HTMLElement} container - The target container element.
	 * @param {string} html - HTML string to inject.
	 */
	#injectFromHtml(container, html) {
		container.innerHTML = html;
	}

	/**
	 * Fetches HTML from API and injects into container.
	 *
	 * @param {HTMLElement} container - The target container element.
	 * @param {Object} config - Configuration with app and event.
	 * @returns {Promise<void>}
	 */
	#injectFromAjax(container, config) {
		const url = window.location.pathname + "?api=injectevent&app=" + config.app + "&event=" + config.event;

		return $.ajax({ url, cache: false })
			.done((result) => {
				try {
					const json = typeof result === "string" ? JSON.parse(result) : result;

					if (!json || json.success === false) {
						this.#showError(container, json?.message ?? "Error", json?.details ?? "");
						config.onError?.(json?.message);
						return;
					}

					container.innerHTML = json.index;
					config.onDone?.();
				} catch (e) {
					this.#showError(container, e.toString());
					config.onError?.(e.toString());
				}
			})
			.fail(() => {
				this.#showError(container, http500);
				config.onError?.(http500);
			})
			.always(() => {
				this.#pending.delete(container);
			});
	}

	/**
	 * Renders error alert inside container.
	 *
	 * @param {HTMLElement} container - The target container element.
	 * @param {string} msg - Error message.
	 * @param {string} [details=''] - Optional error details.
	 */
	#showError(container, msg, details = "") {
		container.innerHTML = `<div class="alert alert-danger" style="word-break:normal"><span class="me-1">${msg}</span><span>${details}</span></div>`;
	}
}
