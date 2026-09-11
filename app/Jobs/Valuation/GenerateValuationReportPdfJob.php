<?php

declare(strict_types=1);

namespace App\Jobs\Valuation;

use App\Enums\Valuation\ReportExportVariant;
use App\Models\ValuationRequest;
use App\Services\Valuation\ReportPdfCacheService;
use App\Services\Valuation\ValuationReportPdfGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

final class GenerateValuationReportPdfJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public int $timeout = 900;

    public function __construct(
        public readonly int $valuationRequestId,
        public readonly string $variant,
        public readonly int $userId,
        public readonly string $cacheKey,
    ) {
        $this->onQueue(config('report_pdf.queue_name', 'reports'));
    }

    public function handle(
        ValuationReportPdfGenerator $generator,
        ReportPdfCacheService $cache,
    ): void {
        try {
            Auth::onceUsingId($this->userId);

            $request = ValuationRequest::query()->findOrFail($this->valuationRequestId);
            $variant = ReportExportVariant::from($this->variant);

            $generator->generate($request, $variant, $this->cacheKey);
        } finally {
            $cache->clearGenerating($this->valuationRequestId, $this->cacheKey);
        }
    }

    public function failed(?Throwable $exception): void
    {
        app(ReportPdfCacheService::class)->clearGenerating($this->valuationRequestId, $this->cacheKey);

        Log::error('valuation report pdf generation failed', [
            'valuation_request_id' => $this->valuationRequestId,
            'variant' => $this->variant,
            'user_id' => $this->userId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
