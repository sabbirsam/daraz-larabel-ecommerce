<?php

namespace App\Services\Otp;

use App\Contracts\SmsGatewayInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LogSmsGateway implements SmsGatewayInterface
{
    public function send(string $phone, string $message): bool
    {
        Log::info("📨 [SMS GATEWAY LOG] Sent to {$phone}: {$message}");

        // Cache last OTP for quick testing and local verification
        Cache::put("last_sms_{$phone}", $message, now()->addMinutes(5));

        return true;
    }
}
