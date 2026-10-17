<?php

declare(strict_types=1);

use App\Models\ValuationAttachment;
use App\Support\Valuation\Report\AttachmentImagesSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function attachmentImage(int $requestId, string $type, string $mime = 'image/jpeg', ?string $name = 'extra.jpg'): ValuationAttachment
{
    $file = UploadedFile::fake()->image('extra.jpg', 50, 50);

    return ValuationAttachment::query()->create([
        'valuation_request_id' => $requestId,
        'type' => $type,
        'original_name' => $name,
        'path' => $file->store("valuation-attachments/{$requestId}", 'local'),
        'disk' => 'local',
        'mime_type' => $mime,
        'size' => $file->getSize(),
    ]);
}

it('embeds image attachments and excludes the pdf attachment', function () {
    $images = (new AttachmentImagesSection)->build(reportFixture());

    expect($images)->toHaveCount(1)
        ->and($images[0]['label'])->toBe('صورة للعقار')
        ->and($images[0]['src'])->toStartWith('data:image/jpeg;base64,');
});

it('skips image attachments whose file is missing', function () {
    $request = reportFixture();
    $attachment = $request->attachments()->where('mime_type', 'image/jpeg')->firstOrFail();
    Storage::disk('local')->delete($attachment->path);

    expect((new AttachmentImagesSection)->build($request->refresh()))->toBe([]);
});

it('excludes the official qima report attachment', function () {
    $request = reportFixture();
    attachmentImage($request->id, (string) config('valuation_form.official_report_type'));

    $images = (new AttachmentImagesSection)->build($request->refresh());

    expect(array_column($images, 'label'))->toBe(['صورة للعقار']);
});

it('falls back to the file extension when the mime type is missing and to the file name as label', function () {
    $request = reportFixture();
    attachmentImage($request->id, '', '', 'extra.jpg');

    $images = (new AttachmentImagesSection)->build($request->refresh());

    expect(array_column($images, 'label'))->toBe(['صورة للعقار', 'extra.jpg']);
});

it('skips webp attachments that cannot be decoded', function () {
    $request = reportFixture();
    attachmentImage($request->id, 'صورة إضافية', 'image/webp');

    expect(array_column((new AttachmentImagesSection)->build($request->refresh()), 'label'))->toBe(['صورة للعقار']);
});

it('converts webp images to png data uris', function () {
    if (! function_exists('imagewebp') || ! (gd_info()['WebP Support'] ?? false)) {
        $this->markTestSkipped('GD has no WebP support.');
    }

    $request = reportFixture();
    $image = imagecreatetruecolor(10, 10);
    ob_start();
    imagewebp($image);
    $path = "valuation-attachments/{$request->id}/photo.webp";
    Storage::disk('local')->put($path, (string) ob_get_clean());
    ValuationAttachment::query()->create([
        'valuation_request_id' => $request->id, 'type' => 'صورة إضافية', 'original_name' => 'photo.webp',
        'path' => $path, 'disk' => 'local', 'mime_type' => 'image/webp', 'size' => 1,
    ]);

    $images = (new AttachmentImagesSection)->build($request->refresh());

    expect($images[1]['src'])->toStartWith('data:image/png;base64,');
});
