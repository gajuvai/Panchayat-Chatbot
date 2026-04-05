<?php

namespace App\Mail;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComplaintStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Complaint $complaint,
        public readonly string    $adminMessage,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Complaint Status Updated - ' . $this->complaint->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.complaint-status-updated',
            with: [
                'complaint'    => $this->complaint,
                'adminMessage' => $this->adminMessage,
                'viewUrl'      => route('resident.complaints.show', $this->complaint),
            ],
        );
    }
}
