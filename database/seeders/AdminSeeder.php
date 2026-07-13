<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder

{
    public function run(): void
    {
        User::create([
            'role_id' => 1,
            'name' => 'System Administrator',
            'username' => 'admin',
            'email' => 'admin@camela.com',
            'password' => 'Admin@123',
        ]);
    }
}