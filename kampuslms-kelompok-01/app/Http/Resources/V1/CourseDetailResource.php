<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CourseDetailResource — Representasi JSON untuk detail satu mata kuliah.
 *
 * Digunakan pada:
 *   - GET /api/v1/courses/{id}
 *
 * Menyertakan jumlah materi dan tugas (withCount) sesuai kontrak.
 */
class CourseDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'code'              => $this->code,
            'name'              => $this->name,
            'description'       => $this->description,
            'sks'               => $this->sks,
            'status'            => $this->status,
            'lecturer'          => [
                'id'   => $this->lecturer->id,
                'name' => $this->lecturer->name,
            ],
            'materials_count'   => $this->materials_count,
            'assignments_count' => $this->assignments_count,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}
