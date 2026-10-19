<x-layout :title="'Dashboard Mahasiswa — Kampus LMS'">
    <div style="max-width: 1200px; margin: 0 auto; padding: 1.5rem 1rem;">
        
        {{-- ── Banner Sambutan ── --}}
        <div style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.2) 0%, rgba(79, 70, 229, 0.15) 100%); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.75rem 2rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(6, 182, 212, 0.3); border: 1px solid #38bdf8; color: var(--text-main); font-size: 0.8rem; font-weight: 700; padding: 0.25rem 0.75rem; border-radius: 9999px; margin-bottom: 0.5rem;">
                    🎓 PERAN: MAHASISWA
                </div>
                <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.25rem;">
                    Selamat Datang, {{ $user->name }}
                </h1>
                <p style="color: var(--text-muted); font-size: 0.95rem;">
                    NIM: {{ $user->nim_nip ?? '-' }} · Pantau materi, penugasan, dan capaian nilai akademik Anda.
                </p>
            </div>
            <div>
                <a href="{{ route('mahasiswa.courses.index') }}" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.65rem 1.25rem; background: var(--primary); color: #ffffff; text-decoration: none; border-radius: var(--radius); font-weight: 600; font-size: 0.9rem; box-shadow: 0 4px 12px var(--primary-glow);">
                    📖 Lihat Mata Kuliah Saya
                </a>
            </div>
        </div>

        {{-- ── Statistik Utama Mahasiswa ── --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
            
            <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                    <span style="color: var(--text-muted); font-size: 0.88rem; font-weight: 600;">Mata Kuliah Diikuti</span>
                    <span style="font-size: 1.5rem;">📚</span>
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.25rem;">
                    {{ $stats['total_enrolled'] }}
                </div>
                <div style="color: var(--text-muted); font-size: 0.8rem;">Kelas terdaftar semester ini</div>
            </div>

            <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                    <span style="color: var(--text-muted); font-size: 0.88rem; font-weight: 600;">Total Beban SKS</span>
                    <span style="font-size: 1.5rem;">⚖️</span>
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.25rem;">
                    {{ $stats['total_sks'] }}
                </div>
                <div style="color: #38bdf8; font-size: 0.8rem; font-weight: 500;">SKS aktif diambil</div>
            </div>

            <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                    <span style="color: var(--text-muted); font-size: 0.88rem; font-weight: 600;">Tugas Terkumpul</span>
                    <span style="font-size: 1.5rem;">📤</span>
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.25rem;">
                    {{ $stats['total_submitted'] }}
                </div>
                <div style="color: #34d399; font-size: 0.8rem;">Submission tersimpan</div>
            </div>

            <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                    <span style="color: var(--text-muted); font-size: 0.88rem; font-weight: 600;">Nilai Diterima</span>
                    <span style="font-size: 1.5rem;">⭐</span>
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.25rem;">
                    {{ $stats['total_graded'] }}
                </div>
                <div style="color: #a5b4fc; font-size: 0.8rem;">Tugas telah dievaluasi dosen</div>
            </div>

        </div>

        {{-- ── Grid 2 Kolom: Tugas Mendatang & Nilai Terkini ── --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(460px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            
            {{-- Kolom Kiri: Penugasan Mendatang --}}
            <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                    <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main);">
                        📝 Penugasan Kuliah Anda
                    </h2>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                    @forelse ($upcomingAssignments as $assign)
                        @php
                            $sub = $mySubmissions->get($assign->id);
                        @endphp
                        <div style="padding: 1rem; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--card-border); border-radius: 0.65rem;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.4rem;">
                                <div>
                                    <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">
                                        <a href="{{ route('mahasiswa.assignments.show', $assign) }}" style="color: var(--text-main); text-decoration: none;" onmouseover="this.style.color='#818cf8'" onmouseout="this.style.color='var(--text-main)'">
                                            {{ $assign->title }}
                                        </a>
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                                        {{ $assign->course->name ?? '' }} · Tenggat: {{ $assign->due_at ? $assign->due_at->format('d M Y, H:i') : 'Tanpa batas' }}
                                    </div>
                                </div>

                                @if ($sub)
                                    <span style="padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: rgba(16, 185, 129, 0.15); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.3);">
                                        ✓ Sudah Dikumpulkan
                                    </span>
                                @else
                                    <span style="padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: rgba(245, 158, 11, 0.15); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.3);">
                                        ⏳ Belum Dikumpulkan
                                    </span>
                                @endif
                            </div>

                            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.6rem; margin-top: 0.75rem;">
                                @if ($sub)
                                    <a href="{{ route('mahasiswa.submissions.show', $sub) }}"
                                       style="font-size: 0.8rem; color: var(--primary-light); text-decoration: none; font-weight: 600;">
                                        Lihat Lembar Tugas →
                                    </a>
                                @else
                                    @can('create', [\App\Models\Submission::class, $assign])
                                    <a href="{{ route('mahasiswa.assignments.submissions.create', $assign) }}"
                                       style="padding: 0.35rem 0.85rem; background: var(--primary); color: #ffffff; text-decoration: none; border-radius: 0.375rem; font-size: 0.8rem; font-weight: 600;">
                                        Kumpulkan Sekarang
                                    </a>
                                    @else
                                    <a href="{{ route('mahasiswa.assignments.show', $assign) }}"
                                       style="padding: 0.35rem 0.85rem; background: rgba(255, 255, 255, 0.08); color: var(--text-muted); text-decoration: none; border-radius: 0.375rem; font-size: 0.8rem; font-weight: 600;">
                                        Lihat Detail Tugas →
                                    </a>
                                    @endcan
                                @endif
                            </div>
                        </div>
                    @empty
                        <p style="color: var(--text-muted); font-size: 0.88rem; text-align: center; padding: 1.5rem 0;">
                            Tidak ada penugasan aktif saat ini.
                        </p>
                    @endforelse
                </div>
            </div>

            {{-- Kolom Kanan: Nilai & Evaluasi Dosen Terbaru --}}
            <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                    <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main);">
                        ⭐ Nilai & Umpan Balik Dosen
                    </h2>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                    @forelse ($recentGrades as $grade)
                        <div style="padding: 1rem; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--card-border); border-radius: 0.65rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                                <div>
                                    <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">
                                        {{ $grade->submission->assignment->title ?? 'Tugas' }}
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                                        {{ $grade->submission->assignment->course->name ?? '' }} · Penilai: {{ $grade->grader->name ?? 'Dosen' }}
                                    </div>
                                </div>
                                <div style="font-size: 1.35rem; font-weight: 800; color: #34d399; background: rgba(16, 185, 129, 0.12); padding: 0.2rem 0.65rem; border-radius: 0.5rem; border: 1px solid rgba(16, 185, 129, 0.3);">
                                    {{ $grade->score }}
                                </div>
                            </div>
                            @if ($grade->feedback)
                                <div style="margin-top: 0.5rem; padding: 0.5rem 0.75rem; background: rgba(0, 0, 0, 0.2); border-radius: 0.375rem; font-size: 0.82rem; color: var(--text-muted); font-style: italic;">
                                    "{{ $grade->feedback }}"
                                </div>
                            @endif
                        </div>
                    @empty
                        <p style="color: var(--text-muted); font-size: 0.88rem; text-align: center; padding: 1.5rem 0;">
                            Belum ada nilai yang diterbitkan oleh dosen.
                        </p>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- ── Section: Daftar Kelas Mata Kuliah Mahasiswa ── --}}
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem;">
                📚 Kelas Kuliah Terdaftar
            </h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem;">
                @forelse ($enrolledCourses as $course)
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
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1rem;">
                                Dosen: <strong style="color: var(--text-main);">{{ $course->lecturer->name ?? 'Belum ditentukan' }}</strong>
                            </p>
                        </div>

                        <div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--text-muted); padding-top: 0.75rem; border-top: 1px solid var(--card-border); margin-bottom: 1rem;">
                                <span>📑 {{ $course->materials_count }} Materi</span>
                                <span>📝 {{ $course->assignments_count }} Tugas</span>
                            </div>

                            @can('view', $course)
                            <a href="{{ route('mahasiswa.courses.show', $course) }}"
                               style="display: block; text-align: center; padding: 0.6rem; background: rgba(79, 70, 229, 0.12); border: 1px solid rgba(99, 102, 241, 0.3); color: var(--primary-light); text-decoration: none; border-radius: 0.5rem; font-weight: 600; font-size: 0.88rem;">
                                Masuk Kelas →
                            </a>
                            @endcan
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; padding: 2rem; background: var(--card-bg); border-radius: 1rem; text-align: center; color: var(--text-muted);">
                        Anda belum terdaftar pada mata kuliah apa pun semester ini. Hubungi Bagian Akademik / Dosen.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</x-layout>
