<?php

declare(strict_types=1);

namespace App\Enums\Valuation;

enum ReportExportVariant: string
{
    case Enforcement = 'enforcement';
    case Full = 'full';
    case FullDraft = 'full-draft';
    case FullEnglish = 'full-en';

    public function label(): string
    {
        return match ($this) {
            self::Enforcement => __('Enforcement Center report'),
            self::Full => __('Full report'),
            self::FullDraft => __('Full report draft'),
            self::FullEnglish => __('English report'),
        };
    }

    /**
     * Locale the report is rendered in; null keeps the current application locale.
     */
    public function locale(): ?string
    {
        return $this === self::FullEnglish ? 'en' : null;
    }

    public function direction(): string
    {
        return $this === self::FullEnglish ? 'ltr' : 'rtl';
    }

    public function bladeView(): string
    {
        return match ($this) {
            self::Enforcement => 'pdf.valuation.enforcement',
            self::Full, self::FullDraft, self::FullEnglish => 'pdf.valuation.full',
        };
    }

    public function isDraft(): bool
    {
        return $this === self::FullDraft;
    }

    public function watermark(): ?string
    {
        return $this->isDraft() ? 'مسودة' : null;
    }

    public function filename(string|int|null $reference): string
    {
        $ref = $reference !== null && $reference !== '' ? (string) $reference : 'unknown';

        return match ($this) {
            self::Enforcement => 'تقرير مركز إنفاذ مرجعى '.$ref.'.pdf',
            self::Full => 'التقرير الكامل مرجعى '.$ref.'.pdf',
            self::FullDraft => 'تقرير مرجعى مسودة'.$ref.'.pdf',
            self::FullEnglish => 'Valuation report '.$ref.'.pdf',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
