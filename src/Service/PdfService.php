<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Part;
use Symfony\Component\HttpFoundation\Response;

class PdfService
{
    public function printLabel($html): Void
    {
        $options = new Options();
        // $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Courier');
        $options->setDefaultMediaType('print');
        $options->setDefaultPaperOrientation('portrait');

        $this->domPdf = new Dompdf($options);

        $customPaper = array(0, 0, 150, 100);
        $this->domPdf->setPaper($customPaper);

        // Load HTML to Dompdf
        $this->domPdf->loadHtml($html);

        // Render the HTML as PDF
        $this->domPdf->render();
        
        // Output the generated PDF to Browser (force download)
        $this->domPdf->stream();
    }

    public function showPdfFile($html)
    {
        // Use the dompdf class
        $this->domPdf->loadHtml($html);

        // // (Optional) Setup the paper size and orientation
        // $this->domPdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $this->domPdf->render();
       
        // Output the generated PDF to Browser
        $this->domPdf->stream();

    }
}
