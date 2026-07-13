<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [

            'id' => $this->id,

            'quantity' => $this->quantity,

            'price' => (float) $this->price,

            'subtotal' => (float) $this->subtotal,

            'product' => new ProductResource(

                $this->whenLoaded('product')

            ),

            'created_at' => $this->created_at,

        ];
    }

}