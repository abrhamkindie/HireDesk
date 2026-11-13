<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar_url' => $this->avatar_url,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'profile' => $this->whenLoaded('profile', fn () => $this->profile ? [
                'phone' => $this->profile->phone,
                'location' => $this->profile->location,
                'headline' => $this->profile->headline,
                'bio' => $this->profile->bio,
                'resume_path' => $this->profile->resume_path,
                'linkedin_url' => $this->profile->linkedin_url,
                'portfolio_url' => $this->profile->portfolio_url,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
