<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_borders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->unique();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('north', 250)->nullable();
            $table->double('north_length')->nullable();
            $table->string('east', 250)->nullable();
            $table->double('east_length')->nullable();
            $table->string('south', 250)->nullable();
            $table->double('south_length')->nullable();
            $table->string('west', 250)->nullable();
            $table->double('west_length')->nullable();
            $table->timestamps();
        });

        Schema::create('property_lands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->unique();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('land_nature', 45)->nullable();
            $table->string('facade', 64)->nullable();
            $table->string('site', 45)->nullable();
            $table->string('door_ex', 45)->nullable();
            $table->string('street', 45)->nullable();
            $table->string('door_in', 45)->nullable();
            $table->string('neighbor', 45)->nullable();
            $table->string('include', 256)->nullable();
            $table->string('heater_no', 45)->nullable();
            $table->string('lift_no', 45)->nullable();
            $table->string('bathroom_a_no', 45)->nullable();
            $table->string('bathroom_e_no', 45)->nullable();
            $table->string('electricity_no', 45)->nullable();
            $table->string('electricity_num', 45)->nullable();
            $table->string('water_no', 45)->nullable();
            $table->string('water_num', 45)->nullable();
            $table->unsignedInteger('ownership_type')->nullable();
            $table->unsignedInteger('furnishing_status')->nullable();
            $table->unsignedInteger('building_type')->nullable();
            $table->unsignedInteger('finishing_status')->nullable();
            $table->float('street_width')->nullable();
            $table->float('total_building_area')->nullable();
            $table->string('additional_features', 100)->nullable();
            $table->float('approved_floors')->nullable();
            $table->string('property_use', 100)->nullable();
            $table->float('approved_building')->nullable();
            $table->string('sector_description', 100)->nullable();
            $table->string('ownership_description', 100)->nullable();
            $table->unsignedInteger('market_value')->nullable();
            $table->string('market_value_description', 100)->nullable();
            $table->string('surrounding_facilities', 256)->nullable();
            $table->timestamps();
        });

        Schema::create('property_facades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->unique();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('facade_type_n', 45)->nullable();
            $table->string('facade_type_s', 45)->nullable();
            $table->string('facade_type_e', 45)->nullable();
            $table->string('facade_type_w', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('property_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->unique();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('electricity', 64)->nullable();
            $table->string('water', 64)->nullable();
            $table->string('sanitation', 45)->nullable();
            $table->string('telephone', 45)->nullable();
            $table->string('internet', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('legacy_user_maps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_user_id')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('legacy_type', 60)->nullable()->index();
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_user_maps');
        Schema::dropIfExists('property_services');
        Schema::dropIfExists('property_facades');
        Schema::dropIfExists('property_lands');
        Schema::dropIfExists('property_borders');
    }
};
