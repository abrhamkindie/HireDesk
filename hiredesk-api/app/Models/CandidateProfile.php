<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateProfile extends Model
{
    protected $fillable = [
        'candidate_id',
        'phone',
        'location',
        'headline',
        'bio',
        'resume_path',
        'linkedin_url',
        'portfolio_url'
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
