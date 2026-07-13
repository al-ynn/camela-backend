<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $images = $this->whenLoaded('images', function () {
            return $this->images
                ->sortBy('sort_order')
                ->map(function ($image) {
                    return asset('storage/' . $image->image_path);
                })
                ->values();
        });

        $primaryImage = null;

        if ($this->relationLoaded('images')) {

            $primary = $this->images
                ->firstWhere('is_primary', true);

            if (!$primary) {
                $primary = $this->images->first();
            }

            if ($primary) {
                $primaryImage = asset(
                    'storage/' . $primary->image_path
                );
            }
        }

        return [

            'id' => $this->id,

            'title' => $this->title,

            'price' => (float) $this->price,

            'compare_price' => $this->compare_price
                ? (float) $this->compare_price
                : null,

            'category' => optional($this->category)->name,

            'description' => $this->description,

            'short_description' => $this->short_description,

            'stock' => $this->stock,

            'status' => $this->status,

            'featured' => (bool) $this->featured,

            /*
            |--------------------------------------------------------------------------
            | Frontend Compatibility
            |--------------------------------------------------------------------------
            */

            'image' => $primaryImage,

            'images' => $images,

            /*
            |--------------------------------------------------------------------------
            | Placeholder until Review Module exists
            |--------------------------------------------------------------------------
            */

            'rating' => [

                'rate' => 5,

                'count' => 0,

            ],

        ];
    }
}
