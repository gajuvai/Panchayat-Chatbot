<?php

namespace App\Http\Controllers\Resident;

use App\Enums\VisitorPassStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VisitorPass;
use App\Notifications\VisitorPassNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitorPassController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->user()->visitorPasses()->latest('expected_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('expected_date', $request->date);
        }

        $passes = $query->paginate(10)->withQueryString();

        return view('resident.visitor-passes.index', compact('passes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'visitor_name'   => ['required', 'string', 'max:100'],
            'visitor_phone'  => ['nullable', 'string', 'max:15'],
            'vehicle_number' => ['nullable', 'string', 'max:20'],
            'purpose'        => ['nullable', 'string', 'max:255'],
            'expected_date'  => ['required', 'date', 'after_or_equal:today'],
            'expected_from'  => ['nullable', 'date_format:H:i'],
            'expected_to'    => ['nullable', 'date_format:H:i', 'after:expected_from'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $data['resident_id'] = $request->user()->id;
        $data['status']      = VisitorPassStatus::Pending;

        $pass = VisitorPass::create($data);

        // Notify all security_head users
        User::whereHas('role', fn ($q) => $q->where('name', 'security_head'))
            ->each(fn (User $officer) => $officer->notify(new VisitorPassNotification($pass)));

        return redirect()->route('resident.visitor-passes.index')
            ->with('success', "Visitor pass {$pass->pass_code} created. Security has been notified.");
    }

    public function destroy(Request $request, VisitorPass $visitorPass): RedirectResponse
    {
        // Authorize: only the resident who owns it can cancel
        abort_unless($visitorPass->resident_id === $request->user()->id, 403);
        abort_unless($visitorPass->isCancellable(), 422, 'This pass cannot be cancelled.');

        $visitorPass->update(['status' => VisitorPassStatus::Cancelled]);

        return redirect()->route('resident.visitor-passes.index')
            ->with('success', 'Visitor pass cancelled.');
    }
}
