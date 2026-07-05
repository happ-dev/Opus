/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-07-06 00:00:00
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-06 00:00:00
 **/

/**
 * Collapse class for managing Bootstrap collapse panels with AJAX support
 *
 * All collapse panels must follow a standardized template structure with specific naming conventions.
 * Each collapse component uses the collapseName as a prefix for consistent identification.
 *
 * @class OpusCollapse
 * @description Handles creation and management of collapse panel components
 *
 * @example
 * // Create a new collapse instance
 * const filterCollapse = new OpusCollapse({ name: 'filter' });
 *
 * @template Collapse Structure
 * Each collapse must contain the following template hierarchy:
 *
 * - `<name>-header`             - Header section (static, server-side rendered)
 *
 * - collapse-body
 *   - `<name>-alerts`           - Alert/notification area
 *   - `<name>-loader`           - Loading indicator
 *   - `<name>-body-row`         - Main content area
 *
 * - `<name>-footer`             - Footer section
 *
 * @param {Object} options - Configuration object for the collapse
 */
class OpusCollapse {
	/**
	 * Creates a new OpusCollapse instance
	 *
	 * @param {Object} options - Configuration object for the collapse
	 * @param {string} options.name - The collapse name used for CSS class generation
	 * @param {string} [options.id] - Element ID, defaults to 'id__' + name
	 * @param {Object} [options.data={}] - Data object to be sent via $.ajax requests
	 * @param {string} [options.api='asyncevent'] - API endpoint for AJAX requests
	 *
	 * @example
	 * const filterCollapse = new OpusCollapse({
	 *     name: 'filter',
	 *     data: { app: 'demo', event: 'demoFilter' },
	 *     api: 'asyncevent',
	 * });
	 */
	constructor(options) {
		this.name = options.name;
		this.id = options.id || "id__" + this.name;
		this.el = document.getElementById(this.id);
		this.data = options.data || {};
		this.api = options.api || "asyncevent";
		this.link = window.location.pathname + "?api=" + this.api;
		this.headerClass = "." + this.name + "-header";
		this.bodyRowClass = "." + this.name + "-body-row";
		this.bodyRowColClass = this.bodyRowClass + " > .col";
		this.loaderClass = "." + this.name + "-loader";
		this.alertsClass = "." + this.name + "-alerts";
		this.footerClass = "." + this.name + "-footer";
	}

	/**
	 * Prepares the collapse for AJAX operations by showing loader and hiding content
	 *
	 * @returns {void}
	 */
	ajaxInitialConditions() {
		$(this.el)
			.find(this.loaderClass)
			.fadeIn()
			.end()
			.find(".alert-danger, .alert-success, " + this.bodyRowClass + ", " + this.footerClass)
			.css("display", "none");
	}

	/**
	 * Configures the collapse header to display error state
	 *
	 * @returns {void}
	 */
	errorHeader() {
		$(this.el)
			.find(this.headerClass)
			.removeClass((_, className) => (className.match(/\b(collapse-header-opus-)\w+\b/g) || []).join(" "))
			.addClass("collapse-header-opus-red");
	}

	/**
	 * Configures the collapse content to display error state styling
	 *
	 * @returns {void}
	 */
	errorContent() {
		$(this.el)
			.find(".collapse")
			.removeClass((_, className) => (className.match(/\b(bs-opus-)[\w-]+\b/g) || []).join(" "))
			.addClass("bs-opus-red-3d");
	}

	/**
	 * Handles AJAX request failures by displaying error state
	 *
	 * @returns {void}
	 */
	ajaxFail() {
		this.errorContent();
		this.errorHeader();

		const $collapse = $(this.el);

		$collapse.find(this.loaderClass).fadeOut("slow", () => {
			$collapse
				.find(this.alertsClass)
				.find(".alert-danger")
				.html(http500)
				.slideDown("slow")
				.end()
				.end()
				.find(this.footerClass)
				.fadeIn("slow");
		});
	}

	/**
	 * Handles caught AJAX errors with custom error processing
	 *
	 * @param {Object} obj - Response object containing error information
	 * @param {string} [obj.message] - Error message from server response
	 * @param {string} [obj.details] - Additional error details from server response
	 * @param {string} catchError - Error type identifier or fallback error message
	 * @returns {void}
	 */
	ajaxCatch(obj, catchError) {
		this.errorContent();
		this.errorHeader();

		const errorMessage =
			catchError === "success-false" ? this.formatSuccessFalseError(obj, catchError) : catchError;

		const $collapse = $(this.el);

		$collapse.find(this.loaderClass).fadeOut("slow", () => {
			$collapse
				.find(this.alertsClass)
				.find(".alert-danger")
				.html(errorMessage)
				.slideDown("slow")
				.end()
				.end()
				.find(this.footerClass)
				.fadeIn("slow");
		});
	}

	/**
	 * Formats error message for success-false responses
	 *
	 * @private
	 * @param {Object} obj - Response object
	 * @param {string} fallback - Fallback error message
	 * @returns {string} Formatted error message with details
	 */
	formatSuccessFalseError(obj, fallback) {
		const message = obj?.message || fallback;
		const details = obj?.details || "";
		return details ? `${message}<br>${details}` : message;
	}

	/**
	 * Updates collapse data object with new key-value pairs
	 *
	 * @param {Object} newData - Object with properties to merge into existing data
	 * @returns {this} Returns this instance for method chaining
	 */
	setData(newData) {
		Object.assign(this.data, newData);
		return this;
	}

	/**
	 * Applies consistent CSS styling to tables within a container
	 *
	 * @param {string|Element|jQuery} container - Container selector, element, or jQuery object
	 * @returns {void}
	 */
	fixTableCSS(container) {
		const $tables = $(container).find("table");
		$tables.find("thead th").addClass("align-middle");
		$tables.find("tbody th").css("font-weight", "normal");
		$tables.find("tbody tr").addClass("align-middle");
	}

	/**
	 * Binds collapse show/hide events with AJAX loading
	 *
	 * @description Registers Bootstrap collapse event listeners for show and hide events.
	 * On show: performs AJAX request, populates body content and shows footer.
	 * If onShow callback is provided, it handles body content injection.
	 * If onShow is not provided, response.body is injected into bodyRowColClass by default.
	 * On hide: resets collapse to initial state and executes optional onHide callback.
	 *
	 * @param {Object} [options={}] - Configuration options
	 * @param {Function|null} [options.onShow=null] - Callback executed after AJAX response is loaded,
	 *     receives parsed response object. Use to customize body content injection
	 * @param {Function|null} [options.onRender=null] - Callback executed after body content is injected,
	 *     before slideDown animation. Use for post-render operations (e.g. syntax highlighting)
	 * @param {Function|null} [options.onHide=null] - Callback executed when collapse is hidden,
	 *     use to trigger external actions (e.g. DataTable reload)
	 *
	 * @example
	 * collapse.bindAjaxCollapse({
	 *     onRender: () => {
	 *         highlightCode(collapse.el);
	 *     },
	 *     onHide: () => {
	 *         dtTable.ajax.reload();
	 *     }
	 * });
	 */
	bindAjaxCollapse(options = {}) {
		const onShow = options.onShow || null;
		const onRender = options.onRender || null;
		const onHide = options.onHide || null;

		this.el.addEventListener("show.bs.collapse", () => {
			this.ajaxInitialConditions();

			$.ajax({
				url: this.link,
				data: this.data,
				cache: false,
			})
				.done((result) => {
					let response;
					try {
						response = JSON.parse(result);
						OpusCollapse.ajaxThrowException(response);

						$(this.el)
							.find(this.loaderClass)
							.fadeOut("slow", () => {
								// body
								if (onShow) onShow(response);
								else $(this.bodyRowColClass).html(response.body);

								// render
								if (onRender) onRender();

								// show body, footer
								$(this.bodyRowClass).slideDown("slow");
								if ($(this.footerClass).html().trim()) $(this.footerClass).fadeIn("slow");
							});
					} catch (error) {
						this.ajaxCatch(response, error);
					}
				})
				.fail(() => {
					this.ajaxFail();
				});
		});

		this.el.addEventListener("hide.bs.collapse", () => {
			OpusCollapse.resetCollapseByName(this.name, this.id);
			if (onHide) onHide();
		});
	}

	/**
	 * Resets a collapse to initial state by clearing body and footer content
	 *
	 * @static
	 * @param {string} collapseName - The collapse name used for element selection
	 * @param {string} [collapseId] - Optional collapse element ID, defaults to 'id__' + collapseName
	 * @returns {void}
	 */
	static resetCollapseByName(collapseName, collapseId) {
		const $collapse = $("#" + (collapseId || "id__" + collapseName));

		$collapse
			.find(".collapse")
			.removeClass((_, className) => (className.match(/\b(bs-opus-)[\w-]+\b/g) || []).join(" "))
			.addClass("bs-opus-green-3d")
			.end()
			.find("." + collapseName + "-header")
			.removeClass((_, className) => (className.match(/\b(collapse-header-opus-)[\w-]+\b/g) || []).join(" "))
			.addClass("collapse-header-opus-green")
			.end()
			.find(".alert-danger, .alert-success")
			.hide()
			.html("")
			.end()
			.find("." + collapseName + "-body-row > .col")
			.html("")
			.end()
			.find("." + collapseName + "-footer")
			.hide();
	}

	/**
	 * Validates AJAX response object and throws exceptions for error conditions
	 *
	 * @static
	 * @param {Object|null|undefined} obj - The AJAX response object to validate
	 * @throws {string} 'empty-save-json' - When obj is null, undefined, or empty object
	 * @throws {string} 'success-false' - When obj.success property is explicitly false
	 */
	static ajaxThrowException(obj) {
		if (!obj || Object.keys(obj).length === 0) throw "empty-save-json";
		if (obj.success === false) throw "success-false";
	}
}
