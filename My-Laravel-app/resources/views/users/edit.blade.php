{{--
    User Edit View — Form Edit Pengguna
    Route: GET /users/{user}/edit → users.edit
    Controller: UserController@edit

    View ini menerima variabel $user (Model User yang akan diedit).
    Password bersifat opsional — hanya diubah jika diisi.
--}}

<x-layout :title="'Edit ' . $user->name . ' — Kampus LMS'">

    <div class="form-container">
        {{-- ── Breadcrumb ── --}}
        <nav class="breadcrumb">
            <a href="{{ route('users.index') }}" class="breadcrumb-link">Pengguna</a>
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">Edit: {{ $user->name }}</span>
        </nav>

        {{-- ── Header ── --}}
        <div class="page-header">
            <h1 class="page-title">Edit Pengguna</h1>
            <p class="page-subtitle">Perbarui informasi pengguna <strong>{{ $user->name }}</strong>.</p>
        </div>

        {{-- ── Form ── --}}
        <div class="form-card">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Nama --}}
                <div class="form-group">
                    <label for="name" class="form-label">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" id="name" name="name"
                           class="form-input @error('name') form-input-error @enderror"
                           value="{{ old('name', $user->name) }}"
                           placeholder="Contoh: Budi Santoso"
                           required>
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="form-group">
                    <label for="email" class="form-label">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email"
                           class="form-input @error('email') form-input-error @enderror"
                           value="{{ old('email', $user->email) }}"
                           placeholder="Contoh: budi@kampuslms.ac.id"
                           required>
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password (opsional saat edit) & Konfirmasi Password --}}
                <div class="form-row">
                    {{-- Password --}}
                    <div class="form-group">
                        <label for="password" class="form-label">
                            Password Baru
                            <span style="color: var(--text-muted); font-weight: 400;">(opsional)</span>
                        </label>
                        <input type="password" id="password" name="password"
                               class="form-input @error('password') form-input-error @enderror"
                               placeholder="Minimal 8 karakter">
                        @error('password')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-input"
                               placeholder="Ketik ulang password baru">
                    </div>
                </div>

                {{-- NIM / NIP & Role --}}
                <div class="form-row">
                    {{-- NIM / NIP --}}
                    <div class="form-group">
                        <label for="nim_nip" class="form-label">NIM / NIP <span style="color: var(--text-muted); font-weight: 400;">(opsional)</span></label>
                        <input type="text" id="nim_nip" name="nim_nip"
                               class="form-input @error('nim_nip') form-input-error @enderror"
                               value="{{ old('nim_nip', $user->nim_nip) }}"
                               placeholder="Contoh: 2024001 atau 198501012020">
                        @error('nim_nip')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Role --}}
                    <div class="form-group">
                        <label for="role" class="form-label">Role <span class="required">*</span></label>
                        <select id="role" name="role"
                                class="form-select @error('role') form-input-error @enderror"
                                required>
                            <option value="">— Pilih Role —</option>
                            <option value="admin"     {{ old('role', $user->role) == 'admin'     ? 'selected' : '' }}>Admin</option>
                            <option value="dosen"     {{ old('role', $user->role) == 'dosen'     ? 'selected' : '' }}>Dosen</option>
                            <option value="mahasiswa" {{ old('role', $user->role) == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                        </select>
                        @error('role')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Tombol Submit --}}
                <div class="form-actions">
                    <button type="submit" class="btn btn-create">
                        💾 Simpan Perubahan
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-cancel">
                        ✕ Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

</x-layout>
