<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Submission extends Model
{
    use HasFactory;

    /**
     * Boot model events.
     *
     * Event `deleting` menghapus berkas fisik dari disk privat saat record dihapus.
     * Catatan: cascadeOnDelete dari database level TIDAK memicu event ini —
     * jika assignment dihapus, berkas submission tetap tertinggal dan harus
     * dihapus secara eksplisit di controller sebelum menghapus induknya.
     */
    protected static function booted(): void
    {
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
