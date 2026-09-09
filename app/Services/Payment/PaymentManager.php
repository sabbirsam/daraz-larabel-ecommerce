<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use InvalidArgumentException;

class PaymentManager
{
    /**
     * Resolve a payment gateway instance by identifier.
     */
    public function resolve(string $gateway = 'sslcommerz'): PaymentGatewayInterface
    {
        return match (strtolower($gateway)) {
            'sslcommerz' => app(SSLCommerzPaymentGateway::class),
            default => throw new InvalidArgumentException("Unsupported payment gateway [{$gateway}]."),
        };
    }
}
