<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreSubmissionRequest — Validasi upload submission tugas oleh mahasiswa.
 *
 * Kontrak: multipart, file
 */
class StoreSubmissionRequest extends FormRequest
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
            'file' => 'required|file|max:20480',
            'note' => 'nullable|string',
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
            'file.required' => 'Berkas tugas wajib diunggah.',
            'file.file'     => 'Berkas harus berupa file yang valid.',
            'file.max'      => 'Ukuran berkas tidak boleh melebihi 20MB.',
        ];
    }
}
