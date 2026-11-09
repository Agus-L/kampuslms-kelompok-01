<x-layout :title="'Dashboard Administrator — Kampus LMS'">
    <div style="max-width: 1200px; margin: 0 auto; padding: 1.5rem 1rem;">
        
        {{-- ── Banner Sambutan ── --}}
        <div style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.2) 0%, rgba(6, 182, 212, 0.15) 100%); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.75rem 2rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: gap; gap: 1rem;">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(79, 70, 229, 0.3); border: 1px solid var(--primary-light); color: var(--text-main); font-size: 0.8rem; font-weight: 700; padding: 0.25rem 0.75rem; border-radius: 9999px; margin-bottom: 0.5rem;">
                    🛡️ PERAN: ADMINISTRATOR UTAMA
                </div>
                <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.25rem;">
                    Selamat Datang, {{ $user->name }}
                </h1>
                <p style="color: var(--text-muted); font-size: 0.95rem;">
                    Pusat kendali dan manajemen terpadu sistem akademik Kampus LMS.
                </p>
            </div>
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="{{ route('admin.users.create') }}" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.65rem 1.25rem; background: var(--primary); color: #ffffff; text-decoration: none; border-radius: var(--radius); font-weight: 600; font-size: 0.9rem; box-shadow: 0 4px 12px var(--primary-glow);">
                    ➕ Daftarkan Pengguna
                </a>
                @can('create', App\Models\Course::class)
                <a href="{{ route('admin.courses.create') }}" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.65rem 1.25rem; background: rgba(255, 255, 255, 0.08); border: 1px solid var(--card-border); color: var(--text-main); text-decoration: none; border-radius: var(--radius); font-weight: 600; font-size: 0.9rem;">
                    📚 Tambah Mata Kuliah
                </a>
                @endcan
            </div>
        </div>

        {{-- ── Statistik Utama (Grid 4 Kolom) ── --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
            
            {{-- Stat 1: Pengguna --}}
            <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                    <span style="color: var(--text-muted); font-size: 0.88rem; font-weight: 600;">Total Pengguna</span>
                    <span style="font-size: 1.5rem;">👥</span>
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem;">
                    {{ $stats['total_users'] }}
                </div>
                <div style="display: flex; gap: 0.5rem; font-size: 0.78rem;">
                    <span style="color: #818cf8;">Admin: {{ $stats['total_admin'] }}</span> ·
                    <span style="color: #34d399;">Dosen: {{ $stats['total_dosen'] }}</span> ·
                    <span style="color: #38bdf8;">Mhs: {{ $stats['total_mahasiswa'] }}</span>
                </div>
            </div>

            {{-- Stat 2: Mata Kuliah --}}
            <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                    <span style="color: var(--text-muted); font-size: 0.88rem; font-weight: 600;">Mata Kuliah</span>
                    <span style="font-size: 1.5rem;">📖</span>
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem;">
                    {{ $stats['total_courses'] }}
                </div>
                <div style="color: #34d399; font-size: 0.82rem; font-weight: 500;">
                    ✓ {{ $stats['active_courses'] }} Berstatus Aktif
                </div>
            </div>

            {{-- Stat 3: Materi & Tugas --}}
            <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                    <span style="color: var(--text-muted); font-size: 0.88rem; font-weight: 600;">Konten Akademik</span>
                    <span style="font-size: 1.5rem;">📑</span>
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem;">
                    {{ $stats['total_materials'] + $stats['total_assignments'] }}
                </div>
                <div style="color: var(--text-muted); font-size: 0.82rem;">
                    {{ $stats['total_materials'] }} Modul Materi · {{ $stats['total_assignments'] }} Penugasan
                </div>
            </div>

            {{-- Stat 4: Submissions --}}
            <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                    <span style="color: var(--text-muted); font-size: 0.88rem; font-weight: 600;">Pengumpulan Tugas</span>
                    <span style="font-size: 1.5rem;">📥</span>
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem;">
                    {{ $stats['total_submissions'] }}
                </div>
                <div style="color: var(--text-muted); font-size: 0.82rem;">
                    Total berkas tugas mahasiswa masuk
                </div>
            </div>

        </div>

        {{-- ── Tabel 2 Kolom: Pengguna Terbaru & Mata Kuliah Terbaru ── --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 1.5rem;">
            
            {{-- Kolom Kiri: Pengguna Terbaru --}}
            <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                    <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main);">
                        👤 Pengguna Terbaru
                    </h2>
                    <a href="{{ route('admin.users.index') }}" style="font-size: 0.85rem; color: var(--primary-light); text-decoration: none; font-weight: 600;">
                        Lihat Semua →
                    </a>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @forelse ($latestUsers as $u)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--card-border); border-radius: 0.5rem;">
                            <div>
                                <div style="font-weight: 600; color: var(--text-main); font-size: 0.92rem;">
                                    {{ $u->name }}
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">
                                    {{ $u->email }} · {{ $u->nim_nip ?? 'N/A' }}
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                @if($u->role === 'mahasiswa')
                                    <a href="{{ route('admin.users.transcript.show', $u) }}" style="font-size: 0.75rem; padding: 0.25rem 0.55rem; border-radius: 0.35rem; background: var(--card-bg); border: 1px solid var(--card-border); color: var(--text-main); text-decoration: none; font-weight: 600;" title="Transkrip Nilai Mahasiswa">
                                        📄 Transkrip
                                    </a>
                                @endif
                                <span style="padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize;
                                    background: {{ $u->role === 'admin' ? 'rgba(79, 70, 229, 0.15)' : ($u->role === 'dosen' ? 'rgba(16, 185, 129, 0.15)' : 'rgba(6, 182, 212, 0.15)') }};
                                    color: {{ $u->role === 'admin' ? '#a5b4fc' : ($u->role === 'dosen' ? '#6ee7b7' : '#67e8f9') }};
                                    border: 1px solid {{ $u->role === 'admin' ? 'rgba(99, 102, 241, 0.3)' : ($u->role === 'dosen' ? 'rgba(16, 185, 129, 0.3)' : 'rgba(6, 182, 212, 0.3)') }};">
                                    {{ $u->role }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p style="color: var(--text-muted); font-size: 0.88rem;">Belum ada pengguna terdaftar.</p>
                    @endforelse
                </div>
            </div>

            {{-- Kolom Kanan: Mata Kuliah Terbaru --}}
            <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                    <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main);">
                        📚 Mata Kuliah Terdaftar
                    </h2>
                    <a href="{{ route('admin.courses.index') }}" style="font-size: 0.85rem; color: var(--primary-light); text-decoration: none; font-weight: 600;">
                        Lihat Semua →
                    </a>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @forelse ($latestCourses as $c)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--card-border); border-radius: 0.5rem;">
                            <div>
                                <div style="font-weight: 600; color: var(--text-main); font-size: 0.92rem;">
                                    <span style="color: var(--primary-light); font-family: monospace; font-weight: 700;">[{{ $c->code }}]</span> {{ $c->name }}
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">
                                    Dosen: {{ $c->lecturer->name ?? 'Belum ditentukan' }} · {{ $c->sks }} SKS
                                </div>
                            </div>
                            <a href="{{ route('admin.courses.show', $c) }}" style="padding: 0.3rem 0.75rem; background: rgba(79, 70, 229, 0.12); color: var(--primary-light); text-decoration: none; border-radius: 0.375rem; font-size: 0.8rem; font-weight: 600;">
                                Detail
                            </a>
                        </div>
                    @empty
                        <p style="color: var(--text-muted); font-size: 0.88rem;">Belum ada mata kuliah.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</x-layout>
