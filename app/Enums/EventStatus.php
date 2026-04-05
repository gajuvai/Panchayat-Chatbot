<?php

namespace App\Enums;

enum EventStatus: string
{
    case Upcoming  = 'upcoming';
    case Ongoing   = 'ongoing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Upcoming  => 'Upcoming',
            self::Ongoing   => 'Ongoing',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Upcoming  => 'bg-blue-100 text-blue-700',
            self::Ongoing   => 'bg-green-100 text-green-700',
            self::Completed => 'bg-gray-100 text-gray-500',
            self::Cancelled => 'bg-red-100 text-red-600',
        };
    }
}
