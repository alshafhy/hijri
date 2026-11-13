<?php

declare(strict_types=1);

namespace App\Support\Valuation;

use App\Enums\Valuation\RequestState;
use App\Models\User;
use App\Models\ValuationRequest;

/**
 * Detail page toolbar visibility: policy ability AND the workflow stage matrix (SYSTEM_SPEC §16.3).
 */
final class ValuationToolbar
{
    public const STAGE_PENDING = 'pending';

    public const STAGE_UNDER_EVALUATION = 'under_evaluation';

    public const STAGE_EVALUATED = 'evaluated';

    public const STAGE_APPROVED = 'approved';

    public const STAGE_CANCELLED = 'cancelled';

    /** @var array<string, array{0: string, 1: list<string>}> action => [policy ability, stages] */
    private const MATRIX = [
        'editInitialData' => ['editInitialData', [self::STAGE_PENDING, self::STAGE_UNDER_EVALUATION, self::STAGE_EVALUATED]],
        'editEvaluatorData' => ['update', [self::STAGE_PENDING, self::STAGE_UNDER_EVALUATION, self::STAGE_EVALUATED]],
        'cancel' => ['cancel', [self::STAGE_PENDING]],
        'feeShares' => ['manageFeeShares', [self::STAGE_PENDING, self::STAGE_UNDER_EVALUATION]],
        'changeEvaluator' => ['changeEvaluator', [self::STAGE_PENDING, self::STAGE_UNDER_EVALUATION]],
        'changePropertyType' => ['changePropertyType', [self::STAGE_PENDING, self::STAGE_UNDER_EVALUATION]],
        'changeCoordinator' => ['changeCoordinator', [self::STAGE_PENDING, self::STAGE_UNDER_EVALUATION]],
        'manualAmount' => ['overrideAmount', [self::STAGE_PENDING, self::STAGE_UNDER_EVALUATION]],
        'send' => ['send', [self::STAGE_PENDING]],
        'takeOver' => ['markUnderEvaluation', [self::STAGE_PENDING]],
        'markEvaluated' => ['markEvaluated', [self::STAGE_UNDER_EVALUATION]],
        'export' => ['exportPdf', [self::STAGE_UNDER_EVALUATION, self::STAGE_EVALUATED, self::STAGE_APPROVED]],
        'approve' => ['approveFinal', [self::STAGE_EVALUATED]],
        'reject' => ['reject', [self::STAGE_EVALUATED]],
        'unapprove' => ['unapprove', [self::STAGE_APPROVED]],
        'officialReport' => ['uploadOfficialReport', [self::STAGE_PENDING, self::STAGE_APPROVED]],
        'duplicate' => ['duplicate', [self::STAGE_APPROVED]],
        'qimaStatus' => ['toggleQimaStatus', [self::STAGE_PENDING, self::STAGE_UNDER_EVALUATION, self::STAGE_EVALUATED, self::STAGE_APPROVED]],
        'downloadAttachments' => ['downloadAttachments', [self::STAGE_PENDING, self::STAGE_UNDER_EVALUATION, self::STAGE_EVALUATED, self::STAGE_APPROVED]],
    ];

    public static function stage(ValuationRequest $request): string
    {
        return match (RequestState::tryFromLegacy($request->state)) {
            RequestState::UnderEvaluation => self::STAGE_UNDER_EVALUATION,
            RequestState::Evaluative, RequestState::Evaluated => self::STAGE_EVALUATED,
            RequestState::Approve => self::STAGE_APPROVED,
            RequestState::Delete, RequestState::Cancelled => self::STAGE_CANCELLED,
            default => self::STAGE_PENDING,
        };
    }

    /**
     * @return array<string, bool>
     */
    public static function for(User $user, ValuationRequest $request): array
    {
        $stage = self::stage($request);
        $actions = [];
        foreach (self::MATRIX as $action => [$ability, $stages]) {
            $actions[$action] = in_array($stage, $stages, true) && $user->can($ability, $request);
        }
        if ($request->uploaded_on_qima) {
            $actions['officialReport'] = false;
        }

        return $actions;
    }
}
