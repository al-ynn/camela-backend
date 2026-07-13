<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Category::create([
            'name' => 'SampleCategory1',
            'slug' => 'samplecategory1',
            'description' => 'This is Sample Category 1',
            'image' => '',
            'is_active' => true,
        ]);

        \App\Models\Category::create([
            'name' => 'SampleCategory2',
            'slug' => 'samplecategory2',
            'description' => 'This is Sample Category 2',
            'image' => '',
            'is_active' => true,
        ]);

    }

}