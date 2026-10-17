<?php

declare(strict_types=1);

namespace App\Actions\Pdf;

use App\Contracts\ReportPdfRenderer;
use App\Support\Pdf\PdfStylesheet;
use App\Support\Pdf\ReportRenderOptions;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\HeaderUtils;

final class RenderDocumentPdfAction
{
    public function __construct(private readonly ReportPdfRenderer $renderer) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(string $view, array $data, string $filename, bool $download): Response
    {
        $bytes = $this->renderer->render(view($view, $data)->render(), new ReportRenderOptions(
            direction: 'rtl',
            css: PdfStylesheet::read(resource_path('css/pdf/documents.css')),
        ));

        $disposition = $download ? HeaderUtils::DISPOSITION_ATTACHMENT : HeaderUtils::DISPOSITION_INLINE;

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => HeaderUtils::makeDisposition($disposition, $filename),
        ]);
    }
}
