<?php

namespace App\Enums;

enum ComplaintStatus: string
{
    case Open       = 'open';
    case InProgress = 'in_progress';
    case Resolved   = 'resolved';
    case Closed     = 'closed';
    case Rejected   = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::Open       => 'Open',
            self::InProgress => 'In Progress',
            self::Resolved   => 'Resolved',
            self::Closed     => 'Closed',
            self::Rejected   => 'Rejected',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Open       => 'bg-blue-100 text-blue-800',
            self::InProgress => 'bg-yellow-100 text-yellow-800',
            self::Resolved   => 'bg-green-100 text-green-800',
            self::Closed     => 'bg-gray-100 text-gray-800',
            self::Rejected   => 'bg-red-100 text-red-800',
        };
    }
}
