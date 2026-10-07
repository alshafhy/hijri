<?php

declare(strict_types=1);

namespace App\Actions\Contract;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class CreateContractAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $actor, array $data): Contract
    {
        Gate::forUser($actor)->authorize('create', Contract::class);

        $contract = Contract::query()->create([
            'contractor_id' => (int) $data['contractor_id'],
            'valuation_request_id' => (int) $data['valuation_request_id'],
            'state' => (int) ($data['state'] ?? Contract::STATE_UNPAID),
        ]);

        activity('Contract')
            ->performedOn($contract)
            ->causedBy($actor)
            ->withProperties(['action' => 'created'])
            ->log('Contract created');

        return $contract->fresh(['contractor', 'valuationRequest']) ?? $contract;
    }
}
