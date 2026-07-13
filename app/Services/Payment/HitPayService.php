<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;


class HitPayService
{
    /**
     * Create Payment
     */
    public function createPayment(User $user)
    {
        $order = Order::where('user_id', $user->id)
            ->where('payment_status', 'UNPAID')
            ->latest()
            ->first();

        if (!$order) {

            return response()->json([

                'message' => 'No pending order found.'

            ], 404);

        }

        /*
        |--------------------------------------------------------------------------
        | Temporary until client gives HitPay credentials
        |--------------------------------------------------------------------------
        */

        if (
            empty(config('services.hitpay.api_key'))
        ) {

            return response()->json([

                'message' => 'HitPay is not configured yet.',

                'order' => $order->order_number,

                'amount' => $order->grand_total

            ]);

        }

         /*
        |--------------------------------------------------------------------------
        | HitPay Payload
        |--------------------------------------------------------------------------
        */

        $payload = [

            'amount' => $order->grand_total,

            'currency' => 'PHP',

            'email' => $user->email,

            'reference_number' => $order->order_number,

            'name' => $user->name,

            'redirect_url' => url('/api/payments/callback'),

            'webhook' => url('/api/payments/webhook'),

        ];

        /*
        |--------------------------------------------------------------------------
        | Send Request to HitPay
        |--------------------------------------------------------------------------
        */

        $response = Http::withToken(

            config('services.hitpay.api_key')

        )->post(

            config('services.hitpay.base_url') . '/v1/payment-requests',

            $payload

        );

        if (!$response->successful()) {

            return response()->json([

                'message' => 'Unable to create payment.',

                'error' => $response->json(),

            ], 500);

        }

        return response()->json([

            'payment_url' => $response['url'],

            'payment_id' => $response['id'],

        ]);
    }
    

    /**
     * Webhook
     */
    public function handleWebhook(Request $request)
    {

        if (!$this->verifyWebhook($request)) {

            abort(403, 'Invalid webhook signature.');

        }
        /*
        |--------------------------------------------------------------------------
        | Get Payload
        |--------------------------------------------------------------------------
        */

        $payload = $request->all();

        /*
        |--------------------------------------------------------------------------
        | Find Order
        |--------------------------------------------------------------------------
        */

        $order = Order::where(

            'order_number',

            $payload['reference_number'] ?? ''

        )->first();

        if (!$order) {

            return response()->json([

                'message' => 'Order not found.'

            ], 404);

        }

        /*
        |--------------------------------------------------------------------------
        | Update Status
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use ($order, $payload) {

            switch ($payload['status'] ?? '') {

                case 'completed':

                    $order->update([

                        'payment_status' => 'PAID',

                        'order_status' => 'PROCESSING',

                        'payment_reference' => $payload['payment_id'] ?? null,

                        'paid_at' => now(),

                    ]);

                    break;

                case 'failed':

                    $order->update([

                        'payment_status' => 'FAILED',

                    ]);

                    break;

                case 'cancelled':

                    $order->update([

                        'payment_status' => 'FAILED',

                    ]);

                    break;
            }

        });

        return response()->json([

            'success' => true

        ]);
    }

    /**
     * Callback
     */
    public function handleCallback(Request $request)
    {
        $reference = $request->get('reference_number');

        if (!$reference) {

            return response()->json([

                'message' => 'Missing reference number.'

            ], 400);

        }

        $order = Order::where(

            'order_number',

            $reference

        )->first();

        if (!$order) {

            return response()->json([

                'message' => 'Order not found.'

            ], 404);

        }

        return response()->json([

            'message' => 'Payment callback received.',

            'payment_status' => $order->payment_status,

            'order_status' => $order->order_status,

            'order_number' => $order->order_number,

        ]);
    }

    private function verifyWebhook(
        Request $request
    ): bool {

        $salt = config('services.hitpay.salt');

        if (!$salt) {

            /*
            |--------------------------------------------------------------------------
            | Development
            |--------------------------------------------------------------------------
            */

            return true;

        }

        $signature = $request->header('Hmac');

        if (!$signature) {

            return false;

        }

        $payload = $request->getContent();

        $expected = hash_hmac(

            'sha256',

            $payload,

            $salt

        );

        return hash_equals(

            $expected,

            $signature

        );
    }
}