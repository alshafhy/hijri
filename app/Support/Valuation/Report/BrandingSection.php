<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

use App\Models\Company;
use App\Support\Pdf\ReportImage;
use App\Support\Valuation\Report\Cards\Card;
use Carbon\Exceptions\InvalidFormatException;
use DateTimeInterface;

/**
 * Company branding (images as data URIs + identity texts) for valuation reports.
 */
final class BrandingSection
{
    private const DISK = 'public';

    /**
     * @return array{logo: ?string, report_logo: ?string, stamp: ?string, name: string, address: ?string, membership_number: ?string, reg_number: ?string, membership_date: ?string, accreditation_end_date: ?string}
     */
    public function build(Company $company): array
    {
        $logo = $this->image($company, 'main_logo_path');

        return [
            'logo' => $logo,
            'report_logo' => $this->image($company, 'report_logo_path') ?? $logo,
            'stamp' => $this->image($company, 'stamp_path'),
            'name' => (string) $company->name,
            'address' => $this->text($company, 'address'),
            'membership_number' => $this->text($company, 'company_membership_number'),
            'reg_number' => $this->text($company, 'company_reg_number'),
            'membership_date' => $this->date($company, 'membership_date'),
            'accreditation_end_date' => $this->date($company, 'accreditation_end_date'),
        ];
    }

    /**
     * Legacy page footer line (name - department: address - membership: number - reg: number); empty values drop their part.
     *
     * @param  ?array{name: string, address: ?string, membership_number: ?string, reg_number: ?string}  $branding
     */
    public function footer(?array $branding): ?string
    {
        if ($branding === null) {
            return null;
        }

        return Card::join([
            $branding['name'],
            Card::join([Card::label('Evaluation and Studies Department'), $branding['address']], ': '),
            $branding['membership_number'] === null ? null : Card::label('Membership of the Saudi Authority for Residents - accredited').': '.$branding['membership_number'],
            $branding['reg_number'] === null ? null : Card::label('company reg number').': '.$branding['reg_number'],
        ], ' - ');
    }

    private function image(Company $company, string $column): ?string
    {
        $path = $company->getAttribute($column);

        return is_string($path) ? ReportImage::fromDisk(self::DISK, $path) : null;
    }

    private function text(Company $company, string $column): ?string
    {
        $value = $company->getAttribute($column);
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function date(Company $company, string $column): ?string
    {
        try {
            $value = $company->getAttribute($column);
        } catch (InvalidFormatException) {
            return null;
        }

        return $value instanceof DateTimeInterface ? $value->format('Y-m-d') : null;
    }
}
