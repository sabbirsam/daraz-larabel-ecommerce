<?php

namespace App\Contracts;

use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Initiate payment transaction for an order.
     *
     * @param Order $order
     * @return array{status: string, redirect_url: string, tran_id: string, payload?: array}
     */
    public function initiate(Order $order): array;

    /**
     * Verify payment transaction callback from gateway.
     *
     * @param Request $request
     * @return array{status: string, tran_id: string, val_id?: ?string, amount: float, card_type?: ?string, payload: array}
     */
    public function verify(Request $request): array;
}
