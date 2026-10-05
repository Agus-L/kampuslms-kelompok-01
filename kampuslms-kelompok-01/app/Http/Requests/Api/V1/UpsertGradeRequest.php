<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpsertGradeRequest — Validasi penilaian submission oleh dosen.
 *
 * Kontrak: score, feedback — upsert, aman dipanggil berulang
 */
class UpsertGradeRequest extends FormRequest
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
            'score'    => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ];
    }

    /**
     * Pesan validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'score.required' => 'Nilai (score) wajib diisi.',
            'score.numeric'  => 'Nilai harus berupa angka.',
            'score.min'      => 'Nilai minimal adalah 0.',
            'score.max'      => 'Nilai maksimal adalah 100.',
        ];
    }
}
