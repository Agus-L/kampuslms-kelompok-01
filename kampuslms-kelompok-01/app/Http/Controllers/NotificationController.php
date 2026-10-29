<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Daftar notifikasi milik user yang sedang login.
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        $unreadCount = $request->user()->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     *
     * Route model binding: {notification} → DatabaseNotification $notification
     *
     * Route: PATCH /notifications/{notification} → notifications.update
     */
    public function update(Request $request, DatabaseNotification $notification)
    {
        abort_unless($notification->notifiable_id === $request->user()->id, 403, 'Akses ditolak: notifikasi bukan milik Anda.');

        $notification->markAsRead();

        return back()->with('success', 'Notifikasi berhasil ditandai sudah dibaca.');
    }

    /**
     * Tandai semua notifikasi yang belum dibaca milik pengguna sebagai sudah dibaca.
     *
     * Route: POST /notifications/mark-all-read → notifications.mark-all-read
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi berhasil ditandai sudah dibaca.');
    }
}
