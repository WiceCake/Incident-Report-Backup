table = $('.invoice-list-table')
reloadTable = table.DataTable({
    ajax: 'http://incident-report.test:81/api/v1/threats/all',
    columns: [
        { data: 'threat_id' },        // Column 0
        { data: 'threat_level' },     // Column 1
        { data: 'threat' },           // Column 2
        { data: 'ip_address' },  // Column 3
        { data: 'timestamp' },        // Column 4
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
            return '<a href="/incident-reports/' + t.threat_id + '">' + t.threat_id + "</a>";
        }
    }, {
        targets: 1, // Threat Name
        render: function (a, e, t, s) {
            const level = t.threat_level;

            if (level === 'High') {
                return `<span class="badge bg-label-danger text-capitalized">${level}</span>`;
            }else if (level === 'Medium') {
                return `<span class="badge bg-label-warning text-capitalized">${level}</span>`;
            }
            return `<span class="badge bg-label-success text-capitalized">Low</span>`;
        }
    },
    {
        targets: 4, // Date Detected
        render: function (a, e, t, s) {
            const date = new Date(t.timestamp);
            return '<span class="d-none">' + moment(date).format("YYYYMMDD") + "</span>" + moment(date).format("MMM DD YYYY h:mm:ss a");
        }
    }, {
        targets: 5, // Action
        visible: true, // Make this visible
        orderable: false,
        render: function (a, e, t, s) {
            return '<div class="d-flex align-items-center"><a href="/security-events/' + t.threat_id + '" data-bs-toggle="tooltip" class="btn btn-icon" data-bs-placement="top" title="Preview Invoice"><i class="bx bx-show bx-md"></i></a></div>';
        }
    }],
    order: [[4, "desc"]],
    language: {
        sLengthMenu: "Show _MENU_",
        search: "",
        searchPlaceholder: "Search Events",
        paginate: {
            next: '<i class="bx bx-chevron-right bx-18px"></i>',
            previous: '<i class="bx bx-chevron-left bx-18px"></i>'
        }
    }
});

reloadTable.ajax.reload();
