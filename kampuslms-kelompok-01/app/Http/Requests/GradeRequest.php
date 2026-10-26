<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki otorisasi untuk membuat request ini.
     * Otorisasi peran dan kepemilikan ditangani terpusat oleh GradePolicy di Controller.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk pemberian dan pembaruan nilai tugas.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $submission = $this->route('submission') ?? $this->route('grade')?->submission;
        $maxScore = $submission?->assignment?->max_score ?? 100;

        return [
            'score'    => ['required', 'integer', 'min:0', "max:{$maxScore}"],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Pesan kustom untuk kegagalan validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'score.required' => 'Nilai wajib diisi.',
            'score.integer'  => 'Nilai harus berupa bilangan bulat utuh (tidak boleh desimal, menggunakan tanda titik atau koma).',
            'score.min'      => 'Nilai minimal adalah 0.',
            'score.max'      => 'Nilai tidak boleh melebihi skor maksimal tugas.',
            'feedback.max'   => 'Umpan balik (feedback) maksimal 2000 karakter.',
        ];
    }
}
