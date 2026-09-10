<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\LegacyUserMap;
use App\Models\User;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

/**
 * Maps legacy users to existing app users by unique email only (no stub creation).
 */
final class LegacyUsersMapImporter extends ResumableImporter
{
    public static function key(): string
    {
        return 'legacy_users';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('users');
    }

    protected function importRow(stdClass $row): void
    {
        $legacyId = (int) $row->id;
        $email = is_string($row->email ?? null) ? trim($row->email) : null;
        if ($email === '') {
            $email = null;
        }
        $name = is_string($row->name ?? null) ? trim($row->name) : null;
        $type = is_string($row->type ?? null) ? trim($row->type) : null;

        $userId = null;
        if ($email !== null) {
            $matches = User::withTrashed()->where('email', $email)->pluck('id');
            if ($matches->count() === 1) {
                $userId = (int) $matches->first();
            }
        }

        if ($this->dryRun) {
            $this->imported++;

            return;
        }

        LegacyUserMap::query()->updateOrCreate(
            ['legacy_user_id' => $legacyId],
            [
                'user_id' => $userId,
                'legacy_type' => $type,
                'email' => $email,
                'name' => $name,
            ]
        );
        $this->imported++;
    }
}
