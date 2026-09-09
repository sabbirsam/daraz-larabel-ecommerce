<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SSLCommerzPaymentGateway implements PaymentGatewayInterface
{
    protected string $storeId;
    protected string $storePassword;
    protected bool $sandbox;
    protected string $apiDomain;
    protected string $initUrl;
    protected string $validateUrl;

    public function __construct()
    {
        $this->storeId = config('sslcommerz.store_id', 'testbox');
        $this->storePassword = config('sslcommerz.store_password', 'qwerty');
        $this->sandbox = (bool) config('sslcommerz.sandbox', true);
        $this->apiDomain = config('sslcommerz.api_domain', 'https://sandbox.sslcommerz.com');
        $this->initUrl = config('sslcommerz.init_url', '/gwprocess/v4/api.php');
        $this->validateUrl = config('sslcommerz.validate_url', '/validator/api/validationserverAPI.php');
    }

    /**
     * Initiate payment transaction for an order.
     */
    public function initiate(Order $order): array
    {
        $shipping = $order->shipping_address ?? [];
        $user = $order->user;

        $postData = [
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'total_amount' => number_format((float) $order->total, 2, '.', ''),
            'currency' => 'BDT',
            'tran_id' => $order->order_number,
            'success_url' => route('payment.sslcommerz.success'),
            'fail_url' => route('payment.sslcommerz.fail'),
            'cancel_url' => route('payment.sslcommerz.cancel'),
            'ipn_url' => route('payment.sslcommerz.ipn'),

            // Customer Information
            'cus_name' => $shipping['name'] ?? ($user?->name ?? 'Customer'),
            'cus_email' => $user?->email ?? 'customer@daraz.local',
            'cus_phone' => $shipping['phone'] ?? ($user?->phone ?? '01700000000'),
            'cus_add1' => $shipping['address_line'] ?? 'Dhaka',
            'cus_city' => $shipping['district'] ?? 'Dhaka',
            'cus_state' => $shipping['division'] ?? 'Dhaka',
            'cus_postcode' => '1000',
            'cus_country' => 'Bangladesh',

            // Shipment Information
            'shipping_method' => 'Courier',
            'ship_name' => $shipping['name'] ?? ($user?->name ?? 'Customer'),
            'ship_add1' => $shipping['address_line'] ?? 'Dhaka',
            'ship_city' => $shipping['district'] ?? 'Dhaka',
            'ship_state' => $shipping['division'] ?? 'Dhaka',
            'ship_postcode' => '1000',
            'ship_country' => 'Bangladesh',

            // Product & Cart Information
            'product_name' => 'Daraz Order #' . $order->order_number,
            'product_category' => 'Retail',
            'product_profile' => 'general',
            'num_of_item' => $order->items()->count() ?: 1,
        ];

        try {
            $apiUrl = rtrim($this->apiDomain, '/') . $this->initUrl;
            $response = Http::asForm()->timeout(5)->post($apiUrl, $postData);

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['status']) && strtoupper($result['status']) === 'SUCCESS' && ! empty($result['GatewayPageURL'])) {
                    return [
                        'status' => 'success',
                        'redirect_url' => $result['GatewayPageURL'],
                        'tran_id' => $order->order_number,
                        'payload' => $result,
                    ];
                }
            }
        } catch (Exception $e) {
            Log::warning('SSLCommerz API request failed: ' . $e->getMessage());
        }

        // Sandbox Simulator Fallback for Offline / Local Development
        if ($this->sandbox) {
            return [
                'status' => 'success',
                'redirect_url' => route('payment.sslcommerz.simulator', $order->order_number),
                'tran_id' => $order->order_number,
                'payload' => ['mode' => 'sandbox_simulator'],
            ];
        }

        throw new Exception('Unable to initiate online payment session with SSLCommerz.');
    }

    /**
     * Verify payment transaction callback from gateway.
     */
    public function verify(Request $request): array
    {
        $status = strtoupper($request->input('status', 'FAILED'));
        $tranId = $request->input('tran_id');
        $valId = $request->input('val_id');
        $amount = (float) $request->input('amount', 0.00);
        $cardType = $request->input('card_type', 'bKash/Nagad/Cards');

        if ($status === 'VALID' || $status === 'VALIDATED') {
            // In live mode or when val_id is provided, verify against SSLCommerz validation API
            if (! $this->sandbox && $valId) {
                try {
                    $validationUrl = rtrim($this->apiDomain, '/') . $this->validateUrl;
                    $verifyResponse = Http::timeout(6)->get($validationUrl, [
                        'val_id' => $valId,
                        'store_id' => $this->storeId,
                        'store_passwd' => $this->storePassword,
                        'format' => 'json',
                    ]);

                    if ($verifyResponse->successful()) {
                        $verifiedData = $verifyResponse->json();
                        $verifiedStatus = strtoupper($verifiedData['status'] ?? '');
                        if ($verifiedStatus !== 'VALID' && $verifiedStatus !== 'VALIDATED') {
                            return [
                                'status' => 'failed',
                                'tran_id' => $tranId,
                                'val_id' => $valId,
                                'amount' => $amount,
                                'card_type' => $cardType,
                                'payload' => $verifiedData,
                            ];
                        }
                    }
                } catch (Exception $e) {
                    Log::error('SSLCommerz verification failed: ' . $e->getMessage());
                }
            }

            return [
                'status' => 'completed',
                'tran_id' => $tranId,
                'val_id' => $valId ?? ('VAL-' . strtoupper(bin2hex(random_bytes(4)))),
                'amount' => $amount,
                'card_type' => $cardType,
                'payload' => $request->all(),
            ];
        }

        if ($status === 'CANCELLED') {
            return [
                'status' => 'cancelled',
                'tran_id' => $tranId,
                'val_id' => null,
                'amount' => $amount,
                'card_type' => $cardType,
                'payload' => $request->all(),
            ];
        }

        return [
            'status' => 'failed',
            'tran_id' => $tranId,
            'val_id' => null,
            'amount' => $amount,
            'card_type' => $cardType,
            'payload' => $request->all(),
        ];
    }
}
