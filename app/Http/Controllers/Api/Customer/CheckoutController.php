<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Services\Checkout\CheckoutService;

class CheckoutController extends Controller
{
    public function __construct(

        private CheckoutService $checkoutService

    ) {}

    public function store(
        CheckoutRequest $request
    )
    {
        $order = $this->checkoutService->checkout(

            $request->user()

        );

        return response()->json([

            'success' => true,

            'message' => 'Checkout completed.',

            'data' => new OrderResource(

                $order

            )

        ], 201);
    }
}