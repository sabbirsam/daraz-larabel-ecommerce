<?php

namespace App\Services\Otp;

use App\Contracts\SmsGatewayInterface;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioSmsGateway implements SmsGatewayInterface
{
    public function send(string $phone, string $message): bool
    {
        $sid = config('sms.drivers.twilio.sid');
        $token = config('sms.drivers.twilio.auth_token');
        $from = config('sms.drivers.twilio.from');

        if (! $sid || ! $token || ! $from) {
            Log::warning('Twilio credentials missing. Falling back to log.');
            return app(LogSmsGateway::class)->send($phone, $message);
        }

        try {
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
            $response = Http::withBasicAuth($sid, $token)->asForm()->post($url, [
                'From' => $from,
                'To' => $phone,
                'Body' => $message,
            ]);

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Twilio SMS delivery failed: ' . $e->getMessage());
            return false;
        }
    }
}
