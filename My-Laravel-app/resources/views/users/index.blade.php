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

    {{-- ── Statistik Ringkas ── --}}
    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-icon">👥</span>
            <span class="stat-value">{{ $users->count() }}</span>
            <span class="stat-label">Total Pengguna</span>
        </div>
        <div class="stat-item">
            <span class="stat-icon">🛡️</span>
            <span class="stat-value">{{ $users->where('role', 'admin')->count() }}</span>
            <span class="stat-label">Admin</span>
        </div>
        <div class="stat-item">
            <span class="stat-icon">🎓</span>
            <span class="stat-value">{{ $users->where('role', 'dosen')->count() }}</span>
            <span class="stat-label">Dosen</span>
        </div>
        <div class="stat-item">
            <span class="stat-icon">📚</span>
            <span class="stat-value">{{ $users->where('role', 'mahasiswa')->count() }}</span>
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
                        @foreach ($users as $index => $user)
                            <tr>
                                <td class="td-center">{{ $index + 1 }}</td>
                                <td>
                                    <div class="user-name-cell">
                                        <span class="user-avatar-sm">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </span>
                                        {{ $user->name }}
                                    </div>
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
    @else
        <div class="empty-state">
            <p>📭 Belum ada pengguna. <a href="{{ route('users.create') }}">Tambah sekarang</a>.</p>
        </div>
    @endif

</x-layout>
