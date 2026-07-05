/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-06-05 16:30:49
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-05 13:45:47
 **/

/**
 * Modal class for managing Bootstrap modal dialogs
 *
 * All modals must follow a standardized template structure with specific naming conventions.
 * Each modal component uses the modalName as a prefix for consistent identification.
 *
 * @class OpusModal
 * @description Handles creation and management of modal dialog components
 *
 * @example
 * // Create a new modal instance
 * const loginModal = new OpusModal('login');
 *
 * @template Modal Structure
 * Each modal must contain the following template hierarchy:
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
 * @param {Object} options - Configuration object for the modal
 */
class OpusModal {
	/**
	 * Creates a new Modal instance
	 *
	 * @param {Object} options - Configuration object for the modal
	 * @param {string} [options.name] - The modal name used for CSS class generation
	 * @param {string} [options.id] - Element ID, defaults to 'id__' + name
	 * @param {Object} [options.data={}] - Data object to be sent via $.ajax requests
	 * @param {string} [options.api='asyncevent'] - API endpoint for AJAX requests
	 * @param {Object} [options.header={}] - Header configuration object
	 * @param {string} [options.header.text] - Header text for the modal
	 * @param {string} [options.header.icon] - Bootstrap icon
	 * @param {string} [options.header.class] - CSS class representing the header color
	 *
	 * @example
	 * const loginModal = new OpusModal({
	 *     name: 'login',
	 *     data: { app: 'users', event: 'login' },
	 *     api: 'asyncevent',
	 *     header: { text: 'User Login', icon: 'person', class: 'text-primary' }
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
	 * Updates the modal header with new options
	 *
	 * @param {Object} options - Header configuration options
	 * @param {string} [options.text] - Header text content
	 * @param {string} [options.icon] - Bootstrap icon class
	 * @param {string} [options.class] - CSS class for header styling
	 *
	 * @example
	 * modal.setHeader({
	 *     text: 'Edit User',
	 *     icon: 'bi-pencil',
	 *     class: 'text-warning'
	 * });
	 */
	setHeader(options) {
		// Update this.header with provided values
		if (options.text !== undefined) this.header.text = options.text;
		if (options.icon !== undefined) this.header.icon = options.icon;
		if (options.class !== undefined) this.header.class = options.class;
		if (options.additionalText !== undefined) this.header.additionalText = options.additionalText;

		// Apply header class if set
		if (this.header.class) {
			$(this.headerClass)
				.removeClass(function (index, className) {
					let matchedClasses = className.match(/\b(modal-header-opus-|bs-opus-)[\w-]+\b/g);
					return (matchedClasses || []).join(" ");
				})
				.addClass(this.header.class);
		}

		// Set icon and text
		$("#id_" + this.name + "-icon-header")
			.removeClass((_, className) => (className.match(/\b(bi-)[\w-]+\b/g) || []).join(" "))
			.addClass(this.header.icon);
		$("#id_" + this.name + "-text-header").html(this.header.text);

		// Set additional text
		if (this.header.additionalText) {
			const h5 = this.el?.querySelector(".modal-header h5");
			if (h5) h5.insertAdjacentHTML("afterend", this.header.additionalText);
		}
	}

	/**
	 * Prepares the modal for AJAX operations by showing loader and hiding content
	 *
	 * @description Shows the loading indicator and hides all content sections to prepare
	 * the modal for new content loading via AJAX requests.
	 *
	 * @returns {void}
	 * @example
	 * modal.ajaxInitialConditions()
	 */
	ajaxInitialConditions() {
		$(this.el)
			.find(this.loaderClass)
			.fadeIn()
			.end()
			.find(".alert-danger, .alert-success, " + this.slideDownClass)
			.hide();
	}

	/**
	 * Configures the modal header to display error state
	 *
	 * @description Applies red error styling to the header
	 * @returns {void}
	 */
	errorHeader() {
		// Use a constant for the pattern to improve readability and reusability
		const headerClassPattern = /\b(modal-header-opus-|bs-opus-)\w+\b/g;

		// Use arrow function and chaining for cleaner syntax
		$(this.headerClass)
			.removeClass((_, className) => (className.match(headerClassPattern) || []).join(" "))
			.addClass("modal-header-opus-red bs-opus-red");
	}

	/**
	 * Configures the modal content to display error state styling
	 *
	 * @description Removes existing shadow/styling classes that match the pattern 'bs-happ-*'
	 * and applies red error styling to the modal content container.
	 *
	 * @example
	 * modal.errorContent();
	 */
	errorContent() {
		const contentClassPattern = /\b(bs-opus-)[\w-]+\b/g;

		$(this.el)
			.find(".modal-content")
			.removeClass((_, className) => (className.match(contentClassPattern) || []).join(" "))
			.addClass("bs-opus-red-3d");
	}

	/**
	 * Configures the modal footer for error state
	 *
	 * @description Removes loading spinners from submit buttons, hides submit/cancel buttons,
	 * and shows the close button to allow user to dismiss the error modal.
	 *
	 * @example
	 * modal.errorFooter();
	 */
	errorFooter() {
		const $footer = $(this.footerClass);

		// Define selectors for better readability
		const spinnerElements = 'button[id^="id_save"] > span, button[id^="id_submit"] > span';
		const actionButtons = 'button[id^="id_save"], button[id^="id_submit"], button[id^="id_cancel"]';
		const closeButton = 'button[id^="id_close"]';

		// Use chaining for better performance
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
	 * @description Sets error header and footer states, then fades out loader and displays
	 * error message with header and footer sections. Uses http500 variable for error text.
	 *
	 * @example
	 * modal.ajaxFail();
	 */
	ajaxFail() {
		// Apply error styling
		this.errorContent();
		this.errorHeader();
		this.errorFooter();

		// Cache the modal element
		const $modal = $(this.el);

		// Use arrow function to avoid 'that' variable
		$modal.find(this.loaderClass).fadeOut("slow", () => {
			// Show error message and sections in one chain
			$modal
				.find(this.alertsClass)
				.find(".alert-danger")
				.html(http500)
				.slideDown("slow")
				.end()
				.end()
				.find(this.headerClass + ", " + this.footerClass)
				.slideDown("slow");
		});
	}

	/**
	 * Handles caught AJAX errors with custom error processing
	 *
	 * @description Processes AJAX response errors, formats error messages with details,
	 * sets error header/footer states, and displays the formatted error message.
	 *
	 * @param {Object} obj - Response object containing error information
	 * @param {string} [obj.message] - Error message from server response
	 * @param {string} [obj.details] - Additional error details from server response
	 * @param {string} catchError - Error type identifier or fallback error message
	 *
	 * @example
	 * // Handle success-false response
	 * modal.ajaxCatch({message: 'Validation failed', details: 'Email required'}, 'success-false');
	 *
	 * // Handle generic error
	 * modal.ajaxCatch({}, 'Network timeout');
	 */
	ajaxCatch(obj, catchError) {
		// Apply error styling
		this.errorContent();
		this.errorHeader();
		this.errorFooter();

		// Format error message based on error type
		const errorMessage =
			catchError === "success-false" ? this.formatSuccessFalseError(obj, catchError) : catchError;

		// Cache the modal element
		const $modal = $(this.el);

		// Use arrow function to avoid 'that' variable
		$modal.find(this.loaderClass).fadeOut("slow", () => {
			// Show error message and sections in one chain
			$modal
				.find(this.alertsClass)
				.find(".alert-danger")
				.html(errorMessage)
				.slideDown("slow")
				.end()
				.end()
				.find(this.headerClass + ", " + this.footerClass)
				.slideDown("slow");
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
	 *
	 * @example
	 * modal.setFooterButtons('edit'); // Shows submit/cancel buttons
	 * modal.setFooterButtons('show'); // Shows only close button
	 */
	setFooterButtons(strategy) {
		const isEditable = /^(add|edit|delete)$/.test(strategy);
		const $footer = $(this.footerClass);

		// Define button selectors once
		const actionButtons = 'button[id^="id_save"], button[id^="id_submit"], button[id^="id_cancel"]';
		const closeButton = 'button[id^="id_close"]';

		// Use chaining and toggle for better performance
		$footer.find(actionButtons).toggle(isEditable).end().find(closeButton).toggle(!isEditable);

		// Only enable save button if in editable mode
		if (isEditable) {
			$footer.find('button[id^="id_save"], button[id^="id_submit"]').prop("disabled", false);
		}
	}

	/**
	 * Applies consistent CSS styling to tables within a container
	 *
	 * @description Standardizes table appearance by adding Bootstrap alignment classes
	 * and normalizing font weights for better visual consistency across the modal.
	 *
	 * @param {string|Element|jQuery} container - Container selector, element, or jQuery object containing tables
	 *
	 * @example
	 * // Using with selector
	 * modal.fixTableCSS('.table-event-body');
	 *
	 * // Using with element
	 * modal.fixTableCSS(document.getElementById('tableContainer'));
	 *
	 * // Using with jQuery object
	 * modal.fixTableCSS($('.modal-body'));
	 */
	fixTableCSS(container) {
		const $tables = $(container).find("table");

		// Apply vertical alignment to header cells
		$tables.find("thead th").addClass("align-middle");

		// Normalize font weight for body header cells and add alignment
		$tables.find("tbody th").css("font-weight", "normal");
		$tables.find("tbody tr").addClass("align-middle");
	}

	/**
	 * Updates modal data object with new key-value pairs
	 *
	 * @param {Object} newData - Object with properties to merge into existing data
	 * @returns {this} Returns this instance for method chaining
	 */
	setData(newData) {
		Object.assign(this.data, newData);
		return this;
	}

	/**
	 * Sets up the modal for post-initialization state
	 *
	 * @description Prepares the modal for user interaction after initial loading by:
	 * - Hiding all alert messages (success and error)
	 * - Hiding the loading indicator
	 * - Hiding the close button
	 * - Showing submit, save and cancel buttons
	 *
	 * This creates a ready state for form submission before any actions are taken.
	 *
	 * @returns {void}
	 */
	postInitialConditions() {
		// Cache the jQuery objects and use chaining for better performance
		const $modal = $(this.el);
		const $footer = $modal.find(this.footerClass);

		// Hide alerts and loader in one operation
		$modal.find(".alert-danger, .alert-success, " + this.loaderClass).hide();

		// Toggle button visibility with a single DOM update
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
	 * @description Displays success message and updates button states
	 * @param {Object} obj - Success response object
	 * @returns {void}
	 */
	postSuccess(obj) {
		// Format success message with details
		const message = obj?.message || "";
		const details = obj?.details || "";
		const successMessage = details ? `${message}<br>${details}` : message;

		// Cache jQuery objects
		const $footer = $(this.footerClass);

		// Update button states in one chain
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

		// Display success message
		$(this.alertsClass).find(".alert-success").html(successMessage).slideDown("slow");
	}

	/**
	 * Handles POST request failures by displaying error state
	 *
	 * @description Sets the modal to error state by applying error header styling,
	 * configuring footer buttons for error handling, and displaying a generic
	 * server error message. Uses the global http500 variable for error text.
	 *
	 * @example
	 * modal.postFail();
	 */
	postFail() {
		// Apply error styling
		this.errorContent();
		this.errorHeader();
		this.errorFooter();

		// Display server error message directly
		$(this.alertsClass).find(".alert-danger").html(http500).slideDown("slow");
	}

	/**
	 * Handles caught POST request errors with custom error processing
	 *
	 * @description Processes POST response errors by setting error styling for content
	 * and header, formatting error messages with details, configuring footer buttons,
	 * and displaying the formatted error message. Similar to ajaxCatch but for POST operations.
	 *
	 * @param {Object} obj - Response object containing error information
	 * @param {string} [obj.message] - Error message from server response
	 * @param {string} [obj.details] - Additional error details from server response
	 * @param {string} catchError - Error type identifier or fallback error message
	 *
	 * @example
	 * // Handle success-false response
	 * modal.postCatch({message: 'Validation failed', details: 'Email required'}, 'success-false');
	 *
	 * // Handle generic error
	 * modal.postCatch({}, 'Network timeout');
	 */
	postCatch(obj, catchError) {
		// Apply error styling
		this.errorContent();
		this.errorHeader();
		this.errorFooter();

		// Format error message based on error type
		const errorMessage =
			catchError === "success-false" ? this.formatSuccessFalseError(obj, catchError) : catchError;

		// Display formatted error message
		$(this.alertsClass).find(".alert-danger").html(errorMessage).slideDown("slow");
	}

	/**
	 * Prepares the modal for form submission by showing loading state
	 *
	 * @description Updates the UI to indicate that a form submission is in progress by:
	 * - Disabling submit and save buttons to prevent multiple submissions
	 * - Adding a spinner animation to the button content to provide visual feedback
	 *
	 * This method should be called when a form is submitted to prevent users from
	 * submitting the same form multiple times while processing is ongoing.
	 *
	 * @returns {void}
	 */
	postSubmitClick() {
		// Cache jQuery object and use chaining for better performance
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
	 * Binds modal show/hide events with AJAX loading
	 *
	 * @description Registers Bootstrap modal event listeners for show and hide events.
	 * On show: executes relatedTarget callback (if provided) to read trigger element data,
	 * performs AJAX request, sets header, populates body content, and configures footer.
	 * If onShow callback is provided, it handles body content injection.
	 * If onShow is not provided, response.body is injected into bodyRowColClass by default.
	 * After $.ajax completes, onSave callback is executed to bind form submit handler.
	 * On hide: resets modal to initial state and executes optional onHide callback.
	 *
	 * @param {Object} options - Configuration options
	 * @param {Function|null} [options.relatedTarget=null] - Callback executed before AJAX request,
	 *     receives Bootstrap modal event. Use to read data attributes from trigger element
	 *     and update modal data via setData()
	 * @param {string} [options.footerStrategy='show'] - Footer button strategy ('show', 'add', 'edit', 'delete')
	 * @param {Function|null} [options.onShow=null] - Callback executed after AJAX response is loaded,
	 *     receives parsed response object. Use to customize body content injection
	 * @param {Function|null} [options.onRender=null] - Callback executed after body content is injected
	 *     and footer is configured, before slideDown animation. Use for post-render operations
	 *     (e.g. syntax highlighting, DataTable initialization)
	 * @param {Function|null} [options.onSave=null] - Callback executed after $.ajax request,
	 *     use to bind form submit handler via bindPostModal()
	 * @param {Function|null} [options.onHide=null] - Callback executed when modal is hidden,
	 *     use to trigger external actions (e.g. DataTable reload)
	 *
	 * @example
	 * // Full usage with all callbacks
	 * modal.bindAjaxModal({
	 *     relatedTarget: (event) => {
	 *         let id = $(event.relatedTarget).attr('data-id');
	 *         modal.setData({ id: id });
	 *     },
	 *     footerStrategy: "edit",
	 *     onShow: (response) => {
	 *         $(modal.bodyRowColClass).html(response.body);
	 *     },
	 *     onRender: () => {
	 *         highlightCode(modal.el);
	 *     },
	 *     onSave: () => {
	 *         modal.bindPostModal({
	 *             buildUrl: () => {
	 *                 const url = new URLSearchParams();
	 *                 url.append('request', 'save');
	 *                 return url;
	 *             },
	 *             buildData: (form) => {
	 *                 return Object.fromEntries(new FormData(form));
	 *             }
	 *         });
	 *     },
	 *     onHide: () => {
	 *         dtStock.ajax.reload();
	 *     }
	 * });
	 *
	 * @example
	 * // Minimal usage with default body injection
	 * modal.bindAjaxModal({ footerStrategy: "show" });
	 */
	bindAjaxModal(options = {}) {
		const relatedTarget = options.relatedTarget || null;
		const strategy = options.footerStrategy || "show";
		const onShow = options.onShow || null;
		const onRender = options.onRender || null;
		const onHide = options.onHide || null;
		const onSave = options.onSave || null;

		this.el.addEventListener("show.bs.modal", (event) => {
			if (relatedTarget) relatedTarget(event);
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
						OpusModal.ajaxThrowException(response);

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
								$(this.slideDownClass).slideDown("slow");
							});
					} catch (error) {
						this.ajaxCatch(response, error);
					}
				})
				.fail(() => {
					this.ajaxFail();
				});

			// save data
			if (onSave) onSave();
		});

		this.el.addEventListener("hide.bs.modal", () => {
			OpusModal.resetModalByName(this.name, this.id);
			if (onHide) onHide();
		});
	}

	/**
	 * Binds form submit handler with POST request
	 *
	 * @description Registers a submit event handler on the modal form. On submit:
	 * validates form, shows loading state, builds URL params and POST data via callbacks,
	 * sends POST request with JSON body and CSRF token, handles success/error states.
	 * Should be called in onSave callback of bindAjaxModal, after $.ajax completes.
	 *
	 * @param {Object} options - Configuration options
	 * @param {Function|null} [options.buildUrl=null] - Callback to build URLSearchParams for the request URL.
	 *     Must return a URLSearchParams instance
	 * @param {Function|null} [options.buildData=null] - Callback to build POST data object.
	 *     Receives form element, must return an object to be JSON.stringified
	 *
	 * @example
	 * modal.bindPostModal({
	 *     buildUrl: () => {
	 *         const url = new URLSearchParams();
	 *         url.append('request', 'baskets-save');
	 *         url.append('id', modal.data.id);
	 *         return url;
	 *     },
	 *     buildData: (form) => {
	 *         const postData = {};
	 *         const dt = $('#id_table').DataTable();
	 *         dt.rows().every(function () {
	 *             $(this.node()).find('input, select').each(function () {
	 *                 if ($(this).val()) postData[$(this).attr('name')] = $(this).val();
	 *             });
	 *         });
	 *         return postData;
	 *     }
	 * });
	 */
	bindPostModal(options = {}) {
		const buildData = options.buildData || null;

		// Use jQuery to properly remove any existing submit handlers and add a new one
		$(this.formId)
			.off("submit")
			.on("submit", (submitEvent) => {
				submitEvent.preventDefault();
				const form = submitEvent.target;

				if (form.checkValidity() === false) {
					submitEvent.stopPropagation();
					return;
				}

				this.postSubmitClick();

				const url = buildUrl ? buildUrl() : new URLSearchParams();
				const postData = buildData ? buildData(form) : {};

				$.post({
					url: form.action + "&" + url.toString(),
					data: JSON.stringify(postData),
					contentType: "application/json",
					headers: {
						"X-CSRF-TOKEN": $(this.formId + ' input[name="csrf"]').val(),
					},
					cache: false,
				})
					.done((result) => {
						let response;
						try {
							response = JSON.parse(result);
							OpusModal.ajaxThrowException(response);
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
	 * Resets a modal to initial state by clearing header, body, and footer content
	 *
	 * @static
	 * @param {string} modalName - The modal name used for element selection
	 * @param {string} [modalId] - Optional modal element ID, defaults to 'id__' + modalName
	 * @returns {void}
	 */
	static resetModalByName(modalName, modalId) {
		const $modal = $("#" + (modalId || "id__" + modalName));

		// Use a single regex pattern for both content and header
		const classPattern = /\b(modal-header-opus-|bs-opus-)[\w-]+\b/g;

		// Reset modal content and header in one chain
		$modal
			.find(".modal-content")
			.removeClass((_, className) => (className.match(/\b(bs-opus-)[\w-]+\b/g) || []).join(" "))
			.addClass("bs-opus-green-3d")
			.end()
			.find("." + modalName + "-header")
			.removeClass((_, className) => (className.match(classPattern) || []).join(" "))
			.addClass("modal-header-opus-green bs-opus-green")
			.end()
			.find("#id_" + modalName + "-icon-header")
			.removeClass((_, className) => (className.match(/\b(bi-)[\w-]+\b/g) || []).join(" "))
			.end()
			.find(".modal-header h5")
			.nextAll()
			.remove()
			.end()
			.end()
			.find("#id_body-" + modalName)
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
	 * @description Performs validation checks on AJAX response objects to ensure they contain
	 * valid data and success status. Throws specific error strings that can be caught and
	 * handled by calling code to display appropriate error messages to users.
	 *
	 * @static
	 * @param {Object|null|undefined} obj - The AJAX response object to validate
	 * @throws {string} 'empty-save-json' - When obj is null, undefined, or empty object
	 * @throws {string} 'success-false' - When obj.success property is explicitly false
	 *
	 * @example
	 * try {
	 *     Modal.ajaxThrowException(response);
	 *     // Process successful response
	 * } catch (error) {
	 *     if (error === 'success-false') {
	 *         // Handle server-side validation errors
	 *     }
	 * }
	 */
	static ajaxThrowException(obj) {
		if (!obj || Object.keys(obj).length === 0) throw "empty-save-json";
		if (obj.success === false) throw "success-false";
	}
}
