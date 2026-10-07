<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Models\Company;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class UploadCompanyBrandingAction
{
    public function __invoke(Company $company, string $type, UploadedFile $file): Company
    {
        $column = match ($type) {
            'logo' => 'main_logo_path',
            'signature' => 'signature_path',
            'stamp' => 'stamp_path',
            default => throw new \InvalidArgumentException('Invalid branding type.'),
        };

        $directory = "company/{$company->id}/{$type}";
        $path = $file->store($directory, 'public');

        $previous = $company->{$column};
        if (is_string($previous) && $previous !== '' && Storage::disk('public')->exists($previous)) {
            Storage::disk('public')->delete($previous);
        }

        $company->forceFill([$column => $path])->save();

        activity('Company')
            ->performedOn($company)
            ->withProperties(['action' => 'branding_uploaded', 'type' => $type, 'path' => $path])
            ->log('Company branding uploaded');

        return $company->refresh();
    }
}
