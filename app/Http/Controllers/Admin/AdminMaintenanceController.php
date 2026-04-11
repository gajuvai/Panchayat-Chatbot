<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MaintenanceStatus;
use App\Http\Controllers\Controller;
use App\Models\MaintenanceMedia;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminMaintenanceController extends Controller
{
    public function index(Request $request): View
    {
        $query = MaintenanceRequest::with(['requestedBy', 'assignedTo', 'category'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('title', 'like', "%{$s}%")
                                       ->orWhere('request_number', 'like', "%{$s}%")
                                       ->orWhereHas('requestedBy', fn ($u) => $u->where('name', 'like', "%{$s}%")));
        }

        $requests = $query->paginate(15)->withQueryString();

        $counts = [
            'pending'     => MaintenanceRequest::where('status', MaintenanceStatus::Pending)->count(),
            'in_progress' => MaintenanceRequest::where('status', MaintenanceStatus::InProgress)->count(),
            'scheduled'   => MaintenanceRequest::where('status', MaintenanceStatus::Scheduled)->count(),
        ];

        return view('admin.maintenance.index', compact('requests', 'counts'));
    }

    public function show(MaintenanceRequest $maintenance): View
    {
        $maintenance->load(['requestedBy', 'assignedTo', 'category', 'media']);
        $staff = User::whereHas('role', fn ($q) => $q->whereIn('name', ['admin', 'security_head']))
            ->orderBy('name')->get();

        return view('admin.maintenance.show', compact('maintenance', 'staff'));
    }

    public function updateStatus(Request $request, MaintenanceRequest $maintenance): RedirectResponse
    {
        $allowed = $maintenance->status->nextAllowedStatuses();

        abort_if(empty($allowed), 422, 'No further status changes allowed.');

        $allowedValues = array_map(fn ($s) => $s->value, $allowed);

        $data = $request->validate([
            'status'            => ['required', 'in:' . implode(',', $allowedValues)],
            'scheduled_at'      => ['nullable', 'date', 'required_if:status,scheduled'],
            'rejection_reason'  => ['nullable', 'string', 'required_if:status,rejected'],
            'completion_notes'  => ['nullable', 'string'],
            'actual_cost'       => ['nullable', 'numeric', 'min:0'],
            'vendor_name'       => ['nullable', 'string', 'max:100'],
            'vendor_contact'    => ['nullable', 'string', 'max:15'],
        ]);

        $newStatus = MaintenanceStatus::from($data['status']);

        $update = ['status' => $newStatus];

        if ($newStatus === MaintenanceStatus::Completed) {
            $update['completed_at'] = now();
            if (isset($data['completion_notes'])) $update['completion_notes'] = $data['completion_notes'];
            if (isset($data['actual_cost'])) $update['actual_cost'] = $data['actual_cost'];
        }
        if ($newStatus === MaintenanceStatus::Scheduled && isset($data['scheduled_at'])) {
            $update['scheduled_at'] = $data['scheduled_at'];
        }
        if ($newStatus === MaintenanceStatus::Rejected && isset($data['rejection_reason'])) {
            $update['rejection_reason'] = $data['rejection_reason'];
        }
        if (isset($data['vendor_name'])) $update['vendor_name'] = $data['vendor_name'];
        if (isset($data['vendor_contact'])) $update['vendor_contact'] = $data['vendor_contact'];

        $maintenance->update($update);

        return redirect()->route('admin.maintenance.show', $maintenance)
            ->with('success', "Status updated to {$newStatus->label()}.");
    }

    public function assign(Request $request, MaintenanceRequest $maintenance): RedirectResponse
    {
        $data = $request->validate([
            'assigned_to'    => ['nullable', 'exists:users,id'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $maintenance->update($data);

        return redirect()->route('admin.maintenance.show', $maintenance)
            ->with('success', 'Assignment updated.');
    }

    public function storeMedia(Request $request, MaintenanceRequest $maintenance): RedirectResponse
    {
        $request->validate([
            'files'   => ['required', 'array', 'max:5'],
            'files.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx'],
            'stage'   => ['required', 'in:before,during,after,document'],
        ]);

        foreach ($request->file('files') as $file) {
            $path = $file->store("maintenance/{$maintenance->id}/{$request->stage}", 'public');

            MaintenanceMedia::create([
                'maintenance_request_id' => $maintenance->id,
                'file_path'   => $path,
                'file_name'   => $file->getClientOriginalName(),
                'mime_type'   => $file->getMimeType(),
                'file_size'   => $file->getSize(),
                'stage'       => $request->stage,
            ]);
        }

        return redirect()->route('admin.maintenance.show', $maintenance)
            ->with('success', count($request->file('files')) . ' file(s) uploaded.');
    }
}
