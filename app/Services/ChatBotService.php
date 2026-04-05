<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Models\Complaint;
use App\Models\Event;
use App\Models\Poll;
use App\Models\User;

class ChatBotService
{
    public function processMessage(string $message, User $user, ChatSession $session): array
    {
        $intent = $this->detectIntent($message);
        $response = $this->handleIntent($intent, $message, $user);

        return [
            'message'      => $response['text'],
            'intent'       => $intent,
            'quick_replies'=> $response['quick_replies'] ?? [],
            'metadata'     => $response['metadata'] ?? null,
        ];
    }

    public function detectIntent(string $message): string
    {
        $message = strtolower(trim($message));

        $intents = [
            'file_complaint'   => ['complaint', 'file', 'report', 'problem', 'issue', 'submit'],
            'check_status'     => ['status', 'track', 'update', 'progress', 'CMP-'],
            'show_rules'       => ['rule', 'rules', 'guideline', 'regulation', 'policy'],
            'upcoming_events'  => ['event', 'meeting', 'gathering', 'schedule', 'upcoming'],
            'active_polls'     => ['poll', 'vote', 'survey', 'voting'],
            'contact_admin'    => ['admin', 'contact', 'reach', 'speak', 'talk', 'manager'],
            'report_emergency' => ['emergency', 'urgent', 'sos', 'help', 'fire', 'danger', 'theft'],
            'greeting'         => ['hello', 'hi', 'hey', 'namaste', 'good morning', 'good evening'],
            'faq'              => ['how', 'what', 'when', 'where', 'why', 'faq'],
        ];

        foreach ($intents as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($message, $keyword)) {
                    return $intent;
                }
            }
        }

        return 'fallback';
    }

    public function handleIntent(string $intent, string $message, User $user): array
    {
        return match($intent) {
            'file_complaint'   => $this->handleFileComplaint($user),
            'check_status'     => $this->handleCheckStatus($message, $user),
            'show_rules'       => $this->handleShowRules(),
            'upcoming_events'  => $this->handleUpcomingEvents(),
            'active_polls'     => $this->handleActivePolls(),
            'contact_admin'    => $this->handleContactAdmin(),
            'report_emergency' => $this->handleEmergency($user),
            'greeting'         => $this->handleGreeting($user),
            'faq'              => $this->handleFaq($message),
            default            => $this->handleFallback(),
        };
    }

    private function handleFileComplaint(User $user): array
    {
        return [
            'text' => "I can help you file a complaint! You can:\n\n📝 **File a new complaint** with text or voice recording\n📷 Attach photos or documents\n\nClick the button below to get started.",
            'quick_replies' => [
                ['text' => 'File Complaint', 'action' => 'navigate', 'url' => route('resident.complaints.create')],
                ['text' => 'View My Complaints', 'action' => 'navigate', 'url' => route('resident.complaints.index')],
                ['text' => 'Track Status', 'action' => 'intent', 'intent' => 'check_status'],
            ],
        ];
    }

    private function handleCheckStatus(string $message, User $user): array
    {
        preg_match('/CMP-\d{4}-\d{5}/i', strtoupper($message), $matches);

        if (!empty($matches)) {
            $complaint = Complaint::where('complaint_number', $matches[0])
                ->where('user_id', $user->id)
                ->first();

            if ($complaint) {
                $status = $complaint->status->label();
                return [
                    'text' => "**Complaint #{$complaint->complaint_number}**\n\nTitle: {$complaint->title}\nStatus: {$status}\nCategory: {$complaint->category->name}\n\nFiled on: {$complaint->created_at->format('d M Y')}",
                    'quick_replies' => [
                        ['text' => 'View Details', 'action' => 'navigate', 'url' => route('resident.complaints.show', $complaint)],
                        ['text' => 'My All Complaints', 'action' => 'navigate', 'url' => route('resident.complaints.index')],
                    ],
                    'metadata' => ['complaint_id' => $complaint->id],
                ];
            }

            return ['text' => "I couldn't find complaint #{$matches[0]} associated with your account. Please check the complaint number and try again.", 'quick_replies' => []];
        }

        $openCount = $user->complaints()->whereIn('status', ['open', 'in_progress'])->count();
        return [
            'text' => "You have **{$openCount}** active complaint(s). You can track all your complaints below.",
            'quick_replies' => [
                ['text' => 'View My Complaints', 'action' => 'navigate', 'url' => route('resident.complaints.index')],
            ],
        ];
    }

    private function handleShowRules(): array
    {
        return [
            'text' => "📖 **Society Rule Book**\n\nOur community rules and guidelines are available in the Rule Book section. You can view all regulations, maintenance guidelines, and community policies there.",
            'quick_replies' => [
                ['text' => 'View Rule Book', 'action' => 'navigate', 'url' => route('rules.index')],
            ],
        ];
    }

    private function handleUpcomingEvents(): array
    {
        $events = Event::where('status', 'upcoming')->where('event_date', '>', now())->take(3)->get();

        if ($events->isEmpty()) {
            return ['text' => "There are no upcoming events at the moment. Check back soon! 📅", 'quick_replies' => []];
        }

        $eventList = $events->map(fn($e) => "📅 **{$e->title}** — {$e->event_date->format('d M Y, h:i A')} at {$e->venue}")->join("\n");

        return [
            'text' => "Upcoming Events:\n\n{$eventList}",
            'quick_replies' => [
                ['text' => 'View All Events', 'action' => 'navigate', 'url' => route('events.index')],
            ],
        ];
    }

    private function handleActivePolls(): array
    {
        $pollCount = Poll::where('is_active', true)->where('ends_at', '>', now())->count();

        return [
            'text' => "🗳️ There are currently **{$pollCount}** active poll(s) for community voting. Your vote matters!",
            'quick_replies' => [
                ['text' => 'View Active Polls', 'action' => 'navigate', 'url' => route('polls.index')],
            ],
        ];
    }

    private function handleContactAdmin(): array
    {
        return [
            'text' => "📞 **Contact Admin**\n\nYou can reach the society admin through:\n• Filing a complaint (fastest response)\n• Email: admin@panchayat.local\n• Office hours: Mon-Sat, 9 AM - 6 PM",
            'quick_replies' => [
                ['text' => 'File a Complaint', 'action' => 'navigate', 'url' => route('resident.complaints.create')],
                ['text' => 'View Announcements', 'action' => 'navigate', 'url' => route('announcements.index')],
            ],
        ];
    }

    private function handleEmergency(User $user): array
    {
        return [
            'text' => "🚨 **EMERGENCY CONTACTS**\n\n🚔 Police: 100\n🚒 Fire: 101\n🚑 Ambulance: 108\n\nFor society-level security emergencies, please use the SOS button below or call the security head immediately.",
            'quick_replies' => [
                ['text' => '🆘 Trigger Emergency Alert', 'action' => 'navigate', 'url' => '#emergency-sos'],
                ['text' => 'Report Security Issue', 'action' => 'navigate', 'url' => route('resident.complaints.create')],
            ],
        ];
    }

    private function handleGreeting(User $user): array
    {
        $hour = now()->hour;
        $greeting = match(true) {
            $hour < 12 => 'Good morning',
            $hour < 18 => 'Good afternoon',
            default    => 'Good evening',
        };

        return [
            'text' => "{$greeting}, **{$user->name}**! 👋\n\nWelcome to Panchayat Chatbot. How can I help you today?",
            'quick_replies' => [
                ['text' => '📋 File Complaint', 'action' => 'intent', 'intent' => 'file_complaint'],
                ['text' => '🔍 Track Status', 'action' => 'intent', 'intent' => 'check_status'],
                ['text' => '📢 Announcements', 'action' => 'navigate', 'url' => route('announcements.index')],
                ['text' => '📅 Events', 'action' => 'intent', 'intent' => 'upcoming_events'],
                ['text' => '📖 Rules', 'action' => 'intent', 'intent' => 'show_rules'],
            ],
        ];
    }

    private function handleFaq(string $message): array
    {
        $faqs = [
            'maintenance' => "🔧 **Maintenance Hours**\nMaintenance requests are handled Mon-Sat, 8 AM - 6 PM. Emergency maintenance is available 24/7 for critical issues.",
            'parking'     => "🚗 **Parking Rules**\nEach flat has one designated parking spot. Visitor parking is available in designated zones only.",
            'pets'        => "🐾 **Pet Policy**\nPets are allowed but must be leashed in common areas. Register your pet with the admin office.",
        ];

        $msg = strtolower($message);
        foreach ($faqs as $key => $answer) {
            if (str_contains($msg, $key)) {
                return ['text' => $answer, 'quick_replies' => [['text' => 'View Rule Book', 'action' => 'navigate', 'url' => route('rules.index')]]];
            }
        }

        return $this->handleFallback();
    }

    private function handleFallback(): array
    {
        return [
            'text' => "I'm not sure I understood that. Here's what I can help you with:",
            'quick_replies' => [
                ['text' => '📋 File Complaint', 'action' => 'intent', 'intent' => 'file_complaint'],
                ['text' => '🔍 Track Status', 'action' => 'intent', 'intent' => 'check_status'],
                ['text' => '📅 Events', 'action' => 'intent', 'intent' => 'upcoming_events'],
                ['text' => '🗳️ Polls', 'action' => 'intent', 'intent' => 'active_polls'],
                ['text' => '📖 Rules', 'action' => 'intent', 'intent' => 'show_rules'],
            ],
        ];
    }
}
