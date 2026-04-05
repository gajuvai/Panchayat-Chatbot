<?php

namespace App\Services;

use App\Mail\ComplaintStatusUpdated;
use App\Models\Complaint;
use App\Models\ComplaintUpdate;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ComplaintService
{
    /**
     * Assign a complaint to a staff member and record the activity.
     */
    public function assign(Complaint $complaint, int $assignToId, User $assignedBy): void
    {
        $oldStatus = $complaint->status->value;

        $complaint->update([
            'assigned_to' => $assignToId,
            'assigned_at' => now(),
            'status'      => 'in_progress',
        ]);

        ComplaintUpdate::create([
            'complaint_id' => $complaint->id,
            'user_id'      => $assignedBy->id,
            'old_status'   => $oldStatus,
            'new_status'   => 'in_progress',
            'message'      => 'Complaint assigned to ' . User::find($assignToId)->name,
            'is_internal'  => true,
        ]);
    }

    /**
     * Update complaint status, record activity, and optionally notify the owner.
     */
    public function updateStatus(Complaint $complaint, array $data, User $updatedBy): void
    {
        $oldStatus = $complaint->status->value;

        $complaint->update([
            'status'           => $data['status'],
            'resolution_notes' => $data['resolution_notes'] ?? $complaint->resolution_notes,
            'resolved_at'      => in_array($data['status'], ['resolved', 'closed'])
                ? now()
                : $complaint->resolved_at,
        ]);

        ComplaintUpdate::create([
            'complaint_id' => $complaint->id,
            'user_id'      => $updatedBy->id,
            'old_status'   => $oldStatus,
            'new_status'   => $data['status'],
            'message'      => $data['message'],
            'is_internal'  => $data['is_internal'] ?? false,
        ]);

        // Email the complaint owner (skip for internal-only updates or anonymous complaints)
        if (!($data['is_internal'] ?? false) && !$complaint->is_anonymous && $complaint->user?->email) {
            try {
                Mail::to($complaint->user->email)
                    ->queue(new ComplaintStatusUpdated($complaint, $data['message']));
            } catch (\Throwable $e) {
                Log::warning('Failed to queue complaint status email', [
                    'complaint_id' => $complaint->id,
                    'error'        => $e->getMessage(),
                ]);
            }
        }
    }
}
