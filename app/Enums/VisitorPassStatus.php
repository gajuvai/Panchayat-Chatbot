<?php

namespace App\Enums;

enum VisitorPassStatus: string
{
    case Pending    = 'pending';
    case Approved   = 'approved';
    case CheckedIn  = 'checked_in';
    case CheckedOut = 'checked_out';
    case Expired    = 'expired';
    case Cancelled  = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending    => 'Pending',
            self::Approved   => 'Approved',
            self::CheckedIn  => 'Checked In',
            self::CheckedOut => 'Checked Out',
            self::Expired    => 'Expired',
            self::Cancelled  => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Pending    => 'bg-yellow-100 text-yellow-700',
            self::Approved   => 'bg-blue-100 text-blue-700',
            self::CheckedIn  => 'bg-green-100 text-green-700',
            self::CheckedOut => 'bg-gray-100 text-gray-600',
            self::Expired    => 'bg-red-100 text-red-600',
            self::Cancelled  => 'bg-gray-100 text-gray-400',
        };
    }
}
