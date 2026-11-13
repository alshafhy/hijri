<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE request_fee_shares MODIFY legacy_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE property_totals MODIFY legacy_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE request_fee_shares MODIFY legacy_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE property_totals MODIFY legacy_id BIGINT UNSIGNED NOT NULL');
    }
};
