<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Enums\Valuation\ReportExportVariant;
use App\Jobs\Valuation\GenerateValuationReportPdfJob;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Services\Valuation\ReportPdfCacheService;
use App\Services\Valuation\ValuationReportPdfGenerator;
use Illuminate\Support\Facades\Gate;

final class QueueValuationReportExportAction
{
    public function __construct(
        private readonly ValuationReportPdfGenerator $generator = new ValuationReportPdfGenerator,
        private readonly ReportPdfCacheService $cache = new ReportPdfCacheService,
    ) {}

    /**
     * @return array{
     *     ready: bool,
     *     generating: bool,
     *     key: string,
     *     filename: string,
     *     status_url: string,
     *     download_url: string,
     *     queued: bool
     * }
     */
    public function execute(User $actor, ValuationRequest $request, ReportExportVariant $variant): array
    {
        Gate::forUser($actor)->authorize('exportPdf', $request);

        $prepared = $this->generator->prepare($request, $variant);
        $key = $prepared['key'];
        $filename = $prepared['filename'];

        $statusUrl = route('dashboard.valuation-requests.exports.status', [
            'valuationRequest' => $request,
            'variant' => $variant->value,
            'key' => $key,
        ]);
        $downloadUrl = route('dashboard.valuation-requests.exports.download', [
            'valuationRequest' => $request,
            'variant' => $variant->value,
            'key' => $key,
        ]);

        if ($this->cache->get($request->id, $key)) {
            activity('valuation_report')
                ->performedOn($request)
                ->causedBy($actor)
                ->withProperties([
                    'variant' => $variant->value,
                    'key' => $key,
                    'event' => 'cache_hit',
                ])
                ->log('valuation report export ready from cache');

            return [
                'ready' => true,
                'generating' => false,
                'key' => $key,
                'filename' => $filename,
                'status_url' => $statusUrl,
                'download_url' => $downloadUrl,
                'queued' => false,
            ];
        }

        $shouldQueue = (bool) config('report_pdf.queue', true)
            && config('queue.default') !== 'sync';

        if ($shouldQueue) {
            if (! $this->cache->isGenerating($request->id, $key)) {
                $this->cache->markGenerating($request->id, $key);
                GenerateValuationReportPdfJob::dispatch(
                    $request->id,
                    $variant->value,
                    $actor->id,
                    $key,
                );
            }

            activity('valuation_report')
                ->performedOn($request)
                ->causedBy($actor)
                ->withProperties([
                    'variant' => $variant->value,
                    'key' => $key,
                    'event' => 'queued',
                ])
                ->log('valuation report export queued');

            return [
                'ready' => false,
                'generating' => true,
                'key' => $key,
                'filename' => $filename,
                'status_url' => $statusUrl,
                'download_url' => $downloadUrl,
                'queued' => true,
            ];
        }

        // Sync / debug: build immediately.
        $this->cache->markGenerating($request->id, $key);
        try {
            $this->generator->generate($request, $variant, $key);
        } finally {
            $this->cache->clearGenerating($request->id, $key);
        }

        activity('valuation_report')
            ->performedOn($request)
            ->causedBy($actor)
            ->withProperties([
                'variant' => $variant->value,
                'key' => $key,
                'event' => 'generated_sync',
            ])
            ->log('valuation report export generated');

        return [
            'ready' => true,
            'generating' => false,
            'key' => $key,
            'filename' => $filename,
            'status_url' => $statusUrl,
            'download_url' => $downloadUrl,
            'queued' => false,
        ];
    }
}
