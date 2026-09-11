<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL only — sqlite recreates from create migrations (legacy_id nullable there).
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE valuation_requests MODIFY legacy_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE properties MODIFY legacy_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE valuation_requests MODIFY legacy_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE properties MODIFY legacy_id BIGINT UNSIGNED NOT NULL');
    }
};
