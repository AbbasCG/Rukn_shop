# Customer Account Area - Code Examples & Templates

## 🔗 Navigation Menu Integration

### Add to Main Navigation

In `resources/views/layouts/navigation.blade.php`, add:

```blade
<!-- Customer Account Links -->
@auth
    <div class="relative group">
        <button class="text-primary-dark hover:text-primary-dark/70 font-semibold transition">
            {{ Auth::user()->name }}
            <svg class="w-4 h-4 inline-block ml-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
        <div class="absolute hidden group-hover:block right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-primary-gray">
            <a href="{{ route('account.index') }}" class="block px-4 py-2 text-primary-dark hover:bg-primary-gray">
                My Account
            </a>
            <a href="{{ route('account.orders.index') }}" class="block px-4 py-2 text-primary-dark hover:bg-primary-gray">
                My Orders
            </a>
            <hr class="my-2 border-primary-gray">
            <form method="POST" action="{{ route('logout') }}" class="block">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 text-primary-dark hover:bg-primary-gray">
                    Logout
                </button>
            </form>
        </div>
    </div>
@else
    <a href="{{ route('login') }}" class="text-primary-dark hover:text-primary-dark/70 font-semibold transition">
        Login
    </a>
@endauth
```

---

## 📦 Dashboard Widget

Show recent orders on a customer dashboard:

```blade
<!-- resources/views/dashboard.blade.php -->
@auth
    <div class="max-w-5xl mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-primary-dark mb-8" style="font-family: 'Signika', sans-serif;">
            Welcome, {{ Auth::user()->name }}!
        </h1>

        <!-- Quick Links -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <a href="{{ route('account.index') }}" class="bg-white rounded-2xl shadow-sm p-6 border border-primary-gray/60 hover:shadow-md transition">
                <h3 class="text-xl font-bold text-primary-dark mb-2">My Account</h3>
                <p class="text-neutral-600">Manage profile & address</p>
            </a>

            <a href="{{ route('account.orders.index') }}" class="bg-white rounded-2xl shadow-sm p-6 border border-primary-gray/60 hover:shadow-md transition">
                <h3 class="text-xl font-bold text-primary-dark mb-2">My Orders</h3>
                <p class="text-neutral-600">View order history</p>
            </a>

            <a href="{{ route('products.index') }}" class="bg-white rounded-2xl shadow-sm p-6 border border-primary-gray/60 hover:shadow-md transition">
                <h3 class="text-xl font-bold text-primary-dark mb-2">Continue Shopping</h3>
                <p class="text-neutral-600">Browse products</p>
            </a>
        </div>

        <!-- Recent Orders -->
        <h2 class="text-2xl font-bold text-primary-dark mb-4" style="font-family: 'Signika', sans-serif;">
            Recent Orders
        </h2>
        @php
            $recentOrders = Auth::user()->orders()->latest()->take(5)->get();
        @endphp

        @if($recentOrders->isEmpty())
            <p class="text-neutral-600">No orders yet. <a href="{{ route('products.index') }}" class="text-primary-dark font-semibold hover:underline">Start shopping</a></p>
        @else
            <div class="space-y-3">
                @foreach($recentOrders as $order)
                    <a href="{{ route('account.orders.show', $order) }}" class="block bg-white rounded-xl p-4 border border-primary-gray/60 hover:shadow-md transition">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-semibold text-primary-dark">Order #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</p>
                                <p class="text-sm text-neutral-600">{{ $order->created_at->format('F d, Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-primary-dark">€{{ number_format($order->total, 2) }}</p>
                                <p class="text-sm text-neutral-600">{{ ucfirst($order->status) }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@else
    <div class="text-center py-16">
        <p class="text-lg text-neutral-600 mb-4">Please login to view your account</p>
        <a href="{{ route('login') }}" class="inline-block px-6 py-3 bg-primary-dark text-white rounded-xl font-semibold hover:bg-primary-dark/90 transition">
            Login Now
        </a>
    </div>
@endauth
```

---

## 🛒 Post-Checkout Redirect

In `CheckoutController@confirmation()`:

```php
public function confirmation(Order $order)
{
    // Verify it's the user's order
    if ($order->user_id !== Auth::id()) {
        abort(403);
    }

    return view('checkout.confirmation', compact('order'));
}
```

Then in the confirmation view, link to:

```blade
<p>You can view your order details anytime in <a href="{{ route('account.orders.show', $order) }}" class="text-primary-dark font-semibold hover:underline">My Orders</a>.</p>
```

---

## 📧 Order Status Email

Create a notification in `app/Notifications/OrderStatusUpdated.php`:

```php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderStatusUpdated extends Notification
{
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Order #{$this->order->id} Status: " . ucfirst($this->order->status))
            ->line("Your order status has been updated to: " . ucfirst($this->order->status))
            ->action('View Order Details', route('account.orders.show', $this->order))
            ->line('Thank you for your order!');
    }
}
```

Then notify the user when status changes:

```php
// In your order status update logic (Admin)
$order->status = $request->status;
$order->save();

$order->user->notify(new OrderStatusUpdated($order));
```

---

## 🔍 Admin Order Link

In admin order view, link to customer view:

```blade
<!-- resources/views/admin/orders/show.blade.php -->
<a href="{{ route('account.orders.show', $order) }}" target="_blank" class="text-blue-600 hover:underline">
    View Customer View
</a>
```

---

## 💰 Order Total Calculation

Helper function for consistent order calculations:

```php
// app/Helpers/OrderHelper.php

namespace App\Helpers;

use App\Models\Order;

class OrderHelper
{
    public static function calculateOrderTotal(Order $order)
    {
        return $order->items->sum(function($item) {
            return $item->price * $item->quantity;
        });
    }

    public static function formatPrice($price)
    {
        return '€' . number_format($price, 2, ',', '.');
    }
}
```

Usage in views:

```blade
{{ \App\Helpers\OrderHelper::formatPrice($order->total) }}
```

---

## 🚀 Blade Components

Create reusable order card component:

```blade
<!-- resources/views/components/order-card.blade.php -->
@props(['order'])

<div class="bg-white rounded-2xl shadow-sm border border-primary-gray/60 p-4 md:p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 hover:shadow-md transition">
    <div class="flex-1">
        <h3 class="text-lg font-bold text-primary-dark">Order #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</h3>
        <p class="text-sm text-neutral-600">{{ $order->created_at->format('F d, Y') }}</p>
        <p class="text-sm text-neutral-700 mt-2"><span class="font-medium">Total:</span> €{{ number_format($order->total, 2) }}</p>
    </div>

    <div class="flex items-center gap-4">
        @php
            $statusColors = [
                'pending' => 'bg-amber-50 text-amber-800',
                'paid' => 'bg-blue-50 text-blue-800',
                'processing' => 'bg-blue-50 text-blue-800',
                'shipped' => 'bg-emerald-50 text-emerald-800',
                'delivered' => 'bg-green-100 text-green-800',
                'cancelled' => 'bg-rose-50 text-rose-800',
            ];
        @endphp

        <span class="inline-block px-4 py-1.5 rounded-full text-sm font-semibold {{ $statusColors[$order->status] ?? 'bg-gray-50 text-gray-800' }}">
            {{ ucfirst($order->status) }}
        </span>

        <a href="{{ route('account.orders.show', $order) }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl border border-neutral-300 text-neutral-800 hover:bg-neutral-100 transition">
            View
        </a>
    </div>
</div>
```

Use in blade:

```blade
<x-order-card :order="$order" />
```

---

## 📊 Admin Statistics

Show customer account stats in admin dashboard:

```blade
<!-- resources/views/admin/dashboard.blade.php -->
@php
    $totalCustomers = \App\Models\User::where('role', 'user')->count();
    $totalOrders = \App\Models\Order::count();
    $activeOrders = \App\Models\Order::whereIn('status', ['pending', 'processing', 'shipped'])->count();
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-primary-gray/60">
        <h3 class="text-neutral-600 font-semibold">Total Customers</h3>
        <p class="text-4xl font-bold text-primary-dark mt-2">{{ $totalCustomers }}</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 border border-primary-gray/60">
        <h3 class="text-neutral-600 font-semibold">Total Orders</h3>
        <p class="text-4xl font-bold text-primary-dark mt-2">{{ $totalOrders }}</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 border border-primary-gray/60">
        <h3 class="text-neutral-600 font-semibold">Active Orders</h3>
        <p class="text-4xl font-bold text-primary-dark mt-2">{{ $activeOrders }}</p>
    </div>
</div>
```

---

## 🔔 Status Update Form

Simple form to update order status:

```blade
<!-- In admin order edit view -->
<form action="{{ route('admin.orders.update', $order) }}" method="POST">
    @csrf
    @method('PATCH')

    <div class="mb-4">
        <label class="block text-sm font-medium text-primary-dark mb-2">Order Status</label>
        <select name="status" class="w-full rounded-xl border border-neutral-300 px-4 py-2.5">
            @foreach(\App\Models\Order::statuses() as $status)
                <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-primary-dark mb-2">Order Notes</label>
        <textarea name="notes" rows="4" class="w-full rounded-xl border border-neutral-300 px-4 py-2.5">{{ $order->notes }}</textarea>
    </div>

    <button type="submit" class="px-6 py-2.5 bg-primary-dark text-white rounded-xl font-semibold hover:bg-primary-dark/90 transition">
        Update Order
    </button>
</form>
```

---

## 🎁 Promo/Marketing

Add seasonal promotions to customer views:

```blade
<!-- resources/views/account/index.blade.php - Add before closing div -->

<!-- Promotional Banner -->
<div class="bg-gradient-to-r from-amber-400 to-orange-400 rounded-2xl p-6 text-white mt-12">
    <h2 class="text-2xl font-bold mb-2" style="font-family: 'Signika', sans-serif;">Special Offer!</h2>
    <p class="mb-4">Get 10% off your next order. Use code: LOYAL10</p>
    <a href="{{ route('products.index') }}" class="inline-block px-6 py-2 bg-white text-orange-600 font-semibold rounded-xl hover:bg-orange-50 transition">
        Shop Now
    </a>
</div>
```

---

## 🔗 URL Slug Formatting

Format order numbers consistently:

```php
// In a Model or Helper
public function getFormattedIdAttribute()
{
    return str_pad($this->id, 6, '0', STR_PAD_LEFT);
}
```

Then use in views:

```blade
Order #{{ $order->formatted_id }}
```

---

## 📱 Mobile Menu Integration

For mobile navigation:

```blade
<!-- In mobile menu section -->
@auth
    <div class="border-t border-primary-gray pt-4">
        <a href="{{ route('account.index') }}" class="block px-4 py-2 text-primary-dark hover:bg-primary-gray rounded-lg">
            My Account
        </a>
        <a href="{{ route('account.orders.index') }}" class="block px-4 py-2 text-primary-dark hover:bg-primary-gray rounded-lg">
            My Orders
        </a>
    </div>
@endauth
```

---

All examples are ready to copy-paste! 🚀
