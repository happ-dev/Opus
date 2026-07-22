/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-24 16:23:34
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-22 18:17:12
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
							2, //  2 lastname
							3, //  3 active
							5, //  5 dept
							6, //  6 position
							8, //  8 hire_date
							9, //  9 salary
							10, // 10 granted
							11, // 11 reason
							12, // 12 amount
							13, // 13 percent
							14, // 14 total
							15, // 15 pay_date
						],
						className: "align-middle",
						orderable: false,
					},
					{
						target: 2, //  2 lastname
						type: "html",
						className: "align-middle",
						render: function (data, type, row) {
							return odtObj.actionLink(row[0], data);
						},
					},
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
				},
				onHide: () => {
					odtObj.dt.ajax.reload();
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
