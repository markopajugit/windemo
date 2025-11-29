<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'notes',
        'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Get the admin who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model.
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Create an audit log entry.
     */
    public static function log(
        string $action,
        Model $auditable,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $notes = null
    ): self {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => get_class($auditable),
            'auditable_id' => $auditable->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'notes' => $notes,
            'ip_address' => request()->ip(),
        ]);
    }
}

