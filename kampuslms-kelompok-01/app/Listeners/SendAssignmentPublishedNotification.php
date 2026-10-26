<?php

namespace App\Listeners;

use App\Events\AssignmentPublished;
use App\Notifications\AssignmentPublishedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

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
class SendAssignmentPublishedNotification implements ShouldQueue
{
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
}
