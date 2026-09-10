<?php

declare(strict_types=1);

namespace App\Enums\Valuation;

/**
 * Known valuation request workflow states from legacy `request.state`.
 * Column remains a free string; use these cases/constants where mapped.
 */
enum RequestState: string
{
    case Waiting = 'waiting';
    case Waiting2 = 'waiting2';
    case UnderEvaluation = 'underEvaluative';
    case Approve = 'approve';
    case Delete = 'delete';

    public function label(): string
    {
        return match ($this) {
            self::Waiting => __('enums.request_state.waiting'),
            self::Waiting2 => __('enums.request_state.waiting2'),
            self::UnderEvaluation => __('enums.request_state.under_evaluation'),
            self::Approve => __('enums.request_state.approve'),
            self::Delete => __('enums.request_state.delete'),
        };
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Approve, self::Delete => true,
            default => false,
        };
    }

    public static function tryFromLegacy(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom($value);
    }
}
