<x-layout :title="'Lupa Kata Sandi — Kampus LMS'">
    <div style="max-width: 480px; margin: 2rem auto; padding: 0 1rem;">
        
        <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 2.25rem; box-shadow: var(--card-shadow); backdrop-filter: blur(12px);">
            
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🔒</div>
                <h1 style="font-size: 1.65rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.35rem;">
                    Pemulihan Kata Sandi
                </h1>
                <p style="color: var(--text-muted); font-size: 0.9rem;">
                    Masukkan alamat email terdaftar akun Anda untuk mengatur ulang kata sandi.
                </p>
            </div>

            @if (session('reset_url'))
                <div class="alert alert-success" style="margin-bottom: 1.5rem; font-size: 0.88rem; line-height: 1.5;">
                    <div>✅ <strong>Tautan Reset Berhasil Dibuat!</strong></div>
                    <div style="margin-top: 0.5rem; font-size: 0.82rem; color: var(--text-muted);">
                        (Simulasi Pengujian Lokal / Development):
                    </div>
                    <div style="margin-top: 0.5rem;">
                        <a href="{{ session('reset_url') }}" style="display: inline-block; padding: 0.4rem 0.8rem; background: var(--primary); color: #ffffff; text-decoration: none; border-radius: 0.375rem; font-weight: 600; font-size: 0.82rem;">
                            👉 Buka Halaman Reset Kata Sandi
                        </a>
                    </div>
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div class="alert alert-error" style="margin-bottom: 1.5rem; font-size: 0.88rem;">
                    @foreach ($errors->all() as $error)
                        <div>❌ {{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST">
                @csrf

                <div style="margin-bottom: 1.5rem;">
                    <label for="email" style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.4rem;">
                        Alamat Email Akun
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           placeholder="nama@kampuslms.test"
                           style="width: 100%; padding: 0.75rem 1rem; background: var(--input-bg); border: 1px solid var(--input-border); border-radius: var(--radius); color: var(--text-main); font-size: 0.95rem; outline: none; transition: border-color 0.2s;"
                           onfocus="this.style.borderColor='var(--primary)'"
                           onblur="this.style.borderColor='var(--input-border)'">
                </div>

                <button type="submit"
                        style="width: 100%; padding: 0.85rem; background: var(--primary); color: #ffffff; border: none; border-radius: var(--radius); font-weight: 600; font-size: 0.95rem; cursor: pointer; transition: background 0.2s; box-shadow: 0 4px 12px var(--primary-glow);"
                        onmouseover="this.style.background='var(--primary-hover)'"
                        onmouseout="this.style.background='var(--primary)'">
                    Kirim Permohonan Reset
                </button>
            </form>

            <div style="margin-top: 1.5rem; text-align: center;">
                <a href="{{ route('login') }}" style="font-size: 0.88rem; color: var(--text-muted); text-decoration: none;">
                    ← Kembali ke Halaman Masuk
                </a>
            </div>

        </div>

    </div>
</x-layout>
