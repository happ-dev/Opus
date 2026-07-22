/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-06-26 22:57:48
 * @Last Modified by:   Tomasz Ulazowski
 * @Last Modified time: 2026-07-20 13:03:05
 **/

/**
 * Offcanvas class for managing Bootstrap offcanvas panels
 *
 * All offcanvas panels must follow a standardized template structure with specific naming conventions.
 * Each offcanvas component uses the offcanvasName as a prefix for consistent identification.
 *
 * @class OpusOffcanvas
 * @description Handles creation and management of offcanvas panel components
 *
 * @example
 * // Create a new offcanvas instance
 * const filterOffcanvas = new OpusOffcanvas({ name: 'filter' });
 *
 * @template Offcanvas Structure
 * Each offcanvas must contain the following template hierarchy:
 *
 * - `<name>-header`
 *   - `id_<name>-icon-header`   - Header icon element
 *   - `id_<name>-text-header`   - Header text content
 *   - `id_<name>-post-header`   - Additional header content
 *
 * - `<name>-body`
 *   - `<name>-alerts`           - Alert/notification area
 *   - `<name>-loader`           - Loading indicator
 *   - `<name>-body-row`         - Main content area
 *
 * - `<name>-footer`             - Footer with action buttons
 *
 * @param {Object} options - Configuration object for the offcanvas
 */
class OpusOffcanvas {
	/**
	 * Creates a new OpusOffcanvas instance
	 *
	 * @param {Object} options - Configuration object for the offcanvas
	 * @param {string} [options.name] - The offcanvas name used for CSS class generation
	 * @param {string} [options.id] - Element ID, defaults to 'id__' + name
	 * @param {Object} [options.data={}] - Data object to be sent via $.ajax requests
	 * @param {string} [options.api='asyncevent'] - API endpoint for AJAX requests
	 * @param {Object} [options.header={}] - Header configuration object
	 * @param {string} [options.header.text] - Header text for the offcanvas
	 * @param {string} [options.header.icon] - Bootstrap icon class
	 * @param {string} [options.header.class] - CSS class representing the header color
	 *
	 * @example
	 * const filterOffcanvas = new OpusOffcanvas({
	 *     name: 'filter',
	 *     data: { app: 'demo', event: 'demoFilter' },
	 *     api: 'asyncevent',
	 *     header: { text: 'Filters', icon: 'bi-funnel', class: 'offcanvas-header-opus-green bs-opus-green' }
	 * });
	 */
	constructor(options) {
		this.name = options.name;
		this.id = options.id || "id__" + this.name;
		this.el = document.getElementById(this.id);
		this.data = options.data || {};
		this.api = options.api || "asyncevent";
		this.link = window.location.pathname + "?api=" + this.api;
		this.header = options.header || {};
		this.headerClass = "." + this.name + "-header";
		this.bodyClass = "." + this.name + "-body";
		this.bodyRowClass = "." + this.name + "-body-row";
		this.bodyRowColClass = this.bodyRowClass + " > .col";
		this.loaderClass = "." + this.name + "-loader";
		this.alertsClass = "." + this.name + "-alerts";
		this.footerClass = "." + this.name + "-footer";
		this.formId = "#id__" + this.name + "-form";
		this.slideDownClass = this.headerClass + ", " + this.bodyRowClass + ", " + this.footerClass;
	}

	/**
	 * Updates the offcanvas header with new options
	 *
	 * @param {Object} options - Header configuration options
	 * @param {string} [options.text] - Header text content
	 * @param {string} [options.icon] - Bootstrap icon class
	 * @param {string} [options.class] - CSS class for header styling
	 *
	 * @example
	 * offcanvas.setHeader({
	 *     text: 'Edit Filters',
	 *     icon: 'bi-funnel-fill',
	 *     class: 'offcanvas-header-opus-green bs-opus-green'
	 * });
	 */
	setHeader(options) {
		if (options.text !== undefined) this.header.text = options.text;
		if (options.icon !== undefined) this.header.icon = options.icon;
		if (options.class !== undefined) this.header.class = options.class;
		if (options.additionalText !== undefined) this.header.additionalText = options.additionalText;

		if (this.header.class) {
			$(this.headerClass)
				.removeClass(function (index, className) {
					let matchedClasses = className.match(/\b(offcanvas-header-opus-|bs-opus-)[\w-]+\b/g);
					return (matchedClasses || []).join(" ");
				})
				.addClass(this.header.class);
		}

		$("#id_" + this.name + "-icon-header")
			.removeClass((_, className) => (className.match(/\b(bi-)[\w-]+\b/g) || []).join(" "))
			.addClass(this.header.icon);
		$("#id_" + this.name + "-text-header").html(this.header.text);

		if (this.header.additionalText) {
			const h5 = this.el?.querySelector(".offcanvas-header h5");
			if (h5) h5.insertAdjacentHTML("afterend", this.header.additionalText);
		}
	}

	/**
	 * Prepares the offcanvas for AJAX operations by showing loader and hiding content
	 *
	 * @returns {void}
	 */
	ajaxInitialConditions() {
		$(this.el)
			.find(this.loaderClass)
			.fadeIn()
			.end()
			.find(".alert-danger, .alert-success, " + this.slideDownClass)
			.css("display", "none");
	}

	/**
	 * Configures the offcanvas header to display error state
	 *
	 * @returns {void}
	 */
	errorHeader() {
		// Use a constant for the pattern to improve readability and reusability
		const headerClassPattern = /\b(offcanvas-header-opus-|bs-opus-)\w+\b/g;

		// Use arrow function and chaining for cleaner syntax
		$(this.headerClass)
			.removeClass((_, className) => (className.match(headerClassPattern) || []).join(" "))
			.addClass("offcanvas-header-opus-red bs-opus-red");

		// Set error icon and text
		$("#id_" + this.name + "-icon-header")
			.removeClass((_, className) => (className.match(/\b(bi-)[\w-]+\b/g) || []).join(" "))
			.addClass("bi-exclamation-triangle");
		$("#id_" + this.name + "-text-header").html(
			"<?= Opus\controller\lang\Lang::getInstance()->get('demo.table.event.bonuses.header.text') ?>",
		);
	}

	/**
	 * Configures the offcanvas content to display error state styling
	 *
	 * @returns {void}
	 */
	errorContent() {
		const contentClassPattern = /\b(bs-opus-)[\w-]+\b/g;

		$(this.el)
			.find(".offcanvas")
			.removeClass((_, className) => (className.match(contentClassPattern) || []).join(" "))
			.addClass("bs-opus-red-3d");
	}

	/**
	 * Configures the offcanvas footer for error state
	 *
	 * @returns {void}
	 */
	errorFooter() {
		const $footer = $(this.footerClass);

		const spinnerElements = 'button[id^="id_save"] > span, button[id^="id_submit"] > span';
		const actionButtons = 'button[id^="id_save"], button[id^="id_submit"], button[id^="id_cancel"]';
		const closeButton = 'button[id^="id_close"]';

		$footer
			.find(spinnerElements)
			.removeClass("spinner-border spinner-border-sm")
			.end()
			.find(actionButtons)
			.prop("disabled", false)
			.hide()
			.end()
			.find(closeButton)
			.prop("disabled", false)
			.show();
	}

	/**
	 * Handles AJAX request failures by displaying error state
	 *
	 * @returns {void}
	 */
	ajaxFail() {
		this.errorContent();
		this.errorHeader();
		this.errorFooter();

		const $offcanvas = $(this.el);

		$offcanvas.find(this.loaderClass).fadeOut("slow", () => {
			$offcanvas
				.find(this.alertsClass)
				.find(".alert-danger")
				.html(http500)
				.slideDown("slow")
				.end()
				.end()
				.find(this.headerClass)
				.slideDown("slow")
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
	 */
	ajaxCatch(obj, catchError) {
		this.errorContent();
		this.errorHeader();
		this.errorFooter();

		const errorMessage =
			catchError === "success-false" ? this.formatSuccessFalseError(obj, catchError) : catchError;

		const $offcanvas = $(this.el);

		$offcanvas.find(this.loaderClass).fadeOut("slow", () => {
			$offcanvas
				.find(this.alertsClass)
				.find(".alert-danger")
				.html(errorMessage)
				.slideDown("slow")
				.end()
				.end()
				.find(this.headerClass)
				.slideDown("slow")
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
	 * Sets footer button visibility and state based on strategy
	 *
	 * @param {string} strategy - Operation strategy ('add', 'edit', 'delete', 'show', etc.)
	 */
	setFooterButtons(strategy) {
		const isEditable = /^(add|edit|delete)$/.test(strategy);
		const $footer = $(this.footerClass);

		const actionButtons = 'button[id^="id_save"], button[id^="id_submit"], button[id^="id_cancel"]';
		const closeButton = 'button[id^="id_close"]';

		$footer.find(actionButtons).toggle(isEditable).end().find(closeButton).toggle(!isEditable);

		if (isEditable) {
			$footer.find('button[id^="id_save"], button[id^="id_submit"]').prop("disabled", false);
		}
	}

	/**
	 * Applies consistent CSS styling to tables within a container
	 *
	 * @param {string|Element|jQuery} target - Table ID, container selector, element, or jQuery object
	 * @param {Object} [options={}] - Options passed to ogl.tableCSS()
	 */
	fixTableCSS(target, options = {}) {
		const thead = options.table?.thead || "table-opus-black";
		const tfoot = options.table?.tfoot || "table-opus-black";
		ogl.tableCSS(target, { thead, tfoot });
	}

	/**
	 * Updates offcanvas data object with new key-value pairs
	 *
	 * @param {Object} newData - Object with properties to merge into existing data
	 * @returns {this} Returns this instance for method chaining
	 */
	setData(newData) {
		Object.assign(this.data, newData);
		return this;
	}

	/**
	 * Sets up the offcanvas for post-initialization state
	 *
	 * @returns {void}
	 */
	postInitialConditions() {
		const $offcanvas = $(this.el);
		const $footer = $offcanvas.find(this.footerClass);

		$offcanvas.find(".alert-danger, .alert-success, " + this.loaderClass).hide();

		$footer
			.find('button[id^="id_close"]')
			.hide()
			.end()
			.find('button[id^="id_submit"], button[id^="id_save"], button[id^="id_cancel"]')
			.show();
	}

	/**
	 * Handles successful AJAX responses by displaying success state
	 *
	 * @param {Object} obj - Success response object
	 * @returns {void}
	 */
	postSuccess(obj) {
		const message = obj?.message || "";
		const details = obj?.details || "";
		const successMessage = details ? `${message}<br>${details}` : message;

		const $footer = $(this.footerClass);

		$footer
			.find('button[id^="id_save"] > span, button[id^="id_submit"] > span')
			.removeClass("me-1 spinner-border spinner-border-sm")
			.end()
			.find('button[id^="id_save"], button[id^="id_cancel"], button[id^="id_submit"]')
			.prop("disabled", false)
			.hide()
			.end()
			.find('button[id^="id_close"]')
			.prop("disabled", false)
			.show();

		$(this.alertsClass).find(".alert-success").html(successMessage).slideDown("slow");
	}

	/**
	 * Handles POST request failures by displaying error state
	 *
	 * @returns {void}
	 */
	postFail() {
		this.errorContent();
		this.errorHeader();
		this.errorFooter();

		$(this.alertsClass).find(".alert-danger").html(http500).slideDown("slow");
	}

	/**
	 * Handles caught POST request errors with custom error processing
	 *
	 * @param {Object} obj - Response object containing error information
	 * @param {string} catchError - Error type identifier or fallback error message
	 */
	postCatch(obj, catchError) {
		this.errorContent();
		this.errorHeader();
		this.errorFooter();

		const errorMessage =
			catchError === "success-false" ? this.formatSuccessFalseError(obj, catchError) : catchError;

		$(this.alertsClass).find(".alert-danger").html(errorMessage).slideDown("slow");
	}

	/**
	 * Prepares the offcanvas for form submission by showing loading state
	 *
	 * @returns {void}
	 */
	postSubmitClick() {
		$(this.footerClass)
			.find('button[id^="id_submit"], button[id^="id_save"]')
			.prop("disabled", true)
			.find("> span")
			.addClass("me-1 spinner-border spinner-border-sm")
			.end()
			.end()
			.find('button[id^="id_cancel"]')
			.prop("disabled", true);
	}

	/**
	 * Binds offcanvas show/hide events with AJAX loading
	 *
	 * @description Registers Bootstrap offcanvas event listeners for show and hide events.
	 * On show: performs AJAX request, sets header, populates body content, and configures footer.
	 * If onShow callback is provided, it handles body content injection.
	 * If onShow is not provided, response.body is injected into bodyRowColClass by default.
	 * After $.ajax completes, onSave callback is executed to bind form submit handler.
	 * On hide: resets offcanvas to initial state and executes optional onHide callback.
	 *
	 * @param {Object} options - Configuration options
	 * @param {string} [options.footerStrategy='show'] - Footer button strategy ('show', 'add', 'edit', 'delete')
	 * @param {Function|null} [options.onShow=null] - Callback executed after AJAX response is loaded,
	 *     receives parsed response object. Use to customize body content injection
	 * @param {Function|null} [options.onRender=null] - Callback executed after body content is injected
	 *     and footer is configured, before slideDown animation. Use for post-render operations
	 *     (e.g. syntax highlighting, DataTable initialization)
	 * @param {Function|null} [options.onSave=null] - Callback executed after $.ajax request,
	 *     use to bind form submit handler via bindPostOffcanvas()
	 * @param {Function|null} [options.onHide=null] - Callback executed when offcanvas is hidden,
	 *     use to trigger external actions (e.g. DataTable reload)
	 *
	 * @example
	 * offcanvas.bindAjaxOffcanvas({
	 *     footerStrategy: "edit",
	 *     onRender: () => {
	 *         highlightCode(offcanvas.el);
	 *     },
	 *     onSave: () => {
	 *         offcanvas.bindPostOffcanvas({
	 *             buildUrl: () => {
	 *                 const url = new URLSearchParams();
	 *                 url.append('request', 'save');
	 *                 return url;
	 *             },
	 *             buildData: (form) => Object.fromEntries(new FormData(form))
	 *         });
	 *     },
	 *     onHide: () => {
	 *         dtTable.ajax.reload();
	 *     }
	 * });
	 */
	bindAjaxOffcanvas(options = {}) {
		const strategy = options.footerStrategy || "show";
		const onShow = options.onShow || null;
		const onRender = options.onRender || null;
		const onHide = options.onHide || null;
		const onSave = options.onSave || null;

		this.el.addEventListener("show.bs.offcanvas", () => {
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
						OpusOffcanvas.ajaxThrowException(response);

						$(this.bodyClass)
							.find(this.loaderClass)
							.fadeOut("slow", () => {
								// header
								this.setHeader(response.header);

								// body
								if (onShow) onShow(response);
								else $(this.bodyRowColClass).html(response.body);

								// footer
								this.setFooterButtons(strategy);

								// render
								if (onRender) onRender();

								// show header, body, footer
								$(this.headerClass + ", " + this.bodyRowClass).slideDown("slow");
								$(this.footerClass).fadeIn("slow");
							});
					} catch (error) {
						this.ajaxCatch(response, error);
					}
				})
				.fail(() => {
					this.ajaxFail();
				});

			if (onSave) onSave();
		});

		this.el.addEventListener("hide.bs.offcanvas", () => {
			OpusOffcanvas.resetOffcanvasByName(this.name, this.id);
			if (onHide) onHide();
		});
	}

	/**
	 * Binds form submit handler with POST request
	 *
	 * @param {Object} options - Configuration options
	 * @param {boolean} [options.json=false] - If true, sends data as JSON with application/json content type
	 * @param {Function|null} [options.buildUrl=null] - Callback to build URLSearchParams for the request URL.
	 *     Must return a URLSearchParams instance
	 * @param {Function|null} [options.buildData=null] - Callback to build POST data object.
	 *     Receives form element, must return a key-value object (JSON.stringified if options.json is true)
	 *
	 * @example
	 * offcanvas.bindPostOffcanvas({
	 *     buildUrl: () => {
	 *         const url = new URLSearchParams();
	 *         url.append('request', 'filter-save');
	 *         return url;
	 *     },
	 *     buildData: (form) => Object.fromEntries(new FormData(form))
	 * });
	 */
	bindPostOffcanvas(options = {}) {
		const json = options.json ?? false;
		const buildUrl = options.buildUrl || null;
		const buildData = options.buildData || null;

		$(this.formId)
			.off("submit")
			.on("submit", (submitEvent) => {
				submitEvent.preventDefault();

				const form = submitEvent.target;
				form.action = this.link;

				if (form.checkValidity() === false) {
					submitEvent.stopPropagation();
					return;
				}

				this.postSubmitClick();

				const url = buildUrl ? buildUrl() : new URLSearchParams();
				const postData = buildData ? buildData(form) : {};

				$.post({
					url: form.action + "&" + url.toString(),
					data: json ? JSON.stringify(postData) : postData,
					...(json && { contentType: "application/json" }),
					cache: false,
				})
					.done((result) => {
						let response;
						try {
							response = JSON.parse(result);
							OpusOffcanvas.ajaxThrowException(response);
							this.postSuccess(response);
						} catch (error) {
							this.postCatch(response, error);
						}
					})
					.fail(() => {
						this.postFail();
					});
			});
	}

	/**
	 * Resets an offcanvas to initial state by clearing header, body, and footer content
	 *
	 * @static
	 * @param {string} offcanvasName - The offcanvas name used for element selection
	 * @param {string} [offcanvasId] - Optional offcanvas element ID, defaults to 'id__' + offcanvasName
	 * @returns {void}
	 */
	static resetOffcanvasByName(offcanvasName, offcanvasId) {
		const $offcanvas = $("#" + (offcanvasId || "id__" + offcanvasName));

		const classPattern = /\b(offcanvas-header-opus-|bs-opus-)[\w-]+\b/g;

		$offcanvas
			.find(".offcanvas")
			.removeClass((_, className) => (className.match(/\b(bs-opus-)[\w-]+\b/g) || []).join(" "))
			.addClass("bs-opus-green-3d")
			.end()
			.find("." + offcanvasName + "-header")
			.removeClass((_, className) => (className.match(classPattern) || []).join(" "))
			.addClass("offcanvas-header-opus-green bs-opus-green")
			.end()
			.find("#id_" + offcanvasName + "-icon-header")
			.removeClass((_, className) => (className.match(/\b(bi-)[\w-]+\b/g) || []).join(" "))
			.end()
			.find(".offcanvas-header h5")
			.nextAll(":not([data-bs-dismiss='offcanvas'])")
			.remove()
			.end()
			.end()
			.find("#id_body-" + offcanvasName)
			.html("")
			.end()
			.find('button[id^="id_save"], button[id^="id_submit"], button[id^="id_cancel"]')
			.prop("disabled", false)
			.hide()
			.find("> span")
			.removeClass("me-1 spinner-border spinner-border-sm")
			.end()
			.end()
			.find('button[id^="id_close"]')
			.prop("disabled", false)
			.show();
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
