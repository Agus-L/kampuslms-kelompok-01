<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdateAssignmentRequest — Validasi untuk memperbarui tugas.
 *
 * Semua field bersifat 'sometimes' agar mendukung partial update (PATCH).
 * Otorisasi kepemilikan dicek di controller.
 */
class UpdateAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_id'    => 'sometimes|integer|exists:courses,id',
            'title'        => 'sometimes|string|max:255',
            'instructions' => 'sometimes|string',
            'due_at'       => 'sometimes|date|after:now',
            'max_score'    => 'sometimes|integer|min:1|max:255',
            'allow_late'   => 'sometimes|boolean',
            'status'       => 'sometimes|in:draft,published',
        ];
    }

    /**
     * Pesan validasi sesuai kontrak API.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'course_id.exists' => 'Course tidak ditemukan.',
            'due_at.after'     => 'Tenggat waktu harus di masa depan.',
        ];
    }
}
