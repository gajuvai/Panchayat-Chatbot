<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AmenityBooking;
use App\Models\LostAndFoundItem;
use App\Models\MaintenanceRequest;
use App\Models\VisitorPass;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'flat_number',
        'block',
        'phone',
        'profile_photo',
        'is_active',
        'is_listed_in_directory',
        'directory_display_name',
        'bio',
        'whatsapp',
        'interests',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'password'               => 'hashed',
            'is_active'              => 'boolean',
            'is_listed_in_directory' => 'boolean',
            'interests'              => 'array',
        ];
    }

    public function scopeListed($query)
    {
        return $query->where('is_listed_in_directory', true);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->directory_display_name ?: $this->name;
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function assignedComplaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'assigned_to');
    }

    public function complaintUpdates(): HasMany
    {
        return $this->hasMany(ComplaintUpdate::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function polls(): HasMany
    {
        return $this->hasMany(Poll::class);
    }

    public function forumThreads(): HasMany
    {
        return $this->hasMany(ForumThread::class);
    }

    public function forumReplies(): HasMany
    {
        return $this->hasMany(ForumReply::class);
    }

    public function chatSessions(): HasMany
    {
        return $this->hasMany(ChatSession::class);
    }

    public function visitorPasses(): HasMany
    {
        return $this->hasMany(VisitorPass::class, 'resident_id');
    }

    public function lostFoundItems(): HasMany
    {
        return $this->hasMany(LostAndFoundItem::class);
    }

    public function amenityBookings(): HasMany
    {
        return $this->hasMany(AmenityBooking::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class, 'requested_by');
    }

    public function assignedMaintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class, 'assigned_to');
    }

    public function approvedVisitorPasses(): HasMany
    {
        return $this->hasMany(VisitorPass::class, 'approved_by');
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function eventRsvps(): HasMany
    {
        return $this->hasMany(EventRsvp::class);
    }

    public function pollVotes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function securityIncidents(): HasMany
    {
        return $this->hasMany(SecurityIncident::class, 'reported_by');
    }

    public function patrolsAssigned(): HasMany
    {
        return $this->hasMany(PatrolAssignment::class, 'assigned_by');
    }

    public function patrolsReceived(): HasMany
    {
        return $this->hasMany(PatrolAssignment::class, 'assigned_to');
    }

    public function emergencyAlerts(): HasMany
    {
        return $this->hasMany(EmergencyAlert::class, 'triggered_by');
    }

    public function ruleBookSections(): HasMany
    {
        return $this->hasMany(RuleBookSection::class);
    }

    public function scopeWithRole($query, string $role)
    {
        return $query->whereHas('role', fn ($q) => $q->where('name', $role));
    }

    public function scopeStaff($query)
    {
        return $query->whereHas('role', fn ($q) => $q->whereIn('name', ['admin', 'security_head']));
    }

    public function hasRole(string $role): bool
    {
        return $this->role?->name === $role;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isSecurityHead(): bool
    {
        return $this->hasRole('security_head');
    }

    public function isResident(): bool
    {
        return $this->hasRole('resident');
    }

    public function getDashboardRoute(): string
    {
        return match($this->role?->name) {
            'admin'         => 'admin.dashboard',
            'security_head' => 'security.dashboard',
            default         => 'resident.dashboard',
        };
    }
}
