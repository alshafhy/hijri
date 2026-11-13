<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Pdf\ReportImage;
use Spatie\Activitylog\Models\Activity;

/**
 * Evaluator / coordinator / final approver signature blocks for valuation reports.
 */
final class SignaturesSection
{
    private const DISK = 'local';

    /**
     * @return array{evaluator: ?array{name: string, membership_number: ?string, membership_category: ?string, membership_branch_type: ?string, signature: ?string}, coordinator: ?array{name: string, membership_number: ?string, membership_category: ?string, membership_branch_type: ?string, signature: ?string}, approver: ?array{name: string, membership_number: ?string, membership_category: ?string, membership_branch_type: ?string, signature: ?string}}
     */
    public function build(ValuationRequest $request): array
    {
        return [
            'evaluator' => $this->block($request->evaluator),
            'coordinator' => $this->block($request->coordinator),
            'approver' => $this->block($this->approver($request)),
        ];
    }

    private function approver(ValuationRequest $request): ?User
    {
        $approverId = Activity::query()
            ->where('log_name', 'valuation')
            ->where('subject_type', $request->getMorphClass())
            ->where('subject_id', $request->id)
            ->where('event', 'approved')
            ->latest('id')
            ->value('causer_id') ?? $request->sub_user_id;

        return $approverId === null ? null : User::query()->find($approverId);
    }

    /**
     * @return ?array{name: string, membership_number: ?string, membership_category: ?string, membership_branch_type: ?string, signature: ?string}
     */
    private function block(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        return [
            'name' => (string) $user->name,
            'membership_number' => $this->text($user, 'membership_number'),
            'membership_category' => $this->text($user, 'membership_category'),
            'membership_branch_type' => $this->text($user, 'membership_branch_type'),
            'signature' => $this->signature($user),
        ];
    }

    private function signature(User $user): ?string
    {
        $path = $user->getAttribute('signature_path');

        return is_string($path) ? ReportImage::fromDisk(self::DISK, $path) : null;
    }

    private function text(User $user, string $column): ?string
    {
        $value = trim((string) $user->getAttribute($column));

        return $value === '' ? null : $value;
    }
}
