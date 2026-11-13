<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['partners', 'contractors', 'offers', 'contracts'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (Schema::hasColumn($tableName, 'legacy_id')) {
                    $table->unsignedBigInteger('legacy_id')->nullable()->change();
                }
            });
        }

        if (Schema::hasTable('offer_estates')) {
            Schema::table('offer_estates', function (Blueprint $table): void {
                $table->unsignedBigInteger('legacy_id')->nullable()->change();
            });
        }

        if (Schema::hasTable('party_contacts')) {
            Schema::table('party_contacts', function (Blueprint $table): void {
                $table->unsignedBigInteger('legacy_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Intentionally leave nullable — reversing would break app-created rows.
    }
};
