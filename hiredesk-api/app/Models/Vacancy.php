<?php

namespace App\Models;

use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use App\Enums\WorkplaceType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Vacancy extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'requirements',
        'employment_type',
        'workplace_type',
        'location',
        'salary_min',
        'salary_max',
        'deadline',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'employment_type' => EmploymentType::class,
            'workplace_type' => WorkplaceType::class,
            'status' => VacancyStatus::class,
            'deadline' => 'date',
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Vacancy $vacancy) {
            if (blank($vacancy->slug)) {
                $vacancy->slug = static::uniqueSlug($vacancy->title);
            }
        });

        static::saving(function (Vacancy $vacancy) {
            if ($vacancy->status === VacancyStatus::Open && $vacancy->published_at === null) {
                $vacancy->published_at = now();
            }
        });
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(Candidate::class, 'applications')
            ->withPivot(['status', 'cover_letter', 'resume_path', 'created_at'])
            ->withTimestamps();
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', VacancyStatus::Open->value)
            ->where(fn (Builder $query) => $query
                ->whereNull('deadline')
                ->orWhereDate('deadline', '>=', today()));
    }

    protected static function uniqueSlug(string $title): string
    {
        $base = Str::limit(Str::slug($title) ?: 'vacancy', 200, '');
        $slug = $base;
        $suffix = 2;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
