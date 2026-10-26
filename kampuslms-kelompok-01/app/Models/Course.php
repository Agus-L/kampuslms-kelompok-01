<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Course extends Model
{
    use HasFactory;

    /**
     * Boot model events.
     *
     * Event `deleting` menghapus seluruh berkas fisik anak saat mata kuliah dihapus.
     * cascadeOnDelete di database menghapus baris tabel anak, tetapi TIDAK
     * memicu event Eloquent — sehingga berkas fisik tertinggal di storage.
     *
     * Urutan: hapus materi (file materi) → hapus tugas (cascade ke submission files)
     * → bersihkan direktori materials/courses/{id}.
     */
    protected static function booted(): void
    {
        static::deleting(function (Course $course) {
            // 1. Hapus semua materi satu-satu agar event deleting di Material terpicu
            foreach ($course->materials as $material) {
                $material->delete();
            }

            // Bersihkan direktori materi mata kuliah
            Storage::disk('local')->deleteDirectory("materials/courses/{$course->id}");

            // 2. Hapus semua tugas satu-satu agar cascade ke submission terpicu
            foreach ($course->assignments as $assignment) {
                $assignment->delete();
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * Kolom yang TIDAK termasuk:
     * - id           → auto-increment PK
     * - created_at   → dikelola otomatis oleh Eloquent
     * - updated_at   → dikelola otomatis oleh Eloquent
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'sks',
        'lecturer_id',
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
            'sks' => 'integer',
        ];
    }

    /* ------------------------------------------------------------------ *
     |  Relations (§4.3)
     * ------------------------------------------------------------------ */

    /**
     * Dosen pengampu mata kuliah.
     */
    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    /**
     * Mahasiswa yang terdaftar — pivot course_user.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('id', 'enrolled_at')
                    ->withTimestamps();
    }

    /**
     * Materi kuliah.
     */
    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    /**
     * Tugas yang diberikan.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }
}
