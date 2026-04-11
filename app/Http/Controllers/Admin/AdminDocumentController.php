<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Document::with(['category', 'uploadedBy'])->latest();

        if ($request->filled('category')) $query->where('category_id', $request->category);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('title', 'like', "%{$s}%")->orWhere('file_name', 'like', "%{$s}%"));
        }

        $documents  = $query->paginate(15)->withQueryString();
        $categories = DocumentCategory::orderBy('name')->get();

        return view('admin.documents.index', compact('documents', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'category_id'  => ['required', 'exists:document_categories,id'],
            'access_level' => ['required', 'in:all,resident,admin'],
            'file'         => ['required', 'file', 'max:25600', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg'],
        ]);

        $file   = $request->file('file');
        $path   = $file->store('documents', 'local'); // private disk

        Document::create([
            'uploaded_by'  => $request->user()->id,
            'category_id'  => $data['category_id'],
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'file_path'    => $path,
            'file_name'    => $file->getClientOriginalName(),
            'mime_type'    => $file->getMimeType(),
            'file_size'    => $file->getSize(),
            'access_level' => $data['access_level'],
        ]);

        return redirect()->route('admin.documents.index')
            ->with('success', 'Document uploaded.');
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'category_id'  => ['required', 'exists:document_categories,id'],
            'access_level' => ['required', 'in:all,resident,admin'],
            'file'         => ['nullable', 'file', 'max:25600', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg'],
        ]);

        if ($request->hasFile('file')) {
            Storage::disk('local')->delete($document->file_path);
            $file             = $request->file('file');
            $data['file_path']  = $file->store('documents', 'local');
            $data['file_name']  = $file->getClientOriginalName();
            $data['mime_type']  = $file->getMimeType();
            $data['file_size']  = $file->getSize();
            $data['version']    = $document->version + 1;
        }

        unset($data['file']);
        $document->update($data);

        return redirect()->route('admin.documents.index')
            ->with('success', 'Document updated.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return redirect()->route('admin.documents.index')
            ->with('success', 'Document deleted.');
    }

    // ─── Category Management ──────────────────────────────────────────────────

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:document_categories,name'],
            'icon' => ['nullable', 'string', 'max:10'],
        ]);
        DocumentCategory::create($data);
        return redirect()->route('admin.documents.index')->with('success', 'Category created.');
    }

    public function destroyCategory(DocumentCategory $documentCategory): RedirectResponse
    {
        if ($documentCategory->documents()->count() > 0) {
            return redirect()->route('admin.documents.index')
                ->with('error', 'Cannot delete — category has documents.');
        }
        $documentCategory->delete();
        return redirect()->route('admin.documents.index')->with('success', 'Category deleted.');
    }
}
