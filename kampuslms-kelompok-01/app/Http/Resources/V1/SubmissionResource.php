<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * SubmissionResource — Representasi JSON untuk satu submission.
 *
 * Digunakan pada:
 *   - GET  /api/v1/assignments/{id}/submissions (collection)
 *   - POST /api/v1/assignments/{id}/submissions (single, 201)
 */
class SubmissionResource extends JsonResource
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
            'assignment_id' => $this->assignment_id,
            'user_id'       => $this->user_id,
            'file_path'     => $this->file_path,
            'original_name' => $this->original_name,
            'file_size'     => $this->file_size,
            'note'          => $this->note,
            'submitted_at'  => $this->submitted_at,
            'is_late'       => $this->is_late,
            'student'       => new UserResource($this->whenLoaded('student')),
            'grade'         => new GradeResource($this->whenLoaded('grade')),
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
