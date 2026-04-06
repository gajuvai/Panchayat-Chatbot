<?php

namespace App\Notifications;

use App\Models\EmergencyAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EmergencyAlertNotification extends Notification
{
    use Queueable;

    public function __construct(public EmergencyAlert $alert) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $typeLabel = ucfirst(str_replace('_', ' ', $this->alert->alert_type));

        return [
            'type'         => 'emergency_alert',
            'alert_id'     => $this->alert->id,
            'alert_type'   => $this->alert->alert_type,
            'title'        => "Emergency Alert: {$typeLabel}",
            'message'      => $this->alert->message,
            'triggered_by' => $this->alert->triggeredBy?->name ?? 'Security',
            'url'          => '#',
        ];
    }
}
