<?php

namespace App\Listeners;

use App\Events\AssignmentPublished;
use App\Notifications\AssignmentPublishedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Listener yang mengirim notifikasi tugas baru ke seluruh mahasiswa
 * yang terdaftar di mata kuliah terkait.
 *
 * Implements ShouldQueue: listener ini diproses oleh queue worker
 * di background agar response HTTP dosen tetap instan.
 * Tanpa queue, puluhan/ratusan INSERT ke tabel notifications +
 * koneksi SMTP akan memblokir siklus request HTTP.
 *
 * @see \App\Events\AssignmentPublished
 */

// 1. "implements ShouldQueue" membuat listener ini dikirim ke antrean (tabel 'jobs'),
//    BUKAN langsung dieksekusi saat dosen menekan tombol di web.
// 2. KETIKA QUEUE WORKER MATI:
//    - Web dosen tetap menampilkan "Tugas berhasil dibuat" (tanpa error).
//    - Tetapi lonceng notifikasi di web mahasiswa TIDAK AKAN MUNCUL (tertahan di tabel 'jobs').
// 3. KETIKA QUEUE WORKER DINYALAKAN (php artisan queue:work):
//    - Job di tabel 'jobs' diproses, barulah notifikasi masuk ke lonceng mahasiswa di web.
class SendAssignmentPublishedNotification implements ShouldQueue
{
    /**
     * Jumlah maksimal percobaan eksekusi job sebelum dinyatakan gagal permanen.
     * Setelah 3 kali gagal, job dipindahkan ke tabel `failed_jobs`.
     */
    public int $tries = 3;

    /**
     * Jeda waktu (detik) sebelum job dicoba ulang setelah gagal.
     * Memberi waktu agar kondisi sementara (misal SMTP down) bisa pulih.
     */
    public int $backoff = 10;

    /**
     * Handle the event.
     *
     * Mengirim notifikasi ke seluruh mahasiswa mata kuliah menggunakan
     * chunking agar memori tetap efisien saat jumlah mahasiswa besar.
     */
    public function handle(AssignmentPublished $event): void
    {
        $assignment = $event->assignment;

        // Eager-load relasi course beserta students agar tidak N+1
        $assignment->loadMissing('course.students');

        // Kirim notifikasi ke seluruh mahasiswa dengan chunking (100 per batch)
        $assignment->course->students()->chunk(100, function ($students) use ($assignment) {
            Notification::send($students, new AssignmentPublishedNotification($assignment));
        });
    }

    /**
     * Tangani kegagalan permanen setelah semua percobaan retry habis.
     *
     * Dicatat ke laravel.log agar admin/dosen bisa mendiagnosis
     * mengapa notifikasi publikasi tugas tidak sampai ke mahasiswa.
     */
    public function failed(AssignmentPublished $event, Throwable $exception): void
    {
        Log::error('SendAssignmentPublishedNotification gagal setelah semua retry.', [
            'assignment_id'    => $event->assignment->id,
            'assignment_title' => $event->assignment->title,
            'course_id'        => $event->assignment->course_id,
            'error'            => $exception->getMessage(),
        ]);
    }
}
