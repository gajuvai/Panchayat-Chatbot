<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = ['user_id', 'notification_type', 'frequency'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * All supported notification types and their default labels.
     */
    public static function types(): array
    {
        return [
            'complaint_update'    => 'Complaint Status Updates',
            'new_announcement'    => 'New Announcements',
            'visitor_pass'        => 'Visitor Pass Activity',
            'maintenance_update'  => 'Maintenance Request Updates',
            'new_event'           => 'New Community Events',
            'duty_reminder'       => 'Duty Roster Assignments',
            'lost_found_response' => 'Lost & Found Responses',
        ];
    }

    /**
     * Determine whether to notify a user for a given type.
     * Emergency alerts are always sent regardless of preferences.
     */
    public static function shouldNotify(User $user, string $type): bool
    {
        // Emergency alerts always go through
        if ($type === 'emergency_alert') return true;

        $pref = self::where('user_id', $user->id)
            ->where('notification_type', $type)
            ->value('frequency');

        return ($pref ?? 'instant') === 'instant';
    }
}
