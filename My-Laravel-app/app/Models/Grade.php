<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * Kolom yang TIDAK termasuk:
     * - id           → auto-increment PK
     * - graded_by    → diisi dari auth()->id() di server, bukan input user
     *                  (mencegah pemalsuan identitas penilai)
     * - created_at   → dikelola otomatis oleh Eloquent
     * - updated_at   → dikelola otomatis oleh Eloquent
     *
     * @var list<string>
     */
    protected $fillable = [
        'submission_id',
        'score',
        'feedback',
        'graded_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'graded_at' => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------ *
     |  Relations (§4.3)
     * ------------------------------------------------------------------ */

    /**
     * Submission yang dinilai.
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * Dosen/grader yang memberi nilai.
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}
