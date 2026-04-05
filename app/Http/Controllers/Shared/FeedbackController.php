<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Feedback;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'complaint_id' => ['nullable', 'exists:complaints,id'],
            'rating'       => ['required', 'integer', 'min:1', 'max:5'],
            'comment'      => ['nullable', 'string', 'max:1000'],
            'category'     => ['required', 'in:service,resolution,staff,general'],
        ]);

        Feedback::create(array_merge($data, ['user_id' => $request->user()->id]));

        return back()->with('success', 'Thank you for your feedback!');
    }
}
