# Catatan Minggu 13 — Kolaborasi, Version Control, dan Peer Review

## Identitas Mahasiswa

Nama: Anastasya Salsabila Khoirunnisa  
NIM: 10241009  

---

## READ — Analisis Riwayat Git Kelompok

Prinsip utama materi Minggu 13 adalah: **"Version control bukan sekadar tempat mencadangkan file, melainkan alat komunikasi, bukti kontribusi individual, dan jaminan keamanan alur kerja pengembangan perangkat lunak secara kolaboratif"**.

Pengamatan dan analisis riwayat Git dilakukan secara langsung pada repositori lokal dan remote kelompok (`kampuslms-kelompok-01`) menggunakan perintah CLI resmi Git:

```bash
git log --oneline --graph --all
git shortlog -sn --all
git log --pretty='%an' | sort | uniq -c
```

Berikut adalah jawaban atas 5 pertanyaan evaluasi riwayat repositori sesuai subbab 13.3 modul:

---

### 1. Berapa Commit atas Nama Anda? Bandingkan dengan Anggota Lain

Berdasarkan penelusuran seluruh cabang pada repositori hingga Minggu 13 menggunakan perintah `git shortlog -sn --all`, tercatat total **326 komit** dengan distribusi kontribusi sebagai berikut:

| Peringkat | Nama Anggota / Alias Git | Identitas Mahasiswa | Jumlah Komit | Persentase Kontribusi |
| :---: | :--- | :--- | :---: | :---: |
| 1 | Agus-L / Agus L Purba | Agus L Purba (10241005) | 95 komit | 29,1% |
| 2 | **kh0oi** | **Anastasya Salsabila Khoirunnisa (10241009)** | **73 komit** | **22,4%** |
| 3 | aanmardiah2306j / Aan Mardiah | Aan Mardiah (10241001) | 60 komit | 18,4% |
| 4 | Aliya Labibah | Aliya Labibah (10241007) | 52 komit | 16,0% |
| 5 | Dwi-ctrl | Annisa Dwi (10241013) | 46 komit | 14,1% |
| **Total** | **Seluruh Anggota Tim Kelompok 01** | **5 Mahasiswa** | **326 komit** | **100,0%** |

**Analisis Perbandingan Kontribusi:**
- Saya (Anastasya Salsabila Khoirunnisa / `kh0oi`) memiliki **73 komit** (sekitar **22,4%** dari total riwayat proyek).
- Kontribusi dalam kelompok terdistribusi secara relatif sehat dan merata (berada di rentang 14% hingga 29% per anggota). Tidak ada anggota yang mendominasi lebih dari 50% atau anggota pasif (*free-rider*) yang memiliki komit di bawah 10%.
- Terdapat catatan teknis pada beberapa rekan (Agus dan Aan) yang memiliki dua nama akun berbeda karena perbedaan konfigurasi lokal `git config user.name` saat berganti lingkungan terminal/laptop.

---

### 2. Adakah Minggu di Mana Anda Tidak Punya Commit Sama Sekali?

Pemeriksaan stempel waktu komit dilakukan menggunakan perintah:

```bash
git log --author="kh0oi" --format="%ad" --date=short
```

**Hasil Penelusuran Siklus Semester:**
- **Bulan September 2026 (Fase Fondasi):**
  - Minggu 01 (09 September 2026): Inisiasi struktur, berkas catatan awal (3 komit).
  - Minggu 02 (14–17 September 2026): Implementasi CRUD dan catatan mingguan (9 komit).
  - Minggu 03 (21–24 September 2026): Relasi basis data, migrasi reversible, dan integrasi dev (10 komit).
  - Minggu 04 (29 September 2026): Fitur pencarian, filter pengguna, dan pagination (10 komit).
- **Bulan Oktober 2026 (Fase Keamanan & Otomatisasi):**
  - Minggu 05 (05 Oktober 2026): Kebijakan otorisasi model dan integrasi scopeBindings (10 komit).
  - Minggu 06 (10–11 Oktober 2026): Dokumentasi REST API kampus pada `docs/api.md` (9 komit).
  - Minggu 07 (21 Oktober 2026): Implementasi 5 policy otorisasi Course, Material, Assignment, Submission, Grade (5 komit).
  - Minggu 08 (26 Oktober 2026): Persiapan evaluasi UTS dan resolusi merge (2 komit).
  - Minggu 09 (29 Oktober 2026): Validasi sanitasi unggah berkas dan catatan (2 komit).
- **Bulan November 2026 (Fase Skalabilitas & Rilis):**
  - Minggu 10 (02–04 November 2026): Penilaian tugas, queue worker, dan notifikasi lonceng (6 komit).
  - Minggu 11 (09–10 November 2026): Optimisasi query, perbaikan N+1, dan pagination global (4 komit).
  - Minggu 12 (14 November 2026): Deployment container Railway, Supervisor worker, dan controller transkrip nilai (2 komit).
  - Minggu 13 (17 November 2026): Audit riwayat Git dan persiapan peer review (aktif).

**Kesimpulan:**  
**Tidak ada minggu di mana saya tidak memiliki komit sama sekali.** Keaktifan berjalan konsisten setiap minggu sepanjang perkuliahan berlangsung. Hal ini membuktikan keterlibatan berkelanjutan dalam pengembangan proyek secara bertahap dan bukan hasil pengerjaan sistem kebut semalam (*SKS*).

---

### 3. Berapa Persen Pesan Commit yang Mengikuti Conventional Commits?

Dari total 73 komit atas nama `kh0oi`:
- **Komit Hasil Merge Pull Request / Sinkronisasi Cabang:** Tercatat 29 komit (sekitar 39,7%) yang dibuat secara otomatis oleh sistem GitHub PR maupun perintah `git merge dev` (format: `Merge pull request #...` atau `Merge branch ...`).
- **Komit Mandiri / Langsung:** Tercatat 44 komit kerja aktif.
  - **Memenuhi Standar Conventional Commits:** Sebanyak **35 komit** (**79,5%** dari komit mandiri, atau 47,9% jika dihitung terhadap total keseluruhan komit). Komit ini menggunakan awalan standar seperti: `feat:`, `feat(scope):`, `docs:`, `docs(scope):`, `chore:`, dan `fix:`.
  - **Pesan Non-Konvensional / Format Bebas:** Sebanyak **9 komit** (sekitar **20,5%** dari komit mandiri).

**Pola Perubahan Kedisiplinan:**  
Komit yang tidak mengikuti standar Conventional Commits terkonsentrasi pada minggu-minggu awal perkuliahan (Minggu 1 hingga Minggu 3), di mana kebiasaan penulisan komit masih berupa pesan langsung seperti `Create minggu-02-catatan-tasya.md`, `Update app.css`, atau `simpan pekerjaan lokal`. Mulai Minggu 4 ke atas, kepatuhan meningkat drastis hingga mendekati 100% dengan penerapan penamaan modul yang jelas (misalnya: `feat(policy): ...`, `feat(deploy): ...`, `docs(minggu-12): ...`).

---

### 4. Temukan Satu Commit Anda Sendiri yang Pesannya Buruk. Tulis Ulang Seharusnya Bagaimana

**Temuan Komit dengan Pesan Buruk:**
- **Hash Komit:** `843d4fe`
- **Pesan Asli:** `simpan pekerjaan lokal`
- **Daftar Berkas yang Diubah:** 5 berkas (komponen user dan layout).

**Kelemahan dan Masalah dari Pesan Tersebut:**
1. Tidak memiliki tipe Conventional Commits (`feat:`, `fix:`, `chore:`, `wip:`), sehingga tidak dapat dikelompokkan secara otomatis dalam catatan rilis (*changelog*).
2. Pesan bersifat sangat ambigu dan tidak komunikatif. Anggota tim lain maupun dosen penguji tidak dapat mengetahui fitur atau modul apa yang diubah tanpa harus memeriksa kode diff baris per baris.
3. Mencerminkan kebiasaan buruk melakukan komit terburu-buru hanya untuk menyimpan perubahan lokal sebelum berpindah cabang (*switch branch*) atau menarik kode terbaru (*pull*).

**Rekonstruksi Pesan Komit yang Seharusnya:**
```text
wip(user): simpan draf filter peran dan penataan layout sebelum sinkronisasi dev
```
*(Atau jika pekerjaan tersebut memang sudah selesai: `feat(user): tambahkan filter peran pengguna dan penyesuaian layout dashboard`)*.

---

### 5. `git log --stat` — Temukan Commit Terbesar Anda. Apakah Seharusnya Dipecah?

Berdasarkan pemeriksaan statistik perubahan baris kode menggunakan perintah `git log --stat`:
- **Komit Kode Terbesar:** Hash `7918972` — `feat: perbarui UI tema gelap terang, filter peran pengguna, dan perapian form`
  - **Statistik:** 8 berkas diubah, 1.074 baris penambahan (+), 305 baris penghapusan (-).
- Komit perubahan lain yang signifikan adalah hash `88b0d5c` (`feat(minggu-12): tambah controller transkrip nilai & rekap presensi tugas (Tahap 3)`) dengan 10 berkas diubah dan 757 baris penambahan (+).

**Apakah Komit Tersebut Seharusnya Dipecah?**  
**Ya, komit `7918972` mutlak seharusnya dipecah.**  
Komit tersebut melanggar prinsip *Single Responsibility Commit* karena mencampurkan tiga domain pekerjaan yang sama sekali berbeda ke dalam satu komit raksasa:
1. Perubahan antarmuka tema visual (Dark/Light Mode).
2. Logika filter peran pengguna pada modul User.
3. Penataan gaya komponen formulir input.

**Rekomendasi Pemecahan Menjadi Tiga Komit Terpisah:**
1. `feat(ui): implementasi penukaran tema gelap terang pada layout aplikasi` (berkas CSS dan layout Blade).
2. `feat(user): tambahkan query filter peran pada UserController dan view index` (controller dan view user).
3. `style(components): rapikan tata letak formulir dan tombol aksi` (berkas view form).

**Dampak Positif Jika Dipecah:**  
Jika kelak ditemukan regresi visual atau bug JavaScript pada sistem tema gelap-terang, anggota tim dapat melakukan pembalikan (*git revert*) hanya pada komit tema tanpa harus membatalkan fitur filter peran pengguna yang sudah berjalan stabil.

---

## BREAK — Enam Percobaan Eksperimen Git dan Keamanan Alur Kerja

[PERINGATAN KEAMANAN]:  
Percobaan nomor 1 wajib menggunakan berkas `.env` PALSU. Repositori kelompok bersifat publik di GitHub. Sekali kredensial sungguhan terdorong (*pushed*), berkas tersebut harus diasumsikan telah terbaca oleh bot pemindai publik internet dan tidak aman lagi.

Berikut hasil investigasi dan pengujian mendalam terhadap 6 skenario eksperimen dari subbab 13.3 modul:

| # | Skenario yang Dicoba | Apa Prediksinya? | Apa yang Terjadi Sebenarnya? (Penjelasan Analisis & Dampak) |
|:---:|:---|:---|:---|
| **1** | Commit berkas `.env` **palsu**, lalu lakukan `git push` | Pipeline Continuous Integration (CI) di GitHub Actions akan otomatis mendeteksi dan menolak (*build fail*). | **Pipeline CI langsung merah (Gagal di Step 2)!** Pada alur kerja CI repositori (`.github/workflows/ci.yml`), terdapat langkah wajib `Check .env not committed`. Perintah `git ls-files --error-unmatch kampuslms-kelompok-01/.env` mendeteksi bahwa berkas `.env` masuk ke pelacakan Git, sehingga proses pengujian otomatis dihentikan seketika dengan status keluar `exit 1`. Fitur ini mencegah rahasia server bocor lebih jauh ke lingkungan staging/produksi. |
| **2** | Hapus `.env` dari pelacakan dengan `git rm --cached .env`, lalu komit lagi | Berkas `.env` tidak lagi terlacak di commit terbaru dan hilang dari HEAD. | **Berkas hilang dari HEAD, TETAPI MASIH TERSIMPAN DI RIWAYAT GIT LAMA!** Menjalankan `git rm --cached` hanya menghapus file dari pohon kerja commit saat ini. Penyerang yang memiliki akses ke repositori cukup menjalankan `git checkout <commit-lama>` atau memeriksa riwayat commit diff di GitHub untuk menyalin seluruh isi kredensial rahasia. **Pelajaran Krusial:** Sekali rahasia ter-push, rahasia itu wajib dianggap bocor dan harus diganti (rotasi password basis data dan generate ulang `APP_KEY`). |
| **3** | Dua anggota mengubah baris yang sama di `routes/web.php`, lalu lakukan `merge` | Terjadi konflik penggabungan (*merge conflict*) karena Git tidak bisa menebak baris mana yang benar. | **Proses merge terhenti dengan status `CONFLICT (content)`!** Git menyisipkan penanda konflik (`<<<<<<< HEAD`, `=======`, `>>>>>>>`). Git tidak memiliki kecerdasan buatan untuk menebak maksud developer. Kedua anggota wajib duduk bersama, membaca kedua perubahan, menyatukan rute yang valid, menghapus penanda konflik, dan melakukan commit penyelesaian secara sadar (sesuai konsep Prompt Pack 13.2 D). |
| **4** | Lakukan `git push` langsung ke cabang `main` | Perintah push ditolak oleh server GitHub karena ada aturan proteksi cabang (*Branch Protection Rule*). | **Ditolak oleh remote GitHub dengan pesan `GH006: Protected branch hook declined`!** Cabang `main` telah dikonfigurasi sebagai *protected branch*. Pengembang dilarang keras melakukan *direct push* atau *force push*. Setiap perubahan kode menuju `main` mutlak harus melalui alur Pull Request (PR) dari cabang `dev` dengan minimal satu persetujuan (*review approval*) dan status CI hijau. |
| **5** | Buka Pull Request (PR) yang memiliki status CI gagal, lalu coba lakukan merge | Tombol penggabungan (*Merge*) pada antarmuka GitHub tidak dapat diklik atau terkunci. | **Tombol "Merge pull request" terkunci abu-abu (*Merge Blocked*)!** GitHub menampilkan peringatan *"Required status checks must pass before merging"*. Sistem branch protection mencegah kode yang merusak pengujian (*broken test*) atau migrasi basis data yang gagal untuk mencemari cabang utama repositori. |
| **6** | Buat komit dengan perintah `git commit --author="Orang Lain <x@x.com>"` | Komit berhasil dibuat dengan mencatut identitas nama dan email orang lain di riwayat Git. | **Kepenulisan (*authorship*) Git berhasil dipalsukan sepenuhnya!** Saat diperiksa melalui `git log` atau `git shortlog`, komit tersebut tercatat atas nama orang lain. Namun, di GitHub komit tersebut berstatus **Unverified** (tidak memiliki centang hijau tanda tangan GPG). Ini membuktikan bahwa bukti kontribusi tidak boleh dinilai hanya dari jumlah komit, melainkan wajib melalui wawancara kode (*interview*) langsung. |

---

## Keterkaitan dengan Prompt Pack & Checkpoint Minggu 13

Analisis dan eksperimen di atas selaras dengan panduan pada Modul Minggu 13:

1. **Keterkaitan dengan Prompt Pack 13.2 A (Audit Alur Kerja Repositori Sendiri):**  
   - Pemeriksaan `git shortlog -sn` dan `git log --graph` memberikan gambaran objektif mengenai pembagian tugas tim. Angka komit Kelompok 01 yang merata (14% - 29%) membuktikan kerja tim yang kooperatif.
   - Evaluasi alur percabangan (*branching*): Sebagian besar anggota telah menggunakan cabang mandiri, namun kecenderungan cabang hidup terlalu lama (*long-lived branch*) dan integrasi yang menumpuk di akhir pekan menjadi evaluasi perbaikan agar alur branch beralih ke cabang fitur pendek (*short-lived feature branch*).
2. **Keterkaitan dengan Prompt Pack 13.2 D (Resolusi Konflik yang Bertanggung Jawab):**  
   Percobaan konflik baris rute membuktikan bahwa menyelesaikan konflik bukanlah perkara memilih salah satu sisi secara buta (*ours* vs *theirs*), melainkan memahami kebutuhan bisnis dari kedua sisi perubahan sebelum menyatukannya.
3. **Keterkaitan dengan Checkpoint Minggu 13:**
   - **Pertanyaan: Kenapa `git rm --cached .env` tidak cukup jika `.env` sudah pernah ter-push?**  
     *Jawaban:* Karena Git adalah sistem pencatatan berbasis *snapshot history*. Perintah `git rm --cached` hanya menghapus berkas dari *commit* paling depan (HEAD), sementara salinan berkas `.env` beserta isinya tetap abadi di dalam commit lama. Siapa pun dapat mengunduh atau memeriksa commit lama tersebut. Solusinya: seluruh kunci rahasia harus segera diganti (dirotasi), dan riwayat Git harus dibersihkan secara permanen menggunakan alat khusus seperti `git-filter-repo` jika diperlukan.
   - **Pertanyaan: Kenapa Pull Request (PR) sebaiknya kecil dan hanya mencakup satu fitur?**  
     *Jawaban:* PR berukuran kecil (kurang dari 200–300 baris diff) sangat mudah dan cepat untuk direview oleh rekan tim, memiliki risiko konflik merge yang minimal, dan mempermudah pelacakan jika ada bug yang lolos. Sebaliknya, PR raksasa yang menggabungkan banyak fitur cenderung di-review secara asal-asalan dan sangat sulit diperbaiki jika terjadi kegagalan.
   - **Pertanyaan: Kenapa penilaian kontribusi mahasiswa tidak bisa hanya berdasarkan jumlah komit Git?**  
     *Jawaban:* Karena jumlah komit sangat mudah dimanipulasi atau dipalsukan (seperti yang dibuktikan pada Percobaan BREAK nomor 6 dengan opsi `--author`). Selain itu, 1 komit raksasa yang berisi perancangan arsitektur kompleks bernilai jauh lebih tinggi secara teknis dibandingkan 20 komit kecil yang hanya memperbaiki saltik (*typo*) dokumentasi. Oleh karena itu, pengujian riil melalui interview individu dan kemampuan menjelaskan kode adalah penentu utama kontribusi.

---

## Kesimpulan Singkat untuk Dipahami (Bahan Evaluasi / Interview Dosen)

1. **Kedisiplinan Version Control:** `git status` dan `git add -p` adalah kebiasaan wajib sebelum melakukan komit untuk menyaring berkas yang tidak diinginkan agar tidak mencemari repositori.
2. **Conventional Commits:** Penggunaan format `tipe(cakupan): deskripsi` bukan sekadar formalitas gaya, melainkan alat rekam jejak profesional yang membedakan penambahan fitur (`feat`), perbaikan celah keamanan (`fix`), dan pembaruan catatan (`docs`).
3. **Keamanan Rahasia (.env):** Repositori publik menganggap setiap file terdorong sebagai informasi terbuka. Pencegahan nomor satu adalah `.gitignore` dan CI scanner; jika sampai terdorong, tindakan utama adalah merotasi kredensial, bukan sekadar menghapus commit terluar.
4. **Proteksi Integritas Cabang:** Cabang produksi (`main`) wajib dikunci dengan aturan branch protection: tidak ada direct push, wajib melewati Pull Request, minimal satu persetujuan rekan tim, dan status CI wajib hijau (*all checks passed*).
5. **Esensi Peer Review:** Review kode yang berkualitas berfokus pada substansi teknis dengan urutan prioritas: **Keamanan** (mencegah IDOR/kebocoran data) -> **Kebenaran Fungsional** -> **Performa** (mencegah N+1 kueri) -> **Keterbacaan Kode**, dengan selalu menyertakan lokasi baris berkas, analisis risiko, dan saran solusi yang konkret.
