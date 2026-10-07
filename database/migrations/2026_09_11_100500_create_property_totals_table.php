<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_totals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->unique();
            $table->foreignId('property_id')->unique()->constrained('properties')->cascadeOnDelete();
            $table->double('profit_percentage')->nullable();
            $table->double('profit_amount')->nullable();
            $table->integer('depreciation_type')->nullable();
            $table->double('calc_amount')->nullable();
            $table->double('depreciation_amount')->nullable();
            $table->integer('forced_sale_percentage')->nullable();
            $table->double('forced_sale_amount')->nullable();
            $table->double('total_amount')->nullable();
            $table->double('total_amount_manual')->nullable();
            $table->boolean('hide_comparisons_info_table')->default(false);
            $table->boolean('hide_evaluation_info_table')->default(false);
            $table->decimal('total_area', 12, 2)->nullable();
            $table->decimal('base_area', 12, 2)->nullable();
            $table->double('base_amount')->nullable();
            $table->double('movables')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_totals');
    }
};
