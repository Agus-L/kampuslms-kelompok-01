<?php

namespace App\Listeners;

use App\Events\SubmissionGraded;
use App\Notifications\SubmissionGradedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

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
}
