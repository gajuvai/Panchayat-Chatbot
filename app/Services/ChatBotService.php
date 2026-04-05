<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\ChatSession;
use App\Models\Complaint;
use App\Models\Event;
use App\Models\Poll;
use App\Models\RuleBookSection;
use App\Models\User;

class ChatBotService
{
    public function processMessage(string $message, User $user, ChatSession $session): array
    {
        $intent   = $this->detectIntent($message);
        $response = $this->handleIntent($intent, $message, $user);

        return [
            'message'       => $response['text'],
            'intent'        => $intent,
            'quick_replies' => $response['quick_replies'] ?? [],
            'metadata'      => $response['metadata'] ?? null,
        ];
    }

    public function detectIntent(string $message): string
    {
        $msg = strtolower(trim($message));

        $intents = [
            'file_complaint'   => ['complaint', 'report', 'problem', 'issue', 'submit'],
            'check_status'     => ['status', 'my complaint', 'track', 'progress', 'cmp-'],
            'show_announcements' => ['announcement', 'announcements', 'news', 'notice', 'update'],
            'upcoming_events'  => ['event', 'events', 'meeting', 'gathering', 'schedule', 'upcoming'],
            'active_polls'     => ['poll', 'polls', 'vote', 'voting', 'survey'],
            'show_rules'       => ['rule', 'rules', 'regulation', 'guideline', 'policy'],
            'report_emergency' => ['emergency', 'alert', 'urgent', 'sos', 'fire', 'danger', 'theft'],
            'contact_admin'    => ['admin', 'contact', 'reach', 'speak', 'manager'],
            'greeting'         => ['hello', 'hi', 'hey', 'namaste', 'help', 'good morning', 'good evening', 'good afternoon'],
            'faq'              => ['how', 'what', 'when', 'where', 'why', 'faq', 'maintenance', 'parking', 'pets'],
        ];

        foreach ($intents as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($msg, $keyword)) {
                    return $intent;
                }
            }
        }

        return 'fallback';
    }

    public function handleIntent(string $intent, string $message, User $user): array
    {
        return match ($intent) {
            'file_complaint'     => $this->handleFileComplaint($user),
            'check_status'       => $this->handleCheckStatus($message, $user),
            'show_announcements' => $this->handleShowAnnouncements(),
            'upcoming_events'    => $this->handleUpcomingEvents(),
            'active_polls'       => $this->handleActivePolls(),
            'show_rules'         => $this->handleShowRules(),
            'report_emergency'   => $this->handleEmergency($user),
            'contact_admin'      => $this->handleContactAdmin(),
            'greeting'           => $this->handleGreeting($user),
            'faq'                => $this->handleFaq($message),
            default              => $this->handleFallback(),
        };
    }

    // ─── Intent Handlers ────────────────────────────────────────────────

    private function handleGreeting(User $user): array
    {
        $hour     = now()->hour;
        $greeting = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 18 => 'Good afternoon',
            default    => 'Good evening',
        };

        $text = "{$greeting}, <strong>{$user->name}</strong>! 👋<br><br>"
            . "Welcome to the <strong>Panchayat Assistant</strong>. Here's what I can help you with:<br><br>"
            . "<ul>"
            . "<li>📋 File or track a <strong>complaint</strong></li>"
            . "<li>📢 View latest <strong>announcements</strong></li>"
            . "<li>📅 Browse upcoming <strong>events</strong></li>"
            . "<li>🗳️ Participate in active <strong>polls</strong></li>"
            . "<li>📖 Read community <strong>rules</strong></li>"
            . "<li>🚨 Report an <strong>emergency</strong></li>"
            . "</ul><br>"
            . "Type your question or tap a quick option below.";

        return [
            'text' => $text,
            'quick_replies' => [
                ['text' => '📋 File Complaint',  'action' => 'intent',   'intent' => 'file_complaint'],
                ['text' => '🔍 Track Status',    'action' => 'intent',   'intent' => 'check_status'],
                ['text' => '📢 Announcements',   'action' => 'intent',   'intent' => 'show_announcements'],
                ['text' => '📅 Events',          'action' => 'intent',   'intent' => 'upcoming_events'],
                ['text' => '🗳️ Polls',           'action' => 'intent',   'intent' => 'active_polls'],
                ['text' => '📖 Rules',           'action' => 'intent',   'intent' => 'show_rules'],
            ],
        ];
    }

    private function handleFileComplaint(User $user): array
    {
        $openCount = $user->complaints()->whereIn('status', ['open', 'in_progress'])->count();

        $text = "I can help you file a complaint! 📝<br><br>"
            . "You can submit a complaint with a description, photos, or documents. "
            . "Once filed, you'll be able to track its progress.<br><br>"
            . "You currently have <strong>{$openCount}</strong> open complaint(s).";

        return [
            'text' => $text,
            'quick_replies' => [
                ['text' => '📝 File New Complaint', 'action' => 'navigate', 'url' => route('resident.complaints.create')],
                ['text' => '📂 My Complaints',      'action' => 'navigate', 'url' => route('resident.complaints.index')],
                ['text' => '🔍 Track Status',       'action' => 'intent',   'intent' => 'check_status'],
            ],
        ];
    }

    private function handleCheckStatus(string $message, User $user): array
    {
        // Check if a specific complaint number was mentioned
        preg_match('/CMP-\d{4}-\d{5}/i', strtoupper($message), $matches);

        if (!empty($matches)) {
            $complaint = Complaint::where('complaint_number', $matches[0])
                ->where('user_id', $user->id)
                ->first();

            if ($complaint) {
                $status = $complaint->status->label();
                $filed  = $complaint->created_at->format('d M Y');
                $text   = "<strong>Complaint #{$complaint->complaint_number}</strong><br>"
                    . "<ul>"
                    . "<li><strong>Title:</strong> {$complaint->title}</li>"
                    . "<li><strong>Status:</strong> {$status}</li>"
                    . "<li><strong>Category:</strong> {$complaint->category->name}</li>"
                    . "<li><strong>Filed on:</strong> {$filed}</li>"
                    . "</ul>";

                return [
                    'text' => $text,
                    'quick_replies' => [
                        ['text' => 'View Details',        'action' => 'navigate', 'url' => route('resident.complaints.show', $complaint)],
                        ['text' => 'All My Complaints',   'action' => 'navigate', 'url' => route('resident.complaints.index')],
                    ],
                    'metadata' => ['complaint_id' => $complaint->id],
                ];
            }

            return [
                'text' => "I couldn't find complaint <strong>{$matches[0]}</strong> linked to your account. Please double-check the complaint number and try again.",
                'quick_replies' => [
                    ['text' => '📂 My Complaints', 'action' => 'navigate', 'url' => route('resident.complaints.index')],
                ],
            ];
        }

        $openCount = $user->complaints()->whereIn('status', ['open', 'in_progress'])->count();

        $text = "You have <strong>{$openCount}</strong> active complaint(s).<br><br>"
            . "You can view all complaints on your complaints page, or share a complaint number (e.g. <em>CMP-2026-00001</em>) and I'll look it up for you.";

        return [
            'text' => $text,
            'quick_replies' => [
                ['text' => '📂 View My Complaints', 'action' => 'navigate', 'url' => route('resident.complaints.index')],
            ],
        ];
    }

    private function handleShowAnnouncements(): array
    {
        $announcements = Announcement::published()
            ->active()
            ->latest('published_at')
            ->take(3)
            ->get();

        if ($announcements->isEmpty()) {
            return [
                'text' => "There are no active announcements right now. Check back soon! 📢",
                'quick_replies' => [
                    ['text' => 'View All Announcements', 'action' => 'navigate', 'url' => route('announcements.index')],
                ],
            ];
        }

        $items = $announcements->map(function ($a) {
            $date = $a->published_at ? $a->published_at->format('d M Y') : $a->created_at->format('d M Y');
            $type = ucfirst($a->type ?? 'general');
            return "<li><strong>{$a->title}</strong> <em>({$type} · {$date})</em></li>";
        })->join('');

        $text = "Here are the latest announcements:<br><br><ul>{$items}</ul>";

        return [
            'text' => $text,
            'quick_replies' => [
                ['text' => 'View All Announcements', 'action' => 'navigate', 'url' => route('announcements.index')],
            ],
        ];
    }

    private function handleUpcomingEvents(): array
    {
        $events = Event::where('status', 'upcoming')
            ->where('event_date', '>', now())
            ->orderBy('event_date')
            ->take(3)
            ->get();

        if ($events->isEmpty()) {
            return [
                'text' => "There are no upcoming events scheduled at the moment. Check back soon! 📅",
                'quick_replies' => [
                    ['text' => 'View Events Page', 'action' => 'navigate', 'url' => route('events.index')],
                ],
            ];
        }

        $items = $events->map(function ($e) {
            $date = $e->event_date->format('d M Y, h:i A');
            return "<li><strong>{$e->title}</strong><br><em>{$date} · {$e->venue}</em></li>";
        })->join('');

        $text = "Here are the next upcoming events:<br><br><ul>{$items}</ul>";

        return [
            'text' => $text,
            'quick_replies' => [
                ['text' => 'View All Events', 'action' => 'navigate', 'url' => route('events.index')],
            ],
        ];
    }

    private function handleActivePolls(): array
    {
        $polls = Poll::where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now())
            ->orderBy('ends_at')
            ->take(5)
            ->get();

        if ($polls->isEmpty()) {
            return [
                'text' => "There are no active polls right now. Check back later — your vote matters! 🗳️",
                'quick_replies' => [
                    ['text' => 'View Polls', 'action' => 'navigate', 'url' => route('polls.index')],
                ],
            ];
        }

        $items = $polls->map(function ($p) {
            $ends    = $p->ends_at->format('d M Y');
            $votes   = $p->getTotalVotes();
            $typeTag = $p->is_anonymous ? 'Anonymous' : 'Public';
            return "<li><strong>{$p->title}</strong><br><em>Closes {$ends} · {$votes} vote(s) · {$typeTag}</em></li>";
        })->join('');

        $count = $polls->count();
        $text  = "There are <strong>{$count}</strong> active poll(s) open for voting:<br><br><ul>{$items}</ul><br>"
            . "Your participation shapes our community decisions!";

        return [
            'text' => $text,
            'quick_replies' => [
                ['text' => '🗳️ Go Vote Now', 'action' => 'navigate', 'url' => route('polls.index')],
            ],
        ];
    }

    private function handleShowRules(): array
    {
        $sections = RuleBookSection::published()
            ->orderBy('section_order')
            ->take(5)
            ->get();

        if ($sections->isEmpty()) {
            return [
                'text' => "The rule book is being updated. Please check back soon! 📖",
                'quick_replies' => [
                    ['text' => 'View Rule Book', 'action' => 'navigate', 'url' => route('rules.index')],
                ],
            ];
        }

        $items = $sections->map(fn($s) => "<li>{$s->title}</li>")->join('');

        $text = "Here are the community rule book sections:<br><br><ul>{$items}</ul><br>"
            . "Click below to read the full text of each section.";

        return [
            'text' => $text,
            'quick_replies' => [
                ['text' => '📖 Open Rule Book', 'action' => 'navigate', 'url' => route('rules.index')],
            ],
        ];
    }

    private function handleEmergency(User $user): array
    {
        $text = "<strong>🚨 EMERGENCY CONTACTS</strong><br><br>"
            . "<ul>"
            . "<li>🚔 <strong>Police:</strong> 100</li>"
            . "<li>🚒 <strong>Fire Brigade:</strong> 101</li>"
            . "<li>🚑 <strong>Ambulance:</strong> 108</li>"
            . "</ul><br>"
            . "For a society-level security emergency, file an urgent complaint immediately or call the security office.";

        return [
            'text' => $text,
            'quick_replies' => [
                ['text' => '🆘 Report Security Issue', 'action' => 'navigate', 'url' => route('resident.complaints.create')],
            ],
        ];
    }

    private function handleContactAdmin(): array
    {
        $text = "<strong>📞 Contact Admin</strong><br><br>"
            . "You can reach the society administration through:<br><br>"
            . "<ul>"
            . "<li>📝 Filing a complaint (fastest response)</li>"
            . "<li>📧 Email: <strong>admin@panchayat.local</strong></li>"
            . "<li>🕘 Office hours: <strong>Mon–Sat, 9 AM – 6 PM</strong></li>"
            . "</ul>";

        return [
            'text' => $text,
            'quick_replies' => [
                ['text' => '📝 File a Complaint',    'action' => 'navigate', 'url' => route('resident.complaints.create')],
                ['text' => '📢 View Announcements',  'action' => 'navigate', 'url' => route('announcements.index')],
            ],
        ];
    }

    private function handleFaq(string $message): array
    {
        $msg = strtolower($message);

        $faqs = [
            'maintenance' => "<strong>🔧 Maintenance Hours</strong><br><br>"
                . "Maintenance requests are handled <strong>Monday–Saturday, 8 AM – 6 PM</strong>. "
                . "Emergency maintenance is available 24/7 for critical issues like water leaks or power failures.",

            'parking' => "<strong>🚗 Parking Rules</strong><br><br>"
                . "Each flat has one designated parking spot. "
                . "Visitor parking is available in the designated zones only. "
                . "Unauthorized vehicles may be towed.",

            'pets' => "<strong>🐾 Pet Policy</strong><br><br>"
                . "Pets are allowed in the society but must be leashed in all common areas. "
                . "Please register your pet with the admin office and carry clean-up bags in outdoor spaces.",
        ];

        foreach ($faqs as $key => $answer) {
            if (str_contains($msg, $key)) {
                return [
                    'text' => $answer,
                    'quick_replies' => [
                        ['text' => '📖 View Full Rule Book', 'action' => 'navigate', 'url' => route('rules.index')],
                    ],
                ];
            }
        }

        return $this->handleFallback();
    }

    private function handleFallback(): array
    {
        $text = "I'm not sure I understood that. Here's what I can help you with:<br><br>"
            . "<ul>"
            . "<li>Say <strong>\"complaint\"</strong> to file or track a complaint</li>"
            . "<li>Say <strong>\"announcement\"</strong> to see the latest news</li>"
            . "<li>Say <strong>\"events\"</strong> to browse upcoming events</li>"
            . "<li>Say <strong>\"poll\"</strong> to see active polls</li>"
            . "<li>Say <strong>\"rules\"</strong> to view community regulations</li>"
            . "<li>Say <strong>\"emergency\"</strong> for urgent contact info</li>"
            . "</ul>";

        return [
            'text' => $text,
            'quick_replies' => [
                ['text' => '📋 File Complaint',   'action' => 'intent', 'intent' => 'file_complaint'],
                ['text' => '📢 Announcements',    'action' => 'intent', 'intent' => 'show_announcements'],
                ['text' => '📅 Events',           'action' => 'intent', 'intent' => 'upcoming_events'],
                ['text' => '🗳️ Polls',            'action' => 'intent', 'intent' => 'active_polls'],
                ['text' => '📖 Rules',            'action' => 'intent', 'intent' => 'show_rules'],
            ],
        ];
    }
}
