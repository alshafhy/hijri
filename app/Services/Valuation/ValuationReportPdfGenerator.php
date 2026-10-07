<?php

declare(strict_types=1);

namespace App\Services\Valuation;

use App\Enums\Valuation\ReportExportVariant;
use App\Models\ValuationRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

final class ValuationReportPdfGenerator
{
    public function __construct(
        private readonly ValuationReportDataBuilder $dataBuilder = new ValuationReportDataBuilder,
        private readonly ReportPdfCacheService $cache = new ReportPdfCacheService,
    ) {}

    /**
     * @return array{html: string, key: string, filename: string, data: array<string, mixed>}
     */
    public function prepare(ValuationRequest $request, ReportExportVariant $variant): array
    {
        $data = $this->dataBuilder->build($request, $variant);
        $html = View::make($variant->bladeView(), $data)->render();
        $key = $this->cache->key($html, [
            'variant' => $variant->value,
            'draft' => $variant->isDraft(),
            'watermark' => $variant->watermark(),
        ]);
        $filename = $variant->filename($data['reference'] ?? $request->id);

        return compact('html', 'key', 'filename', 'data');
    }

    public function renderBytes(string $html): string
    {
        $pdf = Pdf::loadHTML($html)
            ->setPaper(config('report_pdf.paper', 'a4'), config('report_pdf.orientation', 'portrait'))
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->output();
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

        $bytes = $this->renderBytes($prepared['html']);
        $path = $this->cache->put($request->id, $key, $bytes, $prepared['filename'])
            ?? $this->writeTemp($request->id, $key, $bytes);

        return [
            'path' => $path,
            'filename' => $prepared['filename'],
            'key' => $key,
            'bytes' => $bytes,
        ];
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
