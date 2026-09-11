<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('phone_number', 50)->nullable()->after('name_en');
            $table->string('address')->nullable()->after('phone_number');
            $table->string('company_membership_number', 100)->nullable()->after('address');
            $table->string('company_reg_number', 100)->nullable()->after('company_membership_number');
            $table->string('main_logo_path')->nullable()->after('company_reg_number');
            $table->string('signature_path')->nullable()->after('main_logo_path');
            $table->string('stamp_path')->nullable()->after('signature_path');
            $table->unsignedInteger('default_coordinator_share')->default(0)->after('stamp_path');
            $table->unsignedInteger('default_evaluator_share')->default(0)->after('default_coordinator_share');
            $table->unsignedInteger('default_manager_share')->default(0)->after('default_evaluator_share');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'phone_number',
                'address',
                'company_membership_number',
                'company_reg_number',
                'main_logo_path',
                'signature_path',
                'stamp_path',
                'default_coordinator_share',
                'default_evaluator_share',
                'default_manager_share',
            ]);
        });
    }
};
