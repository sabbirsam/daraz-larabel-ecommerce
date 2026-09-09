@extends('layouts.storefront')

@section('title', 'Verify Mobile Number - Daraz Online Shopping')

@section('content')
<div class="min-h-[70vh] flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8">

    <div class="max-w-md w-full bg-white rounded-lg shadow-sm border border-gray-100 p-8 text-center"
         x-data="{
             countdown: 60,
             canResend: false,
             init() {
                 let timer = setInterval(() => {
                     if (this.countdown > 0) {
                         this.countdown--;
                     } else {
                         this.canResend = true;
                         clearInterval(timer);
                     }
                 }, 1000);
             }
         }">

        <!-- Header Icon -->
        <div class="w-16 h-16 rounded-full bg-orange-100 text-daraz flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
        </div>

        <h2 class="text-xl font-black text-gray-900 mb-1">Verify Mobile Number</h2>
        <p class="text-xs text-gray-500 mb-6">
            We sent a 6-digit verification code via SMS to <br>
            <span class="font-bold text-gray-800 text-sm font-mono">{{ $phone }}</span>
        </p>

        <!-- Feedback Alerts -->
        @if(session('error'))
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded text-xs text-left">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-3 rounded text-xs text-left">
                {{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-4 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 p-3 rounded text-xs text-left">
                {{ session('warning') }}
            </div>
        @endif

        <!-- Demo Helper Badge (In Sandbox / Dev) -->
        @if(!empty($demoOtp))
            <div class="mb-5 p-3 bg-amber-50 border border-amber-200 rounded text-xs text-amber-800 flex items-center justify-between">
                <span>⚡ Sandbox Demo Code:</span>
                <span class="font-mono font-black text-sm bg-white px-2 py-0.5 rounded border border-amber-300 tracking-widest">{{ $demoOtp }}</span>
            </div>
        @endif

        <!-- OTP Input Form -->
        <form action="{{ route('verification.phone.verify') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="code" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                    Enter 6-Digit Code
                </label>
                <input type="text" 
                       id="code" 
                       name="code" 
                       maxlength="6" 
                       pattern="\d{6}" 
                       inputmode="numeric" 
                       required 
                       autofocus
                       placeholder="••••••"
                       class="w-full text-center tracking-[0.5em] text-2xl font-black font-mono border-gray-300 rounded-lg py-3 focus:ring-daraz focus:border-daraz">
                @error('code')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" 
                    class="w-full py-3.5 bg-daraz hover:bg-daraz-hover text-white text-xs font-black uppercase tracking-wider rounded-lg shadow-md transition-all active:scale-[0.99]">
                Verify Mobile Number
            </button>
        </form>

        <!-- Resend Code Timer -->
        <div class="mt-6 pt-4 border-t border-gray-100 text-xs text-gray-500 flex items-center justify-center space-x-2">
            <span>Didn't receive the SMS?</span>
            
            <form action="{{ route('verification.phone.resend') }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        :disabled="!canResend" 
                        class="font-bold text-daraz disabled:text-gray-400 disabled:cursor-not-allowed hover:underline">
                    <span x-show="!canResend">Resend in <span x-text="countdown"></span>s</span>
                    <span x-show="canResend">Resend Code</span>
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
