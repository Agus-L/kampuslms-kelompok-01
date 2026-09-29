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
        $notifications = $request->user()->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
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
        $notification->markAsRead();

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }
}
