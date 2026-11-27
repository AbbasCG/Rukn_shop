<?php

namespace App\Support;

use App\Models\Coupon;

/**
 * Shipping and total for a cart subtotal, including the discount code the
 * customer entered (kept in the session). Used by the cart page, checkout
 * and order creation, so they always agree.
 */
class CartTotals
{
    public const SESSION_KEY = 'cart.coupon';

    public function __construct(
        public readonly float $subtotal,
        public readonly float $shipping,
        public readonly float $total,
        /** Shipping the order would cost without the code (what it saves). */
        public readonly float $discount,
        public readonly ?Coupon $coupon,
        /** The entered code exists and is active, but does not apply yet (minimum). */
        public readonly bool $couponBelowMinimum,
    ) {}

    public static function for(float $subtotal): self
    {
        $regularShipping = Shipping::cost($subtotal);
        $coupon = self::enteredCoupon();
        $applies = $coupon !== null && $subtotal > 0 && $coupon->meetsMinimum($subtotal);

        $shipping = $applies ? 0.0 : $regularShipping;

        return new self(
            subtotal: $subtotal,
            shipping: $shipping,
            total: round($subtotal + $shipping, 2),
            discount: $applies ? $regularShipping : 0.0,
            coupon: $coupon,
            couponBelowMinimum: $coupon !== null && ! $applies,
        );
    }

    /** True when a code is applied and saves something or keeps shipping free. */
    public function couponApplies(): bool
    {
        return $this->coupon !== null && ! $this->couponBelowMinimum;
    }

    /** The active code stored in the session, if any (an inactive or deleted one is dropped). */
    public static function enteredCoupon(): ?Coupon
    {
        $code = session(self::SESSION_KEY);
        if (! $code) {
            return null;
        }

        $coupon = Coupon::findByCode($code);
        if (! $coupon || ! $coupon->is_active) {
            session()->forget(self::SESSION_KEY);

            return null;
        }

        return $coupon;
    }
}
