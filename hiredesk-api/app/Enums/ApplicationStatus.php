<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case New = 'new';
    case Screening = 'screening';
    case Shortlisted = 'shortlisted';
    case Interview = 'interview';
    case Offer = 'offer';
    case Hired = 'hired';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Screening => 'Screening',
            self::Shortlisted => 'Shortlisted',
            self::Interview => 'Interview',
            self::Offer => 'Offer',
            self::Hired => 'Hired',
            self::Rejected => 'Rejected',
            self::Withdrawn => 'Withdrawn',
        };
    }

    /**
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::New => [self::Screening, self::Rejected, self::Withdrawn],
            self::Screening => [self::Shortlisted, self::Rejected, self::Withdrawn],
            self::Shortlisted => [self::Interview, self::Rejected, self::Withdrawn],
            self::Interview => [self::Offer, self::Rejected, self::Withdrawn],
            self::Offer => [self::Hired, self::Rejected, self::Withdrawn],
            self::Hired, self::Rejected, self::Withdrawn => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    public function isFinal(): bool
    {
        return $this->allowedTransitions() === [];
    }
}
