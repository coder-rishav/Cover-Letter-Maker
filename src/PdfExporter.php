<?php

declare(strict_types=1);

namespace CoverLetterMaker;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Renders sanitized letter HTML into an A4 PDF with a candidate header.
 */
final class PdfExporter
{
    public function render(string $letterHtml, string $name, string $contact): string
    {
        $safeHtml = Security::sanitizeLetterHtml($letterHtml);
        $safeName = Security::cleanLine($name, 80);
        $safeContact = Security::cleanLine($contact, 200);

        $header = '';
        if ($safeName !== '' || $safeContact !== '') {
            $header = '<header class="header">';
            if ($safeName !== '') {
                $header .= '<div class="candidate-name">' . Security::escape($safeName) . '</div>';
            }
            if ($safeContact !== '') {
                $header .= '<div class="contact">' . Security::escape($safeContact) . '</div>';
            }
            $header .= '</header>';
        }

        $document = '<!DOCTYPE html>'
            . '<html lang="en"><head><meta charset="utf-8"><title>Cover Letter</title><style>'
            . '@page { margin: 2.5cm; }'
            . 'html, body { margin: 0; padding: 0; }'
            . 'body { font-family: "DejaVu Serif", serif; font-size: 11.5pt; line-height: 1.62; color: #1f2937; }'
            . '.header { margin: 0 0 26px; padding-bottom: 12px; border-bottom: 1.2px solid #c7d2fe; }'
            . '.candidate-name { font-family: "DejaVu Sans", sans-serif; font-size: 20pt; font-weight: bold; color: #312e81; }'
            . '.contact { margin-top: 4px; font-family: "DejaVu Sans", sans-serif; font-size: 8.5pt; color: #475569; }'
            . '.letter p { margin: 0 0 10pt; text-align: left; }'
            . '.letter ul { margin: 0 0 10pt 16pt; padding: 0; }'
            . '.letter li { margin: 0 0 4pt; }'
            . '.letter em { font-style: italic; }'
            . '.letter strong { font-weight: bold; }'
            . '</style></head><body>'
            . $header
            . '<main class="letter">' . $safeHtml . '</main>'
            . '</body></html>';

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($document, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    public function filename(string $company, string $role): string
    {
        return 'Cover_Letter_' . Security::safeFilenamePart($company) . '_' . Security::safeFilenamePart($role) . '.pdf';
    }
}
