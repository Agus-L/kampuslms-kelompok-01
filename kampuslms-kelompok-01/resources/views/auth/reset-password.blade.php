<x-layout :title="'Atur Ulang Kata Sandi — Kampus LMS'">
    <div style="max-width: 480px; margin: 2rem auto; padding: 0 1rem;">
        
        <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 2.25rem; box-shadow: var(--card-shadow); backdrop-filter: blur(12px);">
            
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🔑</div>
                <h1 style="font-size: 1.65rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.35rem;">
                    Atur Ulang Kata Sandi
                </h1>
                <p style="color: var(--text-muted); font-size: 0.9rem;">
                    Masukkan kata sandi baru untuk akun Anda.
                </p>
            </div>

            @if (isset($errors) && $errors->any())
                <div class="alert alert-error" style="margin-bottom: 1.5rem; font-size: 0.88rem;">
                    @foreach ($errors->all() as $error)
                        <div>❌ {{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div style="margin-bottom: 1.25rem;">
                    <label for="email" style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.4rem;">
                        Konfirmasi Alamat Email
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email', $email) }}"
                           required
                           placeholder="nama@kampuslms.test"
                           style="width: 100%; padding: 0.75rem 1rem; background: var(--input-bg); border: 1px solid var(--input-border); border-radius: var(--radius); color: var(--text-main); font-size: 0.95rem; outline: none;"
                           onfocus="this.style.borderColor='var(--primary)'"
                           onblur="this.style.borderColor='var(--input-border)'">
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label for="password" style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.4rem;">
                        Kata Sandi Baru
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           required
                           autofocus
                           placeholder="Minimal 8 karakter"
                           style="width: 100%; padding: 0.75rem 1rem; background: var(--input-bg); border: 1px solid var(--input-border); border-radius: var(--radius); color: var(--text-main); font-size: 0.95rem; outline: none;"
                           onfocus="this.style.borderColor='var(--primary)'"
                           onblur="this.style.borderColor='var(--input-border)'">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label for="password_confirmation" style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.4rem;">
                        Ulangi Kata Sandi Baru
                    </label>
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           required
                           placeholder="Ketik ulang kata sandi baru"
                           style="width: 100%; padding: 0.75rem 1rem; background: var(--input-bg); border: 1px solid var(--input-border); border-radius: var(--radius); color: var(--text-main); font-size: 0.95rem; outline: none;"
                           onfocus="this.style.borderColor='var(--primary)'"
                           onblur="this.style.borderColor='var(--input-border)'">
                </div>

                <button type="submit"
                        style="width: 100%; padding: 0.85rem; background: var(--primary); color: #ffffff; border: none; border-radius: var(--radius); font-weight: 600; font-size: 0.95rem; cursor: pointer; transition: background 0.2s; box-shadow: 0 4px 12px var(--primary-glow);"
                        onmouseover="this.style.background='var(--primary-hover)'"
                        onmouseout="this.style.background='var(--primary)'">
                    Simpan Kata Sandi Baru
                </button>
            </form>

            <div style="margin-top: 1.5rem; text-align: center;">
                <a href="{{ route('login') }}" style="font-size: 0.88rem; color: var(--text-muted); text-decoration: none;">
                    ← Batalkan dan Kembali ke Login
                </a>
            </div>

        </div>

    </div>
</x-layout>
