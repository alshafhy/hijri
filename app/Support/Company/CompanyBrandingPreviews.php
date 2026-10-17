<?php

declare(strict_types=1);

namespace App\Support\Company;

use App\Models\Company;
use App\Support\Pdf\ReportImage;

/**
 * Data-URI previews of the uploaded company branding images, keyed by upload type.
 */
final class CompanyBrandingPreviews
{
    private const DISK = 'public';

    private const COLUMNS = [
        'logo' => 'main_logo_path',
        'report_logo' => 'report_logo_path',
        'signature' => 'signature_path',
        'stamp' => 'stamp_path',
    ];

    /**
     * @return array<string, ?string>
     */
    public function build(Company $company): array
    {
        $previews = [];
        foreach (self::COLUMNS as $type => $column) {
            $path = $company->getAttribute($column);
            $previews[$type] = is_string($path) ? ReportImage::fromDisk(self::DISK, $path) : null;
        }

        return $previews;
    }
}
