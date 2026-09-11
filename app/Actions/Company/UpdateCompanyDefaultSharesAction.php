<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Models\Company;

final class UpdateCompanyDefaultSharesAction
{
    /** @param array<string, mixed> $data */
    public function __invoke(Company $company, array $data): Company
    {
        $company->fill([
            'default_coordinator_share' => $data['default_coordinator_share'],
            'default_evaluator_share' => $data['default_evaluator_share'],
            'default_manager_share' => $data['default_manager_share'],
        ])->save();

        activity('Company')
            ->performedOn($company)
            ->withProperties([
                'action' => 'shares_updated',
                'shares' => [
                    'coordinator' => $data['default_coordinator_share'],
                    'evaluator' => $data['default_evaluator_share'],
                    'manager' => $data['default_manager_share'],
                ],
            ])
            ->log('Company default fee shares updated');

        return $company->refresh();
    }
}
