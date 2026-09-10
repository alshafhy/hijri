<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_runs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status', 32)->default('pending')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('import_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_run_id')->constrained('import_runs')->cascadeOnDelete();
            $table->string('importer');
            $table->unsignedBigInteger('last_legacy_id')->default(0);
            $table->unsignedInteger('processed')->default(0);
            $table->timestamps();
            $table->unique(['import_run_id', 'importer']);
        });

        Schema::create('import_quarantine', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_run_id')->nullable()->constrained('import_runs')->nullOnDelete();
            $table->string('entity_type')->index();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->json('payload')->nullable();
            $table->string('reason');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_quarantine');
        Schema::dropIfExists('import_checkpoints');
        Schema::dropIfExists('import_runs');
    }
};
