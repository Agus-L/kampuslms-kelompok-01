<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Material extends Model
{
    use HasFactory;

    /**
     * Boot model events.
     *
     * Event `deleting` menghapus berkas fisik dari disk privat saat record dihapus.
     * Diperlukan untuk kasus cascade: ketika Course dihapus, event ini memastikan
     * berkas materi tidak tertinggal sebagai orphaned file di storage.
     */
    protected static function booted(): void
    {
        static::deleting(function (Material $material) {
            if ($material->file_path && Storage::disk('local')->exists($material->file_path)) {
                Storage::disk('local')->delete($material->file_path);
            }
        });
    }

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

    /* ------------------------------------------------------------------ *
     |  Relations
     * ------------------------------------------------------------------ */

    /**
     * Mata kuliah tempat materi ini berada.     
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * User yang mengunggah materi ini.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
