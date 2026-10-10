<x-layout :title="'Dashboard Dosen — Kampus LMS'">
    <div style="max-width: 1200px; margin: 0 auto; padding: 1.5rem 1rem;">
        
        {{-- ── Banner Sambutan ── --}}
        <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(79, 70, 229, 0.15) 100%); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.75rem 2rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(16, 185, 129, 0.3); border: 1px solid #34d399; color: var(--text-main); font-size: 0.8rem; font-weight: 700; padding: 0.25rem 0.75rem; border-radius: 9999px; margin-bottom: 0.5rem;">
                    👨‍🏫 PERAN: DOSEN PENGAMPU
                </div>
                <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.25rem;">
                    Selamat Datang, {{ $user->name }}
                </h1>
                <p style="color: var(--text-muted); font-size: 0.95rem;">
                    NIP: {{ $user->nim_nip ?? '-' }} · Kelola kelas perkuliahan, penugasan, dan penilaian mahasiswa.
                </p>
            </div>
            <div>
                <a href="{{ route('dosen.courses.index') }}" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.65rem 1.25rem; background: var(--primary); color: #ffffff; text-decoration: none; border-radius: var(--radius); font-weight: 600; font-size: 0.9rem; box-shadow: 0 4px 12px var(--primary-glow);">
                    📚 Kelola Kelas Saya
                </a>
            </div>
        </div>

        {{-- ── Statistik Utama Dosen ── --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
            
            <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                    <span style="color: var(--text-muted); font-size: 0.88rem; font-weight: 600;">Mata Kuliah Diampu</span>
                    <span style="font-size: 1.5rem;">🎓</span>
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.25rem;">
                    {{ $stats['total_courses'] }}
                </div>
                <div style="color: var(--text-muted); font-size: 0.8rem;">Kelas aktif semester ini</div>
            </div>

            <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                    <span style="color: var(--text-muted); font-size: 0.88rem; font-weight: 600;">Mahasiswa Terbimbing</span>
                    <span style="font-size: 1.5rem;">👥</span>
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.25rem;">
                    {{ $stats['total_students'] }}
                </div>
                <div style="color: var(--text-muted); font-size: 0.8rem;">Total mahasiswa enrolled</div>
            </div>

            <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                    <span style="color: var(--text-muted); font-size: 0.88rem; font-weight: 600;">Penugasan Aktif</span>
                    <span style="font-size: 1.5rem;">📝</span>
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.25rem;">
                    {{ $stats['total_assignments'] }}
                </div>
                <div style="color: var(--text-muted); font-size: 0.8rem;">Tugas yang telah diterbitkan</div>
            </div>

            {{-- Stat Krusial: Butuh Penilaian Segera --}}
            <div style="background: var(--card-bg); border: 1px solid {{ $stats['pending_grades_count'] > 0 ? 'rgba(239, 68, 68, 0.4)' : 'var(--card-border)' }}; border-radius: 1rem; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                    <span style="color: {{ $stats['pending_grades_count'] > 0 ? '#f87171' : 'var(--text-muted)' }}; font-size: 0.88rem; font-weight: 700;">
                        Belum Dinilai
                    </span>
                    <span style="font-size: 1.5rem;">⏳</span>
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: {{ $stats['pending_grades_count'] > 0 ? '#f87171' : 'var(--text-main)' }}; margin-bottom: 0.25rem;">
                    {{ $stats['pending_grades_count'] }}
                </div>
                <div style="color: {{ $stats['pending_grades_count'] > 0 ? '#fca5a5' : 'var(--text-muted)' }}; font-size: 0.8rem;">
                    {{ $stats['pending_grades_count'] > 0 ? '⚠️ Butuh evaluasi dosen segera' : '✓ Semua tugas telah dinilai' }}
                </div>
            </div>

        </div>

        {{-- ── Section: Tugas Mahasiswa yang Perlu Dinilai (Action Required) ── --}}
        <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; margin-bottom: 2rem; box-shadow: var(--card-shadow);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <div>
                    <h2 style="font-size: 1.2rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.2rem;">
                        📋 Pengumpulan Tugas Menunggu Penilaian
                    </h2>
                    <p style="font-size: 0.85rem; color: var(--text-muted);">
                        Berkas pengumpulan mahasiswa yang belum diberi nilai.
                    </p>
                </div>
            </div>

            @if ($pendingSubmissions->isEmpty())
                <div style="text-align: center; padding: 2rem 1rem; color: var(--text-muted); background: rgba(255, 255, 255, 0.02); border-radius: 0.5rem;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">🎉</div>
                    <p style="font-weight: 600; color: var(--text-main);">Tidak ada antrean penilaian saat ini!</p>
                    <p style="font-size: 0.85rem;">Seluruh submission tugas mahasiswa telah berhasil Anda beri nilai.</p>
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--card-border); color: var(--text-muted);">
                                <th style="padding: 0.75rem;">Mahasiswa</th>
                                <th style="padding: 0.75rem;">Mata Kuliah & Tugas</th>
                                <th style="padding: 0.75rem;">Waktu Kumpul</th>
                                <th style="padding: 0.75rem;">Status</th>
                                <th style="padding: 0.75rem; text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingSubmissions as $sub)
                                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.04);">
                                    <td style="padding: 0.75rem; font-weight: 600; color: var(--text-main);">
                                        {{ $sub->student->name ?? 'Mahasiswa' }}
                                        <div style="font-size: 0.78rem; color: var(--text-muted);">{{ $sub->student->nim_nip ?? '-' }}</div>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <div style="color: var(--primary-light); font-weight: 600;">{{ $sub->assignment->title }}</div>
                                        <div style="font-size: 0.78rem; color: var(--text-muted);">{{ $sub->assignment->course->name ?? '' }}</div>
                                    </td>
                                    <td style="padding: 0.75rem; color: var(--text-muted); font-size: 0.82rem;">
                                        {{ $sub->submitted_at ? $sub->submitted_at->format('d M Y, H:i') : '-' }}
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <span style="padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3);">
                                            Belum Dinilai
                                        </span>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: right;">
                                        <a href="{{ route('dosen.submissions.show', $sub) }}"
                                           style="display: inline-block; padding: 0.4rem 0.85rem; background: var(--primary); color: #ffffff; text-decoration: none; border-radius: 0.375rem; font-weight: 600; font-size: 0.8rem;">
                                            ✏️ Periksa & Nilai
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ── Section: Mata Kuliah yang Diampu ── --}}
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem;">
                📚 Kelas Perkuliahan Anda
            </h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem;">
                @forelse ($taughtCourses as $course)
                    <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; box-shadow: var(--card-shadow); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <span style="font-family: monospace; font-weight: 700; color: var(--primary-light); background: rgba(79, 70, 229, 0.12); padding: 0.2rem 0.5rem; border-radius: 0.25rem; font-size: 0.85rem;">
                                    {{ $course->code }}
                                </span>
                                <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">
                                    {{ $course->sks }} SKS
                                </span>
                            </div>
                            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">
                                {{ $course->name }}
                            </h3>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.25rem; line-height: 1.4;">
                                {{ Str::limit($course->description ?? 'Tidak ada deskripsi', 80) }}
                            </p>
                        </div>

                        <div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--text-muted); padding-top: 0.75rem; border-top: 1px solid var(--card-border); margin-bottom: 1rem;">
                                <span>👥 {{ $course->students_count }} Mahasiswa</span>
                                <span>📑 {{ $course->materials_count }} Materi</span>
                                <span>📝 {{ $course->assignments_count }} Tugas</span>
                            </div>

                            <a href="{{ route('dosen.courses.show', $course) }}"
                               style="display: block; text-align: center; padding: 0.6rem; background: rgba(79, 70, 229, 0.12); border: 1px solid rgba(99, 102, 241, 0.3); color: var(--primary-light); text-decoration: none; border-radius: 0.5rem; font-weight: 600; font-size: 0.88rem;">
                                Buka Kelas →
                            </a>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; padding: 2rem; background: var(--card-bg); border-radius: 1rem; text-align: center; color: var(--text-muted);">
                        Anda belum diplot untuk mengampu mata kuliah. Hubungi Administrator.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</x-layout>
