<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Stop "you left something in your cart" emails, from the signed link in
 * the email (no login needed). GET shows a button (link scanners in mail
 * apps only GET); POST, also used by one-click unsubscribe, turns them off.
 */
class CartReminderController extends Controller
{
    public function show(Request $request, string $locale, User $user)
    {
        return view('cart-reminders.unsubscribe', ['user' => $user, 'done' => ! $user->cart_reminders, 'action' => $request->fullUrl()]);
    }

    public function unsubscribe(Request $request, string $locale, User $user)
    {
        $user->forceFill(['cart_reminders' => false])->saveQuietly();

        return view('cart-reminders.unsubscribe', ['user' => $user, 'done' => true, 'action' => null]);
    }
}
