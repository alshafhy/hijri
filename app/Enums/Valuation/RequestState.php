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
    case Request = 'request';
    case UnderEvaluation = 'underEvaluative';
    case Evaluative = 'evaluative';
    case Evaluated = 'تم التقييم';
    case Approve = 'approve';
    case Cancelled = 'cancelled';
    case Delete = 'delete';

    public function label(): string
    {
        return match ($this) {
            self::Waiting => __('enums.request_state.waiting'),
            self::Waiting2 => __('enums.request_state.waiting2'),
            self::Request => __('enums.request_state.request'),
            self::UnderEvaluation => __('enums.request_state.under_evaluation'),
            self::Evaluative => __('enums.request_state.evaluative'),
            self::Evaluated => __('enums.request_state.evaluated'),
            self::Approve => __('enums.request_state.approve'),
            self::Cancelled => __('enums.request_state.cancelled'),
            self::Delete => __('enums.request_state.delete'),
        };
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Approve, self::Delete, self::Cancelled => true,
            default => false,
        };
    }

    public static function tryFromLegacy(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        $trimmed = trim($value);
        $lower = mb_strtolower($trimmed);

        return match ($lower) {
            'waiting' => self::Waiting,
            'waiting2' => self::Waiting2,
            'request' => self::Request,
            'underevaluative', 'under_evaluation', 'under evaluation' => self::UnderEvaluation,
            'evaluative' => self::Evaluative,
            'approve' => self::Approve,
            'cancelled', 'canceled' => self::Cancelled,
            'delete' => self::Delete,
            default => match ($trimmed) {
                'تحت التقييم' => self::UnderEvaluation,
                'تم التقييم' => self::Evaluated,
                'تم الاعتماد', 'تم الإعتماد' => self::Approve,
                default => self::tryFrom($trimmed),
            },
        };
    }

    public static function labelFor(?string $value): string
    {
        $mapped = self::tryFromLegacy($value);

        if ($mapped instanceof self) {
            return $mapped->label();
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : __('Unknown');
    }

    /**
     * @return array<string, string> value => label
     */
    public static function filterOptions(): array
    {
        $options = [];
        foreach ([
            self::Waiting,
            self::Waiting2,
            self::Request,
            self::UnderEvaluation,
            self::Evaluative,
            self::Evaluated,
            self::Approve,
            self::Cancelled,
            self::Delete,
        ] as $case) {
            $options[$case->value] = $case->label();
        }

        // Arabic aliases also present in filters for imported/action-written rows
        $options['تحت التقييم'] = self::UnderEvaluation->label();
        $options['تم الاعتماد'] = self::Approve->label();

        return $options;
    }
}
