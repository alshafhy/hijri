<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\ValuationRequest;

class ValuationRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('valuation_request.view');
    }

    public function view(User $user, ValuationRequest $request): bool
    {
        if (! $user->can('valuation_request.view')) {
            return false;
        }

        if ($user->hasAnyRole(['super-admin', 'admin', 'manager'])) {
            return true;
        }

        return $this->isAssigned($user, $request);
    }

    public function create(User $user): bool
    {
        return $user->can('valuation_request.create');
    }

    public function update(User $user, ValuationRequest $request): bool
    {
        if ($request->isQimaLocked() || $request->isFinallyApproved()) {
            return false;
        }

        if (! $user->can('valuation_request.edit')) {
            return false;
        }

        if ($user->hasAnyRole(['super-admin', 'admin', 'manager', 'coordinator'])) {
            return true;
        }

        return $this->isAssignedEvaluator($user, $request);
    }

    public function approveFinal(User $user, ValuationRequest $request): bool
    {
        if ($request->isQimaLocked()) {
            return false;
        }

        return $user->can('valuation_request.approve_final');
    }

    public function unapprove(User $user, ValuationRequest $request): bool
    {
        if ($request->isQimaLocked()) {
            return false;
        }

        // Manager-only unapprove (was UI-only before)
        return $user->can('valuation_request.unapprove')
            && $user->hasAnyRole(['super-admin', 'admin', 'manager']);
    }

    public function markEvaluated(User $user, ValuationRequest $request): bool
    {
        if ($request->isQimaLocked() || $request->isFinallyApproved()) {
            return false;
        }

        if (! $user->can('valuation_request.mark_evaluated')) {
            return false;
        }

        return $this->isAssignedEvaluator($user, $request)
            || $user->hasAnyRole(['super-admin', 'admin', 'manager']);
    }

    public function uploadOfficialReport(User $user, ValuationRequest $request): bool
    {
        return $user->can('valuation_request.qima_upload')
            && $user->hasAnyRole(['super-admin', 'admin', 'manager', 'coordinator']);
    }

    public function lockAfterQima(User $user, ValuationRequest $request): bool
    {
        return $user->can('valuation_request.qima_lock')
            && $user->hasAnyRole(['super-admin', 'admin', 'manager']);
    }

    private function isAssigned(User $user, ValuationRequest $request): bool
    {
        return in_array($user->id, array_filter([
            $request->coordinator_user_id,
            $request->evaluator_user_id,
            $request->sub_user_id,
            $request->fellow_user_id,
        ]), true);
    }

    private function isAssignedEvaluator(User $user, ValuationRequest $request): bool
    {
        return $request->evaluator_user_id === $user->id;
    }
}
