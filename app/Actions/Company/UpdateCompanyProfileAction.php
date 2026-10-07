<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Models\Company;

final class UpdateCompanyProfileAction
{
    /** @param array<string, mixed> $data */
    public function __invoke(Company $company, array $data): Company
    {
        $company->fill([
            'name' => $data['name'],
            'name_en' => $data['name_en'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'address' => $data['address'] ?? null,
            'company_membership_number' => $data['company_membership_number'] ?? null,
            'company_reg_number' => $data['company_reg_number'] ?? null,
        ])->save();

        activity('Company')
            ->performedOn($company)
            ->withProperties(['action' => 'profile_updated'])
            ->log('Company profile updated');

        return $company->refresh();
    }
}
