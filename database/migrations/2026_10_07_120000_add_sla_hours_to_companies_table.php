<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->unsignedSmallInteger('request_sla_hours')->default(5)->after('default_manager_share');
            $table->unsignedSmallInteger('evaluation_sla_hours')->default(5)->after('request_sla_hours');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->dropColumn(['request_sla_hours', 'evaluation_sla_hours']);
        });
    }
};
