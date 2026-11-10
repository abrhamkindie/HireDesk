<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Candidate extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'google_id',
        'avatar_url',
        'resume_path',
        'status',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function cast(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function statusChanges()
    {
        return $this->hasMany(ApplicationStatusHistory::class, 'changed_by');
    }

    public function profile()
    {
        return $this->hasOne(CandidateProfile::class);
    }
}
