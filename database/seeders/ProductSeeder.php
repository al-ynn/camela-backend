<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [

            [
                'category_id' => 1,
                'title' => 'Product Sample 1',
                'sku' => 'PSample001',
                'price' => 1500,
                'stock' => 100,
            ],


        ];

        foreach ($products as $product) {

            Product::create([

                'category_id' => $product['category_id'],

                'title' => $product['title'],

                'slug' => Str::slug($product['title']),

                'sku' => $product['sku'],

                'description' => $product['title'],

                'short_description' => $product['title'],

                'price' => $product['price'],

                'stock' => $product['stock'],

                'low_stock_alert' => 10,

                'status' => 'ACTIVE',

                'featured' => false,

            ]);

        }
    }
}