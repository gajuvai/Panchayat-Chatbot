<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AdminAnalyticsController extends Controller
{
    public function index(): View
    {
        return view('admin.analytics.index');
    }

    public function complaints(): JsonResponse
    {
        $byStatus = Complaint::selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        $byCategory = Complaint::selectRaw('category_id, count(*) as total')
            ->with('category')->groupBy('category_id')->get()
            ->mapWithKeys(fn($r) => [$r->category?->name ?? 'Unknown' => $r->total]);

        $trendData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $trendData[] = [
                'month' => $month->format('M Y'),
                'count' => Complaint::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)->count(),
            ];
        }

        $resolutionRate = Complaint::count() > 0
            ? round((Complaint::where('status', 'resolved')->count() / Complaint::count()) * 100, 1)
            : 0;

        return response()->json(compact('byStatus', 'byCategory', 'trendData', 'resolutionRate'));
    }

    // ─── Analytics v2 ────────────────────────────────────────────────────────

    public function sla(): View
    {
        // Average resolution time by category (in hours)
        $avgByCategory = Complaint::whereNotNull('resolved_at')
            ->with('category')
            ->get()
            ->groupBy(fn ($c) => $c->category?->name ?? 'Unknown')
            ->map(fn ($group) => round($group->avg(fn ($c) => $c->created_at->diffInHours($c->resolved_at)), 1));

        // Average by priority
        $avgByPriority = Complaint::whereNotNull('resolved_at')
            ->get()
            ->groupBy(fn ($c) => $c->priority->value)
            ->map(fn ($group) => round($group->avg(fn ($c) => $c->created_at->diffInHours($c->resolved_at)), 1));

        // SLA breaches: open complaints older than threshold
        $thresholds = ['urgent' => 2, 'high' => 7, 'medium' => 30, 'low' => 60]; // days
        $breaches = Complaint::whereIn('status', ['open', 'in_progress'])
            ->get()
            ->filter(function ($c) use ($thresholds) {
                $threshold = $thresholds[$c->priority->value] ?? 30;
                return $c->created_at->diffInDays(now()) > $threshold;
            })
            ->count();

        // Age buckets for open complaints
        $ageBuckets = [
            '0–7 days'  => Complaint::whereIn('status', ['open','in_progress'])->where('created_at', '>=', now()->subDays(7))->count(),
            '7–30 days' => Complaint::whereIn('status', ['open','in_progress'])->whereBetween('created_at', [now()->subDays(30), now()->subDays(7)])->count(),
            '30+ days'  => Complaint::whereIn('status', ['open','in_progress'])->where('created_at', '<', now()->subDays(30))->count(),
        ];

        return view('admin.analytics.sla', compact('avgByCategory', 'avgByPriority', 'breaches', 'ageBuckets'));
    }

    public function monthly(): View
    {
        $months = collect(range(1, 12))->map(fn ($m) => now()->month($m)->format('M'));

        // Current year vs last year
        $currentYear = now()->year;
        $lastYear    = $currentYear - 1;

        $currentData = $this->monthlyCountsForYear($currentYear);
        $lastData    = $this->monthlyCountsForYear($lastYear);

        // Resolution rate by month (current year)
        $resolutionByMonth = [];
        for ($m = 1; $m <= 12; $m++) {
            $total    = Complaint::whereYear('created_at', $currentYear)->whereMonth('created_at', $m)->count();
            $resolved = Complaint::whereYear('created_at', $currentYear)->whereMonth('created_at', $m)->where('status', 'resolved')->count();
            $resolutionByMonth[] = $total > 0 ? round($resolved / $total * 100, 1) : 0;
        }

        return view('admin.analytics.monthly', compact('months', 'currentYear', 'lastYear', 'currentData', 'lastData', 'resolutionByMonth'));
    }

    private function monthlyCountsForYear(int $year): array
    {
        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $data[] = Complaint::whereYear('created_at', $year)->whereMonth('created_at', $m)->count();
        }
        return $data;
    }

    public function export()
    {
        $complaints = Complaint::with(['user', 'category', 'assignee'])->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="complaints_export_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($complaints) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['#', 'Number', 'Title', 'Category', 'Status', 'Priority', 'Filed By', 'Assigned To', 'Created At', 'Resolved At']);
            foreach ($complaints as $i => $c) {
                fputcsv($handle, [
                    $i + 1,
                    $c->complaint_number,
                    $c->title,
                    $c->category?->name,
                    $c->status->value,
                    $c->priority->value,
                    $c->user->name,
                    $c->assignee?->name ?? 'Unassigned',
                    $c->created_at->format('Y-m-d H:i'),
                    $c->resolved_at?->format('Y-m-d H:i') ?? '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
