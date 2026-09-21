{{--
    User Index View — Daftar Semua Pengguna
    Route: GET /users → users.index
    Controller: UserController@index

    View ini menerima variabel $users (Eloquent Collection berisi
    model User) yang dikirim dari controller via compact('users').
--}}

<x-layout :title="'Daftar Pengguna — Kampus LMS'">

    {{-- ── Flash Message ── --}}
    @if (session('success'))
        <div class="alert alert-success">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            ❌ {{ session('error') }}
        </div>
    @endif

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

    {{-- ── Statistik Ringkas / Tombol Filter Peran ── --}}
    <div class="stats-bar user-filter-bar" role="tablist" aria-label="Filter pengguna berdasarkan peran">
        <button type="button" class="stat-item filter-btn active" data-filter="all" title="Tampilkan Semua Pengguna">
            <span class="stat-icon">👥</span>
            <span class="stat-value">{{ $users->count() }}</span>
            <span class="stat-label">Total Pengguna</span>
        </button>
        <button type="button" class="stat-item filter-btn" data-filter="admin" title="Filter Pengguna Admin">
            <span class="stat-icon">🛡️</span>
            <span class="stat-value">{{ $users->where('role', 'admin')->count() }}</span>
            <span class="stat-label">Admin</span>
        </button>
        <button type="button" class="stat-item filter-btn" data-filter="dosen" title="Filter Pengguna Dosen">
            <span class="stat-icon">🎓</span>
            <span class="stat-value">{{ $users->where('role', 'dosen')->count() }}</span>
            <span class="stat-label">Dosen</span>
        </button>
        <button type="button" class="stat-item filter-btn" data-filter="mahasiswa" title="Filter Pengguna Mahasiswa">
            <span class="stat-icon">📚</span>
            <span class="stat-value">{{ $users->where('role', 'mahasiswa')->count() }}</span>
            <span class="stat-label">Mahasiswa</span>
        </button>
    </div>

    {{-- ── Tabel Pengguna ── --}}
    @if ($users->count() > 0)
        @php
            $existingLetters = $users->map(fn($u) => strtoupper(substr(trim($u->name), 0, 1)))->unique()->values()->all();
        @endphp
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
                                <tr class="alphabet-header-row" data-letter="{{ $firstLetter }}">
                                    <td colspan="6" class="alphabet-header-cell">
                                        <div class="alphabet-badge-wrapper">
                                            <span class="alphabet-badge">{{ $firstLetter }}</span>
                                            <span class="alphabet-line"></span>
                                        </div>
                                    </td>
                                </tr>
                            @endif

                            <tr class="user-row" data-role="{{ $user->role }}" data-letter="{{ $firstLetter }}">
                                <td class="td-center row-number">{{ $index + 1 }}</td>
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
                        <tr id="empty-filter-row" style="display: none;">
                            <td colspan="6" class="empty-filter-cell">
                                📭 Tidak ada data pengguna untuk kategori peran ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Scroll Bar Alfabet (Fixed di Pinggir Kanan Layar, Tengah Vertikal) ── --}}
        <aside class="alphabet-nav" aria-label="Navigasi Cepat Abjad">
            <div class="alphabet-nav-track">
                @foreach (range('A', 'Z') as $char)
                    @php
                        $hasData = in_array($char, $existingLetters);
                    @endphp
                    <button type="button"
                            class="alphabet-nav-item {{ $hasData ? 'has-data' : 'disabled' }}"
                            data-nav-letter="{{ $char }}"
                            {{ !$hasData ? 'disabled' : '' }}
                            title="{{ $hasData ? 'Lompat ke huruf ' . $char : 'Tidak ada nama berawalan ' . $char }}">
                        {{ $char }}
                    </button>
                @endforeach
            </div>
        </aside>
    @else
        <div class="empty-state">
            <p>📭 Belum ada pengguna. <a href="{{ route('users.create') }}">Tambah sekarang</a>.</p>
        </div>
    @endif

</x-layout>
