<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationAttachment;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class UploadOfficialQimaReportAction
{
    public function execute(
        User $actor,
        ValuationRequest $request,
        UploadedFile $file,
        ?string $reportNumber = null,
        ?string $depositNumber = null,
    ): ValuationRequest {
        Gate::forUser($actor)->authorize('uploadOfficialReport', $request);

        $path = $file->store('official-reports/'.$request->id, 'local');
        if ($path === false) {
            throw new \RuntimeException('Unable to store uploaded file.');
        }

        try {
            DB::transaction(function () use ($actor, $request, $file, $path, $reportNumber, $depositNumber): void {
                $request->forceFill(array_filter([
                    'official_report_path' => $path,
                    'uploaded_on_qima' => true,
                    'qima_locked_at' => now(),
                    'number' => $reportNumber,
                    'deposit_number' => $depositNumber,
                ], static fn ($v): bool => $v !== null && $v !== ''))->save();

                ValuationAttachment::query()->create([
                    'valuation_request_id' => $request->id,
                    'type' => config('valuation_form.official_report_type'),
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'disk' => 'local',
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_by' => $actor->id,
                ]);

                ValuationActivity::log($actor, $request, 'official_report_uploaded', 'Official Qima report uploaded and locked', array_filter([
                    'report_number' => $reportNumber,
                    'deposit_number' => $depositNumber,
                ]));
            });
        } catch (Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        return $request->refresh();
    }
}
