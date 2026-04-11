<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationPreferenceController extends Controller
{
    public function index(Request $request): View
    {
        $user  = $request->user();
        $types = NotificationPreference::types();

        // Load existing preferences keyed by type
        $existing = NotificationPreference::where('user_id', $user->id)
            ->pluck('frequency', 'notification_type')
            ->toArray();

        // Merge with defaults
        $preferences = collect($types)->mapWithKeys(fn ($label, $type) => [
            $type => $existing[$type] ?? 'instant',
        ]);

        return view('shared.notification-preferences.index', compact('types', 'preferences'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user  = $request->user();
        $types = array_keys(NotificationPreference::types());

        $data = $request->validate([
            'preferences'   => ['nullable', 'array'],
            'preferences.*' => ['in:instant,off'],
        ]);

        foreach ($types as $type) {
            $frequency = $data['preferences'][$type] ?? 'instant';
            NotificationPreference::updateOrCreate(
                ['user_id' => $user->id, 'notification_type' => $type],
                ['frequency' => $frequency]
            );
        }

        return redirect()->route('notifications.preferences')
            ->with('success', 'Notification preferences saved.');
    }
}
