<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'original_filename' => $this->original_filename,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'url' => Storage::disk('public')->url($this->file_path),
            'uploaded_by' => [
                'id' => $this->uploader->id,
                'name' => $this->uploader->name,
            ],
            'created_at' => $this->created_at,
        ];
    }
}