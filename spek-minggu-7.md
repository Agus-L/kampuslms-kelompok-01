## Peran & Hak Akses

| Aksi | Admin | Dosen | Mahasiswa |
|------|:-----:|:-----:|:---------:|
| CRUD pengguna & role | ✔ | — | — |
| CRUD mata kuliah | ✔ | — | — |
| Kelola enrollment | ✔ | ✔ (MK sendiri) | — |
| CRUD materi | ✔ | ✔ (MK sendiri) | lihat/unduh |
| CRUD tugas | ✔ | ✔ (MK sendiri) | lihat |
| Mengumpulkan tugas | — | — | ✔ (MK yang diikuti) |
| Memberi nilai | — | ✔ (MK sendiri) | — |
| Melihat nilai orang lain | ✔ | ✔ (MK sendiri) | ✘ |

**Aturan keamanan yang akan diuji saat interview:**
- Mahasiswa A **tidak boleh** mengakses submission mahasiswa B, termasuk dengan menebak URL/ID.
- Dosen **tidak boleh** menyentuh data mata kuliah yang bukan miliknya.
- File materi/submission **tidak boleh** dapat diunduh oleh pengguna yang tidak berhak (jangan simpan di `storage/app/public` untuk file bernilai privat).
