<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\CartService;
use Illuminate\Auth\Events\Login;

class MergeCartOnLogin
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function handle(Login $event): void
    {
        $sessionId = session()->getId();

        if ($sessionId && $event->user instanceof User) {
            $this->cartService->mergeGuestCart($event->user, $sessionId);
        }
    }
}
