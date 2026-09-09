<?php

namespace App\Services\Otp;

use App\Contracts\SmsGatewayInterface;
use App\Models\OtpCode;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class OtpService
{
    protected SmsGatewayInterface $smsGateway;

    public function __construct()
    {
        $driver = config('sms.default', 'log');
        $this->smsGateway = match ($driver) {
            'twilio' => app(TwilioSmsGateway::class),
            default => app(LogSmsGateway::class),
        };
    }

    /**
     * Generate a new 6-digit OTP code and send it via SMS.
     *
     * @throws Exception
     */
    public function generate(string $phone, string $action = 'registration'): OtpCode
    {
        $cleanPhone = $this->normalizePhone($phone);
        $cooldown = config('sms.otp.resend_cooldown_seconds', 60);

        // Check rate limiting / resend cooldown
        $recentOtp = OtpCode::where('phone', $cleanPhone)
            ->where('action', $action)
            ->whereNull('verified_at')
            ->where('created_at', '>=', now()->subSeconds($cooldown))
            ->latest()
            ->first();

        if ($recentOtp) {
            $secondsLeft = $cooldown - now()->diffInSeconds($recentOtp->created_at);
            throw new Exception("Please wait {$secondsLeft} second(s) before requesting another code.");
        }

        // Generate 6-digit cryptographic PIN
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expiryMinutes = config('sms.otp.expiry_minutes', 5);

        // Invalidate older unverified codes for this phone & action
        OtpCode::where('phone', $cleanPhone)
            ->where('action', $action)
            ->whereNull('verified_at')
            ->update(['expires_at' => now()]);

        $otp = OtpCode::create([
            'phone' => $cleanPhone,
            'code' => $code,
            'action' => $action,
            'attempts' => 0,
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);

        // Send SMS via configured gateway
        $message = "Your Daraz verification code is: {$code}. Valid for {$expiryMinutes} minutes. Do not share this code.";
        $this->smsGateway->send($cleanPhone, $message);

        // Store last OTP code in cache for seamless dev / sandbox display
        Cache::put("demo_otp_{$cleanPhone}", $code, now()->addMinutes($expiryMinutes));

        return $otp;
    }

    /**
     * Verify an incoming OTP code against the database.
     *
     * @throws Exception
     */
    public function verify(string $phone, string $code, string $action = 'registration'): bool
    {
        $cleanPhone = $this->normalizePhone($phone);
        $cleanCode = trim($code);

        $otp = OtpCode::where('phone', $cleanPhone)
            ->where('action', $action)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $otp) {
            throw new Exception("Invalid or expired verification code. Please request a new code.");
        }

        $maxAttempts = config('sms.otp.max_attempts', 5);

        if ($otp->attempts >= $maxAttempts) {
            $otp->update(['expires_at' => now()]);
            throw new Exception("Too many failed attempts. Please request a new verification code.");
        }

        if (hash_equals((string) $otp->code, (string) $cleanCode)) {
            $otp->update(['verified_at' => now()]);

            // Update user if logged in and phone matches
            if (Auth::check()) {
                /** @var User $user */
                $user = Auth::user();
                if ($this->normalizePhone($user->phone ?? '') === $cleanPhone || empty($user->phone)) {
                    $user->update([
                        'phone' => $cleanPhone,
                        'phone_verified_at' => now(),
                    ]);
                }
            }

            Cache::forget("demo_otp_{$cleanPhone}");

            return true;
        }

        $otp->increment('attempts');
        $remaining = $maxAttempts - $otp->attempts;

        throw new Exception("Incorrect verification code. {$remaining} attempt(s) remaining.");
    }

    /**
     * Normalize mobile phone number.
     */
    public function normalizePhone(string $phone): string
    {
        $cleaned = preg_replace('/[^\d+]/', '', $phone);
        return ltrim($cleaned, '+');
    }
}
