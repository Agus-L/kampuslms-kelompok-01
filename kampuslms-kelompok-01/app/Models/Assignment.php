<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Storage;

class Assignment extends Model
{
    use HasFactory;

    /**
     * Boot model events.
     *
     * Event `deleting` menghapus berkas fisik seluruh submission saat tugas dihapus.
     * cascadeOnDelete di database TIDAK memicu event Eloquent pada child,
     * sehingga kita harus menghapus setiap submission secara eksplisit
     * agar event `deleting` di model Submission ikut terpicu.
     * Setelah itu, direktori submissions/{assignment_id} juga dibersihkan.
     */
    protected static function booted(): void
    {
        static::deleting(function (Assignment $assignment) {
            // Hapus tiap submission satu-satu agar event deleting di Submission terpicu
            foreach ($assignment->submissions as $submission) {
                $submission->delete();
            }

            // Bersihkan direktori tugas yang mungkin tersisa
            Storage::disk('local')->deleteDirectory("submissions/{$assignment->id}");
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * Kolom yang TIDAK termasuk:
     * - id           → auto-increment PK
     * - created_by   → diisi dari auth()->id() di server, bukan input user
     *                  (mencegah pemalsuan identitas pembuat tugas)
     * - created_at   → dikelola otomatis oleh Eloquent
     * - updated_at   → dikelola otomatis oleh Eloquent
     *
     * @var list<string>
     */
    protected $fillable = [
        'course_id',
        'title',
        'instructions',
        'due_at',
        'max_score',
        'allow_late',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'max_score' => 'integer',
            'allow_late' => 'boolean',
        ];
    }

    /* ------------------------------------------------------------------ *
     |  Relations (§4.3)
     * ------------------------------------------------------------------ */

    /**
     * Mata kuliah tempat tugas ini berada.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Submission yang dikumpulkan untuk tugas ini.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Nilai semua submission melalui tabel submissions.
     */
    public function grades(): HasManyThrough
    {
        return $this->hasManyThrough(Grade::class, Submission::class);
    }
}
