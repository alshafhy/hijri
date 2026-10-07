<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\PropertyPicture;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\Gate;

final class DeletePropertyPictureAction
{
    public function execute(User $actor, ValuationRequest $request, PropertyPicture $picture): void
    {
        Gate::forUser($actor)->authorize('deleteAttachment', $request);

        abort_unless(
            $picture->property_id === $request->property?->id,
            404
        );

        $pictureId = $picture->id;
        $picture->delete();

        ValuationActivity::log($actor, $request, 'picture_deleted', 'Property picture deleted', [
            'picture_id' => $pictureId,
        ]);
    }
}
