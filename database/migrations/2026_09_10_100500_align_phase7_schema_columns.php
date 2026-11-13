<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Align Phase 7 columns after initial create migrations already ran.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                if (Schema::hasColumn('properties', 'other_users') && ! Schema::hasColumn('properties', 'other_users_yes')) {
                    $table->renameColumn('other_users', 'other_users_yes');
                }
            });

            Schema::table('properties', function (Blueprint $table) {
                if (Schema::hasColumn('properties', 'other_users_raw') && ! Schema::hasColumn('properties', 'other_users')) {
                    $table->renameColumn('other_users_raw', 'other_users');
                }
            });
        }

        if (Schema::hasTable('valuation_requests') && ! Schema::hasColumn('valuation_requests', 'fellow_id')) {
            Schema::table('valuation_requests', function (Blueprint $table) {
                $table->unsignedBigInteger('fellow_id')->nullable()->index()->after('legacy_fellow_id');
            });
        }

        if (Schema::hasTable('property_adjustments') && ! Schema::hasColumn('property_adjustments', 'transaction_gregorian_date')) {
            Schema::table('property_adjustments', function (Blueprint $table) {
                $table->date('transaction_gregorian_date')->nullable()->after('settlement_ratio');
            });
        }

        if (Schema::hasTable('offers') && ! Schema::hasColumn('offers', 'valuation_request_id')) {
            Schema::table('offers', function (Blueprint $table) {
                $table->foreignId('valuation_request_id')
                    ->nullable()
                    ->after('legacy_id')
                    ->constrained('valuation_requests')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('offers') && Schema::hasColumn('offers', 'valuation_request_id')) {
            Schema::table('offers', function (Blueprint $table) {
                $table->dropConstrainedForeignId('valuation_request_id');
            });
        }

        if (Schema::hasTable('property_adjustments') && Schema::hasColumn('property_adjustments', 'transaction_gregorian_date')) {
            Schema::table('property_adjustments', function (Blueprint $table) {
                $table->dropColumn('transaction_gregorian_date');
            });
        }

        if (Schema::hasTable('valuation_requests') && Schema::hasColumn('valuation_requests', 'fellow_id')) {
            Schema::table('valuation_requests', function (Blueprint $table) {
                $table->dropColumn('fellow_id');
            });
        }

        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                if (Schema::hasColumn('properties', 'other_users') && ! Schema::hasColumn('properties', 'other_users_raw')) {
                    $table->renameColumn('other_users', 'other_users_raw');
                }
            });

            Schema::table('properties', function (Blueprint $table) {
                if (Schema::hasColumn('properties', 'other_users_yes') && ! Schema::hasColumn('properties', 'other_users')) {
                    $table->renameColumn('other_users_yes', 'other_users');
                }
            });
        }
    }
};
