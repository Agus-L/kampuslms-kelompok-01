<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreAssignmentRequest — Validasi untuk membuat tugas baru.
 *
 * Otorisasi:
 *   - User harus role dosen
 *   - User harus pengampu course_id yang dikirim
 *   (Dicek di controller untuk fleksibilitas pesan error 403)
 */
class StoreAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Otorisasi role dicek di controller agar bisa return 403 sesuai kontrak
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
            'course_id'    => 'required|integer|exists:courses,id',
            'title'        => 'required|string|max:255',
            'instructions' => 'required|string',
            'due_at'       => 'required|date|after:now',
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
            'course_id.required'    => 'Course ID wajib diisi.',
            'course_id.exists'      => 'Course tidak ditemukan.',
            'title.required'        => 'Judul tugas wajib diisi.',
            'instructions.required' => 'Instruksi tugas wajib diisi.',
            'due_at.required'       => 'Tenggat waktu wajib diisi.',
            'due_at.after'          => 'Tenggat waktu harus di masa depan.',
        ];
    }
}
