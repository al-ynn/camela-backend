<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        User::create([

            'role_id' => 2,

            'name' => 'Test Customer',

            'username' => 'customer',

            'email' => 'customer@camela.com',

            'password' => 'Customer@123',

        ]);
    }
}