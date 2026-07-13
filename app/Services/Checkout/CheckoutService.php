<?php

namespace App\Services\Checkout;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Services\Inventory\InventoryService;
use App\Services\Cart\CartService;
use App\Models\OrderItem;

class CheckoutService
{
    public function __construct(

        private InventoryService $inventoryService,

        private CartService $cartService

    ) {}

    public function checkout(User $user): Order
    {
        return DB::transaction(function () use ($user) {

            // Load customer's cart
            $items = $user
                ->cartItems()
                ->with('product')
                ->get();

            // Cart must not be empty
            if ($items->isEmpty()) {

                throw new \Exception(
                    'Cart is empty.'
                );

            }

            // Verify stock availability
            foreach ($items as $item) {

                if ($item->product->stock < $item->quantity) {

                    throw new \Exception(

                        "{$item->product->title} has insufficient stock."

                    );

                }

            }

            // Calculate totals
            $subtotal = $items->sum(function ($item) {

                return $item->quantity * $item->product->price;

            });

            $shipping = 0;

            $discount = 0;

            $tax = 0;

            $grandTotal =

                $subtotal

                + $shipping

                + $tax

                - $discount;

            // Create Order
            $order = Order::create([

                'user_id' => $user->id,

                'order_number' => $this->generateOrderNumber(),

                'subtotal' => $subtotal,

                'shipping_fee' => $shipping,

                'discount' => $discount,

                'tax' => $tax,

                'grand_total' => $grandTotal,

                'payment_status' => 'UNPAID',

                'order_status' => 'PENDING',

            

            ]);

            return $order->load([

                'items.product.images',

                'user',

            ]); 

        });

        foreach ($items as $item) {

            OrderItem::create([

                'order_id' => $order->id,

                'product_id' => $item->product_id,

                'quantity' => $item->quantity,

                'price' => $item->product->price,

                'subtotal' =>

                    $item->quantity

                    * $item->product->price,

            ]);

            $this->inventoryService->adjustStock(

                $item->product,

                'STOCK_OUT',

                $item->quantity,

                $user,

                "Order {$order->order_number}"

            );

            $this->cartService->clear($user);

        }
    }

    private function generateOrderNumber(): string
    {
        return

            'CAM-'

            . now()->format('YmdHis')

            . '-'

            . random_int(1000, 9999);
    }
}