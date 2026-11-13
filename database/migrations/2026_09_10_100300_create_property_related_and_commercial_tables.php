<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_comparables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->unsignedTinyInteger('sequence');
            $table->text('real_estate_type')->nullable();
            $table->integer('street_width')->nullable();
            $table->integer('front_number')->nullable();
            $table->string('front_type', 200)->nullable();
            $table->double('area')->nullable();
            $table->double('price')->nullable();
            $table->integer('percentage')->nullable();
            $table->string('coordinates_url')->nullable();
            $table->text('information_source')->nullable();
            $table->timestamps();
            $table->unique(['property_id', 'sequence']);
        });

        Schema::create('property_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->unique();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->integer('type')->nullable();
            $table->date('transaction_date')->nullable();
            $table->string('market_conditions_value')->nullable();
            $table->string('market_conditions_percentage')->nullable();
            $table->string('financing_terms_value')->nullable();
            $table->string('financing_terms_percentage')->nullable();
            $table->string('sale_terms_value')->nullable();
            $table->string('sale_terms_percentage')->nullable();
            $table->integer('area_percentage')->nullable();
            $table->integer('location_status_value')->nullable();
            $table->integer('location_status_percentage')->nullable();
            $table->integer('easy_access_value')->nullable();
            $table->integer('easy_access_percentage')->nullable();
            $table->integer('main_street_width_value')->nullable();
            $table->integer('main_street_width_percentage')->nullable();
            $table->integer('streets_count_value')->nullable();
            $table->integer('streets_count_percentage')->nullable();
            $table->integer('land_shape_value')->nullable();
            $table->integer('land_shape_percentage')->nullable();
            $table->string('land_topography_value')->nullable();
            $table->integer('land_topography_percentage')->nullable();
            $table->string('natural_factors_value')->nullable();
            $table->integer('natural_factors_percentage')->nullable();
            $table->integer('near_main_street_value')->nullable();
            $table->integer('near_main_street_percentage')->nullable();
            $table->string('other_value')->nullable();
            $table->integer('other_percentage')->nullable();
            $table->double('total_adjustments_value')->nullable();
            $table->double('total_adjustments_price')->nullable();
            $table->double('meter_price')->nullable();
            $table->double('meter_price_approximately')->nullable();
            $table->integer('proportional_adjustment')->nullable();
            $table->double('settlement_ratio')->nullable();
            $table->date('transaction_gregorian_date')->nullable();
            $table->timestamps();
        });

        Schema::create('property_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('component_key', 64);
            $table->decimal('area_value', 16, 4)->nullable();
            $table->decimal('price_value', 16, 4)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(['property_id', 'component_key']);
        });

        Schema::create('property_pictures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->unique();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('filename', 255);
            $table->string('description', 64)->nullable();
            $table->integer('orientation')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('relative_path')->nullable();
            $table->boolean('file_exists')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('request_fee_shares', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->unique();
            $table->foreignId('valuation_request_id')->constrained('valuation_requests')->cascadeOnDelete();
            $table->integer('coordinator_share')->nullable();
            $table->integer('evaluator_share')->nullable();
            $table->integer('manager_share')->nullable();
            $table->timestamps();
        });

        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->unique();
            $table->foreignId('valuation_request_id')->nullable()->constrained('valuation_requests')->nullOnDelete();
            $table->unsignedBigInteger('legacy_user_id')->nullable()->index();
            $table->string('number', 250)->nullable();
            $table->string('partner_name', 256)->nullable();
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->string('city', 32)->nullable();
            $table->dateTime('offered_at')->nullable();
            $table->integer('state')->nullable();
            $table->unsignedBigInteger('created_by_legacy_user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->unique();
            $table->foreignId('contractor_id')->nullable()->constrained('contractors')->nullOnDelete();
            $table->foreignId('valuation_request_id')->nullable()->constrained('valuation_requests')->nullOnDelete();
            $table->unsignedBigInteger('legacy_contractor_id')->nullable()->index();
            $table->unsignedBigInteger('legacy_request_id')->nullable()->index();
            $table->integer('state')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('offers');
        Schema::dropIfExists('request_fee_shares');
        Schema::dropIfExists('property_pictures');
        Schema::dropIfExists('property_components');
        Schema::dropIfExists('property_adjustments');
        Schema::dropIfExists('property_comparables');
    }
};
