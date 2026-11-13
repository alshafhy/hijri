<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_contracts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('number')->unique();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->date('contract_date');
            $table->string('client_name');
            $table->string('client_id_number', 50);
            $table->string('client_phone', 30);
            $table->string('client_address')->nullable();
            $table->string('client_email')->nullable();
            $table->string('purpose');
            $table->foreignId('city_id')->nullable()->constrained('geo_cities')->nullOnDelete();
            $table->foreignId('neighborhood_id')->nullable()->constrained('geo_neighborhoods')->nullOnDelete();
            $table->string('property_kind', 100)->nullable();
            $table->string('property_type', 100)->nullable();
            $table->string('instrument_no', 100);
            $table->date('instrument_date')->nullable();
            $table->decimal('contract_value', 14, 2);
            $table->string('representative_name');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('company');
            $table->string('phone_number', 30);
            $table->text('message');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('service_contracts');
    }
};
