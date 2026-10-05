<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CourseResource — Representasi JSON untuk satu mata kuliah.
 *
 * Digunakan pada:
 *   - GET /api/v1/courses (collection)
 *   - Sebagai base untuk CourseDetailResource
 */
class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'code'        => $this->code,
            'name'        => $this->name,
            'description' => $this->description,
            'sks'         => $this->sks,
            'status'      => $this->status,
            'lecturer'    => [
                'id'   => $this->lecturer->id,
                'name' => $this->lecturer->name,
            ],
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
