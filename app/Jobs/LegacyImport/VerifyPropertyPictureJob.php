<?php

declare(strict_types=1);

namespace App\Jobs\LegacyImport;

use App\Models\PropertyPicture;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class VerifyPropertyPictureJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly int $propertyPictureId) {}

    public function handle(): void
    {
        $picture = PropertyPicture::query()->find($this->propertyPictureId);
        if ($picture === null) {
            return;
        }

        $filename = $picture->filename;
        $paths = config('legacy_import.picture_search_paths', []);
        if (! is_array($paths) || $paths === []) {
            $picture->forceFill([
                'file_exists' => false,
                'relative_path' => null,
            ])->save();

            Log::info('legacy_import.picture_missing', [
                'property_picture_id' => $picture->id,
                'legacy_id' => $picture->legacy_id,
                'filename' => $filename,
                'reason' => 'no_search_paths_configured',
            ]);

            return;
        }

        $picture->loadMissing('property.valuationRequest');
        $requestLegacyId = $picture->property?->valuationRequest?->legacy_id;

        $foundRelative = null;
        foreach ($paths as $base) {
            $base = rtrim((string) $base, '/');
            if ($base === '') {
                continue;
            }

            $candidates = [];
            if ($requestLegacyId) {
                $candidates[] = $base.'/'.$requestLegacyId.'/'.$filename;
            }
            $candidates[] = $base.'/'.$filename;

            foreach ($candidates as $absolute) {
                // Existence check only — never load file contents into memory.
                if (is_file($absolute)) {
                    $foundRelative = ltrim(str_replace($base, '', $absolute), '/');
                    break 2;
                }
            }
        }

        if ($foundRelative !== null) {
            $picture->forceFill([
                'file_exists' => true,
                'relative_path' => $foundRelative,
            ])->save();

            return;
        }

        $picture->forceFill([
            'file_exists' => false,
            'relative_path' => null,
        ])->save();

        Log::info('legacy_import.picture_missing', [
            'property_picture_id' => $picture->id,
            'legacy_id' => $picture->legacy_id,
            'filename' => $filename,
            'request_legacy_id' => $requestLegacyId,
        ]);
    }
}
