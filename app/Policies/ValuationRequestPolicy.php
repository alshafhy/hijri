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

    public function send(User $user, ValuationRequest $request): bool
    {
        if ($request->isQimaLocked() || $request->isFinallyApproved()) {
            return false;
        }

        return $user->can('valuation_request.send')
            && ($user->hasAnyRole(['super-admin', 'admin', 'manager', 'coordinator'])
                || $this->isAssigned($user, $request));
    }

    public function markUnderEvaluation(User $user, ValuationRequest $request): bool
    {
        return $this->send($user, $request);
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
        // Managers may unapprove even after Qima lock — action clears the lock.
        return $user->can('valuation_request.unapprove')
            && $user->hasAnyRole(['super-admin', 'admin', 'manager']);
    }

    public function reject(User $user, ValuationRequest $request): bool
    {
        if ($request->isQimaLocked() || $request->isFinallyApproved()) {
            return false;
        }

        return $user->can('valuation_request.reject')
            && $user->hasAnyRole(['super-admin', 'admin', 'manager', 'coordinator']);
    }

    public function cancel(User $user, ValuationRequest $request): bool
    {
        if ($request->isQimaLocked()) {
            return false;
        }

        return $user->can('valuation_request.cancel')
            && $user->hasAnyRole(['super-admin', 'admin', 'manager', 'coordinator']);
    }

    public function restore(User $user, ValuationRequest $request): bool
    {
        return $user->can('valuation_request.view_deleted')
            && $user->hasAnyRole(['super-admin', 'admin', 'manager']);
    }

    public function viewDeleted(User $user): bool
    {
        return $user->can('valuation_request.view_deleted');
    }

    public function duplicate(User $user, ValuationRequest $request): bool
    {
        return $user->can('valuation_request.duplicate')
            && $user->hasAnyRole(['super-admin', 'admin', 'manager', 'coordinator']);
    }

    public function changeEvaluator(User $user, ValuationRequest $request): bool
    {
        if ($request->isQimaLocked() || $request->isFinallyApproved()) {
            return false;
        }

        return $user->can('valuation_request.change_evaluator')
            && $user->hasAnyRole(['super-admin', 'admin', 'manager', 'coordinator']);
    }

    public function changeCoordinator(User $user, ValuationRequest $request): bool
    {
        if ($request->isQimaLocked() || $request->isFinallyApproved()) {
            return false;
        }

        return $user->can('valuation_request.change_coordinator')
            && $user->hasAnyRole(['super-admin', 'admin', 'manager']);
    }

    public function changePropertyType(User $user, ValuationRequest $request): bool
    {
        if ($request->isQimaLocked() || $request->isFinallyApproved()) {
            return false;
        }

        return $user->can('valuation_request.change_property_type')
            && $user->hasAnyRole(['super-admin', 'admin', 'manager', 'coordinator']);
    }

    public function overrideAmount(User $user, ValuationRequest $request): bool
    {
        if ($request->isQimaLocked()) {
            return false;
        }

        return $user->can('valuation_request.override_amount')
            && $user->hasAnyRole(['super-admin', 'admin', 'manager']);
    }

    public function manageFeeShares(User $user, ValuationRequest $request): bool
    {
        if ($request->isQimaLocked()) {
            return false;
        }

        return $user->can('valuation_request.manage_fee_shares')
            && $user->hasAnyRole(['super-admin', 'admin', 'manager', 'coordinator']);
    }

    public function advancedSearch(User $user): bool
    {
        return $user->can('valuation_request.advanced_search');
    }

    public function viewLogs(User $user): bool
    {
        return $user->can('valuation_request.view_logs');
    }

    public function toggleQimaStatus(User $user, ValuationRequest $request): bool
    {
        return $user->can('valuation_request.qima_upload')
            && $user->hasAnyRole(['super-admin', 'admin', 'manager', 'coordinator']);
    }

    public function downloadAttachments(User $user, ValuationRequest $request): bool
    {
        return $this->view($user, $request);
    }

    public function deleteAttachment(User $user, ValuationRequest $request): bool
    {
        return $this->update($user, $request);
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

    public function exportPdf(User $user, ValuationRequest $request): bool
    {
        return $user->can('valuation_request.export_pdf')
            && $this->view($user, $request);
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
