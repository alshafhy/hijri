<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_estates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->unique();
            $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();
            $table->string('estate_kind', 64)->nullable();
            $table->string('estate_type', 255)->nullable();
            $table->string('instrument_no', 64)->nullable();
            $table->unsignedInteger('area')->nullable();
            $table->string('neighborhood', 64)->nullable();
            $table->unsignedInteger('fees')->nullable();
            $table->unsignedTinyInteger('payment_status')->default(0)->index();
            $table->unsignedTinyInteger('status')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['offer_id', 'status']);
        });

        Schema::create('party_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->unique();
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->foreignId('contractor_id')->nullable()->constrained('contractors')->nullOnDelete();
            $table->string('owner_type', 32)->index();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone_number', 32)->nullable();
            $table->tinyInteger('status')->default(1)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['partner_id', 'status']);
            $table->index(['contractor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('party_contacts');
        Schema::dropIfExists('offer_estates');
    }
};
