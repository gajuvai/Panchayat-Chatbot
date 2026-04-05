<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ComplaintStatusUpdated;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\ComplaintUpdate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AdminComplaintController extends Controller
{
    public function index(Request $request): View
    {
        $query = Complaint::with(['user', 'category', 'assignee']);

        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('priority')) $query->where('priority', $request->priority);
        if ($request->filled('category')) $query->where('category_id', $request->category);
        if ($request->filled('assigned')) $query->where('assigned_to', $request->assigned);
        if ($request->filled('search'))   $query->where(function ($q) use ($request) {
            $q->where('title', 'like', "%{$request->search}%")
              ->orWhere('complaint_number', 'like', "%{$request->search}%");
        });

        $complaints = $query->latest()->paginate(15)->withQueryString();
        $categories = ComplaintCategory::where('is_active', true)->get();
        $admins     = User::whereHas('role', fn($q) => $q->whereIn('name', ['admin', 'security_head']))->get();

        return view('admin.complaints.index', compact('complaints', 'categories', 'admins'));
    }

    public function show(Complaint $complaint): View
    {
        $complaint->load(['user', 'category', 'media', 'updates.user', 'assignee', 'feedback']);
        $admins = User::whereHas('role', fn($q) => $q->whereIn('name', ['admin', 'security_head']))->get();
        return view('admin.complaints.show', compact('complaint', 'admins'));
    }

    public function assign(Request $request, Complaint $complaint): RedirectResponse
    {
        $request->validate(['assigned_to' => ['required', 'exists:users,id']]);

        $complaint->update([
            'assigned_to' => $request->assigned_to,
            'assigned_at' => now(),
            'status'      => 'in_progress',
        ]);

        ComplaintUpdate::create([
            'complaint_id' => $complaint->id,
            'user_id'      => $request->user()->id,
            'old_status'   => $complaint->getOriginal('status'),
            'new_status'   => 'in_progress',
            'message'      => 'Complaint assigned to ' . User::find($request->assigned_to)->name,
            'is_internal'  => true,
        ]);

        return back()->with('success', 'Complaint assigned successfully.');
    }

    public function updateStatus(Request $request, Complaint $complaint): RedirectResponse
    {
        $data = $request->validate([
            'status'           => ['required', 'in:open,in_progress,resolved,closed,rejected'],
            'message'          => ['required', 'string'],
            'is_internal'      => ['boolean'],
            'resolution_notes' => ['nullable', 'string'],
        ]);

        $oldStatus = $complaint->status->value;

        $complaint->update([
            'status'           => $data['status'],
            'resolution_notes' => $data['resolution_notes'] ?? $complaint->resolution_notes,
            'resolved_at'      => in_array($data['status'], ['resolved', 'closed']) ? now() : $complaint->resolved_at,
        ]);

        ComplaintUpdate::create([
            'complaint_id' => $complaint->id,
            'user_id'      => $request->user()->id,
            'old_status'   => $oldStatus,
            'new_status'   => $data['status'],
            'message'      => $data['message'],
            'is_internal'  => $data['is_internal'] ?? false,
        ]);

        // Notify the complaint owner by email (skip for internal-only updates or anonymous complaints)
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

        return back()->with('success', 'Complaint status updated.');
    }
}
