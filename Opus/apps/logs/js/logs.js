/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-08-12 16:07:41
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-08-15 12:50:30
 **/

const dtLogs = new OpusDataTable({
	app: "logs",
	event: "logs_dt",
	target: "<?= Opus\config\Config::getConfig('logs')->idTableEvent ?>",
});
OpusSelect.bindSelect(document.querySelector(dtLogs.table));

dtLogs.bindTable({
	drawByInputText: [1, 3, 4, 5],
	drawBySelectMenu: [2],
	hideColumns: [0],
	columnDefs: [
		{
			targets: [
				0, // id__logs
			],
			orderable: false,
		},
		{
			target: 1, // logtime
			type: "html",
			className: "dt-body-center align-middle",
			width: "130px",
			render: function (data, type, row) {
				const [date, time] = data.split(" ");
				const link = dtLogs.actionLink(row[0], date);
				return `${link}<br><small class="ps-2 font-monospace text-secondary">${time.split(".")[0]}</small>`;
			},
		},
		{
			target: 2, // logtype
			orderable: false,
			className: "dt-body-center align-middle",
			width: "130px",
		},
		{
			target: 3, // logpath
			orderable: false,
			className: "dt-body-padding-left align-middle",
			width: "16%",
		},
		{
			targets: [
				4, // logmessage
				5, // logdetails
			],
			orderable: false,
			className: "dt-body-padding-left align-middle",
			width: "35%",
		},
	],
});
