<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

use App\Models\ValuationCustomTableRow;
use App\Models\ValuationRequest;

/**
 * User-defined custom tables (label + free values per row), grouped by table title.
 */
final class CustomTableSection
{
    /**
     * @return list<array{title: string, columns: int, rows: list<array{label: string, values: list<string>}>}>
     */
    public function build(ValuationRequest $request): array
    {
        $groups = [];
        foreach ($this->rows($request) as $row) {
            $title = trim((string) $row->table_title);
            $groups[$title === '' ? __('pdf.custom_table') : $title][] = [
                'label' => (string) $row->label,
                'values' => $this->values($row),
            ];
        }

        $tables = [];
        foreach ($groups as $title => $rows) {
            $tables[] = $this->table((string) $title, $rows);
        }

        return $tables;
    }

    /**
     * @return iterable<ValuationCustomTableRow>
     */
    private function rows(ValuationRequest $request): iterable
    {
        return $request->customTableRows()->reorder()->orderBy('sort_order')->orderBy('id')->get();
    }

    /**
     * @return list<string>
     */
    private function values(ValuationCustomTableRow $row): array
    {
        $values = $row->getAttribute('values');
        if (! is_array($values)) {
            return [];
        }

        return array_values(array_map(static fn (mixed $value): string => is_scalar($value) ? (string) $value : '', $values));
    }

    /**
     * @param  list<array{label: string, values: list<string>}>  $rows
     * @return array{title: string, columns: int, rows: list<array{label: string, values: list<string>}>}
     */
    private function table(string $title, array $rows): array
    {
        $columns = max(array_map(static fn (array $row): int => count($row['values']), $rows));

        return [
            'title' => $title,
            'columns' => $columns,
            'rows' => array_map(static fn (array $row): array => [
                'label' => $row['label'],
                'values' => array_pad($row['values'], $columns, ''),
            ], $rows),
        ];
    }
}
