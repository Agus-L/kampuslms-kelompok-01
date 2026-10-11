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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'required|in:file,link',
            'external_url' => 'nullable|required_if:type,link|url|max:2048',
            'original_name'=> 'nullable|string|max:255',
            'file_path'    => 'nullable|string|max:255',
            'file_size'    => 'nullable|integer',
            'mime_type'    => 'nullable|string|max:100',
        ];
    }

    /**
     * Custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'title.required'        => 'Judul materi wajib diisi.',
            'type.required'         => 'Tipe materi wajib dipilih.',
            'type.in'               => 'Tipe materi harus berupa file atau link.',
            'external_url.required_if' => 'Tautan URL eksternal wajib diisi jika tipe materi berupa link.',
            'external_url.url'      => 'Format tautan URL tidak valid.',
        ];
    }
}
