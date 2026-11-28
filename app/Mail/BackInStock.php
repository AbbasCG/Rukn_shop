<?php

namespace App\Mail;

use App\Mail\Concerns\SentInBackground;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * "It is back": sent once to a customer who asked to be told when a
 * sold-out product (or size/colour) is in stock again, in their language.
 */
class BackInStock extends Mailable implements ShouldQueue
{
    use Queueable, SentInBackground, SerializesModels;

    public function __construct(public Product $product, public ?ProductVariant $variant = null, ?string $locale = null)
    {
        $this->locale($locale ?: config('app.locale'));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('shop.contact_email'), config('app.name'))],
            subject: __('email.back_in_stock.subject', ['product' => $this->product->name], $this->locale),
        );
    }

    public function content(): Content
    {
        $variant = $this->variant?->setRelation('product', $this->product);
        $price = $variant ? $variant->effectivePrice() : $this->product->currentPrice();

        return new Content(
            view: 'emails.stock.back-in-stock',
            text: 'emails.stock.back-in-stock-text',
            with: [
                'product' => $this->product,
                'label' => $variant?->label(),
                'price' => $price,
                'productUrl' => route('products.show', ['locale' => $this->locale, 'product' => $this->product]),
                'imageUrl' => url($this->product->primaryImageUrl()),
                'isRtl' => $this->locale === 'ar',
            ],
        );
    }
}
