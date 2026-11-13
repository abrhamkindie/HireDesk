<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'status_label' => $this->status->label(),
            'status_updated_at' => $this->status_updated_at?->toIso8601String(),
            'cover_letter' => $this->cover_letter,
            'resume_path' => $this->resume_path,
            'resume_disk' => $this->resume_disk,
            'vacancy' => $this->whenLoaded('vacancy', fn () => new VacancyResource($this->vacancy)),
            'candidate' => $this->whenLoaded('candidate', fn () => new CandidateResource($this->candidate)),
            'status_history' => ApplicationStatusHistoryResource::collection(
                $this->whenLoaded('statusHistory')
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
