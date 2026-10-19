<x-layout :title="'Detail Pengumpulan — ' . $submission->assignment->title">
    <div class="page-container">
        {{-- Breadcrumb --}}
        <nav class="breadcrumb">
            @if(auth()->user()->role === 'dosen')
                <a href="{{ route('dosen.courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
                <span class="breadcrumb-separator">›</span>
                <a href="{{ route('dosen.courses.assignments.index', $submission->assignment->course) }}" class="breadcrumb-link">Tugas</a>
                <span class="breadcrumb-separator">›</span>
                <a href="{{ route('dosen.assignments.submissions.index', $submission->assignment) }}" class="breadcrumb-link">Pengumpulan</a>
            @else
                <a href="{{ route('mahasiswa.courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
                <span class="breadcrumb-separator">›</span>
                <a href="{{ route('mahasiswa.courses.assignments.index', $submission->assignment->course) }}" class="breadcrumb-link">Tugas</a>
                <span class="breadcrumb-separator">›</span>
                <a href="{{ route('mahasiswa.assignments.show', $submission->assignment) }}" class="breadcrumb-link">{{ $submission->assignment->title }}</a>
            @endif
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">Detail Submission</span>
        </nav>

        {{-- Detail Submission Header --}}
        <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 2rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
                <div>
                    <span style="font-size: 0.8rem; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 0.25rem 0.6rem; border-radius: 0.25rem;">
                        {{ $submission->assignment->course->code }} — {{ $submission->assignment->course->name }}
                    </span>
                    <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main, #f8fafc); margin: 0.5rem 0 0.25rem 0;">
                        {{ $submission->assignment->title }}
                    </h1>
                    <div style="display: flex; gap: 1rem; font-size: 0.85rem; color: var(--text-muted, #94a3b8); margin-top: 0.5rem; flex-wrap: wrap;">
                        <span>👤 Mahasiswa: <strong style="color: var(--text-main);">{{ $submission->student->name ?? 'Mahasiswa' }} ({{ $submission->student->email ?? '-' }})</strong></span>
                        <span>•</span>
                        <span>⏰ Deadline: <strong style="color: #f87171;">{{ $submission->assignment->due_at->format('d M Y, H:i') }} WITA</strong></span>
                        <span>•</span>
                        <span>🎯 Skor Maks: <strong>{{ $submission->assignment->max_score }}</strong></span>
                    </div>
                </div>

                <div>
                    @if(auth()->user()->role === 'dosen')
                        <a href="{{ route('dosen.assignments.submissions.index', $submission->assignment) }}" class="btn btn-secondary">
                            ← Kembali ke Rekap Pengumpulan
                        </a>
                    @else
                        <a href="{{ route('mahasiswa.assignments.show', $submission->assignment) }}" class="btn btn-secondary">
                            ← Kembali ke Tugas
                        </a>
                    @endif
                </div>
            </div>

            {{-- Status Pengumpulan & Waktu --}}
            <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1.5rem;">
                <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--text-main, #f8fafc); margin: 0 0 0.75rem 0;">Informasi Pengumpulan Berkas</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; font-size: 0.85rem;">
                    <div>
                        <div style="color: var(--text-muted, #94a3b8); margin-bottom: 0.25rem;">Waktu Pengumpulan:</div>
                        <div style="font-weight: 700; color: var(--text-main);">
                            {{ $submission->submitted_at ? $submission->submitted_at->format('d F Y, H:i:s') . ' WITA' : '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="color: var(--text-muted, #94a3b8); margin-bottom: 0.25rem;">Ketepatan Waktu:</div>
                        <div>
                            @if($submission->is_late)
                                <span style="font-weight: 700; color: #f87171; background: rgba(248, 113, 113, 0.15); padding: 0.2rem 0.5rem; border-radius: 0.25rem;">
                                    ⚠️ Terlambat Melewati Deadline
                                </span>
                            @else
                                <span style="font-weight: 700; color: #10b981; background: rgba(16, 185, 129, 0.15); padding: 0.2rem 0.5rem; border-radius: 0.25rem;">
                                    ✓ Tepat Waktu
                                </span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div style="color: var(--text-muted, #94a3b8); margin-bottom: 0.25rem;">Berkas Tugas:</div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.2rem;">📎</span>
                            <div>
                                <a href="{{ asset('storage/' . $submission->file_path) }}" target="_blank" download="{{ $submission->original_name }}" style="color: #38bdf8; font-weight: 700; text-decoration: underline;">
                                    {{ $submission->original_name }}
                                </a>
                                <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">
                                    ({{ number_format($submission->file_size / 1024, 1) }} KB)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($submission->note)
                    <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px dashed var(--card-border, #334155);">
                        <div style="color: var(--text-muted, #94a3b8); font-size: 0.85rem; margin-bottom: 0.25rem;">Catatan Mahasiswa:</div>
                        <div style="color: var(--text-main); font-size: 0.9rem; font-style: italic;">
                            "{{ $submission->note }}"
                        </div>
                    </div>
                @endif
            </div>

            {{-- Bagian Penilaian --}}
            @if(auth()->user()->role === 'dosen')
                {{-- Form Penilaian oleh Dosen --}}
                <div style="background: rgba(99, 102, 241, 0.05); border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 0.75rem; padding: 1.5rem;">
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main, #f8fafc); margin: 0 0 1rem 0;">
                        ⚖️ Formulir Penilaian & Umpan Balik Dosen
                    </h3>

                    @php
                        $gradeRoute = $submission->grade
                            ? route('dosen.grades.update', $submission->grade)
                            : route('dosen.submissions.grades.store', $submission);
                    @endphp

                    <form action="{{ $gradeRoute }}" method="POST">
                        @csrf
                        @if($submission->grade)
                            @method('PUT')
                        @endif

                        <div style="display: grid; grid-template-columns: 200px 1fr; gap: 1.5rem; align-items: start; margin-bottom: 1.25rem;">
                            <div>
                                <label for="score" style="display: block; font-size: 0.9rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">
                                    Nilai / Skor (Maks: {{ $submission->assignment->max_score }}):
                                </label>
                                <input
                                    type="number"
                                    name="score"
                                    id="score"
                                    min="0"
                                    max="{{ $submission->assignment->max_score }}"
                                    step="0.01"
                                    value="{{ old('score', $submission->grade->score ?? '') }}"
                                    required
                                    class="search-input"
                                    style="width: 100%; font-size: 1.1rem; font-weight: 700;"
                                    placeholder="Contoh: 85"
                                >
                                @error('score')
                                    <div style="color: #f87171; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label for="feedback" style="display: block; font-size: 0.9rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">
                                    Catatan / Umpan Balik (Feedback):
                                </label>
                                <textarea
                                    name="feedback"
                                    id="feedback"
                                    rows="3"
                                    class="search-input"
                                    style="width: 100%; resize: vertical;"
                                    placeholder="Berikan koreksi, evaluasi, atau catatan penyemangat bagi mahasiswa..."
                                >{{ old('feedback', $submission->grade->feedback ?? '') }}</textarea>
                                @error('feedback')
                                    <div style="color: #f87171; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1.25rem; font-weight: 700;">
                                💾 {{ $submission->grade ? 'Perbarui Nilai' : 'Simpan Nilai' }}
                            </button>
                        </div>
                    </form>
                </div>
            @else
                {{-- Tampilan Nilai untuk Mahasiswa --}}
                <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.5rem;">
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main, #f8fafc); margin: 0 0 1rem 0;">
                        📊 Hasil Evaluasi & Penilaian
                    </h3>
                    @if($submission->grade)
                        <div style="display: flex; align-items: baseline; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <span style="font-size: 2.2rem; font-weight: 800; color: #10b981;">
                                {{ $submission->grade->score }}
                            </span>
                            <span style="font-size: 1.1rem; color: var(--text-muted);">
                                / {{ $submission->assignment->max_score }}
                            </span>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                            Dinilai oleh: <strong>{{ $submission->grade->grader->name ?? 'Dosen Pengampu' }}</strong> pada {{ $submission->grade->graded_at ? $submission->grade->graded_at->format('d M Y, H:i') . ' WITA' : '-' }}
                        </div>
                        @if($submission->grade->feedback)
                            <div style="background: rgba(99, 102, 241, 0.1); border-left: 4px solid #6366f1; padding: 1rem; border-radius: 0.35rem; color: var(--text-main);">
                                <div style="font-weight: 700; margin-bottom: 0.25rem; font-size: 0.85rem; color: #818cf8;">Catatan dari Dosen:</div>
                                <div style="font-size: 0.95rem; line-height: 1.5;">
                                    {{ $submission->grade->feedback }}
                                </div>
                            </div>
                        @endif
                    @else
                        <div style="color: #f59e0b; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span>⏳</span>
                            <span>Tugas Anda sedang dalam antrean pemeriksaan oleh dosen pengampu.</span>
                        </div>
                    @endif

                    @can('update', $submission)
                        <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px dashed var(--card-border, #334155);">
                            <a href="{{ route('mahasiswa.submissions.edit', $submission) }}" class="btn btn-edit">
                                ✏️ Revisi / Unggah Ulang Berkas Tugas
                            </a>
                        </div>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</x-layout>
