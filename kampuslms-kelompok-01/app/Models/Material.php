<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Material extends Model
{
    use HasFactory;

    /**
     * Boot model events.
     *
     * Invalidation cache statistik dashboard admin saat materi ditambah, diubah, atau dihapus (Build #6).
     * Event `deleting` menghapus berkas fisik dari disk privat saat record dihapus.
     * Diperlukan untuk kasus cascade: ketika Course dihapus, event ini memastikan
     * berkas materi tidak tertinggal sebagai orphaned file di storage.
     */
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('admin.dashboard.stats'));
        static::deleted(fn () => Cache::forget('admin.dashboard.stats'));

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
     |  Helpers — BUILD Minggu 9 No. 7
     * ------------------------------------------------------------------ */

    /**
     * Tampilkan ukuran berkas dalam format terbaca manusia.
     *
     * Contoh keluaran: "512 Bytes", "12,3 KB", "2,1 MB".
     * Digunakan di seluruh Blade view agar logika konversi tidak diulang inline.
     */
    public function humanReadableSize(): string
    {
        $bytes = $this->file_size;

        if ($bytes === null || $bytes === 0) {
            return '0 Bytes';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' Bytes';
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
