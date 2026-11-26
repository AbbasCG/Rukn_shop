<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Name of the hidden honeypot input. People never see or fill it; bots do.
     */
    public const HONEYPOT = 'website';

    public function store(Request $request): RedirectResponse
    {
        // Bots that fill the hidden field get the normal success response,
        // so they learn nothing, but nothing is stored or sent.
        if (filled($request->input(self::HONEYPOT))) {
            return back()->with('success', __('contact.flash.sent'));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        // Stored first: the message is never lost, even if email fails or is not configured.
        $message = ContactMessage::create($validated + [
            'user_id' => Auth::id(),
            'locale' => app()->getLocale(),
        ]);

        $recipient = config('shop.contact_email');
        if (filled($recipient)) {
            try {
                Mail::to($recipient)->send(new ContactMessageReceived($message));
            } catch (\Throwable $e) {
                Log::error('Contact message email could not be sent.', [
                    'contact_message_id' => $message->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return back()->with('success', __('contact.flash.sent'));
    }
}
