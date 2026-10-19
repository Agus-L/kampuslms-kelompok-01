<div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 1.5rem;">
    {{-- Notifikasi Flash --}}
    @if ($message)
        <div style="background: {{ $messageType === 'success' ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' }}; border: 1px solid {{ $messageType === 'success' ? '#10b981' : '#ef4444' }}; color: {{ $messageType === 'success' ? '#10b981' : '#fca5a5' }}; padding: 0.85rem 1.25rem; border-radius: 0.5rem; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.92rem; font-weight: 600;">
                <span>{{ $messageType === 'success' ? '✓' : '⚠️' }}</span>
                <span>{{ $message }}</span>
            </div>
            <button type="button" wire:click="$set('message', '')" style="background:none; border:none; color:inherit; cursor:pointer; font-weight:700; font-size: 1rem;">✕</button>
        </div>
    @endif

    {{-- ── Header Modul Mahasiswa Terdaftar ── --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
        <div>
            <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--text-main); margin: 0 0 0.25rem 0; display: flex; align-items: center; gap: 0.6rem;">
                <span>👥 Mahasiswa Terdaftar</span>
                <span style="font-size: 0.85rem; font-weight: 600; padding: 0.15rem 0.55rem; background: rgba(16, 185, 129, 0.15); color: #10b981; border-radius: 9999px;">
                    {{ $totalCount }} Mahasiswa
                </span>
            </h2>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">
                Daftar mahasiswa yang terdaftar mengikuti mata kuliah ini.
            </p>
        </div>

        <div>
            <a
                href="{{ $enrollRoute }}"
                class="btn btn-primary"
                style="padding: 0.5rem 1.1rem; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none;"
            >
                <span style="font-size: 1rem;">+</span>
                <span>Daftarkan Mahasiswa</span>
            </a>
        </div>
    </div>

    {{-- ── Quick Search Filter jika Mahasiswa Lebih dari 10 ── --}}
    @if ($totalCount > 10)
        <div style="margin-bottom: 1rem;">
            <input
                type="text"
                wire:model.live.debounce.250ms="searchEnrolled"
                placeholder="🔍 Cari nama mahasiswa terdaftar..."
                class="search-input"
                style="width: 100%; font-size: 0.85rem; padding: 0.55rem 0.85rem;"
            >
        </div>
    @endif

    {{-- ── List Mahasiswa Terdaftar ── --}}
    @if ($totalCount === 0)
        <div style="text-align: center; padding: 2.5rem 1rem; background: var(--bg); border: 1px dashed var(--card-border); border-radius: 0.75rem; color: var(--text-muted);">
            <div style="font-size: 2.2rem; margin-bottom: 0.35rem;">👨‍🎓</div>
            <p style="margin: 0; font-size: 0.95rem; font-weight: 600; color: var(--text-main);">Belum ada mahasiswa yang didaftarkan ke kelas ini.</p>
            <p style="margin: 0.25rem 0 1rem 0; font-size: 0.85rem; color: var(--text-muted);">Klik tombol "+ Daftarkan Mahasiswa" di atas untuk menambahkan peserta kelas.</p>
            <a href="{{ $enrollRoute }}" class="btn btn-primary" style="padding: 0.45rem 1rem; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: inline-block;">
                + Daftarkan Mahasiswa Sekarang
            </a>
        </div>
    @elseif (count($enrolledStudents) === 0)
        <div style="text-align: center; padding: 1.5rem 1rem; background: var(--bg); border: 1px dashed var(--card-border); border-radius: 0.5rem; color: var(--text-muted); font-size: 0.88rem;">
            Tidak ditemukan mahasiswa terdaftar dengan nama atau email "{{ $searchEnrolled }}".
        </div>
    @else
        <div style="display: grid; gap: 0.6rem;">
            @foreach ($enrolledStudents as $enrolled)
                <div style="background: var(--bg); border: 1px solid var(--card-border); border-radius: 0.65rem; padding: 0.85rem 1.15rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; transition: border-color 0.2s;">
                    <div style="display: flex; align-items: center; gap: 0.85rem; overflow: hidden;">
                        <div style="width: 38px; height: 38px; min-width: 38px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #06b6d4); display: flex; align-items: center; justify-content: center; font-weight: 700; color: #ffffff; font-size: 0.92rem;">
                            {{ strtoupper(substr($enrolled->name, 0, 1)) }}
                        </div>
                        <div style="overflow: hidden;">
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.92rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $enrolled->name }}
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap;">
                                <span>📧 {{ $enrolled->email }}</span>
                                <span>•</span>
                                <span>Terdaftar: {{ $enrolled->pivot->enrolled_at ? \Carbon\Carbon::parse($enrolled->pivot->enrolled_at)->format('d M Y, H:i') . ' WITA' : ($enrolled->pivot->created_at ? \Carbon\Carbon::parse($enrolled->pivot->created_at)->format('d M Y, H:i') . ' WITA' : '-') }}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <button
                            type="button"
                            wire:click="unenrollStudent({{ $enrolled->id }})"
                            wire:confirm="Apakah Anda yakin ingin mengeluarkan {{ addslashes($enrolled->name) }} dari mata kuliah ini?"
                            class="btn btn-delete"
                            style="font-size: 0.78rem; padding: 0.35rem 0.85rem; font-weight: 600; white-space: nowrap;"
                        >
                            Keluarkan
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── Tombol Selengkapnya / Tampilkan Lebih Sedikit (Berjalan di Halaman Detail Saja) ── --}}
        @if ($hasMore && trim($searchEnrolled) === '')
            <div style="text-align: center; margin-top: 1.25rem;">
                @if (!$showAll)
                    <button
                        type="button"
                        wire:click="toggleShowAll"
                        class="btn btn-secondary"
                        style="padding: 0.55rem 1.5rem; font-size: 0.88rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer;"
                    >
                        <span>Lihat Selengkapnya ({{ $remainingCount }} Mahasiswa Lainnya)</span>
                        <span>↓</span>
                    </button>
                @else
                    <button
                        type="button"
                        wire:click="toggleShowAll"
                        class="btn btn-secondary"
                        style="padding: 0.55rem 1.5rem; font-size: 0.88rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer;"
                    >
                        <span>Tampilkan Lebih Sedikit (10 Pertama)</span>
                        <span>↑</span>
                    </button>
                @endif
            </div>
        @endif
    @endif
</div>
