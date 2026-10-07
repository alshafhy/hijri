<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_contracts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->unique();
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->foreignId('offer_estate_id')->nullable()->constrained('offer_estates')->nullOnDelete();
            $table->foreignId('valuation_request_id')->nullable()->constrained('valuation_requests')->cascadeOnDelete();
            $table->unsignedBigInteger('legacy_partner_id')->nullable();
            $table->unsignedBigInteger('legacy_estate_id')->nullable();
            $table->unsignedBigInteger('legacy_request_id')->nullable()->index();
            $table->tinyInteger('state')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_contracts');
    }
};
