<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'name_translations',
        'slug',
        'description',
        'is_active',
    ];
    
    protected $casts = [
        'name_translations' => 'array',
    ];

    /**
     * The category name in the active locale, falling back to the base name.
     */
    public function getDisplayNameAttribute(): string
    {
        $translated = $this->name_translations[app()->getLocale()] ?? null;

        return filled($translated) ? $translated : (string) $this->name;
    }

    public function products() 
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    
}
