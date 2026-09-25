<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Models\LoanApplication;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function index(LoanApplication $loan_application)
    {
        $this->authorize('view', $loan_application);

        return DocumentResource::collection(
            $loan_application->documents()->with('uploader')->latest()->get()
        );
    }

    public function store(StoreDocumentRequest $request, LoanApplication $loan_application)
    {
        $this->authorize('view', $loan_application);

        $file = $request->file('file');
        $storedPath = $file->store('documents/'.$loan_application->id, 'public');

        $document = Document::create([
            'tenant_id' => $loan_application->tenant_id,
            'loan_application_id' => $loan_application->id,
            'uploaded_by' => $request->user()->id,
            'type' => $request->type,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $storedPath,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return new DocumentResource($document->load('uploader'));
    }
}