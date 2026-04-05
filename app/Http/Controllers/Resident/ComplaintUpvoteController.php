<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Complaint;
use App\Models\ComplaintUpvote;

class ComplaintUpvoteController extends Controller
{
    public function toggle(Request $request, Complaint $complaint)
    {
        $userId = $request->user()->id;
        $existing = ComplaintUpvote::where('complaint_id', $complaint->id)
            ->where('user_id', $userId)->first();

        if ($existing) {
            $existing->delete();
            $complaint->decrement('upvotes');
            $voted = false;
        } else {
            ComplaintUpvote::create(['complaint_id' => $complaint->id, 'user_id' => $userId]);
            $complaint->increment('upvotes');
            $voted = true;
        }

        return response()->json(['voted' => $voted, 'count' => $complaint->fresh()->upvotes]);
    }
}
