<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->unique();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('geo_cities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->unique();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->timestamps();
        });

        Schema::create('geo_neighborhoods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->unique();
            $table->foreignId('city_id')->constrained('geo_cities')->cascadeOnDelete();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->timestamps();
        });

        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->unique();
            $table->unsignedBigInteger('legacy_user_id')->nullable()->index();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->string('x_axis', 64)->nullable();
            $table->string('y_axis', 64)->nullable();
            $table->integer('state')->nullable();
            $table->unsignedBigInteger('created_by_legacy_user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->unique();
            $table->unsignedBigInteger('legacy_user_id')->nullable()->index();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->double('x_axis')->nullable();
            $table->double('y_axis')->nullable();
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->integer('fees')->nullable();
            $table->integer('state')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->unsignedBigInteger('created_by_legacy_user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contractors');
        Schema::dropIfExists('partners');
        Schema::dropIfExists('geo_neighborhoods');
        Schema::dropIfExists('geo_cities');
        Schema::dropIfExists('companies');
    }
};
