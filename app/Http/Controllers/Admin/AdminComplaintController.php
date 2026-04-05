<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\User;
use App\Services\ComplaintService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminComplaintController extends Controller
{
    public function __construct(private ComplaintService $service) {}

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
        $admins     = User::staff()->get();

        return view('admin.complaints.index', compact('complaints', 'categories', 'admins'));
    }

    public function show(Complaint $complaint): View
    {
        $complaint->load(['user', 'category', 'media', 'updates.user', 'assignee', 'feedback']);
        $admins = User::staff()->get();
        return view('admin.complaints.show', compact('complaint', 'admins'));
    }

    public function assign(Request $request, Complaint $complaint): RedirectResponse
    {
        $request->validate(['assigned_to' => ['required', 'exists:users,id']]);
        $this->service->assign($complaint, $request->assigned_to, $request->user());
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

        $this->service->updateStatus($complaint, $data, $request->user());
        return back()->with('success', 'Complaint status updated.');
    }
}
