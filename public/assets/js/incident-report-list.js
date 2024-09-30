table = $('.invoice-list-table')
reloadTable = table.DataTable({
    ajax: 'http://incident-report.test:81/api/v1/incident/reports',
    columns: [
        { data: 'incident_id' },        // Column 0
        { data: 'threat_type' },     // Column 1
        { data: 'threat_name' },           // Column 2
        { data: 'time_issued' },  // Column 3
        { data: 'admin_name' },        // Column 4
        { data: 'status' },            // Column 5
        { data: 'action' }            // Column 6
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
            return '<a href="/incident-reports/' + t.incident_id + '">' + t.incident_id + "</a>";
        }
    },

    {
        targets: 5, // Threat Name
        render: function (a, e, t, s) {
            const level = t.status;

            if (level === 'Processing') {
                return `<span class="badge bg-label-warning text-capitalized">${level}</span>`;
            }

            return `<span class="badge bg-label-success text-capitalized">Completed</span>`;
        }
    },
    {
        targets: 6, // Action
        visible: true, // Make this visible
        orderable: false,
        render: function (a, e, t, s) {
            return '<div class="d-flex align-items-center"><a href="/incident-reports/' + t.threat_id + '" data-bs-toggle="tooltip" class="btn btn-icon" data-bs-placement="top" title="Preview Invoice"><i class="bx bx-show bx-md"></i></a></div>';
        }
    }],
    order: [[4, "desc"]],
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
