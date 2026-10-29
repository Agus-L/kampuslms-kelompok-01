<?php

namespace App\Listeners;

use App\Events\SubmissionGraded;
use App\Notifications\SubmissionGradedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Listener yang mengirim notifikasi penilaian ke mahasiswa pemilik submission.
 *
 * Implements ShouldQueue: meskipun penerima hanya 1 orang, saluran mail
 * melibatkan handshake SMTP (1–3 detik). Dengan queue, tombol "Simpan Nilai"
 * di sisi dosen langsung merespons tanpa risiko timeout jika SMTP lambat.
 *
 * @see \App\Events\SubmissionGraded
 */
class SendSubmissionGradedNotification implements ShouldQueue
{
    /**
     * Jumlah maksimal percobaan eksekusi job sebelum dinyatakan gagal permanen.
     */
    public int $tries = 3;

    /**
     * Jeda waktu (detik) sebelum job dicoba ulang setelah gagal.
     */
    public int $backoff = 10;

    /**
     * Handle the event.
     *
     * Mengirim notifikasi ke mahasiswa pemilik submission.
     * Relasi submission->student diakses untuk menentukan penerima.
     */
    public function handle(SubmissionGraded $event): void
    {
        $grade = $event->grade;

        // Eager-load relasi yang dibutuhkan Notification
        $grade->loadMissing('submission.student', 'submission.assignment.course');

        $student = $grade->submission->student;

        $student->notify(new SubmissionGradedNotification($grade));
    }

    /**
     * Tangani kegagalan permanen setelah semua percobaan retry habis.
     *
     * Dicatat ke laravel.log agar admin bisa mendiagnosis
     * mengapa notifikasi penilaian tidak sampai ke mahasiswa.
     */
    public function failed(SubmissionGraded $event, Throwable $exception): void
    {
        Log::error('SendSubmissionGradedNotification gagal setelah semua retry.', [
            'grade_id'      => $event->grade->id,
            'submission_id' => $event->grade->submission_id,
            'student_id'    => $event->grade->submission?->user_id,
            'error'         => $exception->getMessage(),
        ]);
    }
}
