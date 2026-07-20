<?php

namespace App\Services\Checkout;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
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

    public function checkout(User $user, string $paymentMethod, ?int $shippingAddressId = null, ?int $billingAddressId = null): Order
    {
        return DB::transaction(function () use ($user, $paymentMethod, $shippingAddressId, $billingAddressId) {

            // Load customer's cart
            $items = $user
                ->cartItems()
                ->with('product')
                ->get();

            if ($items->isEmpty()) {
                throw new HttpResponseException(
                    response()->json([
                        'message' => 'Cart is empty.',
                    ], 422)
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

                'payment_method' => $paymentMethod,
                'shipping_address_id' => $shippingAddressId,
                'billing_address_id' => $billingAddressId,

            ]);

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

            }

            return $order->load([

                'items.product.images',

                'user',

            ]);

        });
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
