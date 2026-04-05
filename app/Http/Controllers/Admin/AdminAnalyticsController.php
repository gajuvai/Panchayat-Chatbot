<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use Illuminate\Http\JsonResponse;

class AdminAnalyticsController extends Controller
{
    public function index()
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
