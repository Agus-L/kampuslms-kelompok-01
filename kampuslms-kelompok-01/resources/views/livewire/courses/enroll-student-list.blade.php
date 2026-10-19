<div>
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

    {{-- ── Card Container Utama ── --}}
    <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin: 0 0 0.25rem 0;">
                    📋 Daftar Mahasiswa Belum Terdaftar
                </h2>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">
                    Pilih mahasiswa di bawah ini untuk didaftarkan ke kelas mata kuliah ini.
                </p>
            </div>
            <div>
                <span style="font-size: 0.85rem; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 0.35rem 0.85rem; border-radius: 9999px;">
                    {{ $totalAvailableCount }} Mahasiswa Tersedia
                </span>
            </div>
        </div>

        {{-- Input Pencarian Realtime --}}
        <div style="position: relative; margin-bottom: 1.25rem;">
            <input
                type="text"
                wire:model.live.debounce.250ms="search"
                placeholder="🔍 Ketik nama atau email mahasiswa belum terdaftar..."
                class="search-input"
                style="width: 100%; padding: 0.7rem 1rem 0.7rem 2.4rem; font-size: 0.92rem;"
            >
            <span style="position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); font-size: 1rem; opacity: 0.6;">
                🔍
            </span>
            <div wire:loading wire:target="search" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); font-size: 0.85rem; color: var(--primary-light);">
                Mencari...
            </div>
        </div>

        {{-- List Mahasiswa Belum Terdaftar --}}
        @if ($totalAvailableCount === 0)
            <div style="text-align: center; padding: 3rem 1rem; background: var(--bg); border: 1px dashed var(--card-border); border-radius: 0.75rem; color: var(--text-muted);">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🎉</div>
                <h3 style="color: var(--text-main); font-weight: 700; margin: 0 0 0.25rem 0; font-size: 1.1rem;">Semua Mahasiswa Telah Terdaftar</h3>
                <p style="margin: 0 0 1.25rem 0; font-size: 0.9rem;">Seluruh mahasiswa yang ada di sistem kampus telah terdaftar pada mata kuliah ini.</p>
                @php
                    $rolePrefix = (auth()->check() && auth()->user()->role === 'dosen') ? 'dosen' : 'admin';
                    $courseShowRoute = $rolePrefix === 'dosen' ? route('dosen.courses.show', $course) : route('admin.courses.show', $course);
                @endphp
                <a href="{{ $courseShowRoute }}" class="btn btn-secondary">
                    ← Kembali ke Detail Mata Kuliah
                </a>
            </div>
        @elseif ($availableStudents->isEmpty())
            <div style="text-align: center; padding: 2rem 1rem; background: var(--bg); border: 1px dashed var(--card-border); border-radius: 0.5rem; color: var(--text-muted); font-size: 0.9rem;">
                Tidak ada mahasiswa belum terdaftar yang cocok dengan pencarian "{{ $search }}".
            </div>
        @else
            <div style="display: grid; gap: 0.65rem;">
                @foreach ($availableStudents as $candidate)
                    <div style="background: var(--bg); border: 1px solid var(--card-border); border-radius: 0.65rem; padding: 0.85rem 1.25rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; transition: border-color 0.2s;">
                        <div style="display: flex; align-items: center; gap: 0.85rem; overflow: hidden;">
                            <div style="width: 38px; height: 38px; min-width: 38px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #3b82f6); display: flex; align-items: center; justify-content: center; font-weight: 700; color: #ffffff; font-size: 0.92rem;">
                                {{ strtoupper(substr($candidate->name, 0, 1)) }}
                            </div>
                            <div style="overflow: hidden;">
                                <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $candidate->name }}
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    📧 {{ $candidate->email }}
                                </div>
                            </div>
                        </div>

                        <div>
                            <button
                                type="button"
                                wire:click="enrollStudent({{ $candidate->id }})"
                                class="btn btn-primary"
                                style="padding: 0.45rem 1rem; font-size: 0.82rem; font-weight: 600; white-space: nowrap; cursor: pointer;"
                            >
                                + Daftarkan ke Kelas
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($availableStudents->hasPages())
                <div style="margin-top: 1.5rem;">
                    {{ $availableStudents->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
