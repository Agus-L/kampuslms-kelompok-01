<x-layout :title="'Masuk — Kampus LMS'">
    <div style="max-width: 480px; margin: 2rem auto; padding: 0 1rem;">
        
        <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 2.25rem; box-shadow: var(--card-shadow); backdrop-filter: blur(12px);">
            
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🎓</div>
                <h1 style="font-size: 1.65rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.35rem;">
                    Masuk ke Kampus LMS
                </h1>
                <p style="color: var(--text-muted); font-size: 0.9rem;">
                    Portal Pembelajaran Terintegrasi
                </p>
            </div>

            @if (isset($errors) && $errors->any())
                <div class="alert alert-error" style="margin-bottom: 1.5rem; font-size: 0.88rem;">
                    @foreach ($errors->all() as $error)
                        <div>❌ {{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" id="loginForm">
                @csrf

                <div style="margin-bottom: 1.25rem;">
                    <label for="login" style="display: block; font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.4rem;">
                        Email atau NIM / NIP
                    </label>
                    <input type="text"
                           id="login"
                           name="login"
                           value="{{ old('login') }}"
                           required
                           autofocus
                           placeholder="nama@kampuslms.test atau NIM"
                           style="width: 100%; padding: 0.75rem 1rem; background: var(--input-bg); border: 1px solid var(--input-border); border-radius: var(--radius); color: var(--text-main); font-size: 0.95rem; outline: none; transition: border-color 0.2s;"
                           onfocus="this.style.borderColor='var(--primary)'"
                           onblur="this.style.borderColor='var(--input-border)'">
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <label for="password" style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">
                            Kata Sandi
                        </label>
                        <a href="{{ route('password.request') }}" style="font-size: 0.8rem; color: var(--primary-light); text-decoration: none;">
                            Lupa kata sandi?
                        </a>
                    </div>
                    <input type="password"
                           id="password"
                           name="password"
                           required
                           placeholder="••••••••"
                           style="width: 100%; padding: 0.75rem 1rem; background: var(--input-bg); border: 1px solid var(--input-border); border-radius: var(--radius); color: var(--text-main); font-size: 0.95rem; outline: none; transition: border-color 0.2s;"
                           onfocus="this.style.borderColor='var(--primary)'"
                           onblur="this.style.borderColor='var(--input-border)'">
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-muted); cursor: pointer;">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} style="accent-color: var(--primary);">
                        Ingat sesi saya
                    </label>
                </div>

                <button type="submit"
                        style="width: 100%; padding: 0.85rem; background: var(--primary); color: #ffffff; border: none; border-radius: var(--radius); font-weight: 600; font-size: 0.95rem; cursor: pointer; transition: background 0.2s; box-shadow: 0 4px 12px var(--primary-glow);"
                        onmouseover="this.style.background='var(--primary-hover)'"
                        onmouseout="this.style.background='var(--primary)'">
                    Masuk Sekarang
                </button>
            </form>

            {{-- ── Quick Demo Login Picker (Sangat Berguna untuk Evaluasi Dosen / Interview) ── --}}
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--card-border);">
                <p style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: var(--text-muted); text-align: center; margin-bottom: 0.85rem;">
                    ⚡ Akses Cepat Akun Demo (Interview)
                </p>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem;">
                    <button type="button"
                            onclick="fillDemo('admin@kampuslms.test', 'password')"
                            style="padding: 0.5rem; background: rgba(79, 70, 229, 0.12); border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 0.5rem; color: #a5b4fc; font-size: 0.78rem; font-weight: 600; cursor: pointer; text-align: center;">
                        🔑 Admin
                    </button>
                    <button type="button"
                            onclick="fillDemo('dosen@kampuslms.test', 'password')"
                            style="padding: 0.5rem; background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 0.5rem; color: #6ee7b7; font-size: 0.78rem; font-weight: 600; cursor: pointer; text-align: center;">
                        👨‍🏫 Dosen
                    </button>
                    <button type="button"
                            onclick="fillDemo('mahasiswa@kampuslms.test', 'password')"
                            style="padding: 0.5rem; background: rgba(6, 182, 212, 0.12); border: 1px solid rgba(6, 182, 212, 0.3); border-radius: 0.5rem; color: #67e8f9; font-size: 0.78rem; font-weight: 600; cursor: pointer; text-align: center;">
                        🎓 Mhs
                    </button>
                </div>
            </div>

            <div style="margin-top: 1.5rem; text-align: center;">
                <p style="font-size: 0.82rem; color: var(--text-muted);">
                    Belum punya akun? <span style="color: var(--text-main);">Pendaftaran akun dikelola langsung oleh Administrator Kampus.</span>
                </p>
            </div>
        </div>

    </div>

    <script>
        function fillDemo(email, password) {
            document.getElementById('login').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</x-layout>
