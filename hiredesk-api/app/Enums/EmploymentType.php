<?php

namespace App\Enums;

enum EmploymentType: string
{
    case FullTime = 'fulltime';
    case PartTime = 'parttime';
    case Contract = 'contract';
    case Internship = 'internship';

    public function label(): string
    {
        return match ($this) {
            self::FullTime => 'Full time',
            self::PartTime => 'Part time',
            self::Contract => 'Contract',
            self::Internship => 'Internship',
        };
    }
}
