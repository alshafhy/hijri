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
            $table->string('report_logo_path')->nullable()->after('stamp_path');
            $table->date('membership_date')->nullable()->after('company_membership_number');
            $table->date('accreditation_end_date')->nullable()->after('membership_date');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->dropColumn(['report_logo_path', 'membership_date', 'accreditation_end_date']);
        });
    }
};
