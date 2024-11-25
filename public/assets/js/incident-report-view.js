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
        pdf.save(`incident-reports-${filename}.pdf`);
    });
}


function addInput($id){
    $($id).append(`<input type="text" class="form-control w-100 mb-3" name="actions[]" placeholder="Input action here..." required />`)
}
