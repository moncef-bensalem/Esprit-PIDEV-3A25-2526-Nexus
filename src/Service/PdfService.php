<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    /**
     * Génère un PDF à partir d'un contenu HTML et retourne le contenu binaire.
     *
     * @param string $html     Contenu HTML à convertir
     * @param string $format   Format de page : 'A4', 'A4 landscape', etc.
     */
    public function generateFromHtml(string $html, string $format = 'A4'): string
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', str_contains($format, 'landscape') ? 'landscape' : 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
