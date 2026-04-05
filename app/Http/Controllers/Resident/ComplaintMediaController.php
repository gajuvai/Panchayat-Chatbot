<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Complaint;
use App\Models\ComplaintMedia;

class ComplaintMediaController extends Controller
{
    public function store(Request $request, Complaint $complaint)
    {
        $this->authorize('update', $complaint);

        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,mp3,wav,webm,ogg,m4a'],
        ]);

        $file = $request->file('file');
        $mime = $file->getMimeType();

        $mediaType = match(true) {
            str_starts_with($mime, 'image/')                          => 'image',
            str_starts_with($mime, 'audio/') || in_array($file->getClientOriginalExtension(), ['webm', 'ogg', 'm4a']) => 'voice',
            in_array($file->getClientOriginalExtension(), ['pdf', 'doc', 'docx']) => 'document',
            default                                                   => 'document',
        };

        $path = $file->store("complaints/{$complaint->id}", 'public');

        $media = ComplaintMedia::create([
            'complaint_id' => $complaint->id,
            'file_path'    => $path,
            'file_name'    => $file->getClientOriginalName(),
            'mime_type'    => $mime,
            'file_size'    => $file->getSize(),
            'media_type'   => $mediaType,
        ]);

        return response()->json(['success' => true, 'media_id' => $media->id, 'url' => $media->file_url]);
    }

    public function destroy(Request $request, ComplaintMedia $media)
    {
        $this->authorize('update', $media->complaint);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($media->file_path);
        $media->delete();
        return response()->json(['success' => true]);
    }
}
