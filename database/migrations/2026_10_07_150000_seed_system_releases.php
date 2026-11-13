<?php

declare(strict_types=1);

use Database\Seeders\SystemReleasesSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new SystemReleasesSeeder)->run();
    }

    public function down(): void
    {
        //
    }
};
