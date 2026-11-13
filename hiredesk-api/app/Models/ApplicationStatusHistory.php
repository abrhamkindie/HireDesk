<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

class ApplicationStatusHistory extends Model
{
    use HasFactory;

    /**
     * Audit rows only ever carry a creation timestamp.
     */
    public const UPDATED_AT = null;

    protected $table = 'application_status_histories';

    protected $fillable = [
        'application_id',
        'old_status',
        'new_status',
        'actor_type',
        'actor_id',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'old_status' => ApplicationStatus::class,
            'new_status' => ApplicationStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('Application status history is append-only.');
        });

        static::deleting(function () {
            throw new LogicException('Application status history is append-only.');
        });
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }
}
