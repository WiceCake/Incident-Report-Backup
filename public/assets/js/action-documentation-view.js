function printDiv(divId, filename) {


    html2canvas(divId, { scale: 2 }).then(canvas => {
        var imgData = canvas.toDataURL('image/png');

        // Initialize jsPDF
        const { jsPDF } = window.jspdf;
        var pdf = new jsPDF();

        // Calculate the width and height of the PDF based on canvas size
        var imgWidth = pdf.internal.pageSize.getWidth() - 20; // Width in mm
        var pageHeight = 295; // Height in mm (A4)
        var imgHeight = (canvas.height * imgWidth) / canvas.width;
        var heightLeft = imgHeight;

        var position = 0;

        // Add the image to the PDF
        pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;

        // Check if there is more content to add
        while (heightLeft >= 0) {
            position = heightLeft - imgHeight;
            pdf.addPage();
            pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
        }

        // Save the PDF
        pdf.save(`action-documentation-${filename}.pdf`);
    });
}

// console.log()
incidentID = $('#reportID').val()

table = $('.invoice-list-table')
reloadTable = table.DataTable({
    ajax: '/api/v1/action_documentation/progress?incident_id=' + incidentID,
    columns: [
        { data: 'progress_id' },        // Column 0
        { data: 'log_description' },           // Column 1
        { data: 'method_used' },           // Column 2
        { data: 'time_issued' },  // Column 3
        { data: 'admin_name' },        // Column 4
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
        targets: 0, // Threat Level
        orderable: !1,
        render: function (a, e, t, s) {
            return '<a href="/incident-reports/' + t.progress_id + '">' + t.progress_id + "</a>";
        }
    },
    {
        targets: 1,
        render: function(t) {
            return '<span class="text-wrap text-break">'+ t +'<span>'
        }
    },
    {
        targets: 2,
        render: function(t) {
            return '<span class="text-wrap text-break">'+ t +'<span>'
        }
    },
    {
        targets: 3, // Date Detected
        render: function (a, e, t, s) {
            const date = moment(t.time_issued).tz('Asia/Manila').subtract(8, 'hours');

            return '<span class="d-none">' + date.format("YYYYMMDD") + "</span>" + date.format("MMM DD, YYYY h:mm:ss a");

        }
    }],
    order: [[3, "desc"]],
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
