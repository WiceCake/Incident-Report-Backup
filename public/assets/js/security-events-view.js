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
    $('#timestampIssue').val(`${moment(incidentFormCurrentTime).format('YYYY-MM-DDTHH:mm:ss')}Z`);
}

$(document).ready(function() {
    threatTime = $('#threatTime').val()

    threatDataDate = new Date(threatTime)

    // Security Events Display Date
    $('#dateDataDay').html(moment(threatDataDate).format("MMM DD YYYY"))
    $('#dateDataTime').html(moment(threatDataDate).format("h:mm:ss"))

    // Incident Form
    incidentFormDetectedTime = new Date(threatTime)
    $('#timestampDetected').val(`${moment(threatDataDate).format('YYYY-MM-DDTHH:mm:ss')}Z`);

});
