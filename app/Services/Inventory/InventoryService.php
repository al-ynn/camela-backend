<?php

namespace App\Services\Inventory;

use App\Models\Product;
use App\Models\InventoryTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function adjustStock(
        Product $product,
        string $type,
        int $quantity,
        ?User $user = null,
        ?string $remarks = null
    ): InventoryTransaction
    {
        return DB::transaction(function () use (
            $product,
            $type,
            $quantity,
            $user,
            $remarks
        ) {

            $before = $product->stock;

            switch ($type) {

                case 'STOCK_IN':

                    $after = $before + $quantity;

                    break;

                case 'STOCK_OUT':

                case 'SALE':

                    $after = max(0, $before - $quantity);

                    break;

                case 'RETURN':

                    $after = $before + $quantity;

                    break;

                case 'ADJUSTMENT':

                    $after = $quantity;

                    break;

                default:

                    throw new \Exception('Invalid inventory transaction type.');
            }

            $product->update([

                'stock' => $after

            ]);

            return InventoryTransaction::create([

                'product_id' => $product->id,

                'user_id' => $user?->id,

                'type' => $type,

                'quantity' => $quantity,

                'stock_before' => $before,

                'stock_after' => $after,

                'remarks' => $remarks,

            ]);
        });
    }
}