<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'lottery_id',
        'user_id',
        'ticket_number',
        'purchased_at',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'ticket_number' => 'integer',
    ];

    /**
     * Get the lottery for this ticket.
     */
    public function lottery(): BelongsTo
    {
        return $this->belongsTo(Lottery::class);
    }

    /**
     * Get the user who owns this ticket.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

