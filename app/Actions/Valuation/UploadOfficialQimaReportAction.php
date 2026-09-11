<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;

final class UploadOfficialQimaReportAction
{
    public function execute(User $actor, ValuationRequest $request, UploadedFile $file): ValuationRequest
    {
        Gate::forUser($actor)->authorize('uploadOfficialReport', $request);

        $path = $file->store('official-reports/'.$request->id, 'local');

        $request->forceFill([
            'official_report_path' => $path,
            'uploaded_on_qima' => true,
            'qima_locked_at' => now(),
        ])->save();

        ValuationActivity::log($actor, $request, 'official_report_uploaded', 'Official Qima report uploaded and locked');

        return $request->refresh();
    }
}
