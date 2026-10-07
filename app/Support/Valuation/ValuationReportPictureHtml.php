<?php

declare(strict_types=1);

namespace App\Support\Valuation;

use App\Models\Property;
use Illuminate\Support\Facades\View;

/**
 * Builds HTML fragments for PDF reports without failing on missing images.
 */
final class ValuationReportPictureHtml
{
    public function __construct(
        private readonly PropertyPictureMedia $media = new PropertyPictureMedia,
    ) {}

    public function render(Property $property): string
    {
        $property->loadMissing('pictures');
        $reportImages = $this->media->forPdfReport($property->pictures);

        return View::make('layouts.partials.report_property_pictures', [
            'reportImages' => $reportImages,
        ])->render();
    }
}
