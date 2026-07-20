<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
                $primaryImage = asset('storage/' . $primary->image_path);
            }
        }

        return [

            'id' => $this->id,

            'category_id' => $this->category_id,

            'title' => $this->title,

            'sku' => $this->sku,

            'price' => (float) $this->price,

            'compare_price' => $this->compare_price
                ? (float) $this->compare_price
                : null,

            'category' => optional($this->category)->name,
            'category_slug' => optional($this->category)->slug,
            'category_landing_page' => optional($this->category)->landing_page,

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

'debug' => $this->images,

        ];
    }
}
