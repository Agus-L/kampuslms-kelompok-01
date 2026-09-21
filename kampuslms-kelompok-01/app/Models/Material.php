<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * Kolom yang TIDAK termasuk:
     * - id           → auto-increment PK
     * - uploaded_by  → diisi dari auth()->id() di server, bukan input user
     *                  (mencegah pemalsuan identitas pengunggah)
     * - created_at   → dikelola otomatis oleh Eloquent
     * - updated_at   → dikelola otomatis oleh Eloquent
     *
     * @var list<string>
     */
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'type',
        'file_path',
        'original_name',
        'file_size',
        'mime_type',
        'external_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }
}
