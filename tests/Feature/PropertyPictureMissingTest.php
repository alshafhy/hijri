<?php

declare(strict_types=1);

use App\Models\Property;
use App\Models\PropertyPicture;
use App\Models\ValuationRequest;
use App\Support\Valuation\PropertyPictureMedia;
use App\Support\Valuation\ValuationReportPictureHtml;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('uses a placeholder when the picture file is missing', function () {
    $request = ValuationRequest::query()->create([
        'legacy_id' => 910001,
        'number' => 'T-1',
        'state' => 'waiting',
    ]);

    $property = Property::query()->create([
        'legacy_id' => 910001,
        'valuation_request_id' => $request->id,
        'customer_name' => 'Test',
    ]);

    $picture = PropertyPicture::query()->create([
        'legacy_id' => 910001,
        'property_id' => $property->id,
        'filename' => 'missing-on-disk.jpg',
        'description' => 'Kitchen',
        'file_exists' => false,
        'relative_path' => null,
        'sort_order' => 0,
    ]);

    $media = new PropertyPictureMedia;
    $display = $media->display($picture);

    expect($display['available'])->toBeFalse()
        ->and($display['src'])->toStartWith('data:image/svg+xml')
        ->and($media->isAvailable($picture))->toBeFalse()
        ->and($media->absolutePath($picture))->toBeNull();

    $report = $media->forPdfReport(collect([$picture]));
    expect($report['images'])->toBeEmpty()
        ->and($report['missing'])->toContain('Kitchen')
        ->and($report['notes'][0])->toContain('Kitchen');

    $html = (new ValuationReportPictureHtml($media))->render($property->fresh(['pictures']));
    expect($html)->toContain('Kitchen')
        ->and($html)->not->toContain('missing-on-disk.jpg"')
        ->and($html)->not->toContain('src="/images/requests');
});
