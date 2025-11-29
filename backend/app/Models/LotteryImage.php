<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LotteryImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'lottery_id',
        'image_path',
        'sort_order',
    ];

    protected $appends = ['url'];

    /**
     * Get the lottery that owns the image.
     */
    public function lottery(): BelongsTo
    {
        return $this->belongsTo(Lottery::class);
    }

    /**
     * Get the full URL for the image.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }
}

