<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tentang Proyek - Kampus LMS Kelompok 01</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-glow: rgba(79, 70, 229, 0.25);
            --bg-body: #0b0f19;
            --card-bg: rgba(17, 24, 39, 0.75);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --accent: #06b6d4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg-body);
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.18) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(6, 182, 212, 0.15) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(15, 23, 42, 0.6) 0px, transparent 100%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 2rem;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            backdrop-filter: blur(12px);
        }

        .brand {
            font-size: 1.25rem;
            font-weight: 800;
            background: linear-gradient(135deg, #818cf8, #38bdf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-link {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.2s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--text-main);
        }

        .btn-home {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            color: var(--text-main);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-home:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* Container */
        .container {
            max-width: 1100px;
            width: 100%;
            margin: 2rem auto 4rem;
            padding: 0 1.5rem;
            flex: 1;
        }

        /* Hero Section */
        .header-section {
            text-align: center;
            margin-bottom: 3.5rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1rem;
            background: rgba(79, 70, 229, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 9999px;
            color: #a5b4fc;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            box-shadow: 0 0 20px var(--primary-glow);
        }

        .badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #38bdf8;
            box-shadow: 0 0 10px #38bdf8;
        }

        .title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            letter-spacing: -0.025em;
            line-height: 1.2;
            margin-bottom: 1rem;
        }

        .title-gradient {
            background: linear-gradient(135deg, #ffffff 40%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 1.1rem;
            max-width: 650px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Project Info Card */
        .info-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 1.25rem;
            padding: 2rem;
            backdrop-filter: blur(16px);
            margin-bottom: 3rem;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
        }

        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #4f46e5, #06b6d4, #4f46e5);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .info-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            font-weight: 600;
        }

        .info-value {
            font-size: 1.05rem;
            color: var(--text-main);
            font-weight: 600;
        }

        /* Section Heading */
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title::before {
            content: '';
            display: inline-block;
            width: 4px;
            height: 1.5rem;
            background: linear-gradient(to bottom, #4f46e5, #06b6d4);
            border-radius: 2px;
        }

        /* Authors Grid */
        .authors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.75rem;
        }

        .author-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 1.25rem;
            padding: 1.75rem;
            backdrop-filter: blur(16px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .author-card:hover {
            transform: translateY(-6px);
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: 0 20px 30px -10px rgba(79, 70, 229, 0.25);
        }

        .author-header {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }

        .author-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            font-weight: 700;
            color: #ffffff;
            box-shadow: 0 8px 16px rgba(79, 70, 229, 0.3);
            flex-shrink: 0;
        }

        .author-meta {
            overflow: hidden;
        }

        .author-name {
            font-size: 1.15rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .author-role {
            font-size: 0.85rem;
            color: #38bdf8;
            font-weight: 600;
        }

        .author-details {
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            font-size: 0.9rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            color: var(--text-muted);
        }

        .detail-row span:last-child {
            color: var(--text-main);
            font-weight: 500;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
            font-size: 0.875rem;
            border-top: 1px solid var(--card-border);
            margin-top: auto;
        }

        .footer a {
            color: #a5b4fc;
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer a:hover {
            color: #ffffff;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="{{ url('/') }}" class="brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"></path>
                <path d="M6 6h10"></path>
                <path d="M6 10h10"></path>
            </svg>
            Kampus LMS
        </a>
        <div class="nav-links">
            <a href="{{ url('/') }}" class="nav-link">Beranda</a>
            <a href="{{ url('/tentang') }}" class="nav-link active">Tentang</a>
            <a href="{{ url('/') }}" class="btn-home">&larr; Kembali</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container">
        <!-- Hero Header -->
        <header class="header-section">
            <div class="badge">
                <span class="badge-dot"></span>
                <span>Kelompok 01 &bull; Pemrograman Web</span>
            </div>
            <h1 class="title title-gradient">Tentang Proyek & Tim Pengembang</h1>
            <p class="subtitle">
                Aplikasi Kampus Learning Management System (LMS) yang dikembangkan untuk memenuhi tugas mata kuliah Pemrograman Web.
            </p>
        </header>

        <!-- Project Info -->
        <div class="info-card">
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

        <!-- Authors / Contributors Section -->
        <section>
            <h2 class="section-title">Author / Tim Pengembang</h2>
            <div class="authors-grid">
                
                <!-- Author Card 1: Agus Liberty Purba -->
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

                <!-- Author Card 2: Annisa Dwi Lestari Sonny -->
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

                <!-- Author Card 3: Anasthasya Salsabila Khoirunnisa -->
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

                <!-- Author Card 4: Aliya Labibah -->
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

                <!-- Author Card 5: Aan Mardiah -->
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
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; {{ date('Y') }} <strong>Kampus LMS - Kelompok 01</strong>. Dibuat dengan <a href="https://laravel.com" target="_blank" rel="noopener">Laravel</a>.</p>
    </footer>
</body>
</html>
