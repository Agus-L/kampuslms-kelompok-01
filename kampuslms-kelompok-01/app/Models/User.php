<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * Boot model events.
     * Membersihkan cache statistik dashboard admin saat user baru dibuat, diubah, atau dihapus (Build #6).
     */
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('admin.dashboard.stats'));
        static::deleted(fn () => Cache::forget('admin.dashboard.stats'));
        static::restored(fn () => Cache::forget('admin.dashboard.stats'));
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nim_nip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------ *
     |  Relations (§4.3)
     * ------------------------------------------------------------------ */

    /**
     * Mata kuliah yang diajar (sebagai dosen).
     */
    public function taughtCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'lecturer_id');
    }

    /**
     * Mata kuliah yang diikuti (sebagai mahasiswa) — pivot course_user.
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)
                    ->withPivot('enrolled_at')
                    ->withTimestamps();
    }

    /**
     * Submission tugas yang dikumpulkan.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Nilai yang diberikan (sebagai dosen/grader).
     */
    public function gradesGiven(): HasMany
    {
        return $this->hasMany(Grade::class, 'graded_by');
    }
}
