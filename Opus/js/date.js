/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-07-21 22:08:03
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-22 15:52:52
 **/

/**
 * Custom date and timestamp picker for Opus Framework.
 *
 * Singleton popup pattern — one DOM element reused across all inputs.
 * Supports two modes: date (yyyy-MM-dd) and timestamp (yyyy-MM-dd HH:mm:ss).
 * Mode is auto-detected from input CSS class: .date-opus-picker or .timestamp-opus-picker.
 * Labels and buttons are translated via Opus Lang class (PHP short tags).
 *
 * Features:
 * - Calendar with Monday-first week grid
 * - Month and year selection views (click title to drill up)
 * - Min/max date constraints via data-opus-picker-min/data-opus-picker-max attributes (days offset from today)
 * - Time spinners with wrap-around (timestamp mode)
 * - Real-time input update in timestamp mode
 * - Today and Clear action buttons
 * - Auto-positioning with flip-above when insufficient space below
 * - Closes on outside click and apage:closed event
 * - Safe for repeated bindDate calls (namespaced events .opusDp)
 *
 * @example Date picker
 * // HTML:
 * // <input class="form-control date-opus-picker" data-opus-picker-min="0" data-opus-picker-max="30">
 * // JS:
 * OpusDatePicker.bindDate("#my-container");
 *
 * @example Timestamp picker
 * // HTML:
 * // <input class="form-control timestamp-opus-picker" data-opus-picker-min="-7">
 * // JS:
 * OpusDatePicker.bindDate("#my-container");
 *
 * @example With onSelect callback
 * OpusDatePicker.bindDate("#form", {
 *     onSelect: (val) => console.log("Selected:", val)
 * });
 */
class OpusDatePicker {
	/** @type {jQuery|null} Singleton popup element */
	static popup = null;
	/** @type {jQuery|null} Currently focused input */
	static activeInput = null;

	/**
	 * Initializes date picker on all matching inputs within container.
	 * Appends calendar icon, binds delegated click events.
	 * Safe to call multiple times (re-binds with namespace .opusDp).
	 *
	 * @param {string} container - jQuery selector for event delegation scope
	 * @param {Object} [options={}]
	 * @param {Function} [options.onSelect] - callback fired on date selection, receives value string
	 * @static
	 */
	static bindDate(container, options = {}) {
		const $container = $(container);

		$container.find(".date-opus-picker, .timestamp-opus-picker").each(function () {
			const $input = $(this);

			if ($input.siblings(".opus-picker-icon").length || $input.parent().hasClass("opus-picker-wrap")) return;

			if ($input.parent().hasClass("form-floating")) {
				$input.after('<i class="bi bi-calendar-event opus-picker-icon"></i>');
			} else {
				$input.wrap('<span class="opus-picker-wrap"></span>');
				$input.after('<i class="bi bi-calendar-event opus-picker-icon"></i>');
			}
		});

		$container.off("click.opusDp").on("click.opusDp", ".date-opus-picker, .timestamp-opus-picker", function () {
			OpusDatePicker.open(this, options);
		});

		if (!OpusDatePicker._globalClose) {
			$(document).on("mousedown", function (e) {
				if (!OpusDatePicker.popup || !OpusDatePicker.popup.is(":visible")) return;
				if ($(e.target).closest(".opus-dp").length) return;
				if (OpusDatePicker.activeInput && $(e.target).closest(OpusDatePicker.activeInput).length) return;

				OpusDatePicker.close();
			});

			$(document).on("apage:closed", function () {
				OpusDatePicker.close();
			});

			OpusDatePicker._globalClose = true;
		}
	}

	/**
	 * Opens the picker popup for given input element.
	 * Toggles closed if already open on same input.
	 * Reads mode from CSS class, min/max from data attributes.
	 * In timestamp mode, defaults time to current hour:minute:second when input is empty.
	 *
	 * @param {HTMLElement} input - the input element that triggered open
	 * @param {Object} options - options passed from bindDate
	 * @static
	 */
	static open(input, options) {
		const $input = $(input);

		if (
			OpusDatePicker.activeInput &&
			OpusDatePicker.activeInput.is($input) &&
			OpusDatePicker.popup?.is(":visible")
		) {
			OpusDatePicker.close();
			return;
		}

		OpusDatePicker.createPopup();
		OpusDatePicker.activeInput = $input;

		const mode = $input.hasClass("timestamp-opus-picker") ? "timestamp" : "date";
		OpusDatePicker.popup.data("mode", mode);
		OpusDatePicker.popup.toggleClass("opus-dp-timestamp", mode === "timestamp");
		OpusDatePicker.popup.find(".opus-dp-time").toggle(mode === "timestamp");

		// Min/Max from data attributes (offset in days from today)
		const today = new Date();
		today.setHours(0, 0, 0, 0);
		const minAttr = $input.data("opus-picker-min");
		const maxAttr = $input.data("opus-picker-max");

		OpusDatePicker.popup.data("minDate", minAttr != null ? new Date(today.getTime() + minAttr * 86400000) : null);
		OpusDatePicker.popup.data("maxDate", maxAttr != null ? new Date(today.getTime() + maxAttr * 86400000) : null);

		const parsed = OpusDatePicker.parseValue($input.val());
		const now = new Date();
		const year = parsed?.year ?? now.getFullYear();
		const month = parsed?.month ?? now.getMonth();

		OpusDatePicker.popup.data("year", year);
		OpusDatePicker.popup.data("month", month);
		OpusDatePicker.popup.data("day", parsed?.day ?? null);
		OpusDatePicker.popup.data("view", "days");

		if (mode === "timestamp") {
			OpusDatePicker.popup.find(".opus-dp-h").val(String(parsed?.h ?? now.getHours()).padStart(2, "0"));
			OpusDatePicker.popup.find(".opus-dp-m").val(String(parsed?.m ?? now.getMinutes()).padStart(2, "0"));
			OpusDatePicker.popup.find(".opus-dp-s").val(String(parsed?.s ?? now.getSeconds()).padStart(2, "0"));
		}

		OpusDatePicker.renderCalendar(year, month);
		OpusDatePicker.position($input);
		OpusDatePicker.popup.fadeIn(150);
	}

	/**
	 * Closes the picker popup with fade-out animation.
	 * @static
	 */
	static close() {
		if (!OpusDatePicker.popup) return;

		OpusDatePicker.popup.fadeOut(100);
		OpusDatePicker.activeInput = null;
	}

	/**
	 * Creates the singleton popup DOM element and appends it to body.
	 * Called lazily on first open. Binds all internal events via bindEvents().
	 * @static
	 */
	static createPopup() {
		if (OpusDatePicker.popup) return;

		const html = `
			<div class="opus-dp" tabindex="-1">
				<div class="opus-dp-header">
					<button type="button" class="opus-dp-nav opus-dp-prev"><i class="bi bi-chevron-left"></i></button>
					<span class="opus-dp-title"></span>
					<button type="button" class="opus-dp-nav opus-dp-next"><i class="bi bi-chevron-right"></i></button>
				</div>
				<div class="opus-dp-body"></div>
				<div class="opus-dp-footer">
					<div class="opus-dp-time" style="display:none">
						<div class="opus-dp-time-col">
							<button type="button" class="opus-dp-spin" data-field="h" data-dir="1"><i class="bi bi-chevron-up"></i></button>
							<input type="text" class="opus-dp-h" value="00" maxlength="2">
							<button type="button" class="opus-dp-spin" data-field="h" data-dir="-1"><i class="bi bi-chevron-down"></i></button>
						</div>
						<span class="opus-dp-sep">:</span>
						<div class="opus-dp-time-col">
							<button type="button" class="opus-dp-spin" data-field="m" data-dir="1"><i class="bi bi-chevron-up"></i></button>
							<input type="text" class="opus-dp-m" value="00" maxlength="2">
							<button type="button" class="opus-dp-spin" data-field="m" data-dir="-1"><i class="bi bi-chevron-down"></i></button>
						</div>
						<span class="opus-dp-sep">:</span>
						<div class="opus-dp-time-col">
							<button type="button" class="opus-dp-spin" data-field="s" data-dir="1"><i class="bi bi-chevron-up"></i></button>
							<input type="text" class="opus-dp-s" value="00" maxlength="2">
							<button type="button" class="opus-dp-spin" data-field="s" data-dir="-1"><i class="bi bi-chevron-down"></i></button>
						</div>
					</div>
					<div class="opus-dp-actions">
						<button type="button" class="opus-dp-btn opus-dp-today"><?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.btn.today') ?></button>
						<button type="button" class="opus-dp-btn opus-dp-clear"><?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.btn.clear') ?></button>
					</div>
				</div>
			</div>`;

		$("body").append(html);
		OpusDatePicker.popup = $(".opus-dp");
		OpusDatePicker.bindEvents();
	}

	/**
	 * Sets the selected date on activeInput and closes the popup.
	 * In timestamp mode appends HH:mm:ss from time spinners.
	 *
	 * @param {Date} date - the selected Date object
	 * @static
	 */
	static setDate(date) {
		if (!OpusDatePicker.activeInput) return;

		const $dp = OpusDatePicker.popup;
		const y = date.getFullYear();
		const m = String(date.getMonth() + 1).padStart(2, "0");
		const d = String(date.getDate()).padStart(2, "0");

		let val = `${y}-${m}-${d}`;

		if ($dp.data("mode") === "timestamp") {
			const h = $dp.find(".opus-dp-h").val();
			const min = $dp.find(".opus-dp-m").val();
			const s = $dp.find(".opus-dp-s").val();
			val += ` ${h}:${min}:${s}`;
		}

		OpusDatePicker.activeInput.val(val).trigger("change");
		OpusDatePicker.close();
	}

	/**
	 * Syncs activeInput value in real-time when day is selected or time spinners change.
	 * Only updates if a day has been selected (either from popup state or parsed from input).
	 * @static
	 */
	static updateActiveInput() {
		if (!OpusDatePicker.activeInput) return;

		const $dp = OpusDatePicker.popup;
		const parsed = OpusDatePicker.parseValue(OpusDatePicker.activeInput.val());
		const day = $dp.data("day") ?? parsed?.day;
		if (!day) return;

		const y = $dp.data("year");
		const m = String($dp.data("month") + 1).padStart(2, "0");
		const d = String(day).padStart(2, "0");
		const h = $dp.find(".opus-dp-h").val();
		const min = $dp.find(".opus-dp-m").val();
		const s = $dp.find(".opus-dp-s").val();

		OpusDatePicker.activeInput.val(`${y}-${m}-${d} ${h}:${min}:${s}`).trigger("change");
	}

	/**
	 * Renders the day-grid calendar view for given year/month.
	 * Highlights today, marks active day, disables out-of-range days.
	 * Disables prev/next nav buttons when entire adjacent month is out of range.
	 *
	 * @param {number} year - full year (e.g. 2026)
	 * @param {number} month - zero-based month index (0-11)
	 * @static
	 */
	static renderCalendar(year, month) {
		const $body = OpusDatePicker.popup.find(".opus-dp-body");
		const $title = OpusDatePicker.popup.find(".opus-dp-title");
		const $dp = OpusDatePicker.popup;

		const firstDay = new Date(year, month, 1).getDay();
		const daysInMonth = new Date(year, month + 1, 0).getDate();
		const offset = firstDay === 0 ? 6 : firstDay - 1;

		const minDate = $dp.data("minDate");
		const maxDate = $dp.data("maxDate");

		const months = [
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.jan') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.feb') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.mar') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.apr') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.may') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.jun') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.jul') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.aug') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.sep') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.oct') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.nov') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.dec') ?>",
		];
		$title.text(`${months[month]} ${year}`);

		let html = '<table class="opus-dp-cal"><thead><tr>';
		const days = [
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.day.short.mon') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.day.short.tue') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.day.short.wed') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.day.short.thu') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.day.short.fri') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.day.short.sat') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.day.short.sun') ?>",
		];
		days.forEach((d) => (html += `<th>${d}</th>`));
		html += "</tr></thead><tbody><tr>";

		for (let i = 0; i < offset; i++) {
			html += "<td></td>";
		}

		const today = new Date();
		const isToday = (d) => today.getFullYear() === year && today.getMonth() === month && today.getDate() === d;
		const activeDay =
			$dp.data("day") && $dp.data("month") === month && $dp.data("year") === year ? $dp.data("day") : null;

		for (let d = 1; d <= daysInMonth; d++) {
			const current = new Date(year, month, d);
			const disabled = (minDate && current < minDate) || (maxDate && current > maxDate);
			const todayCls = isToday(d) ? " opus-dp-today-cell" : "";
			const activeCls = activeDay === d ? " active" : "";
			const disCls = disabled ? " disabled" : "";

			html += `<td class="${todayCls}">`;
			html += `<button type="button" class="opus-dp-day${activeCls}${disCls}" data-day="${d}"${disabled ? " disabled" : ""}>${d}</button>`;
			html += "</td>";

			if ((offset + d) % 7 === 0 && d < daysInMonth) {
				html += "</tr><tr>";
			}
		}

		html += "</tr></tbody></table>";
		$body.html(html);

		// Disable prev/next if entire month is out of range
		const $prev = $dp.find(".opus-dp-prev");
		const $next = $dp.find(".opus-dp-next");
		const prevLast = new Date(year, month, 0);
		const nextFirst = new Date(year, month + 1, 1);

		$prev.prop("disabled", !!(minDate && prevLast < minDate));
		$next.prop("disabled", !!(maxDate && nextFirst > maxDate));
	}

	/**
	 * Renders the 4-column month selection grid for given year.
	 *
	 * @param {number} year - full year
	 * @static
	 */
	static renderMonths(year) {
		const $body = OpusDatePicker.popup.find(".opus-dp-body");
		const $title = OpusDatePicker.popup.find(".opus-dp-title");

		$title.text(`${year}`);

		const months = [
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.short.jan') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.short.feb') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.short.mar') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.short.apr') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.short.may') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.short.jun') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.short.jul') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.short.aug') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.short.sep') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.short.oct') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.short.nov') ?>",
			"<?= Opus\controller\lang\Lang::getInstance()->get('opus.datepicker.month.short.dec') ?>",
		];
		let html = '<div class="opus-dp-grid">';

		months.forEach((m, i) => {
			html += `<button type="button" class="opus-dp-cell" data-month="${i}">${m}</button>`;
		});

		html += "</div>";
		$body.html(html);
	}

	/**
	 * Renders the decade year selection grid (10 years).
	 *
	 * @param {number} startYear - any year within the desired decade
	 * @static
	 */
	static renderYears(startYear) {
		const $body = OpusDatePicker.popup.find(".opus-dp-body");
		const $title = OpusDatePicker.popup.find(".opus-dp-title");
		const from = startYear - (startYear % 10);
		const to = from + 9;

		$title.text(`${from} - ${to}`);

		let html = '<div class="opus-dp-grid">';

		for (let y = from; y <= to; y++) {
			html += `<button type="button" class="opus-dp-cell" data-year="${y}">${y}</button>`;
		}

		html += "</div>";
		$body.html(html);
	}

	/**
	 * Binds all internal popup event handlers (navigation, selection, time spinners, masks).
	 * Called once during createPopup().
	 * @static
	 */
	static bindEvents() {
		const $dp = OpusDatePicker.popup;

		// Title click: calendar → months → years
		$dp.on("click", ".opus-dp-title", function () {
			const view = $dp.data("view") ?? "days";
			const year = $dp.data("year");

			if (view === "days") {
				$dp.data("view", "months");
				OpusDatePicker.renderMonths(year);
			} else if (view === "months") {
				$dp.data("view", "years");
				OpusDatePicker.renderYears(year);
			}
		});

		// Prev/Next navigation
		$dp.on("click", ".opus-dp-prev", function () {
			OpusDatePicker.navigate(-1);
		});

		$dp.on("click", ".opus-dp-next", function () {
			OpusDatePicker.navigate(1);
		});

		// Day selection
		$dp.on("click", ".opus-dp-day", function () {
			const day = $(this).data("day");
			const year = $dp.data("year");
			const month = $dp.data("month");

			if ($dp.data("mode") === "timestamp") {
				$dp.data("day", day);
				$dp.find(".opus-dp-day").removeClass("active");
				$(this).addClass("active");
				OpusDatePicker.updateActiveInput();
			} else {
				OpusDatePicker.setDate(new Date(year, month, day));
			}
		});

		// Month selection
		$dp.on("click", ".opus-dp-cell[data-month]", function () {
			const month = $(this).data("month");
			$dp.data("month", month);
			$dp.data("view", "days");
			OpusDatePicker.renderCalendar($dp.data("year"), month);
		});

		// Year selection
		$dp.on("click", ".opus-dp-cell[data-year]", function () {
			const year = $(this).data("year");
			$dp.data("year", year);
			$dp.data("view", "months");
			OpusDatePicker.renderMonths(year);
		});

		// Today button
		$dp.on("click", ".opus-dp-today", function () {
			OpusDatePicker.setDate(new Date());
		});

		// Clear button
		$dp.on("click", ".opus-dp-clear", function () {
			if (OpusDatePicker.activeInput) {
				OpusDatePicker.activeInput.val("");
			}
			OpusDatePicker.close();
		});

		// Time spinners
		$dp.on("click", ".opus-dp-spin", function () {
			const field = $(this).data("field");
			const dir = $(this).data("dir");
			const $field = $dp.find(".opus-dp-" + field);
			const max = field === "h" ? 23 : 59;
			let val = parseInt($field.val()) + dir;

			if (val < 0) val = max;
			if (val > max) val = 0;

			$field.val(String(val).padStart(2, "0"));
			OpusDatePicker.updateActiveInput();
		});

		// Time manual input - mask + validation
		$dp.find(".opus-dp-h").unmask().mask("00");
		$dp.find(".opus-dp-m").unmask().mask("00");
		$dp.find(".opus-dp-s").unmask().mask("00");

		$dp.on("blur", ".opus-dp-h, .opus-dp-m, .opus-dp-s", function () {
			const $field = $(this);
			const max = $field.hasClass("opus-dp-h") ? 23 : 59;
			let val = parseInt($field.val()) || 0;

			if (val < 0) val = 0;
			if (val > max) val = max;

			$field.val(String(val).padStart(2, "0"));
			OpusDatePicker.updateActiveInput();
		});
	}

	/**
	 * Navigates the current view by direction.
	 * Days view: prev/next month. Months view: prev/next year. Years view: prev/next decade.
	 *
	 * @param {number} dir - direction: -1 (prev) or 1 (next)
	 * @static
	 */
	static navigate(dir) {
		const $dp = OpusDatePicker.popup;
		const view = $dp.data("view") ?? "days";
		let year = $dp.data("year");
		let month = $dp.data("month");

		match: switch (view) {
			case "days":
				month += dir;
				if (month < 0) {
					month = 11;
					year--;
				}
				if (month > 11) {
					month = 0;
					year++;
				}
				$dp.data("year", year);
				$dp.data("month", month);
				OpusDatePicker.renderCalendar(year, month);
				break;
			case "months":
				year += dir;
				$dp.data("year", year);
				OpusDatePicker.renderMonths(year);
				break;
			case "years":
				year += dir * 10;
				$dp.data("year", year);
				OpusDatePicker.renderYears(year);
				break;
		}
	}

	/**
	 * Positions the popup below the input, flips above if insufficient viewport space.
	 *
	 * @param {jQuery} $input - the input element to position relative to
	 * @static
	 */
	static position($input) {
		const rect = $input[0].getBoundingClientRect();
		const popupH = OpusDatePicker.popup.outerHeight();
		const spaceBelow = window.innerHeight - rect.bottom;

		let top = rect.bottom + window.scrollY + 4;
		if (spaceBelow < popupH + 8) {
			top = rect.top + window.scrollY - popupH - 4;
		}

		OpusDatePicker.popup.css({
			top: top + "px",
			left: rect.left + window.scrollX + "px",
		});
	}

	/**
	 * Parses input value string into date/time components.
	 * Accepts formats: yyyy-MM-dd and yyyy-MM-dd HH:mm:ss.
	 *
	 * @param {string} val - input value
	 * @returns {{year: number, month: number, day: number, h: number, m: number, s: number}|null}
	 * @static
	 */
	static parseValue(val) {
		if (!val) return null;

		const match = val.match(/^(\d{4})-(\d{2})-(\d{2})(?:\s(\d{2}):(\d{2}):(\d{2}))?$/);
		if (!match) return null;

		return {
			year: parseInt(match[1]),
			month: parseInt(match[2]) - 1,
			day: parseInt(match[3]),
			h: parseInt(match[4] || 0),
			m: parseInt(match[5] || 0),
			s: parseInt(match[6] || 0),
		};
	}
}
