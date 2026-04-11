<?php

namespace App\Enums;

enum MaintenanceStatus: string
{
    case Pending    = 'pending';
    case Approved   = 'approved';
    case Scheduled  = 'scheduled';
    case InProgress = 'in_progress';
    case Completed  = 'completed';
    case Rejected   = 'rejected';
    case Cancelled  = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending    => 'Pending',
            self::Approved   => 'Approved',
            self::Scheduled  => 'Scheduled',
            self::InProgress => 'In Progress',
            self::Completed  => 'Completed',
            self::Rejected   => 'Rejected',
            self::Cancelled  => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Pending    => 'bg-yellow-100 text-yellow-700',
            self::Approved   => 'bg-blue-100 text-blue-700',
            self::Scheduled  => 'bg-indigo-100 text-indigo-700',
            self::InProgress => 'bg-orange-100 text-orange-700',
            self::Completed  => 'bg-green-100 text-green-700',
            self::Rejected   => 'bg-red-100 text-red-600',
            self::Cancelled  => 'bg-gray-100 text-gray-500',
        };
    }

    /** Valid next statuses admin can set from this status */
    public function nextAllowedStatuses(): array
    {
        return match($this) {
            self::Pending    => [self::Approved, self::Rejected],
            self::Approved   => [self::Scheduled, self::Rejected, self::Cancelled],
            self::Scheduled  => [self::InProgress, self::Cancelled],
            self::InProgress => [self::Completed, self::Cancelled],
            default          => [],
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Rejected, self::Cancelled]);
    }
}
