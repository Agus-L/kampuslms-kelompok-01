<div wire:poll.30s class="nav-notification-container" style="display: inline-flex; align-items: center; position: relative;">
    <a href="{{ route('notifications.index') }}"
       class="nav-notification-btn {{ request()->routeIs('notifications.*') ? 'active' : '' }}"
       title="Pusat Notifikasi"
       aria-label="Pusat Notifikasi ({{ $unreadCount }} belum dibaca)"
       style="position: relative; display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 9999px; background: var(--badge-bg); border: 1px solid var(--badge-border); color: var(--text-main); text-decoration: none; font-size: 1.1rem; transition: transform 0.15s ease, background 0.15s ease;">
        <span style="display: inline-block; line-height: 1;">🔔</span>

        @if ($unreadCount > 0)
            <span class="notification-badge"
                  style="position: absolute; top: -3px; right: -3px; background: #ef4444; color: #ffffff; font-size: 0.65rem; font-weight: 700; min-width: 18px; height: 18px; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center; padding: 0 4px; box-shadow: 0 0 0 2px var(--bg-body); line-height: 1;">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </a>
</div>
