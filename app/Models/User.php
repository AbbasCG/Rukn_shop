<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Cart;
use App\Models\Review;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'address',
        'address_line1',
        'address_line2',
        'postal_code',
        'city',
        'country',
        'customer_type',
        'newsletter_opt_in',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'newsletter_opt_in' => 'boolean',
        ];
    }

    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Check if the user has admin role.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Profile photo URL on the media disk (local or R2), or null.
     */
    public function avatarUrl(): ?string
    {
        return \App\Support\Media::url($this->avatar);
    }

    /**
     * Branded, localised reset email instead of Laravel's default
     * notification (English, left-to-right).
     */
    public function sendPasswordResetNotification($token): void
    {
        \Illuminate\Support\Facades\Mail::to($this->email, $this->name)
            ->send(new \App\Mail\PasswordResetEmail($this, $token, app()->getLocale()));
    }
}
