<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PropertyPicture;
use App\Models\ValuationRequest;
use App\Support\Valuation\PropertyPictureMedia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PropertyPictureController extends Controller
{
    public function file(PropertyPicture $propertyPicture, PropertyPictureMedia $media): BinaryFileResponse
    {
        $propertyPicture->loadMissing('property.valuationRequest');
        $request = $propertyPicture->property?->valuationRequest;

        if ($request instanceof ValuationRequest) {
            $this->authorize('view', $request);
        } else {
            $this->authorize('viewAny', ValuationRequest::class);
        }

        $path = $media->absolutePath($propertyPicture);
        abort_if($path === null, 404);

        return response()->file($path);
    }
}
