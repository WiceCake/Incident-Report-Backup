const options = {
    timeZone: 'Asia/Manila',
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: 'numeric',
    minute: '2-digit',
    second: '2-digit',
    hour12: true
};

table = $('.invoice-list-table')
reloadTable = table.DataTable({
    ajax: '/api/v1/completed/reports',
    columns: [
        { data: 'complete_report_id' },        // Column 0
        { data: 'incident_title' },           // Column 2
        { data: 'time_issued' },  // Column 3
        { data: 'admin_name' },        // Column 4
        { data: 'action' }            // Column 5
    ],
    processing: true,
    columnDefs: [{
        className: "control",
        responsivePriority: 2,
        searchable: true,
        targets: !1, // ID
        defaultContent: '-',
        render: function (a, e, t, s) {
            return ""; // Add your content here if necessary
        }
    }, {
        targets: 0, // Threat Level
        orderable: !1,
        render: function (a, e, t, s) {
            return '<a href="/incident-reports/' + t.complete_report_id	 + '">' + t.complete_report_id + "</a>";
        }
    },
    {
        targets: 2, // Date Detected
        render: function (a, e, t, s) {
            const date = moment(t.time_issued).tz('Asia/Manila').subtract(8, 'hours');

            return '<span class="d-none">' + date.format("YYYYMMDD") + "</span>" + date.format("MMM DD, YYYY h:mm:ss a");

        }
    },
    {
        targets: 4, // Action
        visible: true, // Make this visible
        orderable: false,
        render: function (a, e, t, s) {
            return '<div class="d-flex align-items-center"><a href="/completed-reports/' + t.complete_report_id + '" data-bs-toggle="tooltip" class="btn btn-icon" data-bs-placement="top" title="Preview Invoice"><i class="bx bx-show bx-md"></i></a></div>';
        }
    }],
    order: [[2, "desc"]],
    language: {
        sLengthMenu: "Show _MENU_",
        search: "",
        searchPlaceholder: "Search Threat",
        paginate: {
            next: '<i class="bx bx-chevron-right bx-18px"></i>',
            previous: '<i class="bx bx-chevron-left bx-18px"></i>'
        }
    }
});


reloadTable.ajax.reload();

setInterval(function () {
    reloadTable.ajax.reload();
}, 30000)
