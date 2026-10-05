<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * AssignmentResource — Representasi JSON untuk satu tugas.
 *
 * Digunakan pada:
 *   - GET  /api/v1/courses/{id}/assignments (collection)
 *   - POST /api/v1/assignments (single, 201)
 *   - PUT  /api/v1/assignments/{id} (single, 200)
 */
class AssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'course_id'    => $this->course_id,
            'title'        => $this->title,
            'instructions' => $this->instructions,
            'due_at'       => $this->due_at,
            'max_score'    => $this->max_score,
            'allow_late'   => $this->allow_late,
            'status'       => $this->status,
            'created_by'   => $this->created_by,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}
