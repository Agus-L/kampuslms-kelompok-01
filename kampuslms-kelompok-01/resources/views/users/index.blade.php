{{--
    User Index View — Daftar Semua Pengguna
    Route: GET /users → users.index
    Controller: UserController@index

    View ini menerima variabel $users (LengthAwarePaginator berisi
    model User) yang dikirim dari controller via compact('users').
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

    {{-- ── Form Pencarian & Filter ── --}}
    <div class="search-filter-bar">
        <form action="{{ route('users.index') }}" method="GET" class="search-filter-form">
            <div class="search-input-wrapper">
                <span class="search-icon">🔍</span>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama, email, atau NIM/NIP..."
                       class="search-input">
            </div>
            <select name="role" class="filter-select">
                <option value="">Semua Role</option>
                <option value="admin"     {{ request('role') == 'admin'     ? 'selected' : '' }}>🛡️ Admin</option>
                <option value="dosen"     {{ request('role') == 'dosen'     ? 'selected' : '' }}>🎓 Dosen</option>
                <option value="mahasiswa" {{ request('role') == 'mahasiswa' ? 'selected' : '' }}>📚 Mahasiswa</option>
            </select>
            <button type="submit" class="btn btn-search">Cari</button>
            @if(request('search') || request('role'))
                <a href="{{ route('users.index') }}" class="btn btn-reset" title="Reset filter">✕ Reset</a>
            @endif
        </form>
    </div>

    {{-- ── Statistik Ringkas ── --}}
    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-icon">👥</span>
            <span class="stat-value">{{ $users->total() }}</span>
            <span class="stat-label">Total Pengguna</span>
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
                        @foreach ($users as $index => $user)
                            <tr class="user-row" data-role="{{ $user->role }}">
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

        {{-- ── Pagination ── --}}
        @if ($users->hasPages())
            <div class="pagination-wrapper">
                {{ $users->links() }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <p>📭 Tidak ada pengguna yang ditemukan.
                @if(request('search') || request('role'))
                    <a href="{{ route('users.index') }}">Tampilkan semua</a>.
                @else
                    <a href="{{ route('users.create') }}">Tambah sekarang</a>.
                @endif
            </p>
        </div>
    @endif

</x-layout>
