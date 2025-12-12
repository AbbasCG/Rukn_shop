# Customer Account Area - Routes & Usage Guide

## 📋 Complete Route List

```php
// In routes/web.php - All protected by auth middleware

GET  /account                    → AccountController@index      → account.index
POST /account                    → AccountController@update     → account.update
GET  /account/orders             → AccountOrderController@index → account.orders.index
GET  /account/orders/{order}     → AccountOrderController@show  → account.orders.show
```

---

## 🔗 Using Routes in Templates

### Redirect to My Account
```blade
<a href="{{ route('account.index') }}">My Account</a>
```

### Redirect to My Orders
```blade
<a href="{{ route('account.orders.index') }}">My Orders</a>
```

### View Specific Order
```blade
<a href="{{ route('account.orders.show', $order) }}">View Order #{{ $order->id }}</a>
```

### Form POST to Update Account
```blade
<form action="{{ route('account.update') }}" method="POST">
    @csrf
    <!-- form fields -->
</form>
```

---

## 💾 Controller Method Signatures

### AccountController

```php
/**
 * Display the account page
 */
public function index()
{
    // Returns view with $user = Auth::user()
}

/**
 * Update user account information
 * 
 * Validates and updates:
 * - name
 * - email
 * - phone
 * - address_line1, address_line2
 * - postal_code, city, country
 * - customer_type (personal/business)
 * - newsletter_opt_in
 */
public function update(Request $request)
{
    // Returns back() with success message
}
```

### AccountOrderController

```php
/**
 * Display list of user's orders
 * 
 * Returns:
 * - $orders: Paginated collection of Auth::user()->orders()
 * - 10 orders per page
 */
public function index()
{
    // Returns view with $orders
}

/**
 * Display single order details
 * 
 * Security:
 * - Checks if $order->user_id === Auth::id()
 * - Returns 403 Unauthorized if not user's order
 * 
 * Returns:
 * - $order: With eager loaded items and products
 */
public function show(Order $order)
{
    // Returns view with $order
}
```

---

## 📨 Form Submission Details

### Account Update Form

**URL:** POST `/account`

**Fields:**
```php
name                    string, required, max:255
email                   email, required, unique (except own)
phone                   string, optional, max:30
address_line1           string, optional, max:255
address_line2           string, optional, max:255
postal_code             string, optional, max:20
city                    string, optional, max:100
country                 string, optional, max:100
customer_type           in:personal,business (optional)
newsletter_opt_in       boolean (optional)
```

**Validation:**
```php
[
    'name' => 'required|string|max:255',
    'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
    'phone' => 'nullable|string|max:30',
    'address_line1' => 'nullable|string|max:255',
    'address_line2' => 'nullable|string|max:255',
    'postal_code' => 'nullable|string|max:20',
    'city' => 'nullable|string|max:100',
    'country' => 'nullable|string|max:100',
    'customer_type' => 'nullable|in:personal,business',
    'newsletter_opt_in' => 'nullable|boolean',
]
```

**Response:**
- Success: Redirect back with `session('success')` message
- Errors: Redirect back with validation errors in `$errors`

---

## 🏃 Route Model Binding

The `show()` method uses implicit route model binding:

```php
// Route definition
Route::get('/account/orders/{order}', [...]);

// Controller method - $order is auto-resolved from {order} parameter
public function show(Order $order)
{
    // $order is the Order model instance from URL
}
```

---

## 🔐 Authorization Flow

### Accessing /account
```
User visits /account
  ↓
Checks: Is user logged in? (auth middleware)
  ↓
YES → Show AccountController@index with user's data
NO  → Redirect to login
```

### Accessing /account/orders
```
User visits /account/orders
  ↓
Checks: Is user logged in? (auth middleware)
  ↓
YES → Show AccountOrderController@index with user's orders
NO  → Redirect to login
```

### Accessing /account/orders/{order}
```
User visits /account/orders/123
  ↓
Checks: Is user logged in? (auth middleware)
  ↓
YES → Checks: Does order #123 belong to logged-in user?
        ├─ YES → Show order details
        └─ NO  → Return 403 Unauthorized
NO  → Redirect to login
```

---

## 📊 Data Available in Views

### In account/index.blade.php
```php
$user // Currently logged-in user object with all attributes:
// - name, email, phone
// - address_line1, address_line2, postal_code, city, country
// - customer_type, newsletter_opt_in
// - All other User model attributes
```

### In account/orders/index.blade.php
```php
$orders // Paginated collection of user's orders
// Each $order has:
// - id, user_id, status
// - total, subtotal, shipping_cost
// - payment_status, payment_method
// - created_at, updated_at
// - name, email, phone (shipping info)
// - address_line1, address_line2, postal_code, city, country

// Pagination helper:
// $orders->links() for pagination links
```

### In account/orders/show.blade.php
```php
$order // Single order with eager-loaded relations:
// - $order->items (Collection of OrderItems)
// - $order->items[0]->product (Product model)
// - All order attributes as listed above
```

---

## 🧪 Testing Examples

### Test in Blade Template
```blade
<!-- Check if user can access account -->
@auth
    <p>Welcome, {{ Auth::user()->name }}</p>
    <a href="{{ route('account.index') }}">Manage Account</a>
    <a href="{{ route('account.orders.index') }}">View Orders</a>
@else
    <p>Please login</p>
@endauth
```

### Test in Tinker
```bash
php artisan tinker

# Get a user
$user = App\Models\User::first();

# Check their orders
$user->orders()->count();

# Get orders
$user->orders()->get();

# Get specific order
$order = $user->orders()->first();
$order->items()->count();
$order->items()->with('product')->get();
```

---

## 🔄 Request/Response Flow Examples

### Update Account Flow
```
1. User visits /account
2. AccountController@index() returns view with form
3. User fills form and submits
4. POST /account receives data
5. AccountController@update() validates data
6. User model updated
7. Redirect back with success message
8. User sees "Your account has been updated successfully"
```

### View Order Flow
```
1. User visits /account/orders
2. AccountOrderController@index() loads user's orders
3. Show list of orders with status badges
4. User clicks "View Details"
5. Browser navigates to /account/orders/5
6. AccountOrderController@show($order)
7. Checks: $order->user_id === Auth::id()
8. Shows full order with items
```

---

## 🚨 Common Errors & Solutions

### "Call to undefined method orders()"
**Cause:** Pylance linting issue (not a real error)
**Solution:** It works fine at runtime, this is just the IDE being cautious

### "Undefined variable: $user"
**Cause:** Controller didn't pass $user to view
**Solution:** Check AccountController@index() is calling `compact('user')`

### "403 Unauthorized"
**Cause:** Trying to view another user's order
**Solution:** This is correct behavior! Users can only see their own orders

### "Validation error: email unique"
**Cause:** Email already exists (or not unique except own email)
**Solution:** The unique rule already allows user's own email, update to different address

### "Relation 'items' not found"
**Cause:** Trying to use $order->items() but relation not defined
**Solution:** Already added as alias in Order model - should work

---

## 📱 Responsive Design Notes

All views are mobile-responsive:
- Cards stack vertically on mobile
- Forms are single-column on mobile
- Buttons and links are easy to tap
- Tables become flexible on mobile
- Order summary switches to 2-col layout on desktop

---

## 🎨 Styling Hooks

All elements use consistent Tailwind classes:

### Color Classes
```
text-primary-dark         → Main text color (#1F1D20)
bg-primary-dark           → Main button color
border-primary-gray       → Light borders
bg-primary-gray           → Light backgrounds
```

### Card Styling
```
bg-white rounded-2xl shadow-sm border border-primary-gray/60 p-6
```

### Glass Cards
```
backdrop-blur-xl bg-white/80 rounded-3xl shadow-xl border border-white/40 p-6
```

---

## ✅ Production Checklist

Before going live:
- [ ] Test all routes work
- [ ] Test form validation
- [ ] Test authorization (can't view others' orders)
- [ ] Test on mobile devices
- [ ] Add navigation links
- [ ] Update user documentation
- [ ] Set up email notifications (if desired)
- [ ] Test with multiple users
- [ ] Check error handling

---

## 📞 Quick Reference

| Need | File | Line |
|------|------|------|
| Add route | routes/web.php | ~40-43 |
| Update validation | AccountController.php | ~19-35 |
| Customize account form | account/index.blade.php | Various |
| Change orders layout | orders/index.blade.php | Various |
| Modify order details | orders/show.blade.php | Various |
| Update colors | Any view file | Various |

---

Everything is ready to use! 🎉
