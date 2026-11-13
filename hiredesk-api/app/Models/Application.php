<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Observers\ApplicationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy(ApplicationObserver::class)]
class Application extends Model
{
    use HasFactory;

    /**
     * Status and its timestamp are intentionally not fillable: they are only
     * changed through TransitionApplicationStatus.
     */
    protected $fillable = [
        'vacancy_id',
        'candidate_id',
        'cover_letter',
        'resume_path',
        'resume_disk',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'status_updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Application $application) {
            if ($application->status === null) {
                $application->status = ApplicationStatus::New;
            }

            if ($application->status_updated_at === null) {
                $application->status_updated_at = now();
            }
        });
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class)->withTrashed();
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class)->withTrashed();
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class)->latest('id');
    }

    public function latestStatusChange(): HasOne
    {
        return $this->hasOne(ApplicationStatusHistory::class)->latestOfMany();
    }

    public function scopeWithStatus(Builder $query, ApplicationStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function scopeForVacancy(Builder $query, Vacancy|int $vacancy): Builder
    {
        return $query->where('vacancy_id', $vacancy instanceof Vacancy ? $vacancy->getKey() : $vacancy);
    }
}
