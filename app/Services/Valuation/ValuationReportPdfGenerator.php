<?php

declare(strict_types=1);

namespace App\Services\Valuation;

use App\Contracts\ReportPdfRenderer;
use App\Enums\Valuation\ReportExportVariant;
use App\Models\ValuationRequest;
use App\Support\Pdf\ReportRenderOptions;
use Illuminate\Support\Facades\View;

final class ValuationReportPdfGenerator
{
    public function __construct(
        private readonly ValuationReportDataBuilder $dataBuilder = new ValuationReportDataBuilder,
        private readonly ReportPdfCacheService $cache = new ReportPdfCacheService,
        private readonly ?ReportPdfRenderer $renderer = null,
    ) {}

    /**
     * @return array{html: string, key: string, filename: string, data: array<string, mixed>}
     */
    public function prepare(ValuationRequest $request, ReportExportVariant $variant): array
    {
        $previousLocale = app()->getLocale();
        app()->setLocale($variant->locale() ?? $previousLocale);

        try {
            $data = $this->dataBuilder->build($request, $variant);
            $html = View::make($variant->bladeView(), $data)->render();
        } finally {
            app()->setLocale($previousLocale);
        }

        $key = $this->cache->key($html, [
            'variant' => $variant->value,
            'draft' => $variant->isDraft(),
            'watermark' => $variant->watermark(),
            'locale' => $variant->locale(),
            'css' => md5_file($this->cssPath()),
        ]);
        $filename = $variant->filename($data['reference'] ?? $request->id);

        return compact('html', 'key', 'filename', 'data');
    }

    public function renderBytes(string $html, ReportExportVariant $variant): string
    {
        $renderer = $this->renderer ?? app(ReportPdfRenderer::class);

        return $renderer->render($html, new ReportRenderOptions(
            direction: $variant->direction(),
            watermark: $variant->watermark(),
            css: (string) file_get_contents($this->cssPath()),
            paper: strtoupper((string) config('report_pdf.paper', 'A4')),
            orientation: config('report_pdf.orientation', 'portrait') === 'landscape' ? 'L' : 'P',
        ));
    }

    /**
     * Build (or reuse cache) and return absolute path + filename.
     *
     * @return array{path: string, filename: string, key: string, bytes: string}
     */
    public function generate(ValuationRequest $request, ReportExportVariant $variant, ?string $expectedKey = null): array
    {
        $prepared = $this->prepare($request, $variant);
        $key = $prepared['key'];

        if ($expectedKey !== null && $expectedKey !== $key) {
            // Content changed between queue and worker; still store under fresh key,
            // but keep writing the expected key so the waiting client can download.
            $key = $expectedKey;
        }

        if ($cached = $this->cache->get($request->id, $key)) {
            return [
                'path' => $cached,
                'filename' => $this->cache->filename($request->id, $key) ?: $prepared['filename'],
                'key' => $key,
                'bytes' => (string) file_get_contents($cached),
            ];
        }

        $bytes = $this->renderBytes($prepared['html'], $variant);
        $path = $this->cache->put($request->id, $key, $bytes, $prepared['filename'])
            ?? $this->writeTemp($request->id, $key, $bytes);

        return [
            'path' => $path,
            'filename' => $prepared['filename'],
            'key' => $key,
            'bytes' => $bytes,
        ];
    }

    private function cssPath(): string
    {
        return resource_path('css/pdf/report.css');
    }

    private function writeTemp(int $requestId, string $key, string $bytes): string
    {
        $dir = storage_path('app/private/report-pdf-cache/'.$requestId);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $path = $dir.'/'.$key.'.pdf';
        file_put_contents($path, $bytes);

        return $path;
    }
}
