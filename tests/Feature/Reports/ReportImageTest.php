<?php

declare(strict_types=1);

use App\Support\Pdf\ReportImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('turns an image into a data uri and ignores missing or non-image files', function () {
    Storage::fake('public');
    $path = UploadedFile::fake()->image('logo.png', 20, 20)->store('company/1/logo', 'public');
    Storage::disk('public')->put('company/1/notes.txt', 'hello');

    expect(ReportImage::fromDisk('public', $path))->toStartWith('data:image/png;base64,')
        ->and(ReportImage::fromDisk('public', 'company/1/missing.png'))->toBeNull()
        ->and(ReportImage::fromDisk('public', 'company/1/notes.txt'))->toBeNull()
        ->and(ReportImage::fromDisk('public', null))->toBeNull();
});

it('returns null for null, missing and empty absolute paths', function () {
    $empty = tempnam(sys_get_temp_dir(), 'ri');

    expect(ReportImage::fromPath(null))->toBeNull()
        ->and(ReportImage::fromPath('/nonexistent/file.png'))->toBeNull()
        ->and(ReportImage::fromPath((string) $empty))->toBeNull();

    @unlink((string) $empty);
});
