<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

use App\Support\Valuation\Report\Cards\Card;
use DateTimeInterface;

/**
 * Legacy signatures table (valuationTableFooter): evaluator, data validator (coordinator), approver and company stamp
 * columns, followed by the accreditation date.
 *
 * @phpstan-type Signer array{name: string, membership_number: ?string, membership_category: ?string, membership_branch_type: ?string, signature: ?string}
 * @phpstan-type Signatures array{evaluator: ?Signer, coordinator: ?Signer, approver: ?Signer}
 * @phpstan-type Column array{title: string, lines: list<string>, image: ?string, image_class: string}
 */
final class SignaturesPresenter
{
    /**
     * Null when the variant hides signatures (draft), which skips the whole block.
     *
     * @param  Signatures  $signatures
     * @return array{columns: list<Column>, accreditation_date: ?string}|null
     */
    public function build(array $signatures, ?string $stamp, ?DateTimeInterface $approvedAt, bool $show): ?array
    {
        if (! $show) {
            return null;
        }

        return [
            'columns' => [
                $this->signer('resident_name', $signatures['evaluator'], true),
                $this->signer('data_validator', $signatures['coordinator'], false),
                $this->signer('report_approved', $signatures['approver'], true),
                ['title' => Card::label('company_stamp'), 'lines' => [], 'image' => $stamp, 'image_class' => 'rpt-stamp-image'],
            ],
            'accreditation_date' => $approvedAt?->format('Y/m/d'),
        ];
    }

    /**
     * @param  Signer|null  $signer
     * @return Column
     */
    private function signer(string $titleKey, ?array $signer, bool $withMembership): array
    {
        $column = ['title' => Card::label($titleKey), 'lines' => [], 'image' => null, 'image_class' => 'rpt-signature-image'];
        if ($signer === null) {
            return $column;
        }

        $lines = [$signer['name']];
        if ($withMembership) {
            $lines = [...$lines, ...$this->membershipLines($signer)];
        }

        return ['lines' => $lines, 'image' => $signer['signature']] + $column;
    }

    /**
     * @param  Signer  $signer
     * @return list<string>
     */
    private function membershipLines(array $signer): array
    {
        $lines = [];
        if ($signer['membership_number'] !== null) {
            $lines[] = Card::label('membership_number_label').': - '.$signer['membership_number'];
        }
        $category = Card::join([$signer['membership_branch_type'], $signer['membership_category']], ' - ');
        if ($signer['membership_category'] !== null && $category !== null) {
            $lines[] = $category;
        }

        return $lines;
    }
}
