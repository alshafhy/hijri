<?php

declare(strict_types=1);

namespace App\Actions\Contract;

use App\Models\Contract;
use Illuminate\Support\Facades\Cache;

final class MarkContractPaidAction
{
    public function __invoke(Contract $contract): Contract
    {
        $contract->forceFill(['state' => Contract::STATE_PAID])->save();

        activity('Contract')
            ->performedOn($contract)
            ->withProperties(['action' => 'mark_paid'])
            ->log('Contract bill marked paid');

        Cache::forget('financial_board_summary:all');
        $month = $contract->valuationRequest?->started_at?->format('Y-m');
        if ($month) {
            Cache::forget('financial_board_summary:'.$month);
        }

        return $contract->refresh();
    }
}
