<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valuation_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->unique();
            $table->integer('reference')->nullable()->index();
            $table->string('number', 250)->nullable()->index();
            $table->string('deposit_number')->nullable();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('coordinator_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('evaluator_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sub_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('fellow_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('legacy_coordinator_id')->nullable()->index();
            $table->unsignedBigInteger('legacy_evaluator_id')->nullable()->index();
            $table->unsignedBigInteger('legacy_sub_user_id')->nullable();
            $table->unsignedBigInteger('legacy_fellow_id')->nullable();
            $table->unsignedBigInteger('fellow_id')->nullable()->index();
            $table->unsignedBigInteger('legacy_location_id')->nullable()->index();
            $table->string('state', 64)->nullable()->index();
            $table->tinyInteger('approve')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('under_evaluation_at')->nullable();
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->boolean('uploaded_on_qima')->default(false)->index();
            $table->string('official_report_path')->nullable();
            $table->timestamp('qima_locked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->unique();
            $table->foreignId('valuation_request_id')->nullable()->unique()->constrained('valuation_requests')->nullOnDelete();
            $table->unsignedBigInteger('legacy_location_id')->nullable()->index();
            $table->string('customer_name', 128)->nullable();
            $table->string('owner_name', 128)->nullable();
            $table->string('customer_name_en', 250)->nullable();
            $table->string('owner_name_en', 200)->nullable();
            $table->string('property_kind', 45)->nullable();
            $table->string('property_type', 32)->nullable();
            $table->text('notes_real_estate')->nullable();
            $table->text('notes_property')->nullable();
            $table->string('instrument_no', 200)->nullable();
            $table->string('instrument_date', 64)->nullable();
            $table->string('instrument_gregorian_date', 64)->nullable();
            $table->string('license_no')->nullable();
            $table->string('license_date')->nullable();
            $table->string('issued_by')->nullable();
            $table->string('commissioning_date')->nullable();
            $table->string('retail_no')->nullable();
            $table->string('undergo_reasons')->nullable();
            $table->integer('valuation_type')->nullable();
            $table->text('valuation_type_desc')->nullable();
            $table->string('base_value', 100)->nullable();
            $table->text('note_base_value')->nullable();
            $table->text('valuation_way_type')->nullable();
            $table->text('note_valuation_way_type')->nullable();
            $table->boolean('print_valuation_certificate')->nullable();
            $table->text('requested_papers')->nullable();
            $table->text('assumptions')->nullable();
            $table->text('area_of_search')->nullable();
            $table->text('area_approval_way')->nullable();
            $table->text('informations_source')->nullable();
            $table->text('important_assumptions')->nullable();
            $table->text('conclusion_value')->nullable();
            $table->text('type_of_getings_value')->nullable();
            $table->integer('value_assumption')->nullable();
            $table->text('other_users')->nullable();
            $table->boolean('other_users_yes')->nullable();
            $table->integer('valuation_usage')->nullable();
            $table->integer('valuation_usage_old')->nullable();
            $table->string('valuation_usage_desc', 250)->nullable();
            $table->string('deposit_number', 200)->nullable();
            $table->integer('market_valuation_way_main')->nullable();
            $table->integer('income_valuation_way_main')->nullable();
            $table->integer('cost_valuation_way_main')->nullable();
            $table->integer('market_valuation_way_sub')->nullable();
            $table->integer('income_valuation_way_sub')->nullable();
            $table->integer('cost_valuation_way_sub')->nullable();
            $table->integer('forced_sale_percentage')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('evaluation_date')->nullable();
            $table->string('evaluation_date_hijri', 64)->nullable();
            $table->boolean('according_instrument')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('property_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->unique()->constrained('properties')->cascadeOnDelete();
            $table->unsignedBigInteger('legacy_location_id')->nullable()->unique();
            $table->foreignId('city_id')->nullable()->constrained('geo_cities')->nullOnDelete();
            $table->foreignId('neighborhood_id')->nullable()->constrained('geo_neighborhoods')->nullOnDelete();
            $table->string('sketch_name', 45)->nullable();
            $table->string('street', 45)->nullable();
            $table->string('sketch_no', 45)->nullable();
            $table->string('part_no', 45)->nullable();
            $table->string('piece_number', 100)->nullable();
            $table->string('x_axis', 45)->nullable();
            $table->string('y_axis', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_locations');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('valuation_requests');
    }
};
