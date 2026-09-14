<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Assignment extends Model
{
    use HasFactory;

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
