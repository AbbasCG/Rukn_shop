<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * A contact-form message, emailed to the store. Reply-To is the sender.
 */
class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage)
    {
        $this->locale(config('shop.order_notifications.locale') ?: config('app.locale'));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->contactMessage->email, $this->contactMessage->name)],
            subject: __('email.contact.subject', [
                'name' => $this->contactMessage->name,
                'subject' => $this->contactMessage->subject ?: __('email.contact.no_subject', [], $this->locale),
            ], $this->locale),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact.message',
            text: 'emails.contact.message-text',
            with: [
                'contactMessage' => $this->contactMessage,
                'adminUrl' => route('admin.messages.index', ['locale' => $this->locale]),
                'isRtl' => $this->locale === 'ar',
            ],
        );
    }
}
