<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Items a customer sent back, the amount refunded and its credit note.
 */
class OrderReturn extends Model
{
    protected $fillable = [
        'order_id', 'credit_note_number', 'items_amount', 'shipping_amount', 'total',
        'vat_rate', 'vat_amount', 'restocked', 'reason', 'refunded_at', 'created_by',
    ];

    protected $casts = [
        'items_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'restocked' => 'boolean',
        'refunded_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(OrderReturnItem::class);
    }

    public function isRefunded(): bool
    {
        return $this->refunded_at !== null;
    }

    /** Private link to the credit note PDF (works without an account). */
    public function creditNoteUrl(?string $locale = null): string
    {
        return route('credit-notes.show', [
            'locale' => $locale ?? $this->order->locale ?? app()->getLocale(),
            'order' => $this->order_id,
            'token' => $this->order->access_token,
            'return' => $this->id,
        ]);
    }
}
