# Dokumentasi REST API — KampusLMS (Minggu 6)

Dokumentasi resmi REST API KampusLMS berbasis Laravel 12 dan Laravel Sanctum sesuai dengan kontrak spesifikasi `spek-week6.md` dan `implementation-plan.md`.

---

## 1. Ringkasan Arsitektur & Aturan Global

- **Base URL:** `http://localhost:8000/api/v1` (atau domain lokal: `http://kampuslms.test/api/v1`)
- **Autentikasi:** Laravel Sanctum via `Bearer Token` pada HTTP Header `Authorization`.
- **Format Header Request Wajib:**
  ```http
  Accept: application/json
  Content-Type: application/json
  Authorization: Bearer <token_anda>
  ```
  *(Catatan: untuk endpoint upload file multipart, jangan menyetel `Content-Type: application/json`, biarkan klien/curl menentukan boundary multipart secara otomatis).*
- **Format Pembungkus Response (API Resource):**
  - **Single Resource:**
    ```json
    {
      "data": { ... }
    }
    ```
  - **Paginated Collection:**
    ```json
    {
      "data": [ ... ],
      "meta": {
        "current_page": 1,
        "last_page": 5,
        "total": 47
      }
    }
    ```
- **Penanganan Kesalahan Global:**
  - **401 Unauthorized:**
    ```json
    {
      "message": "Unauthenticated."
    }
    ```
  - **403 Forbidden (Scope / Role):**
    ```json
    {
      "message": "Anda tidak memiliki akses ke sumber daya ini."
    }
    ```
  - **422 Unprocessable Content (Validasi):**
    ```json
    {
      "message": "Data yang diberikan tidak valid.",
      "errors": {
        "field_name": [
          "Pesan kesalahan validasi."
        ]
      }
    }
    ```
- **Rate Limiting:**
  - `POST /api/v1/auth/login`: Dibatasi **5 permintaan per menit** per IP/kredensial (`throttle:login`).
  - Endpoint terotentikasi: Dibatasi **60 permintaan per menit** per akun (`throttle:api`).

---

## 2. Kredensial Akun Demo (Seeder)

Data pengguna berikut tersedia secara *default* dari `DemoAccountSeeder` dan `KampusLMSSeeder`:

| Role | Nama | Email | Password | NIM / NIP | Keterangan |
|---|---|---|---|---|---|
| `admin` | Admin KampusLMS | `admin@kampuslms.test` | `password` | `ADM000001` | Administrator sistem |
| `dosen` | Dr. Budi Santoso | `dosen@kampuslms.test` | `password` | `1980010001` | Dosen pengampu demo |
| `dosen` | Prof. Siti Rahayu | `dosen2@kampuslms.test` | `password` | `1975020002` | Dosen lain (untuk uji IDOR / 403) |
| `mahasiswa` | Andi Pratama | `mahasiswa@kampuslms.test` | `password` | `2401010001` | Mahasiswa terdaftar demo |
| `mahasiswa` | Siti Nurhaliza | `siti@kampuslms.test` | `password` | `2401010002` | Mahasiswa lain |

---

## 3. Matriks Endpoint API

| Method | Endpoint | Akses & Scope | Controller & Method |
|---|---|---|---|
| `POST` | `/api/v1/auth/login` | Publik (Rate limit: 5/mnt) | `AuthController@login` |
| `POST` | `/api/v1/auth/logout` | Terotentikasi (`auth:sanctum`) | `AuthController@logout` |
| `GET` | `/api/v1/me` | Terotentikasi (`auth:sanctum`) | `AuthController@me` |
| `GET` | `/api/v1/courses` | Terotentikasi (`dosen` / `mahasiswa`) | `CourseController@index` |
| `GET` | `/api/v1/courses/{id}` | `auth:sanctum` + Scope (Pengampu / Peserta) | `CourseController@show` |
| `GET` | `/api/v1/courses/{id}/materials` | `auth:sanctum` + Scope (Pengampu / Peserta) | `CourseMaterialController@index` |
| `GET` | `/api/v1/courses/{id}/assignments` | `auth:sanctum` + Scope (Pengampu / Peserta) | `CourseAssignmentController@index` |
| `POST` | `/api/v1/assignments` | Dosen Pengampu MK | `AssignmentController@store` |
| `PUT/PATCH` | `/api/v1/assignments/{id}` | Dosen Pemilik | `AssignmentController@update` |
| `DELETE` | `/api/v1/assignments/{id}` | Dosen Pemilik | `AssignmentController@destroy` |
| `GET` | `/api/v1/assignments/{id}/submissions` | Dosen Pemilik | `AssignmentSubmissionController@index` |
| `POST` | `/api/v1/assignments/{id}/submissions` | Mahasiswa Terdaftar di MK | `AssignmentSubmissionController@store` |
| `PUT` | `/api/v1/submissions/{id}/grade` | Dosen Pemilik (Upsert) | `SubmissionGradeController@update` |
| `GET` | `/api/v1/notifications` | Terotentikasi (`auth:sanctum`) | `NotificationController@index` |
| `POST` | `/api/v1/notifications/{id}/read` | Terotentikasi (Pemilik notifikasi) | `NotificationController@markAsRead` |

---

## 4. Rincian Dokumentasi Endpoint

### 4.1 Modul Autentikasi & Profil (Auth)

---

#### 4.1.1 Login Pengguna
- **Method:** `POST`
- **URI:** `/api/v1/auth/login`
- **Akses:** Publik
- **Rate Limit:** 5 request/menit
- **Body Request (`application/json`):**

| Parameter | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `email` | string | Ya | Email terdaftar (format email valid) |
| `password` | string | Ya | Kata sandi akun |

- **Contoh Request (`curl`):**
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "dosen@kampuslms.test",
    "password": "password"
  }'
```

- **Contoh Response Sukses (`200 OK`):**
```json
{
  "data": {
    "token": "1|abcdef1234567890qwertyuiop...",
    "token_type": "Bearer",
    "user": {
      "id": 2,
      "name": "Dr. Budi Santoso",
      "email": "dosen@kampuslms.test",
      "role": "dosen",
      "nim_nip": "1980010001",
      "created_at": "2026-10-05T10:00:00.000000Z"
    }
  }
}
```

- **Contoh Response Gagal Validasi / Kredensial Salah (`422 Unprocessable Content`):**
```json
{
  "message": "Data yang diberikan tidak valid.",
  "errors": {
    "email": [
      "Kredensial yang diberikan tidak cocok dengan data kami."
    ]
  }
}
```

- **Contoh Response Terlalu Banyak Percobaan (`429 Too Many Requests`):**
```json
{
  "message": "Too Many Attempts."
}
```

---

#### 4.1.2 Logout Pengguna
- **Method:** `POST`
- **URI:** `/api/v1/auth/logout`
- **Akses:** Terautentikasi (`auth:sanctum`)
- **Headers:** `Authorization: Bearer <token>`
- **Contoh Request (`curl`):**
```bash
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890qwertyuiop..."
```

- **Contoh Response Sukses (`200 OK`):**
```json
{
  "message": "Berhasil logout."
}
```

- **Contoh Response Gagal / Tanpa Token (`401 Unauthorized`):**
```json
{
  "message": "Unauthenticated."
}
```

---

#### 4.1.3 Profil Pengguna yang Sedang Login
- **Method:** `GET`
- **URI:** `/api/v1/me`
- **Akses:** Terautentikasi (`auth:sanctum`)
- **Headers:** `Authorization: Bearer <token>`
- **Contoh Request (`curl`):**
```bash
curl -X GET http://localhost:8000/api/v1/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890qwertyuiop..."
```

- **Contoh Response Sukses (`200 OK`):**
```json
{
  "data": {
    "id": 2,
    "name": "Dr. Budi Santoso",
    "email": "dosen@kampuslms.test",
    "role": "dosen",
    "nim_nip": "1980010001",
    "created_at": "2026-10-05T10:00:00.000000Z"
  }
}
```

---

### 4.2 Modul Mata Kuliah (Courses)

---

#### 4.2.1 Daftar Mata Kuliah
- **Method:** `GET`
- **URI:** `/api/v1/courses`
- **Akses:** Terautentikasi (`auth:sanctum`)
  - Pengguna role `dosen`: Menampilkan daftar mata kuliah yang diampu (`lecturer_id`).
  - Pengguna role `mahasiswa`: Menampilkan daftar mata kuliah yang diikuti (terdaftar di tabel pivot `course_user`).
- **Query Parameters:**
  - `page` (integer, opsional): Nomor halaman pagination (default: 1, limit: 15 per halaman).
- **Contoh Request (`curl`):**
```bash
curl -X GET "http://localhost:8000/api/v1/courses?page=1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890qwertyuiop..."
```

- **Contoh Response Sukses (`200 OK`):**
```json
{
  "data": [
    {
      "id": 1,
      "code": "IF401",
      "name": "Pemrograman Web",
      "description": "Mata kuliah pengembangan web modern berbasis Laravel dan REST API.",
      "sks": 3,
      "status": "active",
      "lecturer": {
        "id": 2,
        "name": "Dr. Budi Santoso"
      },
      "created_at": "2026-10-05T10:00:00.000000Z",
      "updated_at": "2026-10-05T10:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

---

#### 4.2.2 Detail Mata Kuliah
- **Method:** `GET`
- **URI:** `/api/v1/courses/{id}`
- **Akses:** Terautentikasi (`auth:sanctum`) dengan Scope Check:
  - Dosen harus merupakan pengampu mata kuliah terkait.
  - Mahasiswa harus terdaftar aktif pada mata kuliah terkait.
- **Path Parameters:**
  - `id` (integer): ID Mata Kuliah.
- **Contoh Request (`curl`):**
```bash
curl -X GET http://localhost:8000/api/v1/courses/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890qwertyuiop..."
```

- **Contoh Response Sukses (`200 OK`):**
```json
{
  "data": {
    "id": 1,
    "code": "IF401",
    "name": "Pemrograman Web",
    "description": "Mata kuliah pengembangan web modern berbasis Laravel dan REST API.",
    "sks": 3,
    "status": "active",
    "lecturer": {
      "id": 2,
      "name": "Dr. Budi Santoso"
    },
    "materials_count": 4,
    "assignments_count": 3,
    "created_at": "2026-10-05T10:00:00.000000Z",
    "updated_at": "2026-10-05T10:00:00.000000Z"
  }
}
```

- **Contoh Response Gagal Otorisasi / IDOR (`403 Forbidden`):**
```json
{
  "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

- **Contoh Response Tidak Ditemukan (`404 Not Found`):**
```json
{
  "message": "Record not found."
}
```

---

### 4.3 Modul Materi Kuliah (Materials)

---

#### 4.3.1 Daftar Materi dalam Satu Mata Kuliah
- **Method:** `GET`
- **URI:** `/api/v1/courses/{id}/materials`
- **Akses:** Terautentikasi (`auth:sanctum`) dengan Scope Check (Pengampu atau Mahasiswa terdaftar pada mata kuliah tersebut).
- **Path Parameters:**
  - `id` (integer): ID Mata Kuliah.
- **Query Parameters:**
  - `page` (integer, opsional): Halaman pagination.
- **Contoh Request (`curl`):**
```bash
curl -X GET "http://localhost:8000/api/v1/courses/1/materials" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890qwertyuiop..."
```

- **Contoh Response Sukses (`200 OK`):**
```json
{
  "data": [
    {
      "id": 1,
      "course_id": 1,
      "title": "Pengenalan REST API dan Laravel Sanctum",
      "description": "Slide materi kuliah dan panduan konfigurasi API.",
      "type": "document",
      "file_path": "materials/materi-minggu-06.pdf",
      "original_name": "materi-minggu-06.pdf",
      "file_size": 1048576,
      "mime_type": "application/pdf",
      "external_url": null,
      "uploaded_by": 2,
      "created_at": "2026-10-05T10:00:00.000000Z",
      "updated_at": "2026-10-05T10:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

- **Contoh Response Gagal Otorisasi (`403 Forbidden`):**
```json
{
  "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

---

### 4.4 Modul Tugas Kuliah (Assignments)

---

#### 4.4.1 Daftar Tugas dalam Satu Mata Kuliah
- **Method:** `GET`
- **URI:** `/api/v1/courses/{id}/assignments`
- **Akses:** Terautentikasi (`auth:sanctum`) dengan Scope Check (Dosen pengampu atau Mahasiswa terdaftar).
- **Path Parameters:**
  - `id` (integer): ID Mata Kuliah.
- **Query Parameters:**
  - `status` (string, opsional): Filter berdasarkan status tugas (`draft` atau `published`).
  - `page` (integer, opsional): Halaman pagination.
- **Contoh Request (`curl`):**
```bash
curl -X GET "http://localhost:8000/api/v1/courses/1/assignments?status=published&page=1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890qwertyuiop..."
```

- **Contoh Response Sukses (`200 OK`):**
```json
{
  "data": [
    {
      "id": 1,
      "course_id": 1,
      "title": "Tugas 1: Integrasi REST API Sanctum",
      "instructions": "Bangun endpoint REST API sesuai kontrak spesifikasi.",
      "due_at": "2026-10-30T23:59:00.000000Z",
      "max_score": 100,
      "allow_late": true,
      "status": "published",
      "created_by": 2,
      "created_at": "2026-10-05T10:00:00.000000Z",
      "updated_at": "2026-10-05T10:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

---

#### 4.4.2 Buat Tugas Baru
- **Method:** `POST`
- **URI:** `/api/v1/assignments`
- **Akses:** Dosen Pengampu (`auth:sanctum`, role `dosen`, pengampu mata kuliah `course_id`).
- **Body Request (`application/json`):**

| Parameter | Tipe | Wajib | Aturan Validasi | Deskripsi |
|---|---|---|---|---|
| `course_id` | integer | Ya | `required, integer, exists:courses,id` | ID Mata Kuliah yang diampu |
| `title` | string | Ya | `required, string, max:255` | Judul tugas |
| `instructions` | string | Ya | `required, string` | Instruksi pengerjaan |
| `due_at` | string / date | Ya | `required, date, after:now` | Batas akhir pengumpulan (waktu masa depan) |
| `max_score` | integer | Tidak | `sometimes, integer, min:1, max:255` | Nilai maksimum tugas (default: 100) |
| `allow_late` | boolean | Tidak | `sometimes, boolean` | Boleh terlambat atau tidak (default: false) |
| `status` | string | Tidak | `sometimes, in:draft,published` | Status publikasi tugas |

- **Catatan Implementasi:** Nilai `created_by` otomatis diset dari ID user dosen yang sedang login di server, bukan dari input request.
- **Contoh Request (`curl`):**
```bash
curl -X POST http://localhost:8000/api/v1/assignments \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890qwertyuiop..." \
  -d '{
    "course_id": 1,
    "title": "Tugas 2: Dokumentasi OpenAPI",
    "instructions": "Buatkan file dokumentasi markdown API dengan curl lengkap.",
    "due_at": "2026-11-15 23:59:00",
    "max_score": 100,
    "allow_late": true,
    "status": "published"
  }'
```

- **Contoh Response Sukses (`201 Created`):**
```json
{
  "data": {
    "id": 4,
    "course_id": 1,
    "title": "Tugas 2: Dokumentasi OpenAPI",
    "instructions": "Buatkan file dokumentasi markdown API dengan curl lengkap.",
    "due_at": "2026-11-15T23:59:00.000000Z",
    "max_score": 100,
    "allow_late": true,
    "status": "published",
    "created_by": 2,
    "created_at": "2026-10-05T12:00:00.000000Z",
    "updated_at": "2026-10-05T12:00:00.000000Z"
  }
}
```

- **Contoh Response Gagal Otorisasi / Bukan Pengampu (`403 Forbidden`):**
```json
{
  "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

- **Contoh Response Gagal Validasi (`422 Unprocessable Content`):**
```json
{
  "message": "Data yang diberikan tidak valid.",
  "errors": {
    "due_at": [
      "Tenggat waktu harus di masa depan."
    ]
  }
}
```

---

#### 4.4.3 Perbarui Tugas
- **Method:** `PUT` atau `PATCH`
- **URI:** `/api/v1/assignments/{id}`
- **Akses:** Dosen Pemilik (`auth:sanctum`, role `dosen`, pengampu mata kuliah tugas terkait).
- **Path Parameters:**
  - `id` (integer): ID Tugas.
- **Body Request (`application/json`):**
  - Menerima parameter yang sama seperti `POST /api/v1/assignments`, seluruh field bersifat opsional (*partial update* didukung).
- **Contoh Request (`curl`):**
```bash
curl -X PATCH http://localhost:8000/api/v1/assignments/4 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890qwertyuiop..." \
  -d '{
    "max_score": 95,
    "status": "published"
  }'
```

- **Contoh Response Sukses (`200 OK`):**
```json
{
  "data": {
    "id": 4,
    "course_id": 1,
    "title": "Tugas 2: Dokumentasi OpenAPI",
    "instructions": "Buatkan file dokumentasi markdown API dengan curl lengkap.",
    "due_at": "2026-11-15T23:59:00.000000Z",
    "max_score": 95,
    "allow_late": true,
    "status": "published",
    "created_by": 2,
    "created_at": "2026-10-05T12:00:00.000000Z",
    "updated_at": "2026-10-05T12:30:00.000000Z"
  }
}
```

- **Contoh Response Gagal Otorisasi (`403 Forbidden`):**
```json
{
  "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

---

#### 4.4.4 Hapus Tugas
- **Method:** `DELETE`
- **URI:** `/api/v1/assignments/{id}`
- **Akses:** Dosen Pemilik (`auth:sanctum`, role `dosen`, pengampu mata kuliah tugas terkait).
- **Path Parameters:**
  - `id` (integer): ID Tugas.
- **Contoh Request (`curl`):**
```bash
curl -X DELETE http://localhost:8000/api/v1/assignments/4 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890qwertyuiop..."
```

- **Contoh Response Sukses (`204 No Content`):**
*(Respons kosong tanpa body).*

- **Contoh Response Gagal Otorisasi (`403 Forbidden`):**
```json
{
  "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

---

### 4.5 Modul Pengumpulan Tugas (Submissions)

---

#### 4.5.1 Daftar Pengumpulan Tugas Mahasiswa
- **Method:** `GET`
- **URI:** `/api/v1/assignments/{id}/submissions`
- **Akses:** Dosen Pemilik (`auth:sanctum`, role `dosen`, pengampu mata kuliah tugas terkait).
- **Path Parameters:**
  - `id` (integer): ID Tugas.
- **Query Parameters:**
  - `page` (integer, opsional): Halaman pagination.
- **Contoh Request (`curl`):**
```bash
curl -X GET "http://localhost:8000/api/v1/assignments/1/submissions" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890qwertyuiop..."
```

- **Contoh Response Sukses (`200 OK`):**
```json
{
  "data": [
    {
      "id": 1,
      "assignment_id": 1,
      "user_id": 4,
      "file_path": "submissions/tugas1_andi.pdf",
      "original_name": "tugas1_andi.pdf",
      "file_size": 204800,
      "note": "Berikut hasil pengerjaan tugas 1 saya Pak.",
      "submitted_at": "2026-10-05T14:00:00.000000Z",
      "is_late": false,
      "student": {
        "id": 4,
        "name": "Andi Pratama",
        "email": "mahasiswa@kampuslms.test",
        "role": "mahasiswa",
        "nim_nip": "2401010001",
        "created_at": "2026-10-05T10:00:00.000000Z"
      },
      "grade": {
        "id": 1,
        "submission_id": 1,
        "score": 90,
        "feedback": "Bagus, implementasi sesuai dengan standar REST.",
        "graded_by": 2,
        "graded_at": "2026-10-05T16:00:00.000000Z",
        "grader": {
          "id": 2,
          "name": "Dr. Budi Santoso",
          "email": "dosen@kampuslms.test",
          "role": "dosen",
          "nim_nip": "1980010001",
          "created_at": "2026-10-05T10:00:00.000000Z"
        },
        "created_at": "2026-10-05T16:00:00.000000Z",
        "updated_at": "2026-10-05T16:00:00.000000Z"
      },
      "created_at": "2026-10-05T14:00:00.000000Z",
      "updated_at": "2026-10-05T14:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

- **Contoh Response Gagal Otorisasi (`403 Forbidden`):**
```json
{
  "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

---

#### 4.5.2 Unggah Pengumpulan Tugas (Mahasiswa)
- **Method:** `POST`
- **URI:** `/api/v1/assignments/{id}/submissions`
- **Akses:** Mahasiswa Terdaftar (`auth:sanctum`, role `mahasiswa`, terdaftar pada mata kuliah tugas ini).
- **Path Parameters:**
  - `id` (integer): ID Tugas.
- **Content-Type:** `multipart/form-data`
- **Form Data Parameters:**

| Parameter | Tipe | Wajib | Aturan Validasi | Deskripsi |
|---|---|---|---|---|
| `file` | file | Ya | `required, file, max:20480` | Berkas tugas (maksimum 20MB) |
| `note` | string | Tidak | `nullable, string` | Catatan tambahan dari mahasiswa |

- **Aturan Bisnis:**
  - Pengumpulan hanya diizinkan **1 kali** untuk setiap mahasiswa pada tugas yang sama.
  - Jika tenggat waktu telah lewat dan tugas tidak mengizinkan pengumpulan terlambat (`allow_late = false`), permintaan akan ditolak.
- **Contoh Request (`curl`):**
```bash
curl -X POST http://localhost:8000/api/v1/assignments/1/submissions \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 3|mahasiswatoken12345..." \
  -F "file=@/path/ke/berkas/tugas1_andi.pdf" \
  -F "note=Berikut pengumpulan tugas 1 saya Pak."
```

- **Contoh Response Sukses (`201 Created`):**
```json
{
  "data": {
    "id": 2,
    "assignment_id": 1,
    "user_id": 4,
    "file_path": "submissions/randomstring_tugas1_andi.pdf",
    "original_name": "tugas1_andi.pdf",
    "file_size": 204800,
    "note": "Berikut pengumpulan tugas 1 saya Pak.",
    "submitted_at": "2026-10-05T14:30:00.000000Z",
    "is_late": false,
    "student": {
      "id": 4,
      "name": "Andi Pratama",
      "email": "mahasiswa@kampuslms.test",
      "role": "mahasiswa",
      "nim_nip": "2401010001",
      "created_at": "2026-10-05T10:00:00.000000Z"
    },
    "grade": null,
    "created_at": "2026-10-05T14:30:00.000000Z",
    "updated_at": "2026-10-05T14:30:00.000000Z"
  }
}
```

- **Contoh Response Gagal / Sudah Pernah Mengumpulkan (`422 Unprocessable Content`):**
```json
{
  "message": "Data yang diberikan tidak valid.",
  "errors": {
    "file": [
      "Anda sudah mengumpulkan tugas ini."
    ]
  }
}
```

- **Contoh Response Gagal / Tenggat Waktu Lewat (`422 Unprocessable Content`):**
```json
{
  "message": "Data yang diberikan tidak valid.",
  "errors": {
    "file": [
      "Tenggat waktu pengumpulan tugas telah lewat."
    ]
  }
}
```

- **Contoh Response Gagal Otorisasi / Bukan Mahasiswa Terdaftar (`403 Forbidden`):**
```json
{
  "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

---

### 4.6 Modul Penilaian Tugas (Grades)

---

#### 4.6.1 Berikan / Perbarui Nilai Submission (Upsert)
- **Method:** `PUT`
- **URI:** `/api/v1/submissions/{id}/grade`
- **Akses:** Dosen Pemilik (`auth:sanctum`, role `dosen`, pengampu mata kuliah tempat tugas berada).
- **Path Parameters:**
  - `id` (integer): ID Submission mahasiswa.
- **Body Request (`application/json`):**

| Parameter | Tipe | Wajib | Aturan Validasi | Deskripsi |
|---|---|---|---|---|
| `score` | numeric | Ya | `required, numeric, min:0, max:100` | Nilai angka (0 sampai 100) |
| `feedback` | string | Tidak | `nullable, string` | Umpan balik / catatan perbaikan |

- **Perilaku Khusus (Upsert Idempoten Sesuai Kontrak):**
  - Menggunakan `updateOrCreate` pada tabel `grades` dengan kunci `submission_id`.
  - Mengembalikan **`201 Created`** apabila nilai dibuat untuk pertama kali.
  - Mengembalikan **`200 OK`** apabila nilai telah ada dan diperbarui.
- **Contoh Request (`curl`):**
```bash
curl -X PUT http://localhost:8000/api/v1/submissions/1/grade \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890qwertyuiop..." \
  -d '{
    "score": 92,
    "feedback": "Hasil pekerjaan sangat baik dan rapi."
  }'
```

- **Contoh Response Sukses Pertama Kali (`201 Created`):**
```json
{
  "data": {
    "id": 1,
    "submission_id": 1,
    "score": 92,
    "feedback": "Hasil pekerjaan sangat baik dan rapi.",
    "graded_by": 2,
    "graded_at": "2026-10-05T15:00:00.000000Z",
    "grader": {
      "id": 2,
      "name": "Dr. Budi Santoso",
      "email": "dosen@kampuslms.test",
      "role": "dosen",
      "nim_nip": "1980010001",
      "created_at": "2026-10-05T10:00:00.000000Z"
    },
    "created_at": "2026-10-05T15:00:00.000000Z",
    "updated_at": "2026-10-05T15:00:00.000000Z"
  }
}
```

- **Contoh Response Sukses Pembaruan Nilai (`200 OK`):**
*(Format payload JSON identik dengan di atas, dengan kode status HTTP `200 OK`)*.

- **Contoh Response Gagal Otorisasi / Bukan Dosen Pemilik (`403 Forbidden`):**
```json
{
  "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

- **Contoh Response Gagal Validasi Nilai Negatif / > 100 (`422 Unprocessable Content`):**
```json
{
  "message": "Data yang diberikan tidak valid.",
  "errors": {
    "score": [
      "Nilai maksimal adalah 100."
    ]
  }
}
```

---

### 4.7 Modul Notifikasi Pengguna (Notifications)

---

#### 4.7.1 Daftar Notifikasi Pengguna
- **Method:** `GET`
- **URI:** `/api/v1/notifications`
- **Akses:** Terautentikasi (`auth:sanctum`).
- **Query Parameters:**
  - `page` (integer, opsional): Halaman pagination.
- **Contoh Request (`curl`):**
```bash
curl -X GET "http://localhost:8000/api/v1/notifications" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890qwertyuiop..."
```

- **Contoh Response Sukses (`200 OK`):**
```json
{
  "data": [
    {
      "id": "7b8f9e12-3456-4abc-9def-1234567890ab",
      "type": "App\\Notifications\\NewAssignmentNotification",
      "data": {
        "title": "Tugas Baru Tersedia",
        "message": "Dosen telah menambahkan Tugas 1: Integrasi REST API Sanctum."
      },
      "read_at": null,
      "created_at": "2026-10-05T10:30:00.000000Z",
      "updated_at": "2026-10-05T10:30:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

---

#### 4.7.2 Tandai Notifikasi Sebagai Telah Dibaca
- **Method:** `POST`
- **URI:** `/api/v1/notifications/{id}/read`
- **Akses:** Terautentikasi (`auth:sanctum`, hanya pemilik notifikasi terkait).
- **Path Parameters:**
  - `id` (string/UUID): ID notifikasi.
- **Contoh Request (`curl`):**
```bash
curl -X POST "http://localhost:8000/api/v1/notifications/7b8f9e12-3456-4abc-9def-1234567890ab/read" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890qwertyuiop..."
```

- **Contoh Response Sukses (`200 OK`):**
```json
{
  "data": {
    "id": "7b8f9e12-3456-4abc-9def-1234567890ab",
    "type": "App\\Notifications\\NewAssignmentNotification",
    "data": {
      "title": "Tugas Baru Tersedia",
      "message": "Dosen telah menambahkan Tugas 1: Integrasi REST API Sanctum."
    },
    "read_at": "2026-10-05T15:30:00.000000Z",
    "created_at": "2026-10-05T10:30:00.000000Z",
    "updated_at": "2026-10-05T15:30:00.000000Z"
  }
}
```

- **Contoh Response Gagal / Notifikasi Bukan Milik User (`404 Not Found`):**
```json
{
  "message": "Notifikasi tidak ditemukan."
}
```

---

## 5. Panduan Skenario Pengujian Otorisasi & Validasi (Curl Walkthrough)

Berikut adalah panduan eksekusi perintah terminal untuk memverifikasi kepatuhan sistem terhadap kontrak dan pengamanan otorisasi:

### Skenario 1: Uji Akses Tanpa Token (Harus `401 Unauthorized`)
```bash
curl -i http://localhost:8000/api/v1/courses \
  -H "Accept: application/json"
# Ekspektasi: HTTP/1.1 401 Unauthorized
# Body: {"message":"Unauthenticated."}
```

### Skenario 2: Login Dosen & Simpan Token
```bash
# Simpan token dosen ke dalam variabel shell
TOKEN_DOSEN=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"dosen@kampuslms.test","password":"password"}' \
  | grep -o '"token":"[^"]*' | grep -o '[^"]*$')

echo "Token Dosen: $TOKEN_DOSEN"
```

### Skenario 3: Login Mahasiswa & Simpan Token
```bash
TOKEN_MAHASISWA=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"mahasiswa@kampuslms.test","password":"password"}' \
  | grep -o '"token":"[^"]*' | grep -o '[^"]*$')

echo "Token Mahasiswa: $TOKEN_MAHASISWA"
```

### Skenario 4: Uji Mahasiswa Menembus Endpoint Dosen (Harus `403 Forbidden`)
```bash
# Mahasiswa mencoba membuat tugas baru
curl -i -X POST http://localhost:8000/api/v1/assignments \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN_MAHASISWA" \
  -d '{
    "course_id": 1,
    "title": "Tugas Ilegal",
    "instructions": "Percobaan",
    "due_at": "2026-11-20 23:59:00"
  }'
# Ekspektasi: HTTP/1.1 403 Forbidden
# Body: {"message":"Anda tidak memiliki akses ke sumber daya ini."}
```

### Skenario 5: Uji Dosen Lain Mengakses Mata Kuliah Bukan Pengampu (Harus `403 Forbidden`)
```bash
# Login sebagai Prof. Siti Rahayu (dosen2)
TOKEN_DOSEN2=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"dosen2@kampuslms.test","password":"password"}' \
  | grep -o '"token":"[^"]*' | grep -o '[^"]*$')

# Dosen 2 mencoba mengakses detail mata kuliah milik Dosen 1 (ID 1)
curl -i http://localhost:8000/api/v1/courses/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN_DOSEN2"
# Ekspektasi: HTTP/1.1 403 Forbidden
# Body: {"message":"Anda tidak memiliki akses ke sumber daya ini."}
```

### Skenario 6: Uji Akses Sah Dosen Pengampu (Harus `200 OK`)
```bash
curl -i http://localhost:8000/api/v1/courses/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN_DOSEN"
# Ekspektasi: HTTP/1.1 200 OK
# Body: {"data":{"id":1,"code":"IF401",...,"materials_count":...,"assignments_count":...}}
```

### Skenario 7: Uji Nilai Upsert (201 saat baru, 200 saat update)
```bash
# Berikan nilai pertama kali -> HTTP 201 Created
curl -i -X PUT http://localhost:8000/api/v1/submissions/1/grade \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN_DOSEN" \
  -d '{"score":85,"feedback":"Kerja bagus"}'

# Berikan nilai kedua kali (update) -> HTTP 200 OK
curl -i -X PUT http://localhost:8000/api/v1/submissions/1/grade \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN_DOSEN" \
  -d '{"score":90,"feedback":"Revisi telah dinilai ulang"}'
```

---

## 6. Catatan Keselarasan dengan Spek & Rencana Implementasi

1. **Struktur Pagination:**
   Sesuai kontrak `spek-week6.md`, seluruh endpoint koleksi memanfaatkan `PaginatedResourceCollection` sehingga hanya merender metadata `current_page`, `last_page`, dan `total`, tanpa tautan URL yang berlebihan.
2. **Pola Upsert Penilaian:**
   Endpoint `PUT /api/v1/submissions/{id}/grade` telah diimplementasikan dengan `Grade::updateOrCreate` dan mengembalikan kode HTTP `201 Created` saat penilaian pertama kali diinput dan `200 OK` saat diperbarui.
3. **Pemisahan Otorisasi 401 dan 403:**
   Sistem membedakan secara tegas kondisi tanpa token / token tidak valid (`401 Unauthenticated`) dengan kondisi akses lintas hak atau IDOR (`403 Forbidden`) sesuai `bootstrap/app.php`.
