<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        // ──────────────────────────────────────────────────────────────────────────
        // 1. TABEL: courses
        // ──────────────────────────────────────────────────────────────────────────
        Schema::table('courses', function (Blueprint $table) {
            /**
             * Index Komposit: ['lecturer_id', 'status']
             *
             * QUERY YANG DIUNTUNGKAN:
             * - Dashboard Dosen & Daftar Mata Kuliah Dosen:
             *   Course::where('lecturer_id', $user->id)->where('status', 'active')
             *   SQL: SELECT * FROM courses WHERE lecturer_id = ? AND status = ?
             *
             * ALASAN & DAMPAK:
             * - Mengeliminasi full-scan baris ketika dosen memiliki banyak riwayat mata kuliah.
             * - Kolom 'lecturer_id' memiliki kardinalitas lebih tinggi sehingga ditempatkan di awal,
             *   diikuti oleh 'status'.
             *
             * BIAYA TULIS (WRITE COST):
             * - Sedikit memperlambat INSERT saat admin membuat mata kuliah baru (Course::create)
             *   dan UPDATE saat status mata kuliah diubah (misal dari draft -> active).
             *
             * VERIFIKASI EXPLAIN:
             * EXPLAIN SELECT * FROM courses WHERE lecturer_id = 2 AND status = 'active';
             * (Memastikan 'type' berubah dari ALL menjadi ref/range)
             */
            $table->index(['lecturer_id', 'status'], 'idx_courses_lecturer_status');

            /**
             * Index Tunggal: 'status'
             *
             * QUERY YANG DIUNTUNGKAN:
             * - Dashboard Admin:
             *   Course::where('status', 'active')->count()
             *   SQL: SELECT COUNT(*) FROM courses WHERE status = 'active'
             * - Filter katalog mata kuliah publik & mahasiswa:
             *   Course::where('status', $request->status)->paginate(...)
             *
             * BIAYA TULIS (WRITE COST):
             * - Tambahan overhead indexing kecil saat membuat atau mengubah status kursus.
             *
             * VERIFIKASI EXPLAIN:
             * EXPLAIN SELECT COUNT(*) FROM courses WHERE status = 'active';
             */
            $table->index('status', 'idx_courses_status');
        });

        // ──────────────────────────────────────────────────────────────────────────
        // 2. TABEL: assignments
        // ──────────────────────────────────────────────────────────────────────────
        Schema::table('assignments', function (Blueprint $table) {
            /**
             * Index Komposit: ['course_id', 'status']
             *
             * QUERY YANG DIUNTUNGKAN:
             * - Dashboard Mahasiswa (Tugas Mendatang):
             *   Assignment::whereIn('course_id', $courseIds)->where('status', 'published')->orderBy('due_at', 'desc')
             *   SQL: SELECT * FROM assignments WHERE course_id IN (...) AND status = 'published'
             * - Relasi withCount di CourseController:
             *   withCount(['assignments' => fn ($q) => $q->where('status', 'published')])
             *
             * ALASAN:
             * - Migrasi awal (2025_01_02_000004) hanya memiliki index ['course_id', 'due_at'].
             *   Ketika query menambahkan filter status='published', database harus menyaring
             *   baris draft satu per satu. Indeks komposit ini menyaring status secara langsung.
             *
             * BIAYA TULIS (WRITE COST):
             * - Menambah waktu eksekusi saat dosen membuat tugas baru atau mempublikasikan tugas.
             *
             * VERIFIKASI EXPLAIN:
             * EXPLAIN SELECT * FROM assignments WHERE course_id = 1 AND status = 'published';
             */
            $table->index(['course_id', 'status'], 'idx_assignments_course_status');
        });

        // ──────────────────────────────────────────────────────────────────────────
        // 3. TABEL: submissions
        // ──────────────────────────────────────────────────────────────────────────
        Schema::table('submissions', function (Blueprint $table) {
            /**
             * Index Tunggal: 'user_id'
             *
             * QUERY YANG DIUNTUNGKAN:
             * - Dashboard Mahasiswa:
             *   Submission::where('user_id', $user->id)->count() (Statistik total tugas dikumpul)
             *   Submission::where('user_id', $user->id)->whereIn('assignment_id', $ids)->get() (mySubmissions)
             *   SQL: SELECT * FROM submissions WHERE user_id = ?
             * - Rapor & riwayat pengumpulan mahasiswa:
             *   Mahasiswa::submissions()->latest('submitted_at')->get()
             *
             * ALASAN SANGAT KRUSIAL:
             * - Migrasi awal (2025_01_02_000005) HANYA memiliki unique index ['assignment_id', 'user_id'].
             *   Berdasarkan prinsip B-Tree, index komposit hanya dapat digunakan jika kolom PERTAMA
             *   ('assignment_id') ada di klausul WHERE. Query yang HANYA memfilter 'user_id'
             *   selama ini mengalami FULL TABLE SCAN (type: ALL)!
             * - Index pada 'user_id' ini mengeliminasi pemindaian seluruh tabel submissions.
             *
             * BIAYA TULIS (WRITE COST):
             * - Menambah satu operasi update index saat mahasiswa mengunggah submission (Submission::create).
             *   Mengingat seorang mahasiswa hanya submit 1 kali per tugas, biaya tulis ini sangat kecil
             *   dibandingkan frekuensi baca (buka dashboard berkali-kali).
             *
             * VERIFIKASI EXPLAIN:
             * EXPLAIN SELECT * FROM submissions WHERE user_id = 5;
             */
            $table->index('user_id', 'idx_submissions_user_id');

            /**
             * Index Komposit: ['assignment_id', 'is_late']
             *
             * QUERY YANG DIUNTUNGKAN:
             * - Halaman Daftar Pengumpulan Dosen (Filter status keterlambatan):
             *   Submission::where('assignment_id', $assignmentId)->where('is_late', true)
             *   SQL: SELECT * FROM submissions WHERE assignment_id = ? AND is_late = 1
             *
             * BIAYA TULIS (WRITE COST):
             * - Overhead minor saat evaluasi apakah pengumpulan terlambat atau tepat waktu.
             *
             * VERIFIKASI EXPLAIN:
             * EXPLAIN SELECT * FROM submissions WHERE assignment_id = 1 AND is_late = 1;
             */
            $table->index(['assignment_id', 'is_late'], 'idx_submissions_assignment_is_late');
        });

        // ──────────────────────────────────────────────────────────────────────────
        // 4. TABEL: grades
        // ──────────────────────────────────────────────────────────────────────────
        Schema::table('grades', function (Blueprint $table) {
            /**
             * Index Tunggal: 'graded_at'
             *
             * QUERY YANG DIUNTUNGKAN:
             * - Dashboard Mahasiswa (Nilai Terbaru):
             *   Grade::whereHas('submission', fn ($q) => $q->where('user_id', $user->id))
             *        ->latest('graded_at')->take(5)->get()
             *   SQL: SELECT * FROM grades ORDER BY graded_at DESC LIMIT 5
             *
             * ALASAN:
             * - Menghindari filesort (sort in-memory) di engine database saat mengurutkan
             *   berdasarkan waktu penilaian.
             *
             * BIAYA TULIS (WRITE COST):
             * - Menambah overhead saat dosen menyimpan atau memperbarui nilai mahasiswa.
             *
             * VERIFIKASI EXPLAIN:
             * EXPLAIN SELECT * FROM grades ORDER BY graded_at DESC LIMIT 5;
             */
            $table->index('graded_at', 'idx_grades_graded_at');
        });

        /**
         * ──────────────────────────────────────────────────────────────────────────
         * CATATAN: INDEX YANG SENGAJA TIDAK DITAMBAHKAN (Prompt Pack C Bagian 11.2)
         * ──────────────────────────────────────────────────────────────────────────
         * 1. users.name / courses.name:
         *    - Kueri pencarian menggunakan pola `LIKE '%keyword%'` (diawali wildcard %).
         *    - B-Tree index standar TIDAK BISA dimanfaatkan oleh mesin database jika diawali
         *      wildcard %, sehingga index biasa akan membuang ruang RAM/disk tanpa manfaat.
         * 2. materials.description / assignments.instructions:
         *    - Tipe data TEXT berukuran besar. Mengindeks kolom teks panjang tanpa full-text
         *      search engine akan memperlambat I/O disk secara signifikan.
         * 3. submissions.is_late (secara mandiri):
         *    - Kolom boolean memiliki kardinalitas sangat rendah (hanya bernilai 0 atau 1).
         *      Mengindeks kolom boolean secara berdiri sendiri tidak selektif dan tidak efisien.
         *      Oleh karena itu digabungkan dalam indeks komposit ['assignment_id', 'is_late'].
         */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex('idx_courses_lecturer_status');
            $table->dropIndex('idx_courses_status');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex('idx_assignments_course_status');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex('idx_submissions_user_id');
            $table->dropIndex('idx_submissions_assignment_is_late');
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->dropIndex('idx_grades_graded_at');
        });
    }
};
