<?php

namespace App\Contracts;

interface SmsGatewayInterface
{
    /**
     * Send an SMS message to the designated mobile phone number.
     *
     * @param string $phone
     * @param string $message
     * @return bool
     */
    public function send(string $phone, string $message): bool;
}
