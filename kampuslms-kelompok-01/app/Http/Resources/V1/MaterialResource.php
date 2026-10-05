<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * MaterialResource — Representasi JSON untuk satu materi.
 *
 * Digunakan pada:
 *   - GET /api/v1/courses/{id}/materials (collection)
 */
class MaterialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'course_id'     => $this->course_id,
            'title'         => $this->title,
            'description'   => $this->description,
            'type'          => $this->type,
            'file_path'     => $this->file_path,
            'original_name' => $this->original_name,
            'file_size'     => $this->file_size,
            'mime_type'     => $this->mime_type,
            'external_url'  => $this->external_url,
            'uploaded_by'   => $this->uploaded_by,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
