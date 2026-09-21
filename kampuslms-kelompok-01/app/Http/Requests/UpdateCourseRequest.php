<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Ganti dengan Policy di minggu 7
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $course = $this->route('course');

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('courses', 'code')->ignore($course),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'sks' => [
                'required',
                'integer',
                'min:1',
                'max:6',
            ],
            'lecturer_id' => [
                'required',
                Rule::exists('users', 'id')
                    ->where('role', 'dosen')
                    ->whereNull('deleted_at'),
            ],
            'status' => [
                'required',
                Rule::in(['draft', 'active', 'archived']),
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode mata kuliah wajib diisi.',
            'code.string'   => 'Kode mata kuliah harus berupa teks.',
            'code.max'      => 'Kode mata kuliah maksimal terdiri dari :max karakter.',
            'code.unique'   => 'Kode mata kuliah ini sudah digunakan oleh mata kuliah lain.',

            'name.required' => 'Nama mata kuliah wajib diisi.',
            'name.string'   => 'Nama mata kuliah harus berupa teks.',
            'name.max'      => 'Nama mata kuliah tidak boleh lebih dari :max karakter.',

            'description.string' => 'Deskripsi mata kuliah harus berupa teks.',

            'sks.required' => 'Bobot SKS wajib ditentukan.',
            'sks.integer'  => 'Bobot SKS harus berupa angka bulat.',
            'sks.min'      => 'Bobot SKS minimal :min SKS.',
            'sks.max'      => 'Bobot SKS maksimal :max SKS.',

            'lecturer_id.required' => 'Dosen pengampu wajib dipilih.',
            'lecturer_id.exists'   => 'Dosen yang dipilih tidak valid atau bukan merupakan dosen aktif.',

            'status.required' => 'Status mata kuliah wajib dipilih.',
            'status.in'       => 'Status mata kuliah harus salah satu dari: draft, active, atau archived.',
        ];
    }
}
