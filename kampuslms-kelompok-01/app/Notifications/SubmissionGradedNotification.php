<?php

namespace App\Notifications;

use App\Models\Grade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Notifikasi submission telah dinilai oleh dosen.
 *
 * Dikirim ke 1 mahasiswa pemilik submission yang bersangkutan.
 *
 * Saluran:
 * - database → insert ke tabel `notifications` (lonceng in-app)
 * - mail     → email ke inbox mahasiswa
 *
 * Implements ShouldQueue agar handshake SMTP tidak memblokir
 * response HTTP dosen saat menyimpan nilai.
 */
class SubmissionGradedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Jumlah maksimal percobaan pengiriman notifikasi.
     */
    public int $tries = 3;

    /**
     * Jeda waktu (detik) sebelum notifikasi dicoba ulang.
     */
    public int $backoff = 10;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Grade $grade)
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
     * Sesuai ketentuan Modul Minggu 10 (Deliverable 8):
     * Payload database HANYA menyimpan ID dan judul, TIDAK MEMUAT nilai (score)
     * atau umpan balik (feedback) untuk mencegah kebocoran data sensitif.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $submission = $this->grade->submission;
        $assignment = $submission->assignment;
        $course     = $assignment->course;

        return [
            'grade_id'         => $this->grade->id,
            'submission_id'    => $submission->id,
            'assignment_title' => $assignment->title,
            'course_name'      => $course->name,
            'message'          => "Hasil pengumpulan '{$assignment->title}' pada mata kuliah {$course->name} telah diperiksa oleh dosen.",
            'action_url'       => route('mahasiswa.submissions.show', $submission->id),
        ];
    }

    /**
     * Representasi email notifikasi.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $submission = $this->grade->submission;
        $assignment = $submission->assignment;
        $course     = $assignment->course;

        $mail = (new MailMessage)
            ->subject("[LMS] Nilai Keluar: {$assignment->title}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Tugas Anda pada mata kuliah **{$course->name}** telah dinilai.")
            ->line("**Tugas:** {$assignment->title}")
            ->line("**Skor:** {$this->grade->score} / {$assignment->max_score}");

        if ($this->grade->feedback) {
            $mail->line("**Catatan Dosen:** {$this->grade->feedback}");
        }

        return $mail
            ->action('Lihat Detail Nilai', route('mahasiswa.submissions.grades.index', $submission->id))
            ->line('Terima kasih telah mengerjakan tugas.');
    }

    /**
     * Tangani kegagalan pengiriman notifikasi setelah semua retry habis.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('SubmissionGradedNotification gagal terkirim.', [
            'grade_id' => $this->grade->id,
            'error'    => $exception->getMessage(),
        ]);
    }
}
