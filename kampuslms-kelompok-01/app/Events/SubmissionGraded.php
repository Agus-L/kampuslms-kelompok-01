<?php

namespace App\Events;

use App\Models\Grade;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event yang dipicu saat dosen memberi atau memperbarui nilai pada submission.
 *
 * Data yang dibawa: objek Grade (memiliki relasi ke submission, submission->student,
 * dan submission->assignment).
 * Listener yang mendengarkan event ini akan mengirim notifikasi ke mahasiswa
 * pemilik submission yang bersangkutan.
 *
 * @see \App\Listeners\SendSubmissionGradedNotification
 */
class SubmissionGraded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Grade $grade)
    {
        //
    }
}
