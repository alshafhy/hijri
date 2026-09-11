<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Valuation\QueueValuationReportExportAction;
use App\Enums\Valuation\ReportExportVariant;
use App\Http\Controllers\Controller;
use App\Models\ValuationRequest;
use App\Services\Valuation\ReportPdfCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ValuationReportExportController extends Controller
{
    public function queue(
        Request $httpRequest,
        ValuationRequest $valuationRequest,
        string $variant,
        QueueValuationReportExportAction $action,
    ): JsonResponse|View|Response {
        $exportVariant = $this->resolveVariant($variant);
        $this->authorize('exportPdf', $valuationRequest);

        $result = $action->execute($httpRequest->user(), $valuationRequest, $exportVariant);

        if ($httpRequest->expectsJson() || $httpRequest->ajax()) {
            return response()->json($result);
        }

        if ($result['ready']) {
            return redirect()->to($result['download_url']);
        }

        return view('valuation_requests.exports.waiting', [
            'request' => $valuationRequest,
            'variant' => $exportVariant,
            'result' => $result,
        ]);
    }

    public function status(
        ValuationRequest $valuationRequest,
        string $variant,
        string $key,
        ReportPdfCacheService $cache,
    ): JsonResponse {
        $this->authorize('exportPdf', $valuationRequest);
        $this->resolveVariant($variant);

        return response()->json([
            'ready' => (bool) $cache->get($valuationRequest->id, $key),
            'generating' => $cache->isGenerating($valuationRequest->id, $key),
        ]);
    }

    public function download(
        Request $httpRequest,
        ValuationRequest $valuationRequest,
        string $variant,
        string $key,
        ReportPdfCacheService $cache,
    ): Response {
        $exportVariant = $this->resolveVariant($variant);
        $this->authorize('exportPdf', $valuationRequest);

        $path = $cache->get($valuationRequest->id, $key);
        abort_if($path === null, 404);

        $filename = $cache->filename($valuationRequest->id, $key)
            ?: $exportVariant->filename($valuationRequest->reference ?? $valuationRequest->id);

        activity('valuation_report')
            ->performedOn($valuationRequest)
            ->causedBy($httpRequest->user())
            ->withProperties([
                'variant' => $exportVariant->value,
                'key' => $key,
                'event' => 'downloaded',
            ])
            ->log('valuation report export downloaded');

        return $cache->downloadResponse((string) file_get_contents($path), $filename);
    }

    private function resolveVariant(string $variant): ReportExportVariant
    {
        $resolved = ReportExportVariant::tryFrom($variant);
        abort_if($resolved === null, 404);

        return $resolved;
    }
}
