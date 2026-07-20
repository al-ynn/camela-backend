<?php

namespace App\Services\Product;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {

           $product = Product::create([

            'category_id'=>$data['category_id'],

            'title'=>$data['title'],

            'slug'=>Str::slug($data['title']),

            'sku'=>$data['sku'],

            'short_description'=>$data['short_description'] ?? null,

            'description'=>$data['description'],

            'price'=>$data['price'],

            'compare_price'=>$data['compare_price'] ?? null,

            'cost_price'=>$data['cost_price'] ?? null,

            'stock'=>$data['stock'],

            'low_stock_alert'=>$data['low_stock_alert'] ?? 5,

            'weight'=>$data['weight'] ?? null,

            'status'=>$data['status'],

            'featured'=>$data['featured'] ?? false,

            'seo_title'=>$data['seo_title'] ?? null,

            'seo_description'=>$data['seo_description'] ?? null,

        ]);

            if (!empty($data['images'])) {

                foreach ($data['images'] as $index => $image) {

                    $path = $image->store(
                        'products',
                        'public'
                    );

                    $product->images()->create([

                        'image_path' => $path,

                        'is_primary' => $index === 0,

                        'sort_order' => $index,

                    ]);
                }
            }

            return $product;

        });
    }

    public function getAll()
    {
        return Product::with([
            'category',
            'images'
        ])
        ->latest()
        ->paginate(10);
    }

    public function update(
        Product $product,
        array $data
    ): Product
    {
        return DB::transaction(function () use ($product, $data) {

            if (array_key_exists('title', $data)) {
                $data['slug'] = Str::slug($data['title']);
            }

            $product->update([

                'category_id'=>$data['category_id'] ?? $product->category_id,

                'title'=>$data['title'] ?? $product->title,

                'slug'=>$data['slug'] ?? $product->slug,

                'sku'=>$data['sku'] ?? $product->sku,

                'short_description'=>$data['short_description'] ?? $product->short_description,

                'description'=>$data['description'] ?? $product->description,

                'price'=>$data['price'] ?? $product->price,

                'compare_price'=>$data['compare_price'] ?? $product->compare_price,

                'cost_price'=>$data['cost_price'] ?? $product->cost_price,

                'stock'=>$data['stock'] ?? $product->stock,

                'low_stock_alert'=>$data['low_stock_alert'] ?? $product->low_stock_alert,

                'weight'=>$data['weight'] ?? $product->weight,

                'status'=>$data['status'] ?? $product->status,

                'featured'=>$data['featured'] ?? $product->featured,

                'seo_title'=>$data['seo_title'] ?? $product->seo_title,

                'seo_description'=>$data['seo_description'] ?? $product->seo_description,

            ]);

            return $product;
        });
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function catalog(array $filters = [])
    {
        $query = Product::query()
            ->with(['category', 'images'])
            ->where('status', 'ACTIVE');

        if (!empty($filters['search'])) {

            $query->where(function ($q) use ($filters) {

                $q->where('title', 'like', '%' . $filters['search'] . '%')
                ->orWhere('description', 'like', '%' . $filters['search'] . '%');

            });

        }

        if (!empty($filters['category'])) {

            $query->whereHas('category', function ($q) use ($filters) {

                $q->where('name', $filters['category']);

            });

        }

        if (!empty($filters['featured'])) {

            $query->where('featured', true);

        }

        if (!empty($filters['sort'])) {

            switch ($filters['sort']) {

                case 'price_asc':
                    $query->orderBy('price');
                    break;

                case 'price_desc':
                    $query->orderByDesc('price');
                    break;

                case 'latest':
                    $query->latest();
                    break;

                default:
                    $query->latest();
                    break;
            }

        } else {

            $query->latest();

        }
        
        return $query->paginate(12);
    }

    public function findBySlug(string $slug): Product
    {
        return Product::with([

            'category',

            'images'

        ])
        ->where('slug', $slug)
        ->where('status', 'ACTIVE')
        ->firstOrFail();
    }
    
    public function related(Product $product)
    {
        return Product::with([

            'images',

            'category'

        ])
        ->where('category_id', $product->category_id)

        ->where('id', '!=', $product->id)

        ->where('status', 'ACTIVE')

        ->take(4)

        ->get();
    }

    public function search(string $keyword)
    {
        return Product::with([
            'category',
            'images'
        ])
        ->where('status', 'ACTIVE')
        ->where(function ($q) use ($keyword) {

            $q->where('title', 'like', "%{$keyword}%")
            ->orWhere('description', 'like', "%{$keyword}%");

        })
        ->take(10)
        ->get();
    }

    public function searchSuggestions(string $keyword)
    {
        return Product::query()

            ->where('status', 'ACTIVE')

            ->where('title', 'like', "%{$keyword}%")

            ->limit(8)

            ->get([

                'id',

                'title',

                'slug',

                'price'

            ]);
    }

    public function find($value): Product
    {
        return Product::with([
            'category',
            'images'
        ])
        ->where('status', 'ACTIVE')
        ->where(function ($query) use ($value) {

            if (is_numeric($value)) {
                $query->where('id', $value);
            } else {
                $query->where('slug', $value);
            }

        })
        ->firstOrFail();
    }

    public function byCategory(string $category)
    {
        return Product::with([
                'category',
                'images'
            ])
            ->whereHas('category', function ($q) use ($category) {

                $q->whereRaw('LOWER(slug) = ?', [strtolower($category)])
                ->orWhereRaw('LOWER(name) = ?', [strtolower($category)]);

            })
            ->where('status', 'ACTIVE')
            ->paginate(12);
    }

}
