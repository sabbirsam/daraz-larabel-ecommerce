<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentManager $paymentManager
    ) {}

    /**
     * Local Sandbox Payment Simulator for SSLCommerz.
     */
    public function simulator(string $orderNumber): View
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        return view('payment.simulator', compact('order'));
    }

    /**
     * SSLCommerz Success Callback.
     */
    public function success(Request $request): View
    {
        $tranId = $request->input('tran_id');
        $order = Order::where('order_number', $tranId)->firstOrFail();

        $gateway = $this->paymentManager->resolve('sslcommerz');
        $verification = $gateway->verify($request);

        $payment = Payment::updateOrCreate(
            ['order_id' => $order->id, 'gateway' => 'sslcommerz'],
            [
                'transaction_id' => $verification['val_id'] ?? $tranId,
                'amount' => $order->total,
                'currency' => 'BDT',
                'status' => $verification['status'],
                'payload' => $verification['payload'] ?? $request->all(),
            ]
        );

        if ($verification['status'] === 'completed') {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
            ]);
        }

        return view('payment.success', compact('order', 'payment', 'verification'));
    }

    /**
     * SSLCommerz Failure Callback.
     */
    public function fail(Request $request): View
    {
        $tranId = $request->input('tran_id');
        $order = Order::where('order_number', $tranId)->first();

        if ($order) {
            $order->update([
                'payment_status' => 'failed',
            ]);

            Payment::updateOrCreate(
                ['order_id' => $order->id, 'gateway' => 'sslcommerz'],
                [
                    'transaction_id' => $request->input('val_id') ?? $tranId,
                    'amount' => $order->total,
                    'currency' => 'BDT',
                    'status' => 'failed',
                    'payload' => $request->all(),
                ]
            );
        }

        return view('payment.fail', compact('order', 'tranId'));
    }

    /**
     * SSLCommerz Cancellation Callback.
     */
    public function cancel(Request $request): View
    {
        $tranId = $request->input('tran_id');
        $order = Order::where('order_number', $tranId)->first();

        if ($order) {
            $order->update([
                'payment_status' => 'cancelled',
            ]);

            Payment::updateOrCreate(
                ['order_id' => $order->id, 'gateway' => 'sslcommerz'],
                [
                    'transaction_id' => $request->input('val_id') ?? $tranId,
                    'amount' => $order->total,
                    'currency' => 'BDT',
                    'status' => 'cancelled',
                    'payload' => $request->all(),
                ]
            );
        }

        return view('payment.cancel', compact('order', 'tranId'));
    }

    /**
     * SSLCommerz Asynchronous IPN (Instant Payment Notification) Webhook.
     */
    public function ipn(Request $request): Response
    {
        $tranId = $request->input('tran_id');
        $order = Order::where('order_number', $tranId)->first();

        if (! $order) {
            return response('Order not found', 404);
        }

        $gateway = $this->paymentManager->resolve('sslcommerz');
        $verification = $gateway->verify($request);

        Payment::updateOrCreate(
            ['order_id' => $order->id, 'gateway' => 'sslcommerz'],
            [
                'transaction_id' => $verification['val_id'] ?? $tranId,
                'amount' => $order->total,
                'currency' => 'BDT',
                'status' => $verification['status'],
                'payload' => $verification['payload'] ?? $request->all(),
            ]
        );

        if ($verification['status'] === 'completed') {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
            ]);
        }

        return response('IPN Processed Successfully', 200);
    }
}
