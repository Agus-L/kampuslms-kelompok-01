<?php

namespace App\Events;

use App\Models\Assignment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event yang dipicu saat dosen mempublikasikan tugas baru.
 *
 * Data yang dibawa: objek Assignment (memiliki relasi ke course dan course->students).
 * Listener yang mendengarkan event ini akan mengirim notifikasi ke seluruh
 * mahasiswa yang terdaftar di mata kuliah terkait.
 *
 * @see \App\Listeners\SendAssignmentPublishedNotification
 */
class AssignmentPublished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Assignment $assignment)
    {
        //
    }
}
