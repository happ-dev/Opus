/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-07-13 21:33:02
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-16 19:12:14
 **/

/**
 * DataTable wrapper for Opus Framework
 *
 * Provides server-side/client-side DataTable initialization with Opus styling,
 * CRUD buttons, strategy-based row actions, and column filtering.
 *
 * @example
 * const dt = new OpusDataTable({ app: "demo", event: "payroll", target: "#modal" });
 * dt.bindTable({
 *     order: [1, "asc"],
 *     columnDefs: [{ visible: false, targets: [0] }],
 *     drawBySelectMenu: [3, 4],
 *     drawByInputText: [1, 2],
 *     headerSlot: { id: "my-filters", columns: 6 },
 *     footerCallback: function (row, data, start, end, display) {},
 *     fnDrawCallback: function (oSettings) {},
 *     initComplete: function (settings, json) {}
 * });
 */
class OpusDataTable {
	/**
	 * @param {Object} options
	 * @param {string} options.app - Application name (required)
	 * @param {string} options.event - Table event name (required)
	 * @param {string} [options.container="main"] - CSS selector for the container holding the table
	 * @param {string} [options.strategy="show"] - Default action strategy: "show"|"edit"|"delete"|"add"
	 * @param {string} [options.target] - Modal/offcanvas selector for action links (e.g. "#modal-id")
	 * @param {string} [options.id] - Table ID override; auto-detected from container if omitted
	 * @param {string} [options.api="tableevent"] - API endpoint name
	 * @param {string} [options.process="serverside"] - Process type for the request
	 */
	constructor(options) {
		this.container = document.querySelector(options.container || "main");
		this.strategy = options.strategy || "show";
		this.app = options.app;
		this.event = options.event;
		this.target = options.target?.replace(/^(?!#)/, "#") || null;
		this.table = options.id
			? options.id.replace(/^(?!#)/, "#")
			: `#${this.container.querySelector("table[id$='-dt']")?.id}`;
		this.tableWrapper = this.table + "_wrapper";
		this.api = options.api || "tableevent";
		this.process = options.process || "serverside";
		this.link =
			window.location.pathname +
			"?api=" +
			this.api +
			"&app=" +
			this.app +
			"&event=" +
			this.event +
			"&process=" +
			this.process;
		this.radio = "data-" + this.app + "-" + this.event + "-toggle";
		this.headerSlot = null;
		this.dt = null;
	}

	/**
	 * Initializes DataTable with Opus layout, buttons, and event bindings
	 *
	 * @param {Object} [options={}]
	 * @param {Object} [options.table] - Table CSS classes
	 * @param {string} [options.table.thead="table-opus-green"] - CSS class for thead
	 * @param {string} [options.table.tfoot="table-opus-green"] - CSS class for tfoot
	 * @param {string} [options.lang=window.APP_LANGUAGE] - Language code ("en"|"pl")
	 * @param {Array} [options.lengthMenu=[[25,50,75,100],[25,50,75,100]]] - Page length options
	 * @param {Array} [options.order=[1,"desc"]] - Default column ordering [colIndex, direction]
	 * @param {boolean} [options.stateSave=false] - Persist table state in localStorage
	 * @param {boolean} [options.processing=true] - Show processing indicator
	 * @param {boolean} [options.serverSide=true] - Enable server-side processing; determines ajax and reload strategy
	 * @param {boolean} [options.fixedColumns=true] - Enable fixed columns
	 * @param {number} [options.searchDelay=500] - Delay in ms before search request
	 * @param {Array} [options.columnDefs=[]] - DataTables columnDefs array
	 * @param {Function|null} [options.footerCallback] - Called on every draw for footer calculations
	 *   @callback footerCallback
	 *   @param {HTMLElement} row - Footer row element
	 *   @param {Array} data - Full dataset
	 *   @param {number} start - Start index of current page
	 *   @param {number} end - End index of current page
	 *   @param {Array} display - Display indexes
	 * @param {Function|null} [options.fnDrawCallback] - Called after every table draw
	 *   @callback fnDrawCallback
	 *   @param {Object} oSettings - DataTables settings object
	 * @param {Function|null} [options.initComplete] - Called once after first DataTable draw; `this` = OpusDataTable instance
	 *   @callback initComplete
	 *   @param {Object} settings - DataTables settings object
	 *   @param {Object} json - Ajax response (null if client-side)
	 * @param {Array|null} [options.drawBySelectMenu] - Column indexes with <select> filters in thead
	 * @param {Array|null} [options.drawByInputText] - Column indexes with <input> filters in thead
	 * @param {Object|null} [options.headerSlot] - Additional slot in the top bar
	 * @param {string} options.headerSlot.id - ID for the slot div
	 * @param {number} [options.headerSlot.columns=7] - Bootstrap grid columns (1-12)
	 */
	bindTable(options = {}) {
		// Set default values for options
		const thead = options.table?.thead || "table-opus-green";
		const tfoot = options.table?.tfoot || "table-opus-green";
		const lang = options.lang || window.APP_LANGUAGE;
		const lengthMenu = options.lengthMenu || [
			[25, 50, 75, 100],
			[25, 50, 75, 100],
		];
		const order = options.order || [1, "desc"];
		const stateSave = options.stateSave ?? false;
		const processing = options.processing ?? true;
		const serverSide = options.serverSide ?? true;
		const fixedColumns = options.fixedColumns ?? true;
		const searchDelay = options.searchDelay ?? 500;
		const columnDefs = options.columnDefs || [];
		const footerCallback = options.footerCallback || null;
		const fnDrawCallback = options.fnDrawCallback || null;
		const initComplete = options.initComplete || null;
		const drawBySelectMenu = options.drawBySelectMenu || null;
		const drawByInputText = options.drawByInputText || null;
		const headerSlot = options.headerSlot || null;

		// CSS styling to table
		ogl.tableCSS(this.table, { thead: thead, tfoot: tfoot });

		// instance DataTable
		this.dt = $(this.table).DataTable({
			// Language
			...(lang !== "en" && {
				language: { url: `vendor/datatables/${lang}.json` },
			}),
			lengthMenu,
			order,
			stateSave,
			processing,
			serverSide,
			fixedColumns,
			searchDelay,
			...(serverSide && { ajax: this.link }),
			columnDefs,
			...(footerCallback && { footerCallback }),
			fnDrawCallback: (oSettings) => {
				if (fnDrawCallback) fnDrawCallback(oSettings);
			},
			initComplete: (settings, json) => {
				$(this.table)
					.find(".search-filter")
					.on("click", function (event) {
						event.stopPropagation();
					});

				// Set dt opus layout
				this.opusTableStyle();

				// Buttons
				this.addButton();
				this.toggleButtons();
				this.refreshButton(settings);

				// Additional header slot
				if (headerSlot) {
					this.additionalHeaderSlot(headerSlot.id, headerSlot.columns);
				}

				if (initComplete) {
					initComplete.call(this, settings, json);
				}
			},
		});

		// Draw by select menu
		if (drawBySelectMenu) this.drawBySelectMenu(drawBySelectMenu);

		// Draw by input text
		if (drawByInputText) this.drawByInputText(drawByInputText);

		// Reload table
		serverSide ? this.reloadTable() : this.reloadBody();
	}

	/** Destroys DataTable instance and cleans up */
	destroyTable() {
		if (this.dt) {
			this.dt.destroy();
			this.dt = null;
		}
	}

	/** Applies Opus CSS classes to DataTable wrapper rows */
	opusTableStyle() {
		const $rows = $(this.tableWrapper).addClass("bs-opus-black-3d").children("div");

		// row 1: top bar
		$rows
			.eq(0)
			.removeClass(function (i, c) {
				return (c.match(/mt-\S+/g) || []).join(" ");
			})
			.addClass("table-opus-dt-row-0");

		// row 2: table
		$rows
			.eq(1)
			.removeClass(function (i, c) {
				return (c.match(/mt-\S+/g) || []).join(" ");
			})
			.children("div")
			.first()
			.addClass("p-0");

		// row 3: bottom bar
		$rows
			.eq(2)
			.removeClass(function (i, c) {
				return (c.match(/mt-\S+/g) || []).join(" ");
			})
			.addClass("table-opus-dt-row-2");
	}

	/** Replaces default pagination text with Bootstrap Icons */
	pagingIcons() {
		const $paging = $(this.tableWrapper).find(".dt-paging-button");
		$paging.filter(".first").html('<i class="bi bi-chevron-double-left"></i>');
		$paging.filter(".previous").html('<i class="bi bi-chevron-left"></i>');
		$paging.filter(".next").html('<i class="bi bi-chevron-right"></i>');
		$paging.filter(".last").html('<i class="bi bi-chevron-double-right"></i>');
	}

	/**
	 * Hides columns after DataTable init (avoid with fixedColumns, use columnDefs instead)
	 * @param {Array<number>} columns - Column indexes to hide
	 */
	hideColumns(columns) {
		this.dt.columns(columns).visible(false);
	}

	/**
	 * Binds <select> elements in thead to column search
	 * Supports single and multiple select (comma-separated values)
	 * @param {Array<number>} columns - Column indexes containing <select> filters
	 */
	drawBySelectMenu(columns) {
		this.dt.columns(columns).every(function () {
			const col = this;

			$("select", col.header()).on("click", function (event) {
				event.stopPropagation();
			});

			$("select", col.header()).on("change", function () {
				const searchValue = $(this).prop("multiple") ? ($(this).val() || []).join(",") : this.value;

				if (col.search() !== searchValue) {
					col.search(searchValue).draw();
				}
			});
		});
	}

	/**
	 * Binds <input> elements in thead to column search on keyup/change
	 * @param {Array<number>} columns - Column indexes containing <input> filters
	 */
	drawByInputText(columns) {
		this.dt.columns(columns).every(function () {
			const col = this;

			$("input", col.header()).on("click", function (event) {
				event.stopPropagation();
			});

			$("input", col.header()).on("keyup change", function () {
				if (col.search() !== this.value) {
					col.search(this.value).draw();
				}
			});
		});
	}

	/**
	 * Generates an action link HTML for table cells
	 * @param {string|number} id - Row primary key value
	 * @param {string} text - Link display text
	 * @returns {string} HTML anchor with data attributes for modal/strategy
	 */
	actionLink(id, text) {
		return `
			<a class="action-link-opus" data-bs-toggle="modal" data-bs-target="${this.target}"
				data-app="${this.app}" data-event="${this.event}" data-id="${id}" data-process="editor"
				data-strategy="${this.strategy}" data-table-id="${this.table}">
				${text}
			</a>
		`;
	}

	/** Appends "Add" button to dt-length bar if table has data-add="1" */
	addButton() {
		const text = "<?= Opus\controller\lang\Lang::getInstance()->get('html.buttons.dt.add') ?>";

		if ($(this.table).attr("data-add") === "1") {
			$(`<button type="button" class="btn btn-light btn-sm table-opus-dt-row-0-btn-margin bs-opus-black-3d ms-1"
				data-bs-toggle="modal" data-bs-target="${this.target}" data-app="${this.app}"
				data-event="${this.event}" data-process="editor" data-strategy="add" data-table-id="${this.table}">
				<i class="me-1 bi bi-plus-lg"></i><em>${text}</em>
			</button>`).appendTo($(this.tableWrapper).find(".dt-length"));
		}
	}

	/** Appends show/edit/delete radio toggle buttons based on table data-* attributes */
	toggleButtons() {
		const textShow = "<?= Opus\controller\lang\Lang::getInstance()->get('html.buttons.dt.show') ?>";
		const textEdit = "<?= Opus\controller\lang\Lang::getInstance()->get('html.buttons.dt.edit') ?>";
		const textDelete = "<?= Opus\controller\lang\Lang::getInstance()->get('html.buttons.dt.delete') ?>";
		const $table = $(this.table);

		const showHTML =
			$table.attr("data-show") === "1"
				? `<input type="radio" class="btn-check" name="${this.radio}" value="show" id="id__${this.radio}-show" autocomplete="off" checked>
				<label class="btn btn-sm btn-outline-light" for="id__${this.radio}-show">
					<i class="me-1 bi bi-zoom-in"></i><em>${textShow}</em>
				</label>`
				: "";

		const editHTML =
			$table.attr("data-edit") === "1"
				? `<input type="radio" class="btn-check" name="${this.radio}" value="edit" id="id__${this.radio}-edit" autocomplete="off">
				<label class="btn btn-sm btn-outline-light" for="id__${this.radio}-edit">
					<i class="me-1 bi bi-pencil-square"></i><em>${textEdit}</em>
				</label>`
				: "";

		const deleteHTML =
			$table.attr("data-delete") === "1"
				? `<input type="radio" class="btn-check" name="${this.radio}" value="delete" id="id__${this.radio}-delete" autocomplete="off">
				<label class="btn btn-sm btn-outline-light" for="id__${this.radio}-delete">
					<i class="me-1 bi bi-trash"></i><em>${textDelete}</em>
				</label>`
				: "";

		$(`<div class="btn-group btn-group-toggle table-opus-dt-row-0-btn-margin bs-opus-black-3d ms-2" data-toggle="buttons" style="color: var(--bs-gray-200) !important;">
			${showHTML}${editHTML}${deleteHTML}
		</div>`).appendTo($(this.tableWrapper).find(".dt-length"));
	}

	/**
	 * Appends refresh button that clears all filters and redraws
	 * @param {Object} oSettings - DataTables settings object (from initComplete)
	 */
	refreshButton(oSettings) {
		$(`<button type="button" class="btn btn-light btn-sm table-opus-dt-row-0-btn-margin dt-clear-filter bs-opus-black-3d ms-2">
			<i class="bi bi-arrow-repeat"></i>
		</button>`).appendTo($(this.tableWrapper).find(".dt-length"));

		$(this.tableWrapper).on("click", ".dt-clear-filter", () => {
			oSettings.api.columns().every(function () {
				$("input", this.header()).val(null);
				$("select", this.header()).val(null);
			});

			$(this.tableWrapper).find('.dt-search > input[type="search"]').val(null);
			oSettings.api.columns().search(null);
			this.dt.search(null).draw();
		});
	}

	/** Binds radio toggle change to ajax.reload() (server-side mode) */
	reloadTable() {
		jQuery(document).on("change", `.dt-length input:radio[name=${this.radio}]`, (event) => {
			this.setStrategy($(event.target).val());
			this.dt.ajax.reload();
		});
	}

	/** Binds radio toggle change to update data-strategy on existing links (client-side mode) */
	reloadBody() {
		jQuery(document).on("change", `.dt-length input:radio[name=${this.radio}]`, (e) => {
			this.setStrategy($(e.target).val());
			$(this.table).find("a[data-strategy]").attr("data-strategy", this.strategy);
		});
	}

	/**
	 * Updates current strategy
	 * @param {string} newStrategy - "show"|"edit"|"delete"|"add"
	 */
	setStrategy(newStrategy) {
		this.strategy = newStrategy;
	}

	/**
	 * Updates event name and rebuilds link/radio attributes
	 * @param {string} newEvent - New event name
	 */
	setEvent(newEvent) {
		this.event = newEvent;
		this.link =
			window.location.pathname +
			"?api=" +
			this.api +
			"&app=" +
			this.app +
			"&event=" +
			this.event +
			"&process=" +
			this.process;
		this.radio = "data-" + this.app + "-" + this.event + "-toggle";
	}

	/**
	 * Inserts additional column div in the top bar row
	 * @param {string} id - ID for the new div
	 * @param {number} [columns=7] - Bootstrap grid columns (1-12)
	 */
	additionalHeaderSlot(id, columns = 7) {
		this.headerSlot = id;
		$(`<div id="${id}" class="col-md-${columns}"></div>`).insertAfter(
			$(this.tableWrapper).find(".row:first-child div.col-md-auto:first-child"),
		);
	}
}
