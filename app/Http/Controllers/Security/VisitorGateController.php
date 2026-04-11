<?php

namespace App\Http\Controllers\Security;

use App\Enums\VisitorPassStatus;
use App\Http\Controllers\Controller;
use App\Models\VisitorPass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitorGateController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->date ? now()->parse($request->date) : today();

        $passes = VisitorPass::with('resident')
            ->whereDate('expected_date', $date)
            ->orderByRaw("CASE status
                WHEN 'checked_in'  THEN 1
                WHEN 'approved'    THEN 2
                WHEN 'pending'     THEN 3
                WHEN 'checked_out' THEN 4
                WHEN 'expired'     THEN 5
                WHEN 'cancelled'   THEN 6
                ELSE 7 END")
            ->get()
            ->groupBy(fn ($p) => $p->status->value);

        // Also upcoming passes (next 7 days) for the sidebar widget
        $upcoming = VisitorPass::with('resident')
            ->whereDate('expected_date', '>', $date)
            ->whereDate('expected_date', '<=', $date->copy()->addDays(7))
            ->whereIn('status', [VisitorPassStatus::Pending, VisitorPassStatus::Approved])
            ->orderBy('expected_date')
            ->limit(10)
            ->get();

        return view('security.visitors.index', compact('passes', 'date', 'upcoming'));
    }

    public function approve(VisitorPass $visitorPass): RedirectResponse
    {
        abort_unless($visitorPass->status === VisitorPassStatus::Pending, 422, 'Pass is not pending.');

        $visitorPass->update([
            'status'      => VisitorPassStatus::Approved,
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', "Pass {$visitorPass->pass_code} approved.");
    }

    public function checkIn(VisitorPass $visitorPass): RedirectResponse
    {
        abort_unless(
            in_array($visitorPass->status, [VisitorPassStatus::Pending, VisitorPassStatus::Approved]),
            422,
            'Visitor cannot be checked in at this stage.'
        );

        $visitorPass->update([
            'status'       => VisitorPassStatus::CheckedIn,
            'checked_in_at'=> now(),
            'approved_by'  => $visitorPass->approved_by ?? auth()->id(),
        ]);

        return back()->with('success', "{$visitorPass->visitor_name} checked in at " . now()->format('h:i A'));
    }

    public function checkOut(VisitorPass $visitorPass): RedirectResponse
    {
        abort_unless($visitorPass->status === VisitorPassStatus::CheckedIn, 422, 'Visitor is not checked in.');

        $visitorPass->update([
            'status'         => VisitorPassStatus::CheckedOut,
            'checked_out_at' => now(),
        ]);

        return back()->with('success', "{$visitorPass->visitor_name} checked out at " . now()->format('h:i A'));
    }
}
