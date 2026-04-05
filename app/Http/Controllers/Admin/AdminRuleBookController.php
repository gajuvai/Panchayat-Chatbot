<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RuleBookSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminRuleBookController extends Controller
{
    public function index(): View
    {
        $rules = RuleBookSection::with('author')->orderBy('section_order')->get();
        return view('admin.rules.index', compact('rules'));
    }

    public function create(): View
    {
        $nextOrder = (RuleBookSection::max('section_order') ?? 0) + 1;
        return view('admin.rules.create', compact('nextOrder'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'content'       => ['required', 'string'],
            'section_order' => ['required', 'integer', 'min:1'],
            'is_published'  => ['nullable', 'boolean'],
        ]);

        $data['user_id']      = $request->user()->id;
        $data['is_published'] = $request->boolean('is_published');

        RuleBookSection::create($data);

        return redirect()->route('admin.rules.index')
            ->with('success', 'Rule book section created.');
    }

    public function show(RuleBookSection $rule): View
    {
        $rule->load('author');
        return view('admin.rules.show', compact('rule'));
    }

    public function edit(RuleBookSection $rule): View
    {
        return view('admin.rules.edit', compact('rule'));
    }

    public function update(Request $request, RuleBookSection $rule): RedirectResponse
    {
        $data = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'content'       => ['required', 'string'],
            'section_order' => ['required', 'integer', 'min:1'],
            'is_published'  => ['nullable', 'boolean'],
        ]);

        $data['is_published'] = $request->boolean('is_published');
        $rule->update($data);

        return redirect()->route('admin.rules.show', $rule)
            ->with('success', 'Rule book section updated.');
    }

    public function destroy(RuleBookSection $rule): RedirectResponse
    {
        $rule->delete();
        return redirect()->route('admin.rules.index')
            ->with('success', 'Rule book section deleted.');
    }
}
