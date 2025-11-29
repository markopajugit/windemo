<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Lottery extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'product_value',
        'ticket_price',
        'total_tickets',
        'charity_percentage',
        'starts_at',
        'ends_at',
        'status',
        'winner_user_id',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'product_value' => 'decimal:2',
        'ticket_price' => 'decimal:2',
        'charity_percentage' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'approved_at' => 'datetime',
        'total_tickets' => 'integer',
    ];

    protected $appends = ['tickets_sold'];

    /**
     * Get the user who created the lottery.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the winner of the lottery.
     */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    /**
     * Get the admin who approved the lottery.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the images for the lottery.
     */
    public function images(): HasMany
    {
        return $this->hasMany(LotteryImage::class)->orderBy('sort_order');
    }

    /**
     * Get the tickets for the lottery.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the audit logs for this lottery.
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * Get the number of tickets sold.
     */
    public function getTicketsSoldAttribute(): int
    {
        return $this->tickets()->count();
    }

    /**
     * Get the next available ticket number.
     */
    public function getNextTicketNumber(): int
    {
        return ($this->tickets()->max('ticket_number') ?? 0) + 1;
    }

    /**
     * Check if lottery is active and can accept ticket purchases.
     */
    public function canPurchaseTickets(): bool
    {
        return $this->status === 'active'
            && now()->between($this->starts_at, $this->ends_at)
            && $this->tickets_sold < $this->total_tickets;
    }

    /**
     * Scope for active lotteries.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('ends_at', '>', now());
    }

    /**
     * Scope for lotteries that have ended and need winner selection.
     */
    public function scopeNeedsWinnerSelection($query)
    {
        return $query->where('status', 'active')
            ->where('ends_at', '<=', now())
            ->whereNull('winner_user_id');
    }

    /**
     * Scope for popular lotteries (most tickets sold).
     */
    public function scopePopular($query)
    {
        return $query->active()
            ->withCount('tickets')
            ->orderByDesc('tickets_count');
    }
}

