<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\ComplaintUpdate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->user()->complaints()->with('category');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $complaints  = $query->latest()->paginate(10)->withQueryString();
        $categories  = ComplaintCategory::where('is_active', true)->get();

        return view('resident.complaints.index', compact('complaints', 'categories'));
    }

    public function create(): View
    {
        $categories = ComplaintCategory::where('is_active', true)->get();
        return view('resident.complaints.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:complaint_categories,id'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority'    => ['required', 'in:low,medium,high,urgent'],
            'location'    => ['nullable', 'string', 'max:255'],
            'is_anonymous'=> ['boolean'],
        ]);

        $complaint = $request->user()->complaints()->create($data);

        return redirect()->route('resident.complaints.show', $complaint)
            ->with('success', "Complaint #{$complaint->complaint_number} filed successfully.");
    }

    public function show(Complaint $complaint): View
    {
        $this->authorize('view', $complaint);
        $complaint->load(['category', 'media', 'updates.user', 'assignee', 'feedback']);
        $categories = ComplaintCategory::where('is_active', true)->get();
        return view('resident.complaints.show', compact('complaint', 'categories'));
    }

    public function edit(Complaint $complaint): View
    {
        $this->authorize('update', $complaint);
        $categories = ComplaintCategory::where('is_active', true)->get();
        return view('resident.complaints.edit', compact('complaint', 'categories'));
    }

    public function update(Request $request, Complaint $complaint): RedirectResponse
    {
        $this->authorize('update', $complaint);

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority'    => ['required', 'in:low,medium,high,urgent'],
            'location'    => ['nullable', 'string', 'max:255'],
        ]);

        $complaint->update($data);

        return redirect()->route('resident.complaints.show', $complaint)
            ->with('success', 'Complaint updated successfully.');
    }

    public function destroy(Complaint $complaint): RedirectResponse
    {
        $this->authorize('delete', $complaint);
        $complaint->delete();
        return redirect()->route('resident.complaints.index')
            ->with('success', 'Complaint withdrawn.');
    }
}
