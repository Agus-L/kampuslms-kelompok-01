{{--
    User Index View — Daftar Semua Pengguna
    Route: GET /users ── users.index
    Controller: UserController@index

    Minggu 4 (BUILD 5):
    - Menerapkan pencarian (q: nama, email, nim_nip)
    - Filter peran (role: admin, dosen, mahasiswa)
    - Navigasi cepat abjad A-Z (melompat ke huruf & halaman yang sesuai)
    - Pagination 15 per halaman dengan withQueryString()
--}}

<x-layout :title="'Daftar Pengguna — Kampus LMS'">

    {{-- ── Header Halaman ── --}}
    <div class="page-header">
        <div class="page-header-top">
            <div>
                <div class="page-badge">
                    <span class="page-badge-dot"></span>
                    Modul Pengguna
                </div>
                <h1 class="page-title">Daftar Pengguna</h1>
                <p class="page-subtitle">
                    Kelola seluruh pengguna sistem Kampus LMS.
                    Tambah, edit, atau hapus data pengguna.
                </p>
            </div>
            {{-- Tombol Tambah Pengguna --}}
            <a href="{{ route('users.create') }}" class="btn btn-create">
                + Tambah Pengguna
            </a>
        </div>
    </div>

    {{-- ─────────────────────────────────────────────────────────────
         Minggu 4 — Pencarian & Filter (query string, bukan session)

         KENAPA query string?
         → Pengguna bisa membagikan URL hasil pencarian.
         → Tombol back browser tetap berfungsi.
         → Membuka dua tab tidak saling mengganggu.
         → Sesuai prinsip modul 4.1: "filter dan pencarian ke query string,
           jangan ke session."
    ───────────────────────────────────────────────────────────── --}}
    <div class="search-filter-bar">
        <form method="GET" action="{{ route('users.index') }}" class="search-filter-form">
            {{-- Input Pencarian --}}
            <div class="search-input-wrapper">
                <span class="search-icon">🔍</span>
                <input
                    type="text"
                    name="q"
                    class="search-input"
                    placeholder="Cari nama, email, atau NIM/NIP..."
                    value="{{ request('q') }}"
                    aria-label="Cari pengguna"
                >
            </div>

            {{-- Filter Role / Peran --}}
            <select name="role" class="filter-select" aria-label="Filter berdasarkan peran">
                <option value="">Semua Peran</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="dosen" {{ request('role') === 'dosen' ? 'selected' : '' }}>Dosen</option>
                <option value="mahasiswa" {{ request('role') === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
            </select>

            {{-- Tombol Cari --}}
            <button type="submit" class="btn btn-search">
                🔍 Cari
            </button>

            {{-- Tombol Reset — hanya tampil jika ada filter aktif --}}
            @if (request('q') || request('role'))
                <a href="{{ route('users.index') }}" class="btn btn-reset" title="Hapus semua filter">
                    ↺ Reset
                </a>
            @endif
        </form>
    </div>

    {{-- ── Info Hasil Pencarian ── --}}
    @if (request('q') || request('role'))
        <div class="search-info">
            <span class="search-info-icon">🔍</span>
            <span>
                Menampilkan <strong>{{ $users->total() }}</strong> hasil
                @if (request('q'))
                    untuk pencarian "<strong>{{ request('q') }}</strong>"
                @endif
                @if (request('role'))
                    dengan peran <strong>{{ ucfirst(request('role')) }}</strong>
                @endif
            </span>
        </div>
    @endif

    {{-- ── Statistik Ringkas (Total Keseluruhan Sistem) ── --}}
    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-icon">👥</span>
            <span class="stat-value">{{ $stats['total'] }}</span>
            <span class="stat-label">Total Pengguna</span>
        </div>
        <div class="stat-item">
            <span class="stat-icon">🛡️</span>
            <span class="stat-value">{{ $stats['admin'] }}</span>
            <span class="stat-label">Admin</span>
        </div>
        <div class="stat-item">
            <span class="stat-icon">👨‍🏫</span>
            <span class="stat-value">{{ $stats['dosen'] }}</span>
            <span class="stat-label">Dosen</span>
        </div>
        <div class="stat-item">
            <span class="stat-icon">🎓</span>
            <span class="stat-value">{{ $stats['mahasiswa'] }}</span>
            <span class="stat-label">Mahasiswa</span>
        </div>
    </div>

    {{-- ── Tabel Pengguna ── --}}
    @if ($users->count() > 0)
        <div class="table-card">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>NIM / NIP</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $currentLetter = null;
                        @endphp
                        @foreach ($users as $index => $user)
                            @php
                                $firstLetter = strtoupper(substr(trim($user->name), 0, 1));
                            @endphp

                            @if ($firstLetter !== $currentLetter)
                                @php
                                    $currentLetter = $firstLetter;
                                @endphp
                                <tr class="alphabet-header-row" id="letter-{{ $firstLetter }}" data-letter="{{ $firstLetter }}">
                                    <td colspan="6" class="alphabet-header-cell">
                                        <div class="alphabet-badge-wrapper">
                                            <span class="alphabet-badge">{{ $firstLetter }}</span>
                                            <span class="alphabet-line"></span>
                                        </div>
                                    </td>
                                </tr>
                            @endif

                            <tr class="user-row" data-role="{{ $user->role }}" data-letter="{{ $firstLetter }}">
                                <td class="td-center row-number">{{ $users->firstItem() + $index }}</td>
                                <td class="td-name">
                                    {{ $user->name }}
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->nim_nip ?? '—' }}</td>
                                <td>
                                    <span class="role-badge role-badge-{{ $user->role }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-edit" title="Edit">
                                            ✏️ Edit
                                        </a>
                                        <form action="{{ route('users.destroy', $user) }}" method="POST"
                                              onsubmit="return confirm('Yakin ingin menghapus pengguna {{ $user->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-delete" title="Hapus">
                                                🗑️ Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Scroll Bar Alfabet (Fixed di Pinggir Kanan Layar, Tengah Vertikal) ── --}}
        <aside class="alphabet-nav" aria-label="Navigasi Cepat Abjad">
            <div class="alphabet-nav-track">
                @foreach (range('A', 'Z') as $char)
                    @php
                        $targetPage = $letterPages[$char] ?? null;
                        $hasLetter = $targetPage !== null;
                        $currentPage = $users->currentPage();
                        $targetUrl = $hasLetter
                            ? route('users.index', array_merge(request()->query(), ['page' => $targetPage])) . '#letter-' . $char
                            : '#';
                    @endphp
                    <a href="{{ $targetUrl }}"
                       class="alphabet-nav-item {{ $hasLetter ? 'has-data' : 'disabled' }}"
                       data-nav-letter="{{ $char }}"
                       data-target-page="{{ $targetPage }}"
                       data-current-page="{{ $currentPage }}"
                       title="{{ $hasLetter ? 'Lompat ke huruf ' . $char . ' (Halaman ' . $targetPage . ')' : 'Tidak ada nama berawalan ' . $char }}">
                        {{ $char }}
                    </a>
                @endforeach
            </div>
        </aside>
    @else
        <div class="empty-state">
            @if (request('q') || request('role'))
                <p>🔍 Tidak ada pengguna yang cocok dengan pencarian/filter Anda.
                    <a href="{{ route('users.index') }}">Tampilkan semua</a>.
                </p>
            @else
                <p>👥 Belum ada pengguna. <a href="{{ route('users.create') }}">Tambah sekarang</a>.</p>
            @endif
        </div>
    @endif

    {{-- ─────────────────────────────────────────────────────────────
         Minggu 4 — Pagination Links

         $users->links() menampilkan tautan navigasi halaman.
         Karena controller sudah memanggil ->withQueryString(),
         tautan halaman 2 dst. otomatis membawa filter yang aktif.
         Contoh: /users?q=budi&role=dosen&page=2
    ───────────────────────────────────────────────────────────── --}}
    @if ($users->hasPages())
        <div class="pagination-wrapper">
            {{ $users->links() }}
        </div>
    @endif

</x-layout>