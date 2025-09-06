<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cookie;

class MergeGuestCart
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function handle(Login $event)
    {
        $user = $event->user;
        $uuid = Cookie::get('guest_cart_uuid');
        if (! $uuid) return;

        $guest = \App\Models\Cart::where('uuid', $uuid)->first();
        if (! $guest) return;

        $this->cartService->mergeGuestCartToUser($guest, $user);
        // clear cookie in response — can't access response here; frontend should clear
    }
}
