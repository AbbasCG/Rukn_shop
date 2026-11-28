<?php

namespace App\Support;

use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

/**
 * Favourites. Customers with an account keep them in the database; guests
 * in a cookie (one year), which moves into the account when they log in.
 */
class Wishlist
{
    public const COOKIE = 'wishlist';

    public const COOKIE_DAYS = 365;

    public const MAX = 100;

    /** Cache key on the current request (never shared between requests). */
    private const MEMO = 'wishlist.ids';

    /** Product ids, most recently added first. @return list<int> */
    public static function ids(): array
    {
        $userId = Auth::id();
        $memo = request()->attributes->get(self::MEMO);
        if (is_array($memo) && $memo['user'] === $userId) {
            return $memo['ids'];
        }

        $ids = $userId
            ? WishlistItem::where('user_id', $userId)->latest('id')->pluck('product_id')->map(fn ($id) => (int) $id)->all()
            : self::cookieIds();
        self::remember($ids);

        return $ids;
    }

    private static function remember(?array $ids): void
    {
        if ($ids === null) {
            request()->attributes->remove(self::MEMO);
        } else {
            request()->attributes->set(self::MEMO, ['user' => Auth::id(), 'ids' => $ids]);
        }
    }

    public static function has(int $productId): bool
    {
        return in_array($productId, self::ids(), true);
    }

    public static function count(): int
    {
        return count(self::ids());
    }

    /** Add or remove; returns true when the product is now a favourite. */
    public static function toggle(Product $product): bool
    {
        $saved = ! self::has($product->id);

        if (Auth::check()) {
            if ($saved) {
                WishlistItem::firstOrCreate(['user_id' => Auth::id(), 'product_id' => $product->id]);
            } else {
                WishlistItem::where('user_id', Auth::id())->where('product_id', $product->id)->delete();
            }
            self::remember(null);
        } else {
            $ids = array_values(array_diff(self::ids(), [$product->id]));
            if ($saved) {
                array_unshift($ids, $product->id);
            }
            self::storeCookie(array_slice($ids, 0, self::MAX));
        }

        return $saved;
    }

    /** After login: the guest's favourites join the account's. */
    public static function mergeGuestInto(User $user): void
    {
        $guest = self::cookieIds();
        if (! $guest) {
            return;
        }

        $existing = Product::whereIn('id', $guest)->pluck('id');
        foreach (array_reverse($existing->all()) as $productId) {
            WishlistItem::firstOrCreate(['user_id' => $user->id, 'product_id' => $productId]);
        }

        Cookie::queue(Cookie::forget(self::COOKIE));
        request()->cookies->remove(self::COOKIE);
        self::remember(null);
    }

    /** @return list<int> */
    private static function cookieIds(): array
    {
        $raw = request()->cookie(self::COOKIE);
        $ids = is_string($raw) ? explode(',', $raw) : [];

        return array_values(array_unique(array_filter(array_map('intval', $ids), fn ($id) => $id > 0)));
    }

    private static function storeCookie(array $ids): void
    {
        self::remember($ids);
        Cookie::queue(self::COOKIE, implode(',', $ids), self::COOKIE_DAYS * 24 * 60);
        // Read back in this same request.
        request()->cookies->set(self::COOKIE, implode(',', $ids));
    }
}
