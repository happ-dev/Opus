/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-24 16:23:34
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-08-15 21:41:56
 **/

// #region objDynamicModal
// Object for opus-demo-dynamic-modal created in PHP DemoDynamicModal class
const objDynamicModal = new OpusModal({
	name: "opus-demo-dynamic-modal",
	data: { app: "demo", event: "demoDynamicModal" },
});

objDynamicModal.bindAjaxModal({
	onRender: () => {
		highlightCode(objDynamicModal.el);
	},
});
// #endregion objDynamicModal

// #region apage:closed
document.addEventListener("apage:closed", () => {
	const container = document.querySelector(".async-page-opus");
	if (container) OpusSelect.destroy(container);
});
// #endregion apage:closed

// #region apage:loaded
document.addEventListener("apage:loaded", (e) => {
	switch (e.detail.event) {
		case "demoOffcanvas":
			highlightCode(document.querySelector(".async-page-opus"));

			// #region objDynamicOffcanvas
			const objDynamicOffcanvas = new OpusOffcanvas({
				name: "opus-demo-dynamic-offcanvas",
				data: { app: "demo", event: "demoDynamicOffcanvas" },
			});
			objDynamicOffcanvas.bindAjaxOffcanvas();
			// #endregion objDynamicOffcanvas
			break;

		case "demoCollapse":
			highlightCode(document.querySelector(".async-page-opus"));

			// #region objDynamicCollapse
			const objDynamicCollapse = new OpusCollapse({
				name: "opus-demo-dynamic-collapse",
				data: { app: "demo", event: "demoDynamicCollapse" },
			});
			objDynamicCollapse.bindAjaxCollapse();
			// #endregion objDynamicCollapse
			break;

		case "demoButtons":
			highlightCode(document.querySelector(".async-page-opus"));
			ogl.tableCSS("#id_opus-demo-buttons-options-tab-info");
			break;

		case "demoDatePicker":
			// #region OpusDatePicker
			highlightCode(document.querySelector(".async-page-opus"));
			ogl.tableCSS("#id_opus-demo-datepicker-options-tab-info");
			OpusDatePicker.bindDate(document.querySelector(".async-page-opus"));
			// #endregion OpusDatePicker
			break;

		case "demoSelect":
			// #region OpusSelect
			highlightCode(document.querySelector(".async-page-opus"));
			ogl.tableCSS("#id_opus-demo-select-options-tab-info");
			OpusSelect.bindSelect(document.querySelector(".async-page-opus"));
			// #endregion OpusSelect
			break;

		case "demoTable": {
			// #region objTable
			highlightCode(document.querySelector(".async-page-opus"));
			const odtObj = new OpusDataTable({
				app: "demo",
				event: "apTableDemo_dt",
				target: "<?= Opus\config\Config::getConfig('demo')->idTableEvent ?>",
			});

			odtObj.bindTable({
				columnDefs: [
					{
						targets: [
							0, //  0 id__payroll
							4, //  4 dept_id
							7, //  7 contract
						],
						visible: false,
						searchable: false,
						orderable: false,
					},
					{
						targets: [
							1, //  1 firstname
							6, //  6 position
							13, // 13 percent
						],
						visible: false,
						orderable: false,
					},
					{
						target: 2, //  2 lastname
						type: "html",
						className: "align-middle dt-body-right pe-3",
						render: function (data, type, row) {
							const link = odtObj.actionLink(row[0], data);
							const firstname = `<br><small class="font-monospace text-secondary">${row[1]}</small>`;
							return link + firstname;
						},
					},
					{
						targets: [
							3, //  3 active
							10, // 10 granted
						],
						type: "html",
						className: "dt-body-center align-middle",
						orderable: false,
						width: "100px",
						render: function (data, type, row) {
							return data === true
								? "<?= Opus\controller\lang\Lang::getInstance()->get('event.message.true') ?>"
								: "<?= Opus\controller\lang\Lang::getInstance()->get('event.message.false') ?>";
						},
					},
					{
						target: 5, //  5 dept
						orderable: false,
						type: "html",
						width: "14%",
						className: "align-middle dt-body-right pe-3",
						render: function (data, type, row) {
							return `${data}<br><small class="font-monospace text-secondary">${row[6]}</small>`;
						},
					},
					{
						targets: [
							8, //  8 hire_date
							15, // 15 pay_date
						],
						type: "html",
						className: "align-middle dt-body-center",
						width: "130px",
						render: function (data, type, row) {
							return data ? data.split(" ")[0] : "";
						},
					},
					{
						targets: 11, // 11 reason
						className: "align-middle",
						orderable: false,
					},
					{
						targets: [12, 13, 14],
						visible: false,
						searchable: false,
						orderable: false,
					},
					{
						target: 9, //  9 salary (+ amount, percent, total)
						type: "html",
						orderable: false,
						width: "210px",
						className: "align-middle",
						render: function (data, type, row) {
							const line = (label, val, bold) =>
								`<div class="d-flex justify-content-between"><small class="text-secondary">${label}</small><span class="table-opus-mask-fiat${bold ? " fw-semibold" : ""}">${val}</span></div>`;
							let html = line(
								"<?= Opus\controller\lang\Lang::getInstance()->get('demo.table.db.bonuses.total') ?>",
								row[14] || data,
								true,
							);
							html += line(
								"<?= Opus\controller\lang\Lang::getInstance()->get('demo.table.db.payroll.salary') ?>",
								data,
								false,
							);
							if (row[12])
								html += line(
									"<?= Opus\controller\lang\Lang::getInstance()->get('demo.table.db.bonuses.amount') ?>",
									row[12],
									false,
								);
							if (row[13])
								html += line(
									"<?= Opus\controller\lang\Lang::getInstance()->get('demo.table.db.bonuses.percent') ?>",
									row[13] + "%",
									false,
								);
							return html;
						},
					},
				],
				fnDrawCallback: function () {
					this.find(".table-opus-mask-fiat").unmask().mask("# ##0.00", ogl.standardMask());
				},
				drawBySelectMenu: [3, 10],
				drawByInputText: [8, 15],
				lengthMenu: [
					[10, 25, 100],
					[10, 25, 100],
				],
			});

			const objBonusesEvent = new OpusModal({
				name: "opus-demo-bonuses-table-edit-modal",
				data: { app: "demo", event: "demoTableBonuses" },
			});

			objBonusesEvent.bindAjaxModal({
				relatedTarget: (event) => {
					let id = $(event.relatedTarget).attr("data-id");
					objBonusesEvent.setData({ id: id, strategy: "add", request: "new-bonus" });
				},
				onRender: () => {
					$(".form-control-opus-mask-fiat").mask("# ##0.00", ogl.standardMask());
					OpusDatePicker.bindDate(objBonusesEvent.el);
					OpusSelect.bindSelect(objBonusesEvent.el);
				},
				onHide: () => {
					odtObj.dt.ajax.reload();
					OpusSelect.destroy(objBonusesEvent.el);
				},
				onSave: () => {
					objBonusesEvent.bindPostModal({
						json: true,
						buildUrl: () => {
							const url = new URLSearchParams();
							url.append("app", objBonusesEvent.data.app);
							url.append("event", objBonusesEvent.data.event);
							url.append("request", "award-bonus");
							return url;
						},
						buildData: (form) => {
							// Build data object for POST request
							const postData = {};
							const $formContainer = $(objBonusesEvent.formId);

							// Add form field values to the data object
							$formContainer.find('[id^="id_input_"], input[type=checkbox]:checked').each(function () {
								const name = $(this).attr("name");
								const value = $(this).val();

								if (value) {
									postData[name] = value;
								}
							});

							return postData;
						},
					});
				},
			});

			document.addEventListener(
				"apage:closed",
				() => {
					odtObj.destroyTable();
				},
				{ once: true },
			);
			// #endregion objTable
			break;
		}
	}
});
// #endregion apage:loaded
