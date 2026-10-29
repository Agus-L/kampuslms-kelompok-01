<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationBell extends Component
{
    /**
     * Render komponen lonceng dengan jumlah notifikasi belum dibaca.
     */
    public function render()
    {
        $unreadCount = auth()->check()
            ? auth()->user()->unreadNotifications()->count()
            : 0;

        return view('livewire.notification-bell', [
            'unreadCount' => $unreadCount,
        ]);
    }
}
