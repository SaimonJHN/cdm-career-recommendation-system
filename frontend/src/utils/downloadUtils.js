import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';

export const downloadCertificatePDF = async (certificateData) => {
  try {
    const element = document.getElementById('certificate-template');
    
    if (!element) {
      throw new Error('Certificate element not found');
    }

    const canvas = await html2canvas(element, {
      scale: 2,
      useCORS: true,
      backgroundColor: '#ffffff',
    });

    const imgData = canvas.toDataURL('image/png');
    const pdf = new jsPDF({
      orientation: 'landscape',
      unit: 'mm',
      format: 'a4',
    });

    const imgWidth = 297; // A4 landscape width in mm
    const imgHeight = (canvas.height * imgWidth) / canvas.width;

    pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);
    pdf.save(`CDM-Certificate-${certificateData.student_number}.pdf`);

    return true;
  } catch (error) {
    console.error('Error downloading certificate:', error);
    throw error;
  }
};

export const downloadExamResultsImage = async (resultsElement) => {
  try {
    const canvas = await html2canvas(resultsElement, {
      scale: 2,
      useCORS: true,
      backgroundColor: '#ffffff',
    });

    const link = document.createElement('a');
    link.href = canvas.toDataURL('image/png');
    link.download = `CDM-Exam-Results-${new Date().toISOString().split('T')[0]}.png`;
    link.click();

    return true;
  } catch (error) {
    console.error('Error downloading image:', error);
    throw error;
  }
};
