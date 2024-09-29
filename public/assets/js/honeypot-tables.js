table = $('.invoice-list-table')
table.DataTable({
    ajax: 'http://incident-report.test:81/api/v1/logs/honeypot',
    columns: [
        { data: 'threat_id' },        // Column 0
        { data: 'threat' },           // Column 1
        { data: 'timestamp' },        // Column 2
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
    },
    {
        targets: 1,
        render: function(t) {
            return '<span class="text-wrap text-break">'+ t +'<span>'
        }
    },
    {
        targets: 2, // Date Detected
        render: function (a, e, t, s) {
            const date = new Date(t.timestamp);
            return '<span class="d-none">' + moment(date).format("YYYYMMDD") + "</span>" + moment(date).format("MMM DD YYYY h:mm:ss a");
        }
    },],
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
