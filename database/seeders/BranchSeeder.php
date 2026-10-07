<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branchId = (int) config('users.default_branch_id', 1);

        Branch::query()->updateOrCreate(
            ['id' => $branchId],
            [
                'name' => 'Main Branch',
                'ar_name' => 'الفرع الرئيسي',
                'is_active' => true,
            ]
        );
    }
}
