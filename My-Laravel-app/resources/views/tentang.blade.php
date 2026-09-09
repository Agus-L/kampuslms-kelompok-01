{{--
    Halaman Tentang Proyek & Tim Pengembang
    Route: GET /tentang → tentang
    Controller: (langsung dari web.php dengan view())

    KENAPA sekarang pakai <x-layout>?
    → Sebelumnya file ini standalone (punya <html>, <head>, <body> sendiri).
      Setelah migrasi Vite, semua halaman menggunakan x-layout agar:
      1. Navbar & footer konsisten di seluruh halaman
      2. CSS dimuat satu kali via @vite() di layout.blade.php
      3. Tidak ada duplikasi kode HTML boilerplate

    CSS halaman ini (badge, title, author-card, dsb.) sudah
    dipindahkan ke resources/css/app.css.
--}}
<x-layout title="Tentang Proyek - Kampus LMS Kelompok 01">

    {{-- ── Hero Header ── --}}
    <header class="header-section">
        <div class="badge">
            <span class="badge-dot"></span>
            <span>Kelompok 01 &bull; Pemrograman Web</span>
        </div>
        <h1 class="title title-gradient">Tentang Proyek &amp; Tim Pengembang</h1>
        <p class="subtitle">
            Aplikasi Kampus Learning Management System (LMS) yang dikembangkan untuk memenuhi tugas mata kuliah Pemrograman Web.
        </p>
    </header>

    {{-- ── Project Info ── --}}
    <div class="project-info-card">
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Nama Proyek</span>
                <span class="info-value">Kampus LMS</span>
            </div>
            <div class="info-item">
                <span class="info-label">Kelompok</span>
                <span class="info-value">Kelompok 01</span>
            </div>
            <div class="info-item">
                <span class="info-label">Framework</span>
                <span class="info-value">Laravel {{ app()->version() }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Status Pengembangan</span>
                <span class="info-value" style="color: #4ade80;">Aktif</span>
            </div>
        </div>
    </div>

    {{-- ── Authors / Contributors Section ── --}}
    <section>
        <h2 class="section-title">Author / Tim Pengembang</h2>
        <div class="authors-grid">

            {{-- Author Card 1: Agus Liberty Purba --}}
            <article class="author-card">
                <div class="author-header">
                    <div class="author-avatar">ALP</div>
                    <div class="author-meta">
                        <h3 class="author-name">Agus Liberty Purba</h3>
                        <span class="author-role">Project Contributor</span>
                    </div>
                </div>
                <div class="author-details">
                    <div class="detail-row">
                        <span>Institusi</span>
                        <span>Institut Teknologi Kalimantan</span>
                    </div>
                    <div class="detail-row">
                        <span>Peran</span>
                        <span>Developer</span>
                    </div>
                </div>
            </article>

            {{-- Author Card 2: Annisa Dwi Lestari Sonny --}}
            <article class="author-card">
                <div class="author-header">
                    <div class="author-avatar" style="background: linear-gradient(135deg, #ec4899, #8b5cf6);">ADLS</div>
                    <div class="author-meta">
                        <h3 class="author-name">Annisa Dwi Lestari Sonny</h3>
                        <span class="author-role">Project Contributor</span>
                    </div>
                </div>
                <div class="author-details">
                    <div class="detail-row">
                        <span>Institusi</span>
                        <span>Institut Teknologi Kalimantan</span>
                    </div>
                    <div class="detail-row">
                        <span>Peran</span>
                        <span>Developer</span>
                    </div>
                </div>
            </article>

            {{-- Author Card 3: Anasthasya Salsabila Khoirunnisa --}}
            <article class="author-card">
                <div class="author-header">
                    <div class="author-avatar" style="background: linear-gradient(135deg, #f59e0b, #ef4444);">ASK</div>
                    <div class="author-meta">
                        <h3 class="author-name">Anasthasya Salsabila Khoirunnisa</h3>
                        <span class="author-role">Project Contributor</span>
                    </div>
                </div>
                <div class="author-details">
                    <div class="detail-row">
                        <span>Institusi</span>
                        <span>Institut Teknologi Kalimantan</span>
                    </div>
                    <div class="detail-row">
                        <span>Peran</span>
                        <span>Developer</span>
                    </div>
                </div>
            </article>

            {{-- Author Card 4: Aliya Labibah --}}
            <article class="author-card">
                <div class="author-header">
                    <div class="author-avatar" style="background: linear-gradient(135deg, #10b981, #06b6d4);">ALB</div>
                    <div class="author-meta">
                        <h3 class="author-name">Aliya Labibah</h3>
                        <span class="author-role">Project Contributor</span>
                    </div>
                </div>
                <div class="author-details">
                    <div class="detail-row">
                        <span>Institusi</span>
                        <span>Institut Teknologi Kalimantan</span>
                    </div>
                    <div class="detail-row">
                        <span>Peran</span>
                        <span>Developer</span>
                    </div>
                </div>
            </article>

            {{-- Author Card 5: Aan Mardiah --}}
            <article class="author-card">
                <div class="author-header">
                    <div class="author-avatar" style="background: linear-gradient(135deg, #6366f1, #d946ef);">AM</div>
                    <div class="author-meta">
                        <h3 class="author-name">Aan Mardiah</h3>
                        <span class="author-role">Project Contributor</span>
                    </div>
                </div>
                <div class="author-details">
                    <div class="detail-row">
                        <span>Institusi</span>
                        <span>Institut Teknologi Kalimantan</span>
                    </div>
                    <div class="detail-row">
                        <span>Peran</span>
                        <span>Developer</span>
                    </div>
                </div>
            </article>

        </div>
    </section>

</x-layout>
