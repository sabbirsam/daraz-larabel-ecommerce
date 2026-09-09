<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Otp\OtpService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PhoneVerificationController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Display the phone verification view.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user && $user->phone_verified_at) {
            return redirect()->intended(route('dashboard'))->with('info', 'Your phone number is already verified.');
        }

        $phone = $user?->phone ?? session('verify_phone');

        if (! $phone) {
            return redirect()->route('profile.edit')->with('warning', 'Please add a mobile number to verify.');
        }

        $demoOtp = Cache::get("demo_otp_{$this->otpService->normalizePhone($phone)}");

        return view('auth.verify-phone', compact('phone', 'demoOtp'));
    }

    /**
     * Verify the submitted OTP code.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ]);

        $user = Auth::user();
        $phone = $user?->phone ?? session('verify_phone');

        if (! $phone) {
            return redirect()->route('register')->with('error', 'No mobile number found for verification.');
        }

        try {
            $this->otpService->verify($phone, $request->input('code'), 'registration');

            if ($user) {
                $user->update(['phone_verified_at' => now()]);
            }

            session()->forget('verify_phone');

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Mobile phone verified successfully! Welcome to Daraz.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Resend a new OTP code.
     */
    public function resend(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $phone = $user?->phone ?? session('verify_phone');

        if (! $phone) {
            return back()->with('error', 'Mobile number not found.');
        }

        try {
            $this->otpService->generate($phone, 'registration');
            return back()->with('success', 'A fresh 6-digit code has been dispatched to your mobile number.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
