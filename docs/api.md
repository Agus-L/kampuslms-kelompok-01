# 📖 Dokumentasi REST API — KampusLMS (Minggu 6)

Dokumentasi resmi REST API KampusLMS berbasis Laravel 12 dan Laravel Sanctum sesuai dengan kontrak spesifikasi `spek-week6.md` dan `implementation-plan.md`.

---

## 📌 Ringkasan Sistem API

- **Base URL:** `http://localhost:8000/api/v1` (atau domain lokal Anda, contoh: `http://kampuslms.test/api/v1`)
- **Autentikasi:** Laravel Sanctum via `Bearer Token` pada HTTP Header `Authorization`.
- **Format Data:** JSON (`Accept: application/json` & `Content-Type: application/json` wajib disertakan pada request).
- **Pembungkus Response (API Resource):**
  - **Single Item:** `{ "data": { ... } }`
  - **Collection (Paginated):** `{ "data": [ ... ], "meta": { "current_page": 1, "last_page": 5, "total": 47 } }`
- **Rate Limiting:**
  - Login Endpoint (`POST /api/v1/auth/login`): Maksimal **5 request / menit** per IP/kredensial.
  - Endpoint Terotentikasi Umum: Maksimal **60 request / menit** per user.

---

## 🔑 Akun Demo (Seeder)

Gunakan akun demo berikut yang tersedia pada `DemoAccountSeeder` dan `KampusLMSSeeder` untuk pengujian `curl` atau API Client:

| Peran (Role) | Email | Password | NIM / NIP | Keterangan |
|---|---|---|---|---|
| **Dosen** | `dosen@kampuslms.test` | `password` | `1980010001` | Dr. Budi Santoso (Pengampu MK) |
| **Mahasiswa** | `mahasiswa@kampuslms.test` | `password` | `2401010001` | Andi Pratama (Mahasiswa Terdaftar) |
| **Admin** | `admin@kampuslms.test` | `password` | `ADM000001` | Admin KampusLMS |

---

## 🚦 Kode Status HTTP (HTTP Status Codes)

| Kode | Keterangan | Kondisi Penggunaan |
|---|---|---|
| `200 OK` | Request berhasil | Mengembalikan data (GET, PUT/PATCH update, Logout) |
| `201 Created` | Sumber daya berhasil dibuat | Pengembalian objek baru (POST assignment, POST submission, PUT grade baru) |
| `204 No Content` | Berhasil tanpa konten | Untuk penghapusan (DELETE assignment) |
| `401 Unauthorized` | Belum login / Token tidak valid | Bearer token tidak disertakan atau sudah kadaluarsa/dihapus |
| `403 Forbidden` | Tidak memiliki hak akses | User sudah terautentikasi tetapi tidak berhak (misal: mahasiswa akses endpoint dosen) |
| `404 Not Found` | Data tidak ditemukan | ID resource tidak ada di database |
| `422 Unprocessable Content` | Validasi input gagal | Format input salah, field required kurang, atau syarat bisnis melanggar |
| `429 Too Many Requests` | Melebihi batas rate limit | Terlalu banyak percobaan request dalam 1 menit |

---

## 📑 Daftar Endpoint API

### 1. Autentikasi & Profil User

---

#### 1.1 Login & Terbitkan Token
- **Method:** `POST`
- **URI:** `/api/v1/auth/login`
- **Akses:** Publik (Rate Limit: 5 request/menit)
- **Body Parameters (`application/json`):**

| Parameter | Tipe | Wajib | Deskripsi |
|---|---|---|---|
| `email` | string (email) | Ya | Email terdaftar (contoh: `dosen@kampuslms.test`) |
| `password` | string | Ya | Password akun |

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

- **Contoh Response Gagal Validasi / Password Salah (`422 Unprocessable Content`):**
```json
{
  "message": "Kredensial yang diberikan tidak cocok dengan data kami.",
  "errors": {
    "email": [
      "Kredensial yang diberikan tidak cocok dengan data kami."
    ]
  }
}
```

---

#### 1.2 Logout (Revoke Token)
- **Method:** `POST`
- **URI:** `/api/v1/auth/logout`
- **Akses:** Terautentikasi (`auth:sanctum`)
- **Headers:** `Authorization: Bearer <token>`
- **Contoh Request (`curl`):**
```bash
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890..."
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

#### 1.3 Profil User yang Login (`/me`)
- **Method:** `GET`
- **URI:** `/api/v1/me`
- **Akses:** Terautentikasi (`auth:sanctum`)
- **Headers:** `Authorization: Bearer <token>`
- **Contoh Request (`curl`):**
```bash
curl -X GET http://localhost:8000/api/v1/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890..."
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

### 2. Modul Mata Kuliah (Courses)

---

#### 2.1 Daftar Mata Kuliah
- **Method:** `GET`
- **URI:** `/api/v1/courses`
- **Akses:** Terautentikasi (`auth:sanctum`)
- **Perilaku Akses Berbasis Peran:**
  - **Dosen:** Mengembalikan daftar mata kuliah yang diampu (`lecturer_id === user.id`).
  - **Mahasiswa:** Mengembalikan daftar mata kuliah yang diikuti via pivot `course_user`.
- **Query Parameters:** `page` (integer, opsional)
- **Contoh Request (`curl`):**
```bash
curl -X GET "http://localhost:8000/api/v1/courses?page=1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890..."
```

- **Contoh Response Sukses (`200 OK`):**
```json
{
  "data": [
    {
      "id": 1,
      "code": "MK001",
      "name": "Pemrograman Web",
      "description": "Mata kuliah dasar Pemrograman Web dan REST API Laravel",
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

#### 2.2 Detail Mata Kuliah
- **Method:** `GET`
- **URI:** `/api/v1/courses/{id}`
- **Akses:** Terautentikasi (`auth:sanctum` + scope check)
- **Path Parameters:** `id` (integer) — ID Mata Kuliah
- **Keterangan:** Menyertakan `materials_count` dan `assignments_count`.
- **Contoh Request (`curl`):**
```bash
curl -X GET http://localhost:8000/api/v1/courses/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890..."
```

- **Contoh Response Sukses (`200 OK`):**
```json
{
  "data": {
    "id": 1,
    "code": "MK001",
    "name": "Pemrograman Web",
    "description": "Mata kuliah dasar Pemrograman Web dan REST API Laravel",
    "sks": 3,
    "status": "active",
    "lecturer": {
      "id": 2,
      "name": "Dr. Budi Santoso"
    },
    "materials_count": 5,
    "assignments_count": 3,
    "created_at": "2026-10-05T10:00:00.000000Z",
    "updated_at": "2026-10-05T10:00:00.000000Z"
  }
}
```

- **Contoh Response Gagal / Tidak Berhak (`403 Forbidden`):**
```json
{
  "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

- **Contoh Response Gagal / ID Tidak Ada (`404 Not Found`):**
```json
{
  "message": "No query results for model [App\\Models\\Course] 999"
}
```

---

#### 2.3 Daftar Materi dalam Mata Kuliah
- **Method:** `GET`
- **URI:** `/api/v1/courses/{id}/materials`
- **Akses:** Terautentikasi (`auth:sanctum` + scope check)
- **Path Parameters:** `id` (integer) — ID Mata Kuliah
- **Query Parameters:** `page` (integer, opsional)
- **Contoh Request (`curl`):**
```bash
curl -X GET http://localhost:8000/api/v1/courses/1/materials \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890..."
```

- **Contoh Response Sukses (`200 OK`):**
```json
{
  "data": [
    {
      "id": 1,
      "course_id": 1,
      "title": "Materi REST API & Sanctum",
      "content": "Pengenalan arsitektur REST API dan otentikasi Sanctum",
      "file_path": "materials/minggu-06-rest-api.pdf",
      "uploaded_by": 2,
      "uploader": {
        "id": 2,
        "name": "Dr. Budi Santoso",
        "email": "dosen@kampuslms.test",
        "role": "dosen",
        "nim_nip": "1980010001",
        "created_at": "2026-10-05T10:00:00.000000Z"
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

#### 2.4 Daftar Tugas dalam Mata Kuliah
- **Method:** `GET`
- **URI:** `/api/v1/courses/{id}/assignments`
- **Akses:** Terautentikasi (`auth:sanctum` + scope check)
- **Path Parameters:** `id` (integer) — ID Mata Kuliah
- **Query Parameters:**
  - `status` (string, opsional): `draft` atau `published`
  - `page` (integer, opsional)
- **Contoh Request (`curl`):**
```bash
curl -X GET "http://localhost:8000/api/v1/courses/1/assignments?status=published&page=1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890..."
```

- **Contoh Response Sukses (`200 OK`):**
```json
{
  "data": [
    {
      "id": 1,
      "course_id": 1,
      "title": "Tugas 1: Integrasi Sanctum",
      "instructions": "Implementasikan endpoint auth dengan Sanctum",
      "due_at": "2026-10-15 23:59:00",
      "max_score": 100,
      "allow_late": false,
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

### 3. Modul Tugas & Pengumpulan (Assignments & Submissions)

---

#### 3.1 Buat Tugas Baru (Dosen)
- **Method:** `POST`
- **URI:** `/api/v1/assignments`
- **Akses:** Dosen Pengampu (`auth:sanctum`, role `dosen`, pengampu `course_id`)
- **Body Parameters (`application/json`):**

| Parameter | Tipe | Wajib | Aturan | Deskripsi |
|---|---|---|---|---|
| `course_id` | integer | Ya | `exists:courses,id` | ID Mata Kuliah |
| `title` | string | Ya | `max:255` | Judul tugas |
| `instructions` | string | Tidak | - | Petunjuk pengerjaan |
| `due_at` | string | Ya | `date_format:Y-m-d H:i:s` | Tanggal & waktu tenggat |
| `max_score` | integer | Ya | `between:1,100` | Nilai maksimum |
| `allow_late` | boolean | Tidak | boolean | Izinkan pengumpulan terlambat |
| `status` | string | Ya | `in:draft,published` | Status publikasi |

- **Contoh Request (`curl`):**
```bash
curl -X POST http://localhost:8000/api/v1/assignments \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890..." \
  -d '{
    "course_id": 1,
    "title": "Tugas 2: API Resource Course",
    "instructions": "Buat Resource untuk endpoint Course & Assignment",
    "due_at": "2026-10-20 23:59:00",
    "max_score": 100,
    "allow_late": false,
    "status": "published"
  }'
```

- **Contoh Response Sukses (`201 Created`):**
```json
{
  "data": {
    "id": 2,
    "course_id": 1,
    "title": "Tugas 2: API Resource Course",
    "instructions": "Buat Resource untuk endpoint Course & Assignment",
    "due_at": "2026-10-20 23:59:00",
    "max_score": 100,
    "allow_late": false,
    "status": "published",
    "created_by": 2,
    "created_at": "2026-10-05T11:00:00.000000Z",
    "updated_at": "2026-10-05T11:00:00.000000Z"
  }
}
```

- **Contoh Response Gagal / Bukan Dosen Pengampu (`403 Forbidden`):**
```json
{
  "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

---

#### 3.2 Update Tugas (Dosen Pemilik)
- **Method:** `PUT` / `PATCH`
- **URI:** `/api/v1/assignments/{id}`
- **Akses:** Dosen Pemilik (`auth:sanctum`, role `dosen`, pengampu course tugas)
- **Path Parameters:** `id` (integer) — ID Assignment
- **Body Parameters (`application/json`):** Sama seperti pada `POST /api/v1/assignments` (kecuali `course_id` dapat dikirim opsional/tetap sama).
- **Contoh Request (`curl`):**
```bash
curl -X PUT http://localhost:8000/api/v1/assignments/2 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890..." \
  -d '{
    "course_id": 1,
    "title": "Tugas 2: API Resource Course (Revisi)",
    "instructions": "Tambahkan skrip curl pengujian",
    "due_at": "2026-10-22 23:59:00",
    "max_score": 100,
    "allow_late": true,
    "status": "published"
  }'
```

- **Contoh Response Sukses (`200 OK`):**
```json
{
  "data": {
    "id": 2,
    "course_id": 1,
    "title": "Tugas 2: API Resource Course (Revisi)",
    "instructions": "Tambahkan skrip curl pengujian",
    "due_at": "2026-10-22 23:59:00",
    "max_score": 100,
    "allow_late": true,
    "status": "published",
    "created_by": 2,
    "created_at": "2026-10-05T11:00:00.000000Z",
    "updated_at": "2026-10-05T12:00:00.000000Z"
  }
}
```

---

#### 3.3 Hapus Tugas (Dosen Pemilik)
- **Method:** `DELETE`
- **URI:** `/api/v1/assignments/{id}`
- **Akses:** Dosen Pemilik (`auth:sanctum`, role `dosen`, pengampu course tugas)
- **Path Parameters:** `id` (integer) — ID Assignment
- **Contoh Request (`curl`):**
```bash
curl -X DELETE http://localhost:8000/api/v1/assignments/2 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890..."
```

- **Contoh Response Sukses (`204 No Content`):**
*(Tanpa body response / 0 byte)*

- **Contoh Response Gagal / Tidak Berhak (`403 Forbidden`):**
```json
{
  "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

---

#### 3.4 Lihat Daftar Submission Tugas (Dosen Pemilik)
- **Method:** `GET`
- **URI:** `/api/v1/assignments/{id}/submissions`
- **Akses:** Dosen Pemilik (`auth:sanctum`, role `dosen`, pengampu course tugas)
- **Path Parameters:** `id` (integer) — ID Assignment
- **Query Parameters:** `page` (integer, opsional)
- **Contoh Request (`curl`):**
```bash
curl -X GET http://localhost:8000/api/v1/assignments/1/submissions \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890..."
```

- **Contoh Response Sukses (`200 OK`):**
```json
{
  "data": [
    {
      "id": 1,
      "assignment_id": 1,
      "user_id": 3,
      "file_path": "submissions/tugas1_andi.pdf",
      "original_name": "tugas1_andi.pdf",
      "file_size": 204800,
      "note": "Berikut jawaban tugas 1 saya",
      "submitted_at": "2026-10-05T12:00:00.000000Z",
      "is_late": false,
      "student": {
        "id": 3,
        "name": "Andi Pratama",
        "email": "mahasiswa@kampuslms.test",
        "role": "mahasiswa",
        "nim_nip": "2401010001",
        "created_at": "2026-10-05T10:00:00.000000Z"
      },
      "grade": null,
      "created_at": "2026-10-05T12:00:00.000000Z",
      "updated_at": "2026-10-05T12:00:00.000000Z"
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

#### 3.5 Pengumpulan Tugas (Mahasiswa)
- **Method:** `POST`
- **URI:** `/api/v1/assignments/{id}/submissions`
- **Akses:** Mahasiswa Terdaftar (`auth:sanctum`, role `mahasiswa`, terdaftar pada mata kuliah)
- **Path Parameters:** `id` (integer) — ID Assignment
- **Content-Type:** `multipart/form-data`
- **Form Data Parameters:**

| Parameter | Tipe | Wajib | Aturan | Deskripsi |
|---|---|---|---|---|
| `file` | file | Ya | `file, mimes:pdf,zip,doc,docx, max:10240` | Berkas tugas (maks 10MB) |
| `note` | string | Tidak | `max:500` | Catatan pengumpulan |

- **Contoh Request (`curl`):**
```bash
curl -X POST http://localhost:8000/api/v1/assignments/1/submissions \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 3|mahasiswa_token_hash..." \
  -F "file=@/path/to/tugas1_andi.pdf" \
  -F "note=Berikut berkas tugas 1 saya Pak"
```

- **Contoh Response Sukses (`201 Created`):**
```json
{
  "data": {
    "id": 1,
    "assignment_id": 1,
    "user_id": 3,
    "file_path": "submissions/abc123xyz_tugas1_andi.pdf",
    "original_name": "tugas1_andi.pdf",
    "file_size": 204800,
    "note": "Berikut berkas tugas 1 saya Pak",
    "submitted_at": "2026-10-05T14:00:00.000000Z",
    "is_late": false,
    "student": {
      "id": 3,
      "name": "Andi Pratama",
      "email": "mahasiswa@kampuslms.test",
      "role": "mahasiswa",
      "nim_nip": "2401010001",
      "created_at": "2026-10-05T10:00:00.000000Z"
    },
    "grade": null,
    "created_at": "2026-10-05T14:00:00.000000Z",
    "updated_at": "2026-10-05T14:00:00.000000Z"
  }
}
```

- **Contoh Response Gagal / Sudah Pernah Mengumpulkan (`422 Unprocessable Content`):**
```json
{
  "message": "Anda sudah mengumpulkan tugas ini.",
  "errors": {
    "file": [
      "Anda sudah mengumpulkan tugas ini."
    ]
  }
}
```

---

#### 3.6 Penilaian Submission (Dosen - Upsert)
- **Method:** `PUT`
- **URI:** `/api/v1/submissions/{id}/grade`
- **Akses:** Dosen Pemilik (`auth:sanctum`, role `dosen`, pengampu course tempat tugas berada)
- **Path Parameters:** `id` (integer) — ID Submission
- **Body Parameters (`application/json`):**

| Parameter | Tipe | Wajib | Aturan | Deskripsi |
|---|---|---|---|---|
| `score` | integer | Ya | `between:0,100` | Nilai yang diberikan |
| `feedback` | string | Tidak | `max:1000` | Ulasan/umpan balik dari dosen |

- **Keterangan Tambahan:** Menggunakan pola *upsert* (`updateOrCreate`). Mengembalikan **`201 Created`** saat nilai dibuat pertama kali dan **`200 OK`** saat nilai diperbarui.
- **Contoh Request (`curl`):**
```bash
curl -X PUT http://localhost:8000/api/v1/submissions/1/grade \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890..." \
  -d '{
    "score": 90,
    "feedback": "Pengerjaan sangat baik dan penanganan error lengkap."
  }'
```

- **Contoh Response Sukses Pertama Kali (`201 Created`):**
```json
{
  "data": {
    "id": 1,
    "submission_id": 1,
    "score": 90,
    "feedback": "Pengerjaan sangat baik dan penanganan error lengkap.",
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

- **Contoh Response Sukses Pembaruan/Update (`200 OK`):**
*(Struktur body persis sama dengan di atas, namun status code HTTP yang dikembalikan adalah 200 OK)*.

---

### 4. Modul Notifikasi (Notifications)

---

#### 4.1 Daftar Notifikasi User
- **Method:** `GET`
- **URI:** `/api/v1/notifications`
- **Akses:** Terautentikasi (`auth:sanctum`)
- **Query Parameters:** `page` (integer, opsional)
- **Contoh Request (`curl`):**
```bash
curl -X GET http://localhost:8000/api/v1/notifications \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890..."
```

- **Contoh Response Sukses (`200 OK`):**
```json
{
  "data": [
    {
      "id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
      "type": "App\\Notifications\\AssignmentCreatedNotification",
      "data": {
        "message": "Tugas baru telah ditambahkan: Tugas 1: Integrasi Sanctum",
        "assignment_id": 1
      },
      "read_at": null,
      "created_at": "2026-10-05T10:00:00.000000Z"
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

#### 4.2 Tandai Notifikasi Telah Dibaca
- **Method:** `POST`
- **URI:** `/api/v1/notifications/{id}/read`
- **Akses:** Terautentikasi (`auth:sanctum`)
- **Path Parameters:** `id` (string/UUID) — ID Notifikasi
- **Contoh Request (`curl`):**
```bash
curl -X POST http://localhost:8000/api/v1/notifications/9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d/read \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef1234567890..."
```

- **Contoh Response Sukses (`200 OK`):**
```json
{
  "data": {
    "id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
    "type": "App\\Notifications\\AssignmentCreatedNotification",
    "data": {
      "message": "Tugas baru telah ditambahkan: Tugas 1: Integrasi Sanctum",
      "assignment_id": 1
    },
    "read_at": "2026-10-05T16:00:00.000000Z",
    "created_at": "2026-10-05T10:00:00.000000Z"
  }
}
```

- **Contoh Response Gagal / Notifikasi Tidak Ada (`404 Not Found`):**
```json
{
  "message": "Notifikasi tidak ditemukan."
}
```

---

## 🧪 Panduan Menjalankan Pengujian API via Terminal (`curl`)

Berikut adalah alur lengkap pengujian API KampusLMS dari terminal:

```bash
# 1. Login sebagai Dosen
TOKEN_DOSEN=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"dosen@kampuslms.test","password":"password"}' \
  | grep -o '"token":"[^"]*' | grep -o '[^"]*$')

echo "Token Dosen: $TOKEN_DOSEN"

# 2. Ambil Profil User
curl -s http://localhost:8000/api/v1/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN_DOSEN"

# 3. Ambil Daftar Mata Kuliah
curl -s http://localhost:8000/api/v1/courses \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN_DOSEN"

# 4. Login sebagai Mahasiswa
TOKEN_MAHASISWA=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"mahasiswa@kampuslms.test","password":"password"}' \
  | grep -o '"token":"[^"]*' | grep -o '[^"]*$')

echo "Token Mahasiswa: $TOKEN_MAHASISWA"

# 5. Uji Otorisasi: Mahasiswa coba buat tugas (Harus 403 Forbidden)
curl -i -X POST http://localhost:8000/api/v1/assignments \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN_MAHASISWA" \
  -d '{"course_id":1,"title":"Tugas Ilegal","due_at":"2026-10-30 23:59:00","max_score":100,"status":"published"}'
```
