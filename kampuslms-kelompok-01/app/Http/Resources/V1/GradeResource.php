<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * GradeResource — Representasi JSON untuk satu nilai.
 *
 * Digunakan pada:
 *   - PUT /api/v1/submissions/{id}/grade (single, 200/201 — upsert)
 *   - Disematkan di dalam SubmissionResource via whenLoaded('grade')
 */
class GradeResource extends JsonResource
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
            'submission_id' => $this->submission_id,
            'score'         => $this->score,
            'feedback'      => $this->feedback,
            'graded_by'     => $this->graded_by,
            'graded_at'     => $this->graded_at,
            'grader'        => new UserResource($this->whenLoaded('grader')),
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
