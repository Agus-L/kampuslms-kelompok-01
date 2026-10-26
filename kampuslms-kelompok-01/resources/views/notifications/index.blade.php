<x-layout title="Pusat Notifikasi — Kampus LMS">
    <div class="courses-container" style="max-width: 960px; margin: 0 auto; padding: 1.5rem 1rem;">
        
        {{-- Header & Aksi Cepat --}}
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem; border-bottom: 1px solid var(--card-border); padding-bottom: 1.25rem;">
            <div>
                <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 0.6rem;">
                    <span>🔔</span> Pusat Notifikasi
                </h1>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.35rem; margin-bottom: 0;">
                    Pemberitahuan tugas baru, pengumuman nilai, dan aktivitas kelas Anda.
                </p>
            </div>

            @if ($unreadCount > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" 
                            style="display: inline-flex; align-items: center; gap: 0.5rem; background: var(--badge-bg); border: 1px solid var(--badge-border); color: var(--primary-light); padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease;">
                        <span>✓✓</span> Tandai Semua Sudah Dibaca ({{ $unreadCount }})
                    </button>
                </form>
            @endif
        </div>

        {{-- Daftar Notifikasi --}}
        @if ($notifications->isEmpty())
            <div style="text-align: center; padding: 4rem 1.5rem; background: var(--card-bg); border: 1px solid var(--card-border); border-radius: var(--radius); box-shadow: var(--card-shadow);">
                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.7;">📭</div>
                <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">Belum Ada Notifikasi</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; max-width: 420px; margin: 0 auto;">
                    Anda belum memiliki notifikasi saat ini. Notifikasi akan otomatis muncul ketika ada tugas baru atau penilaian dari dosen.
                </p>
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 0.85rem; margin-bottom: 2rem;">
                @foreach ($notifications as $notification)
                    @php
                        $isUnread = is_null($notification->read_at);
                        $data = $notification->data;
                        $actionUrl = $data['action_url'] ?? null;

                        // Jika notifikasi memiliki assignment_id dan user adalah mahasiswa, arahkan langsung ke halaman detail tugas
                        if (!empty($data['assignment_id']) && auth()->check() && auth()->user()->role === 'mahasiswa') {
                            $actionUrl = route('mahasiswa.assignments.show', $data['assignment_id']);
                        }

                        if ($actionUrl) {
                            $parsedPath = parse_url($actionUrl, PHP_URL_PATH);
                            $parsedQuery = parse_url($actionUrl, PHP_URL_QUERY);
                            if ($parsedPath) {
                                $actionUrl = $parsedPath . ($parsedQuery ? '?' . $parsedQuery : '');
                            }
                        }
                    @endphp

                    <div style="display: flex; flex-direction: column; gap: 0.75rem; background: var(--card-bg); border: 1px solid {{ $isUnread ? 'var(--card-hover-border)' : 'var(--card-border)' }}; border-left: 4px solid {{ $isUnread ? 'var(--primary)' : 'transparent' }}; border-radius: var(--radius); padding: 1.25rem; transition: all 0.2s ease; box-shadow: var(--card-shadow);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                                <span style="font-size: 1.2rem;">
                                    {{ str_contains($notification->type, 'Assignment') ? '📝' : '📊' }}
                                </span>
                                <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.2rem 0.55rem; border-radius: 9999px; background: var(--badge-bg); color: var(--badge-text); border: 1px solid var(--badge-border);">
                                    {{ $data['course_name'] ?? 'Mata Kuliah' }}
                                </span>
                                @if ($isUnread)
                                    <span style="font-size: 0.72rem; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 9999px; background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);">
                                        Baru
                                    </span>
                                @endif
                            </div>
                            
                            <span style="font-size: 0.78rem; color: var(--text-muted);">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>

                        {{-- Pesan / Konten Notifikasi --}}
                        <div style="color: var(--text-main); font-size: 0.95rem; line-height: 1.5;">
                            {{ $data['message'] ?? ($data['title'] ?? 'Ada pembaruan aktivitas pada akun Anda.') }}
                        </div>

                        {{-- Tombol Aksi --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap; margin-top: 0.25rem; padding-top: 0.75rem; border-top: 1px solid rgba(255, 255, 255, 0.05);">
                            <div>
                                @if ($actionUrl)
                                    <a href="{{ $actionUrl }}"
                                       style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; font-weight: 600; color: var(--primary-light); text-decoration: none;">
                                        Lihat Rincian →
                                    </a>
                                @endif
                            </div>

                            @if ($isUnread)
                                <form action="{{ route('notifications.update', $notification->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            style="background: none; border: 1px solid var(--card-border); color: var(--text-muted); font-size: 0.78rem; padding: 0.3rem 0.65rem; border-radius: 0.375rem; cursor: pointer; transition: all 0.15s ease;">
                                        Tandai sudah dibaca
                                    </button>
                                </form>
                            @else
                                <span style="font-size: 0.75rem; color: var(--text-muted); opacity: 0.7;">
                                    ✓ Dibaca {{ $notification->read_at->diffForHumans() }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination Links --}}
            <div style="margin-top: 1.5rem;">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-layout>
