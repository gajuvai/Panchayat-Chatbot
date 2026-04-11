<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentLibraryController extends Controller
{
    public function index(Request $request): View
    {
        $user  = $request->user();
        $query = Document::with(['category', 'uploadedBy'])
            ->accessibleBy($user)
            ->latest();

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('title', 'like', "%{$s}%")
                                       ->orWhere('description', 'like', "%{$s}%"));
        }

        $documents  = $query->paginate(15)->withQueryString();
        $categories = DocumentCategory::active()->withCount(['documents' => fn ($q) => $q->accessibleBy($user)])->orderBy('name')->get();

        return view('shared.documents.index', compact('documents', 'categories'));
    }

    public function download(Document $document, Request $request): Response
    {
        abort_unless($document->isAccessibleBy($request->user()), 403);

        abort_unless(Storage::disk('local')->exists($document->file_path), 404, 'File not found.');

        $document->increment('download_count');

        return response()->download(
            Storage::disk('local')->path($document->file_path),
            $document->file_name
        );
    }
}
