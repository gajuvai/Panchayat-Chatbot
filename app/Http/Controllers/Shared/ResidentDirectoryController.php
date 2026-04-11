<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResidentDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::listed()
            ->whereHas('role', fn ($q) => $q->where('name', 'resident'))
            ->orderBy('block')
            ->orderBy('flat_number');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('directory_display_name', 'like', "%{$search}%")
                  ->orWhere('flat_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('block')) {
            $query->where('block', $request->block);
        }

        $residents = $query->paginate(18)->withQueryString();

        // Distinct blocks for filter dropdown
        $blocks = User::listed()
            ->whereHas('role', fn ($q) => $q->where('name', 'resident'))
            ->whereNotNull('block')
            ->distinct()
            ->orderBy('block')
            ->pluck('block');

        return view('shared.directory.index', compact('residents', 'blocks'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'is_listed_in_directory' => ['boolean'],
            'directory_display_name' => ['nullable', 'string', 'max:100'],
            'bio'                    => ['nullable', 'string', 'max:300'],
            'whatsapp'               => ['nullable', 'string', 'max:15'],
            'interests'              => ['nullable', 'string', 'max:300'],
        ]);

        // Convert comma-separated interests string to array
        $interestsRaw = trim($data['interests'] ?? '');
        $data['interests'] = $interestsRaw
            ? array_values(array_filter(array_map('trim', explode(',', $interestsRaw))))
            : null;

        $data['is_listed_in_directory'] = $request->boolean('is_listed_in_directory');

        $user->update($data);

        return redirect()->route('directory.index')
            ->with('success', $data['is_listed_in_directory']
                ? 'Your profile is now listed in the community directory.'
                : 'Your profile has been removed from the directory.');
    }
}
