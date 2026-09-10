<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

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

        return $request->refresh();
    }
}
