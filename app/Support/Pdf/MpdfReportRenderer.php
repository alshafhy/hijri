<?php

declare(strict_types=1);

namespace App\Support\Pdf;

use App\Contracts\ReportPdfRenderer;
use Illuminate\Support\Facades\File;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

final class MpdfReportRenderer implements ReportPdfRenderer
{
    public function render(string $html, ReportRenderOptions $options): string
    {
        $tempDir = (string) config('report_pdf.temp_dir');
        File::ensureDirectoryExists($tempDir);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => $options->paper,
            'orientation' => $options->orientation,
            'tempDir' => $tempDir,
            'fontDir' => [...(new ConfigVariables)->getDefaults()['fontDir'], (string) config('report_pdf.font_dir')],
            'fontdata' => (new FontVariables)->getDefaults()['fontdata'] + (array) config('report_pdf.fonts'),
            'default_font' => (string) config('report_pdf.default_font'),
            'directionality' => $options->direction,
            'autoScriptToLang' => false,
            'autoLangToFont' => false,
            'margin_top' => 10, 'margin_bottom' => 10, 'margin_left' => 10, 'margin_right' => 10,
            'margin_header' => 0, 'margin_footer' => 0,
        ]);
        $mpdf->SetBasePath(public_path().'/');

        if ($options->watermark !== null) {
            $mpdf->SetWatermarkText($options->watermark, (float) config('report_pdf.watermark_alpha'));
            $mpdf->showWatermarkText = true;
            $mpdf->watermark_font = 'dejavusans';
        }

        if ($options->css !== '') {
            $mpdf->WriteHTML($options->css, HTMLParserMode::HEADER_CSS);
        }
        $this->writeBody($mpdf, $html);

        return $mpdf->Output('', Destination::STRING_RETURN);
    }

    /**
     * mPDF rejects HTML longer than pcre.backtrack_limit; reports embed base64 images well past the 1M default.
     */
    private function writeBody(Mpdf $mpdf, string $html): void
    {
        $previous = (string) ini_get('pcre.backtrack_limit');
        ini_set('pcre.backtrack_limit', (string) max((int) $previous, strlen($html) * 2));

        try {
            $mpdf->WriteHTML($html, HTMLParserMode::DEFAULT_MODE);
        } finally {
            ini_set('pcre.backtrack_limit', $previous);
        }
    }
}
