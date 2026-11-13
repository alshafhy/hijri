<?php

declare(strict_types=1);

namespace App\Jobs\LegacyImport;

use App\Models\PropertyPicture;
use App\Support\Legacy\PropertyPictureVerifier;
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

    public function handle(PropertyPictureVerifier $verifier): void
    {
        $picture = PropertyPicture::query()->find($this->propertyPictureId);
        if ($picture === null) {
            return;
        }

        $result = $verifier->verifyAndPersist($picture);

        if ($result['found']) {
            return;
        }

        Log::info('legacy_import.picture_missing', [
            'property_picture_id' => $picture->id,
            'legacy_id' => $picture->legacy_id,
            'filename' => $picture->filename,
            'request_legacy_id' => $result['request_legacy_id'],
        ]);
    }
}
