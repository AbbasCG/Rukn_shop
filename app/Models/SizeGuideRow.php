<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One line of the abaya size guide: a length and the body heights it fits.
 */
class SizeGuideRow extends Model
{
    protected $fillable = ['size', 'height_min', 'height_max', 'position'];

    protected $casts = [
        'height_min' => 'integer',
        'height_max' => 'integer',
        'position' => 'integer',
    ];

    public static function ordered()
    {
        return static::orderBy('position')->orderBy('id')->get();
    }

    /** The abaya length in centimetres (sizes are inches). */
    public function lengthCm(): ?int
    {
        return is_numeric($this->size) ? (int) round((float) $this->size * 2.54) : null;
    }
}
