<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
 

class Application extends Model
{
    use HasFactory  ;
    protected $fillable = [
        'vacancy_id',
        'candidate_id',
        'cover_letter',
        'resume_path',
        'status',
        'applied_at',
        'status_updated_at',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'status_updated_at' => 'datetime',
    ];

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

public function statusHistory(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class);
    }
   
}
