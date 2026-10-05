<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\NotificationCollection;
use App\Http\Resources\V1\NotificationResource;
use Illuminate\Http\Request;

/**
 * API NotificationController — Notifikasi Pengguna.
 *
 * Endpoint:
 *   GET  /api/v1/notifications        → index (auth)
 *   POST /api/v1/notifications/{id}/read → markAsRead (auth)
 */
class NotificationController extends Controller
{
    /**
     * GET /api/v1/notifications
     *
     * Menampilkan daftar notifikasi milik pengguna yang sedang login.
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return new NotificationCollection($notifications);
    }

    /**
     * POST /api/v1/notifications/{id}/read
     *
     * Menandai satu notifikasi sebagai telah dibaca.
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        abort_unless($notification, 404, 'Notifikasi tidak ditemukan.');

        $notification->markAsRead();

        return new NotificationResource($notification);
    }
}
