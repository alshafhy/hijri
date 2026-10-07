<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Models\Company;

final class UpdateCompanySlaAction
{
    /** @param array<string, mixed> $data */
    public function __invoke(Company $company, array $data): Company
    {
        $company->fill([
            'request_sla_hours' => (int) $data['request_sla_hours'],
            'evaluation_sla_hours' => (int) $data['evaluation_sla_hours'],
        ])->save();

        return $company->refresh();
    }
}
