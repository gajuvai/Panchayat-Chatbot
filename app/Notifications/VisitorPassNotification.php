<?php

namespace App\Notifications;

use App\Models\VisitorPass;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VisitorPassNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly VisitorPass $pass) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'visitor_pass',
            'pass_id'       => $this->pass->id,
            'pass_code'     => $this->pass->pass_code,
            'visitor_name'  => $this->pass->visitor_name,
            'expected_date' => $this->pass->expected_date->format('d M Y'),
            'resident_name' => $this->pass->resident->name,
            'flat'          => $this->pass->resident->block
                                   ? $this->pass->resident->block . '-' . $this->pass->resident->flat_number
                                   : ($this->pass->resident->flat_number ?? ''),
            'title'         => 'New Visitor Pass',
            'message'       => $this->pass->visitor_name . ' expected on ' . $this->pass->expected_date->format('d M Y')
                               . ' (by ' . $this->pass->resident->name . ')',
            'url'           => route('security.visitors.index'),
        ];
    }
}
