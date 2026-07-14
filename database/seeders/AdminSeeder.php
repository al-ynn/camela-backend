<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = Role::where('name', 'ADMIN')->value('id');

        if (!$adminRoleId) {
            return;
        }

        User::updateOrCreate(
            ['email' => 'admin1@example.com'],
            [
                'role_id' => $adminRoleId,
                'name' => 'Admin One',
                'username' => 'admin1',
                'password' => 'password',
            ]
        );
    }
}
