<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Notifikasi tugas baru dipublikasikan.
 *
 * Dikirim ke seluruh mahasiswa yang terdaftar di mata kuliah terkait.
 *
 * Saluran:
 * - database → insert ke tabel `notifications` (lonceng in-app)
 * - mail     → email ke inbox mahasiswa
 *
 * Implements ShouldQueue agar pengiriman ke banyak mahasiswa
 * tidak memblokir response HTTP dosen.
 */
class AssignmentPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Jumlah maksimal percobaan pengiriman notifikasi per mahasiswa.
     */
    public int $tries = 3;

    /**
     * Jeda waktu (detik) sebelum notifikasi dicoba ulang.
     */
    public int $backoff = 10;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Assignment $assignment)
    {
        //
    }

    /**
     * Saluran pengiriman notifikasi.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Payload yang disimpan ke tabel `notifications` (kolom `data`, JSON).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $course = $this->assignment->course;

        return [
            'assignment_id' => $this->assignment->id,
            'course_id'     => $course->id,
            'course_name'   => $course->name,
            'title'         => $this->assignment->title,
            'due_at'        => $this->assignment->due_at?->toIso8601String(),
            'message'       => "Tugas baru '{$this->assignment->title}' telah dipublikasikan di mata kuliah {$course->name}.",
            'action_url'    => route('mahasiswa.assignments.show', $this->assignment->id),
        ];
    }

    /**
     * Representasi email notifikasi.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $course = $this->assignment->course;
        $dueLabel = $this->assignment->due_at
            ? $this->assignment->due_at->translatedFormat('l, d F Y – H:i')
            : 'Tidak ada batas waktu';

        return (new MailMessage)
            ->subject("[LMS] Tugas Baru: {$this->assignment->title} – {$course->name}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Tugas baru telah dipublikasikan di mata kuliah **{$course->name}**.")
            ->line("**Judul:** {$this->assignment->title}")
            ->line("**Tenggat:** {$dueLabel}")
            ->action('Lihat Tugas', route('mahasiswa.assignments.show', $this->assignment->id))
            ->line('Segera kerjakan sebelum batas waktu berakhir.');
    }

    /**
     * Tangani kegagalan pengiriman notifikasi setelah semua retry habis.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('AssignmentPublishedNotification gagal terkirim.', [
            'assignment_id' => $this->assignment->id,
            'error'         => $exception->getMessage(),
        ]);
    }
}
