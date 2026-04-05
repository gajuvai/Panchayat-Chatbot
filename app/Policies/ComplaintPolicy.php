<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Complaint $complaint): bool
    {
        return $user->id === $complaint->user_id || $user->isAdmin() || $user->isSecurityHead();
    }

    public function create(User $user): bool
    {
        return $user->isResident();
    }

    public function update(User $user, Complaint $complaint): bool
    {
        return $user->id === $complaint->user_id && $complaint->status->value === 'open';
    }

    public function delete(User $user, Complaint $complaint): bool
    {
        return $user->id === $complaint->user_id && $complaint->status->value === 'open';
    }

    public function restore(User $user, Complaint $complaint): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Complaint $complaint): bool
    {
        return $user->isAdmin();
    }
}
