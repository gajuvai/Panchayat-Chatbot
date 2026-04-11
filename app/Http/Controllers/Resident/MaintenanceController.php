<?php

namespace App\Http\Controllers\Resident;

use App\Enums\MaintenanceStatus;
use App\Http\Controllers\Controller;
use App\Models\ComplaintCategory;
use App\Models\MaintenanceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function index(Request $request): View
    {
        $query = MaintenanceRequest::where('requested_by', $request->user()->id)
            ->with(['category', 'assignedTo'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests   = $query->paginate(10)->withQueryString();
        $categories = ComplaintCategory::where('is_active', true)->orderBy('name')->get();

        return view('resident.maintenance.index', compact('requests', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['nullable', 'exists:complaint_categories,id'],
            'location'    => ['nullable', 'string', 'max:255'],
            'priority'    => ['required', 'in:low,medium,high,urgent'],
        ]);

        $data['requested_by'] = $request->user()->id;
        $data['status']       = MaintenanceStatus::Pending;

        $req = MaintenanceRequest::create($data);

        return redirect()->route('resident.maintenance.index')
            ->with('success', "Maintenance request {$req->request_number} submitted.");
    }
}
