<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_buildings', function (Blueprint $table) {
            $table->id();
            $table->string('legacy_key', 40)->nullable()->unique();
            $table->foreignId('property_id')->unique()->constrained('properties')->cascadeOnDelete();
            $table->string('kind', 20)->nullable();
            $table->integer('floor_no')->nullable();
            $table->string('build_age', 20)->nullable();
            $table->string('build_state', 45)->nullable();
            $table->string('architectural', 45)->nullable();
            $table->string('quality_insulation', 45)->nullable();
            $table->string('bishop_type', 45)->nullable();
            $table->string('structure_construction', 45)->nullable();
            $table->integer('default_build_age')->nullable();
            $table->integer('remaining_build_age')->nullable();
            $table->string('house_unit_no', 32)->nullable();
            $table->string('commercial_unit_no', 32)->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('property_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->unique();
            $table->foreignId('property_id')->unique()->constrained('properties')->cascadeOnDelete();
            foreach (['populated', 'identical', 'contrary', 'concrete_roof', 'ready', 'ground_extension', 'need_cost', 'cost'] as $column) {
                $table->string($column, 45)->nullable();
            }
            $table->string('inside_inspection')->nullable();
            $table->string('outside_inspection')->nullable();
            $table->unsignedTinyInteger('best_use')->nullable()->default(1);
            $table->timestamps();
        });

        Schema::create('property_acs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->unique();
            $table->foreignId('property_id')->unique()->constrained('properties')->cascadeOnDelete();
            $table->string('types')->nullable();
            $table->integer('ac_count')->nullable();
            $table->timestamps();
        });

        Schema::create('property_floorings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->unique();
            $table->foreignId('property_id')->unique()->constrained('properties')->cascadeOnDelete();
            foreach (['yards', 'reception', 'entrance', 'rooms'] as $room) {
                $table->string("flooring_{$room}")->nullable();
                $table->string("{$room}_description", 100)->nullable();
            }
            $table->timestamps();
        });

        Schema::create('valuation_custom_table_rows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->unique();
            $table->foreignId('valuation_request_id')->constrained('valuation_requests')->cascadeOnDelete();
            $table->string('table_title')->nullable();
            $table->string('label');
            $table->json('values')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('valuation_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('legacy_key', 40)->nullable()->unique();
            $table->foreignId('valuation_request_id')->constrained('valuation_requests')->cascadeOnDelete();
            $table->string('type')->nullable();
            $table->string('original_name')->nullable();
            $table->string('path');
            $table->string('disk', 20)->default('local');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('property_pictures', function (Blueprint $table) {
            $table->unsignedBigInteger('legacy_id')->nullable()->change();
        });

        Schema::table('property_adjustments', function (Blueprint $table) {
            $table->unsignedBigInteger('legacy_id')->nullable()->change();
        });

        Schema::table('property_lands', function (Blueprint $table) {
            foreach (['facade', 'door_ex', 'street', 'door_in', 'neighbor', 'include', 'surrounding_facilities'] as $column) {
                $table->string($column)->nullable()->change();
            }
        });

        Schema::table('property_facades', function (Blueprint $table) {
            foreach (['facade_type_n', 'facade_type_s', 'facade_type_e', 'facade_type_w'] as $column) {
                $table->string($column)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valuation_attachments');
        Schema::dropIfExists('valuation_custom_table_rows');
        Schema::dropIfExists('property_floorings');
        Schema::dropIfExists('property_acs');
        Schema::dropIfExists('property_questions');
        Schema::dropIfExists('property_buildings');
    }
};
