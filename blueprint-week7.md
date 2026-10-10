# Blueprint Otorisasi & Rancangan Policy (Minggu 7)

**Proyek:** KampusLMS (Laravel 12)  
**Peran Sistem:** `admin`, `dosen`, `mahasiswa`  
**Model Utama:** `Course`, `Material`, `Assignment`, `Submission`, `Grade`  
**Status Dokumen:** *Final Disepakati (Ready for Implementation)*

---

## 1. Matriks Hak Akses Final & Aturan Keamanan

Berdasarkan dokumen spesifikasi `spek-minggu-7.md` dan hasil diskusi kesepakatan tata kelola akademik:

| Aksi | Admin | Dosen | Mahasiswa |
|:---|:---:|:---:|:---:|
| **CRUD Pengguna & Role** | ✔ | — | — |
| **CRUD Mata Kuliah** | ✔ | — | — |
| **Lihat Katalog Umum Mata Kuliah** | ✔ (Semua MK) | — (Hanya MK Sendiri) | — (Hanya MK Sendiri) |
| **Kelola Enrollment (Peserta)** | ✔ | ✔ (MK sendiri) | — |
| **CRUD Materi Perkuliahan** | ✔ | ✔ (MK sendiri) | lihat/unduh (MK yang diikuti) |
| **CRUD Penugasan (Assignment)** | ✔ | ✔ (MK sendiri) | lihat (MK yang diikuti) |
| **Mengumpulkan Tugas (Submit)** | — | — | ✔ (MK yang diikuti, 1x kirim) |
| **Revisi Pengumpulan Tugas** | — | ✔ (Persetujuan / Revert to Draft) | ✔ (Hanya jika disetujui / Draft) |
| **Menghapus Berkas Submission** | ✘ | ✘ | ✘ (Terkunci permanen untuk Audit Trail) |
| **Memberi Nilai (Grade)** | — | ✔ (MK sendiri) | — |
| **Ubah / Reset Nilai** | — | ✔ (MK sendiri) | — |
| **Melihat Nilai Mahasiswa Lain** | ✔ (Audit/Rekap) | ✔ (MK sendiri) | ✘ (Hanya nilai sendiri) |

---

### Aturan Keamanan Krusial (Interview Check & Audit Trail):
1. **Pencegahan IDOR (Insecure Direct Object Reference):** Mahasiswa A **tidak boleh** mengakses submission maupun nilai mahasiswa B, termasuk dengan memanipulasi atau menebak URL/ID.
2. **Isolasi Kepemilikan Dosen:** Dosen **tidak boleh** menyentuh, mengubah, atau menilai data mata kuliah yang bukan miliknya.
3. **Proteksi File Privat:** File materi dan berkas submission **tidak boleh** disimpan di folder publik (`storage/app/public`), melainkan di storage privat dan diunduh melalui controller berotorisasi.
4. **Academic Audit Trail (Integritas Data):** Berkas submission yang sudah dikumpulkan tidak boleh dihapus secara sepihak untuk mencegah manipulasi riwayat akademik.
5. **Alur Revisi Terkendali (Controlled Revision Flow):** Submission bersifat satu kali kirim (*locked*). Pembaruan hanya dimungkinkan jika mahasiswa mengajukan revisi dan disetujui oleh dosen pengampu.

---

## 2. Alur Khusus: Pengajuan Revisi Tugas (LMS Real-World Flow)

```
[Mahasiswa Mengunggah Tugas]
              │
              ▼
    Status: "Submitted"
  (Terkunci, Mahasiswa TIDAK BISA update berkas)
              │
              ▼
   Mahasiswa Klik "Ajukan Revisi"
  (Input alasan: misal salah lampiran PDF)
              │
              ▼
    [Kirim Notifikasi ke Dosen]
              │
              ├─── Dosen TOLAK ───► Status tetap "Submitted", Berkas tetap terkunci
              │
              └─── Dosen SETUJU (Revert to Draft)
                          │
                          ▼
                  Status: "Draft"
            [Kirim Notifikasi ke Mahasiswa]
                          │
                          ▼
            Mahasiswa SEKARANG BISA Update / Unggah Ulang Berkas
```

---

## 3. Rancangan Lengkap Policy untuk Setiap Model

---

### A. `CoursePolicy` (Model: `Course`)
*Mengatur hak akses katalog dan pengelolaan mata kuliah.*

1. **`viewAny(User $user)`**
   - **Aturan:** Pengguna dengan peran **Admin** diizinkan melihat katalog global seluruh mata kuliah di kampus. Pengguna dengan peran **Dosen** dan **Mahasiswa** TIDAK memiliki akses ke katalog umum (mereka hanya diarahkan ke daftar mata kuliah masing-masing via controller peran).
   - **Pemeriksaan Relasi:** Hanya mengecek peran `$user->role === 'admin'`.

2. **`view(User $user, Course $course)`**
   - **Aturan:**
     - **Admin:** Diizinkan melihat detail mata kuliah mana pun.
     - **Dosen:** Diizinkan melihat HANYA jika ia adalah dosen pengampu mata kuliah tersebut.
       - 🔍 *Pemeriksaan Relasi:* Memeriksa kepemilikan pengampu (`$course->lecturer_id === $user->id`, relasi **`$course->lecturer`**).
     - **Mahasiswa:** Diizinkan melihat HANYA jika mahasiswa terdaftar aktif di mata kuliah tersebut.
       - 🔍 *Pemeriksaan Relasi:* Memeriksa kepesertaan di tabel pivot (`$course->students()->where('user_id', $user->id)->exists()`, relasi **`$course->students`**).

3. **`create(User $user)`**
   - **Aturan:** Pengguna dengan peran **Admin** diizinkan membuat mata kuliah baru. Dosen dan Mahasiswa TIDAK diizinkan.

4. **`update(User $user, Course $course)`**
   - **Aturan:** Pengguna dengan peran **Admin** diizinkan memperbarui informasi mata kuliah. Dosen dan Mahasiswa TIDAK diizinkan.

5. **`delete(User $user, Course $course)`**
   - **Aturan:** Pengguna dengan peran **Admin** diizinkan menghapus mata kuliah. Dosen dan Mahasiswa TIDAK diizinkan.

6. **`manageEnrollment(User $user, Course $course)`** *(Aksi Otorisasi Tambahan)*
   - **Aturan:** 
     - **Admin:** Diizinkan mengelola pendaftaran mahasiswa pada mata kuliah mana pun.
     - **Dosen:** Diizinkan mengelola pendaftaran mahasiswa HANYA pada mata kuliah yang ia ampu sendiri.
       - 🔍 *Pemeriksaan Relasi:* `$course->lecturer_id === $user->id` (relasi **`$course->lecturer`**).
     - **Mahasiswa:** Dilarang mengelola pendaftaran mahasiswa.

---

### B. `MaterialPolicy` (Model: `Material`)
*Mengatur hak akses berkas materi dan modul perkuliahan.*

1. **`viewAny(User $user)`**
   - **Aturan:** Pengguna dengan peran **Admin** diizinkan melihat daftar seluruh materi. Dosen dan Mahasiswa melihat daftar materi yang dibatasi oleh relasi mata kuliah masing-masing.

2. **`view(User $user, Material $material)` & Hak Unduh Berkas**
   - **Aturan:**
     - **Admin:** Diizinkan melihat dan mengunduh berkas materi apa pun.
     - **Dosen:** Diizinkan melihat dan mengunduh HANYA jika materi berada di mata kuliah yang ia ampu.
       - 🔍 *Pemeriksaan Relasi:* `$material->course->lecturer_id === $user->id` (relasi **`$material->course`**).
     - **Mahasiswa:** Diizinkan melihat dan mengunduh HANYA jika mahasiswa terdaftar di mata kuliah tempat materi tersebut diunggah.
       - 🔍 *Pemeriksaan Relasi:* `$material->course->students()->where('user_id', $user->id)->exists()` (relasi **`$material->course->students`**).

3. **`create(User $user, Course $course)`**
   - **Aturan:**
     - **Admin:** Diizinkan membuat materi di mata kuliah mana pun.
     - **Dosen:** Diizinkan membuat materi HANYA pada mata kuliah yang ia ampu sendiri.
       - 🔍 *Pemeriksaan Relasi:* `$course->lecturer_id === $user->id` (relasi **`$course->lecturer`**).
     - **Mahasiswa:** TIDAK diizinkan membuat materi.

4. **`update(User $user, Material $material)`**
   - **Aturan:**
     - **Admin:** Diizinkan memperbarui materi apa pun.
     - **Dosen:** Diizinkan memperbarui HANYA jika materi berada di mata kuliah yang ia ampu.
       - 🔍 *Pemeriksaan Relasi:* `$material->course->lecturer_id === $user->id` (relasi **`$material->course`**).
     - **Mahasiswa:** TIDAK diizinkan.

5. **`delete(User $user, Material $material)`**
   - **Aturan:**
     - **Admin:** Diizinkan menghapus materi apa pun.
     - **Dosen:** Diizinkan menghapus HANYA jika materi berada di mata kuliah yang ia ampu.
       - 🔍 *Pemeriksaan Relasi:* `$material->course->lecturer_id === $user->id` (relasi **`$material->course`**).
     - **Mahasiswa:** TIDAK diizinkan.

---

### C. `AssignmentPolicy` (Model: `Assignment`)
*Mengatur hak akses pembuatan instruksi dan penugasan kuliah.*

1. **`viewAny(User $user)`**
   - **Aturan:** Pengguna dengan peran **Admin** diizinkan melihat semua tugas. Dosen dan Mahasiswa melihat tugas dalam lingkup mata kuliah mereka.

2. **`view(User $user, Assignment $assignment)`**
   - **Aturan:**
     - **Admin:** Diizinkan melihat detail tugas apa pun.
     - **Dosen:** Diizinkan melihat tugas HANYA jika tugas berada di mata kuliah yang ia ampu.
       - 🔍 *Pemeriksaan Relasi:* `$assignment->course->lecturer_id === $user->id` (relasi **`$assignment->course`**).
     - **Mahasiswa:** Diizinkan melihat instruksi tugas HANYA jika terdaftar di mata kuliah terkait.
       - 🔍 *Pemeriksaan Relasi:* `$assignment->course->students()->where('user_id', $user->id)->exists()` (relasi **`$assignment->course->students`**).

3. **`create(User $user, Course $course)`**
   - **Aturan:**
     - **Admin:** Diizinkan membuat tugas baru di mata kuliah mana pun.
     - **Dosen:** Diizinkan membuat tugas baru HANYA pada mata kuliah yang ia ampu sendiri.
       - 🔍 *Pemeriksaan Relasi:* `$course->lecturer_id === $user->id` (relasi **`$course->lecturer`**).
     - **Mahasiswa:** TIDAK diizinkan.

4. **`update(User $user, Assignment $assignment)`**
   - **Aturan:**
     - **Admin:** Diizinkan memperbarui tugas apa pun.
     - **Dosen:** Diizinkan memperbarui tugas HANYA jika berada di mata kuliah yang ia ampu.
       - 🔍 *Pemeriksaan Relasi:* `$assignment->course->lecturer_id === $user->id` (relasi **`$assignment->course`**).
     - **Mahasiswa:** TIDAK diizinkan.

5. **`delete(User $user, Assignment $assignment)`**
   - **Aturan:**
     - **Admin:** Diizinkan menghapus tugas apa pun.
     - **Dosen:** Diizinkan menghapus tugas HANYA jika berada di mata kuliah yang ia ampu.
       - 🔍 *Pemeriksaan Relasi:* `$assignment->course->lecturer_id === $user->id` (relasi **`$assignment->course`**).
     - **Mahasiswa:** TIDAK diizinkan.

---

### D. `SubmissionPolicy` (Model: `Submission`)
*Mengatur hak akses pengumpulan berkas tugas mahasiswa, anti-IDOR, dan alur revisi.*

1. **`viewAny(User $user)`**
   - **Aturan:**
     - **Admin:** Diizinkan melihat seluruh rekap submission di sistem (keperluan audit dan troubleshooting teknis).
     - **Dosen:** Diizinkan melihat daftar submission HANYA untuk penugasan pada mata kuliah yang ia ampu.
     - **Mahasiswa:** Diizinkan melihat daftar submission HANYA milik dirinya sendiri.

2. **`view(User $user, Submission $submission)` & Hak Unduh Berkas**
   - **Aturan:**
     - **Admin:** Diizinkan melihat dan mengunduh berkas tugas mahasiswa untuk tujuan audit.
     - **Dosen:** Diizinkan melihat dan mengunduh berkas tugas HANYA jika tugas berasal dari mata kuliah yang ia ampu.
       - 🔍 *Pemeriksaan Relasi:* `$submission->assignment->course->lecturer_id === $user->id` (relasi bersarang **`$submission->assignment->course`**).
     - **Mahasiswa:** Diizinkan melihat dan mengunduh berkas HANYA jika submission tersebut adalah miliknya sendiri (Pencegahan IDOR mutlak).
       - 🔍 *Pemeriksaan Relasi:* `$submission->user_id === $user->id` (relasi **`$submission->student`**).

3. **`create(User $user, Assignment $assignment)` (Kumpul Tugas Pertama Kali)**
   - **Aturan:**
     - **Admin** dan **Dosen:** TIDAK diizinkan mengumpulkan tugas.
     - **Mahasiswa:** Diizinkan mengumpulkan tugas HANYA jika terdaftar di mata kuliah tugas tersebut DAN belum pernah mengumpulkan tugas untuk assignment ini.
       - 🔍 *Pemeriksaan Relasi:* `$assignment->course->students()->where('user_id', $user->id)->exists()` (relasi **`$assignment->course->students`**).

4. **`update(User $user, Submission $submission)` (Revisi Pengumpulan Berkas)**
   - **Aturan:**
     - **Dosen** dan **Admin:** TIDAK diizinkan mengubah berkas tugas mahasiswa.
     - **Mahasiswa:** Diizinkan memperbarui/mengunggah ulang berkas HANYA jika:
       1. Submission tersebut adalah milik dirinya sendiri (`$submission->user_id === $user->id`), **DAN**
       2. Status pengumpulan berada dalam kondisi **Draft / Diizinkan Revisi** oleh dosen. *(Jika status masih `Submitted` dan belum diizinkan revisi, aksi ditolak)*.
       - 🔍 *Pemeriksaan Relasi:* `$submission->user_id === $user->id` (relasi **`$submission->student`**).

5. **`requestRevision(User $user, Submission $submission)`** *(Aksi Baru)*
   - **Aturan:** Mahasiswa diizinkan mengajukan permohonan revisi HANYA jika submission adalah miliknya sendiri, berstatus `Submitted`, dan tugas tersebut belum diberi nilai permanen oleh dosen.
     - 🔍 *Pemeriksaan Relasi:* `$submission->user_id === $user->id` (relasi **`$submission->student`**).

6. **`allowRevision(User $user, Submission $submission)`** *(Aksi Baru)*
   - **Aturan:** Dosen diizinkan menyetujui revisi (mengembalikan status ke `Draft`) HANYA jika tugas tersebut berada di mata kuliah yang ia ampu.
     - 🔍 *Pemeriksaan Relasi:* `$submission->assignment->course->lecturer_id === $user->id` (relasi bersarang **`$submission->assignment->course`**).

7. **`delete(User $user, Submission $submission)`**
   - **Aturan:** **DITOLAK UNTUK SEMUA PERAN (`return false`)**.
     - Berkas submission yang telah masuk ke sistem tidak boleh dihapus demi menjaga *academic audit trail* dan akuntabilitas evaluasi perkuliahan.

---

### E. `GradePolicy` (Model: `Grade`)
*Mengatur hak akses penilaian, koreksi skor, dan feedback tugas.*

1. **`viewAny(User $user)`**
   - **Aturan:**
     - **Admin:** Diizinkan melihat rekapitulasi seluruh nilai di sistem untuk keperluan arsip dan pelaporan akademik.
     - **Dosen:** Diizinkan melihat daftar nilai pada mata kuliah yang diampunya.
     - **Mahasiswa:** Diizinkan melihat rekap nilai HANYA untuk nilai miliknya sendiri.

2. **`view(User $user, Grade $grade)`**
   - **Aturan:**
     - **Admin:** Diizinkan melihat nilai siapa pun.
     - **Dosen:** Diizinkan melihat nilai HANYA jika submission tersebut berada di mata kuliah yang ia ampu.
       - 🔍 *Pemeriksaan Relasi:* `$grade->submission->assignment->course->lecturer_id === $user->id` (relasi bersarang **`$grade->submission->assignment->course`**).
     - **Mahasiswa:** Diizinkan melihat nilai HANYA jika nilai tersebut milik submission dirinya sendiri (Dilarang keras melihat nilai mahasiswa lain).
       - 🔍 *Pemeriksaan Relasi:* `$grade->submission->user_id === $user->id` (relasi **`$grade->submission->student`**).

3. **`create(User $user, Submission $submission)` (Memberi Nilai)**
   - **Aturan:**
     - **Dosen:** Diizinkan memberi nilai HANYA pada submission tugas yang berada di mata kuliah miliknya sendiri.
       - 🔍 *Pemeriksaan Relasi:* `$submission->assignment->course->lecturer_id === $user->id` (relasi bersarang **`$submission->assignment->course`**).
     - **Admin** dan **Mahasiswa:** TIDAK diizinkan memberi nilai.

4. **`update(User $user, Grade $grade)` (Koreksi / Pembaruan Skor)**
   - **Aturan:**
     - **Dosen:** Diizinkan memperbarui skor dan feedback nilai HANYA jika berada di mata kuliah miliknya sendiri (misal: ada komplain nilai atau kesalahan input nilai).
       - 🔍 *Pemeriksaan Relasi:* `$grade->submission->assignment->course->lecturer_id === $user->id` (relasi bersarang **`$grade->submission->assignment->course`**).
     - **Admin** dan **Mahasiswa:** TIDAK diizinkan memperbarui nilai.

5. **`delete(User $user, Grade $grade)` (Reset / Batalkan Nilai)**
   - **Aturan:**
     - **Dosen:** Diizinkan menghapus / mereset rekaman nilai HANYA jika nilai tersebut berada di mata kuliah miliknya sendiri (berguna jika dosen ingin mengosongkan nilai saat menyetujui pengajuan revisi tugas).
       - 🔍 *Pemeriksaan Relasi:* `$grade->submission->assignment->course->lecturer_id === $user->id` (relasi bersarang **`$grade->submission->assignment->course`**).
     - **Admin** dan **Mahasiswa:** TIDAK diizinkan menghapus nilai.

---

## 4. Panduan Teknis Implementasi di Laravel 12

1. **Pembuatan File Policy:**
   ```bash
   php artisan make:policy CoursePolicy --model=Course
   php artisan make:policy MaterialPolicy --model=Material
   php artisan make:policy AssignmentPolicy --model=Assignment
   php artisan make:policy SubmissionPolicy --model=Submission
   php artisan make:policy GradePolicy --model=Grade
   ```

2. **Otorisasi di Controller:**
   Gunakan `$this->authorize('action', $model)` atau helper `Gate::authorize('action', $model)`.
   Contoh di `Dosen\SubmissionController`:
   ```php
   $this->authorize('view', $submission);
   ```

3. **Otorisasi di Route Web & API:**
   Gunakan middleware route model binding:
   ```php
   Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])
       ->middleware('can:view,submission');
   ```

4. **Otorisasi di Blade UI:**
   Gunakan direktif `@can`:
   ```blade
   @can('requestRevision', $submission)
       <button type="button" class="btn btn-warning">Ajukan Revisi</button>
   @endcan

   @can('allowRevision', $submission)
       <button type="button" class="btn btn-primary">Izinkan Revisi</button>
   @endcan
   ```
