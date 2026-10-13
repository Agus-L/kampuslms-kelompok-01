<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Otorisasi ditangani terpusat oleh AssignmentPolicy di controller
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
            'title'        => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'due_at'       => 'required|date',
            'max_score'    => 'required|integer|min:1|max:100',
            'allow_late'   => 'sometimes|boolean',
            'status'       => 'sometimes|in:draft,published,closed',
        ];
    }

    /**
     * Custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'title.required'        => 'Judul penugasan wajib diisi.',
            'due_at.required'       => 'Tenggat waktu pengumpulan (deadline) wajib diisi.',
            'due_at.date'           => 'Format tenggat waktu tidak valid.',
            'max_score.required'    => 'Nilai maksimal wajib diisi.',
            'max_score.integer'     => 'Nilai maksimal harus berupa angka.',
            'max_score.min'         => 'Nilai maksimal minimal adalah 1.',
            'max_score.max'         => 'Nilai maksimal tidak boleh lebih dari 100.',
        ];
    }
}
