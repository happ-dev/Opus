/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-05-24 16:23:34
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-07-05 19:04:17
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
	}
});
// #endregion apage:loaded
