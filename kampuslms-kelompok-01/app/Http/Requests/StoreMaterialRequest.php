<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Otorisasi ditangani terpusat oleh MaterialPolicy di controller
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Minggu 9: Validasi upload berkas fisik (bukan metadata-only lagi).
     *
     * - Jika type=file → field `file` wajib diisi berkas PDF/PPTX/DOC/DOCX, maks 20 MB.
     * - Jika type=link → field `external_url` wajib diisi URL valid.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'required|in:file,link',

            // Upload berkas fisik — wajib saat type=file
            // Max 20480 KB = 20 MB
            'file'         => 'required_if:type,file|file|mimes:pdf,ppt,pptx,doc,docx|max:20480',

            // Tautan eksternal — wajib saat type=link
            'external_url' => 'nullable|required_if:type,link|url|max:2048',
        ];
    }

    /**
     * Custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'title.required'           => 'Judul materi wajib diisi.',
            'type.required'            => 'Tipe materi wajib dipilih.',
            'type.in'                  => 'Tipe materi harus berupa file atau link.',
            'file.required_if'         => 'Berkas dokumen wajib diunggah jika tipe materi berupa file.',
            'file.file'                => 'Field file harus berupa berkas yang valid.',
            'file.mimes'               => 'Format berkas harus PDF, PPT, PPTX, DOC, atau DOCX.',
            'file.max'                 => 'Ukuran berkas tidak boleh melebihi 20 MB.',
            'external_url.required_if' => 'Tautan URL eksternal wajib diisi jika tipe materi berupa link.',
            'external_url.url'         => 'Format tautan URL tidak valid.',
        ];
    }
}
