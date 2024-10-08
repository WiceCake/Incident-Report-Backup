table = $('.invoice-list-table')
reloadTable = table.DataTable({
    ajax: '/api/v1/logs/user',
    columns: [
        { data: 'report_id' },        // Column 0
        { data: 'event' },           // Column 1
        { data: 'admin_name' },        // Column 2
        { data: 'timestamp' },        // Column 3
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
        targets: 3, // Date Detected
        render: function (a, e, t, s) {
            const date = moment(t.timestamp).tz('Asia/Manila').subtract(8, 'hours');

            return '<span class="d-none">' + date.format("YYYYMMDD") + "</span>" + date.format("MMM DD, YYYY h:mm:ss a");

        }
    },
    {
        targets: 1,
        render: function(t) {
            return '<span class="text-wrap text-break">'+ t +'<span>'
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

setInterval(function(){
    reloadTable.ajax.reload();
}, 30000)
