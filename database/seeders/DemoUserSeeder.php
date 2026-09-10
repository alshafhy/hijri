<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = config('users.admin');

        $user = User::query()->updateOrCreate(
            ['username' => $admin['username']],
            [
                'name' => $admin['name'],
                'email' => $admin['email'],
                'password' => Hash::make((string) $admin['password']),
                'branch_id' => (int) config('users.default_branch_id', 1),
                'status' => (int) config('users.status.active'),
                'remember_token' => null,
            ]
        );

        $user->assignRole('super-admin');
    }
}
