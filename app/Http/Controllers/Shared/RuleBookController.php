<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\RuleBookSection;

class RuleBookController extends Controller
{
    public function index()
    {
        $sections = RuleBookSection::published()->orderBy('section_order')->get();
        return view('shared.rules.index', compact('sections'));
    }

    public function show(RuleBookSection $ruleBookSection)
    {
        abort_unless($ruleBookSection->is_published, 404);
        return view('shared.rules.show', ['section' => $ruleBookSection]);
    }
}
