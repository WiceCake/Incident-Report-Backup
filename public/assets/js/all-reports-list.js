

table = $('.invoice-list-table').DataTable({
    ajax: '/api/v1/reports/list',
    columns: [
        { data: 'id' },             // Column 0
        { data: 'report_type' },    // Column 1
        { data: 'threat_name' },    // Column 2
        { data: 'admin_name' },     // Column 3
        { data: 'timestamp' },      // Column 4
        { data: 'status' }          // Column 5
    ],
    processing: true,
    columnDefs: [
        {
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
            targets: 0, // Threat Level
            orderable: !1,
            render: function (a, e, t, s) {
                return '<a href="/incident-reports/' + t.id + '">' + t.id + "</a>";
            }
        },
        {
            targets: 4, // Date Detected
            render: function (a, e, t, s) {
                const date = moment(t.timestamp).tz('Asia/Manila').subtract(8, 'hours');

                return '<span class="d-none">' + "</span>" + date.format("MMM DD, YYYY h:mm:ss a");

            }
        },
        {
            targets: 5, // Threat Name
            render: function (a, e, t, s) {
                const level = t.status;
                let badgeClass = 'bg-label-success';

                if (level === 'Under Review') badgeClass = 'bg-label-secondary';
                else if (level === 'Approved') badgeClass = 'bg-label-warning';
                else if (level === 'In Progress') badgeClass = 'bg-label-primary';
                else if (level === 'Pending Approval') badgeClass = 'bg-label-info';

                return `<span class="badge ${badgeClass} text-capitalized">${level}</span>`;

            }
        },
    ],
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

$('#startDate, #endDate').on('change', function () {
    console.log($('#endDate').val()); // Check if it logs the value
    table.draw();
});


$.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
    const minDate = $('#startDate').val() ? new Date($('#startDate').val()).getTime() : null;
    const maxDate = $('#endDate').val() ? new Date($('#endDate').val()).getTime() : null;
    const timestamp = new Date(data[4]).getTime();
    const reportType = data[1];
    const selectedType = $('#reportType').val();
    const status = data[5];
    const selectedStatus = $('#status').val();

    // Only show entries that match selected report type and are within the date range
    if (
        (minDate === null || timestamp >= minDate) &&
        (maxDate === null || timestamp <= maxDate) &&
        (selectedType === "" || reportType === selectedType) &&
        (selectedStatus === "" || status === selectedStatus)
    ) {
        return true;
    }

    return false;
});

$('#startDate, #endDate, #reportType, #status').on('change', function() {
    table.draw();
});

table.ajax.reload();

