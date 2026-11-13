<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateProfile extends Model
{
    use HasFactory;

    /**
     * candidate_id is deliberately not fillable: profiles are always created
     * through the candidate relation so one candidate cannot claim another's.
     */
    protected $fillable = [
        'phone',
        'location',
        'headline',
        'bio',
        'resume_path',
        'linkedin_url',
        'portfolio_url',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
