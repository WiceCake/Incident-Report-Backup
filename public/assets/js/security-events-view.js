function printDiv(divId, filename) {


    html2canvas(divId).then(canvas => {
        var imgData = canvas.toDataURL('image/png');

        // Initialize jsPDF
        const { jsPDF } = window.jspdf;
        var pdf = new jsPDF();

        // Calculate the width and height of the PDF based on canvas size
        var imgWidth = 190; // Width in mm
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
        pdf.save(`security-events-${filename}.pdf`);
    });
}

function changeTime(){
    incidentFormCurrentTime = new Date()
    $('#timestampIssueDisplay').val(`${moment(incidentFormCurrentTime).format('MMM DD, YYYY hh:mm:ss a')}`);

    // $('#timestampIssueDisplay').val(`${moment(incidentFormCurrentTime).format('YYYY-MM-DDTHH:mm:ss.SSS[Z]')}`);
    $('#timestampIssue').val(`${moment(incidentFormCurrentTime).format('YYYY-MM-DDTHH:mm:ss.SSS[Z]')}`);
}

