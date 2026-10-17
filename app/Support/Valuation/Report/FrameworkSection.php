<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

use App\Support\Valuation\Report\Cards\Card;

/**
 * Legacy `evaluationFramework`: ethics / competence / quality card and the valuation standards page (Arabic report only).
 */
final class FrameworkSection
{
    /** Group label => [item label key => item text key]. */
    private const GROUPS = [
        'ethics' => [
            'integrity_objectivity' => 'integrity_objectivity_des',
            'transparency_disclosure' => 'transparency_disclosure_des',
            'privacy_label' => 'privacy_des',
        ],
        'competence' => [
            'qualifications_experience' => 'qualifications_experience_des',
            'knowledge_skills' => 'knowledge_skills_des',
            'professional_development' => 'professional_development_des',
        ],
        'quality' => [
            'scientific_methodology' => 'scientific_methodology_des',
            'accuracy_reliability' => 'accuracy_reliability_des',
            'continuous_improvement' => 'continuous_improvement_des',
        ],
    ];

    /**
     * @return array{groups: list<array{label: string, items: list<array{label: string, text: string}>}>, show_standards: bool, standards: list<array{title: string, description: string}>}
     */
    public function build(string $locale): array
    {
        $showStandards = $locale === 'ar';

        return [
            'groups' => $this->groups(),
            'show_standards' => $showStandards,
            'standards' => $showStandards ? $this->standards() : [],
        ];
    }

    /**
     * @return list<array{label: string, items: list<array{label: string, text: string}>}>
     */
    private function groups(): array
    {
        $groups = [];
        foreach (self::GROUPS as $group => $items) {
            $rows = [];
            foreach ($items as $labelKey => $textKey) {
                $rows[] = ['label' => Card::label($labelKey), 'text' => Card::label($textKey)];
            }
            $groups[] = ['label' => Card::label($group), 'items' => $rows];
        }

        return $groups;
    }

    /**
     * @return list<array{title: string, description: string}>
     */
    private function standards(): array
    {
        return array_values(array_map(
            static fn (mixed $standard): array => [
                'title' => Card::label('standard_'.(string) $standard),
                'description' => Card::label('standard_'.(string) $standard.'_des'),
            ],
            (array) config('report_branding.standards', []),
        ));
    }
}
