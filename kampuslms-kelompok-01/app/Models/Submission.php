<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Submission extends Model
{
    use HasFactory;

    /**
     * Boot model events.
     *
     * Invalidation cache statistik dashboard admin saat submission ditambah, diubah, atau dihapus (Build #6).
     * Event `deleting` menghapus berkas fisik dari disk privat saat record dihapus.
     * Catatan: cascadeOnDelete dari database level TIDAK memicu event ini —
     * jika assignment dihapus, berkas submission tetap tertinggal dan harus
     * dihapus secara eksplisit di controller sebelum menghapus induknya.
     */
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('admin.dashboard.stats'));
        static::deleted(fn () => Cache::forget('admin.dashboard.stats'));

        static::deleting(function (Submission $submission) {
            Storage::disk('local')->delete($submission->file_path);
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * Kolom yang TIDAK termasuk:
     * - id           → auto-increment PK
     * - user_id      → diisi dari auth()->id() di server, bukan input user
     *                  (mencegah mahasiswa mengirim atas nama orang lain)
     * - created_at   → dikelola otomatis oleh Eloquent
     * - updated_at   → dikelola otomatis oleh Eloquent
     *
     * @var list<string>
     */
    protected $fillable = [
        'assignment_id',
        'user_id',
        'file_path',
        'original_name',
        'file_size',
        'note',
        'submitted_at',
        'is_late',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'is_late' => 'boolean',
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
     |  Relations (§4.3)
     * ------------------------------------------------------------------ */

    /**
     * Tugas yang dikumpulkan.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Mahasiswa yang mengumpulkan.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Nilai untuk submission ini (one-to-one).
     */
    public function grade(): HasOne
    {
        return $this->hasOne(Grade::class);
    }
}
