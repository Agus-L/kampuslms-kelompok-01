#!/usr/bin/env bash
# ============================================================================
# scripts/test-api.sh — Skrip Pengujian Otorisasi API KampusLMS (Minggu 6)
# ============================================================================
#
# Skrip ini menguji otorisasi seluruh endpoint REST API KampusLMS
# berdasarkan Prompt C modul 03-modul-minggu-04-07.md (Bagian 6.2C).
#
# Untuk setiap endpoint, diuji 4 kondisi:
#   1. Tanpa token sama sekali        → harus 401
#   2. Token mahasiswa di endpoint dosen → harus 403
#   3. Token dosen A mengakses data dosen B → harus 403
#   4. Token yang benar dengan hak yang benar → harus 200/201/204
#
# Prasyarat:
#   - Server Laravel berjalan di BASE_URL (default: http://localhost:8000)
#   - Database sudah di-seed dengan KampusLMSSeeder
#   - curl tersedia di PATH
#
# Penggunaan:
#   bash scripts/test-api.sh
#
#   Atur BASE_URL jika server berjalan di alamat lain:
#   BASE_URL=http://kampuslms.test bash scripts/test-api.sh
#
# ============================================================================

# Jangan pakai set -e agar skrip tidak berhenti saat grep gagal di get_token
set -uo pipefail

# ── Konfigurasi ──────────────────────────────────────────────────────────────

BASE_URL="${BASE_URL:-http://localhost:8000}"
API="${BASE_URL}/api/v1"

# Akun demo dari seeder
DOSEN1_EMAIL="dosen@kampuslms.test"
DOSEN1_PASS="password"

DOSEN2_EMAIL="dosen2@kampuslms.test"
DOSEN2_PASS="password"

MAHASISWA_EMAIL="mahasiswa@kampuslms.test"
MAHASISWA_PASS="password"

# ID resource yang diharapkan ada dari seeder
COURSE_ID=1          # Mata kuliah milik dosen1
ASSIGNMENT_ID=1      # Tugas di course milik dosen1
SUBMISSION_ID=1      # Submission mahasiswa di assignment milik dosen1

# ── Warna output ─────────────────────────────────────────────────────────────

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m' # No Color

# ── Counter ──────────────────────────────────────────────────────────────────

PASS_COUNT=0
FAIL_COUNT=0
TOTAL_COUNT=0

# ── Fungsi Helper ────────────────────────────────────────────────────────────

# Melakukan HTTP request dan mengembalikan status code saja
# Usage: http_status METHOD URL [TOKEN] [DATA] [CONTENT_TYPE]
http_status() {
    local method="$1"
    local url="$2"
    local token="${3:-}"
    local data="${4:-}"
    local content_type="${5:-application/json}"

    local cmd=(curl -s -o /dev/null -w '%{http_code}' -X "$method" "$url" -H "Accept: application/json" -H "Content-Type: ${content_type}")

    if [[ -n "$token" ]]; then
        cmd+=(-H "Authorization: Bearer ${token}")
    fi

    if [[ -n "$data" ]]; then
        cmd+=(-d "$data")
    fi

    "${cmd[@]}" 2>/dev/null || echo "000"
}

# Melakukan login dan mengembalikan token
# Usage: get_token EMAIL PASSWORD
get_token() {
    local email="$1"
    local password="$2"
    local payload
    payload=$(printf '{"email":"%s","password":"%s"}' "$email" "$password")

    local response
    response=$(curl -s -X POST "${API}/auth/login" \
        -H "Accept: application/json" \
        -H "Content-Type: application/json" \
        -d "$payload" 2>/dev/null)

    # Ekstrak token dari response JSON (mengembalikan string kosong jika gagal)
    echo "$response" | grep -o '"token":"[^"]*' | grep -o '[^"]*$' || true
}

# Fungsi assert: cek apakah status code sesuai ekspektasi
# Usage: assert_status DESCRIPTION EXPECTED ACTUAL
assert_status() {
    local desc="$1"
    local expected="$2"
    local actual="$3"

    TOTAL_COUNT=$((TOTAL_COUNT + 1))

    if [[ "$actual" == "$expected" ]]; then
        PASS_COUNT=$((PASS_COUNT + 1))
        echo -e "  ${GREEN}✔ PASS${NC} [${expected}] ${desc}"
    else
        FAIL_COUNT=$((FAIL_COUNT + 1))
        echo -e "  ${RED}✗ FAIL${NC} [expected ${expected}, got ${actual}] ${desc}"
    fi
}

# Assert yang menerima beberapa kemungkinan status code
# Usage: assert_status_any DESCRIPTION ACTUAL CODE1 CODE2 ...
assert_status_any() {
    local desc="$1"
    local actual="$2"
    shift 2
    local expected_list="$*"

    TOTAL_COUNT=$((TOTAL_COUNT + 1))

    for expected in $expected_list; do
        if [[ "$actual" == "$expected" ]]; then
            PASS_COUNT=$((PASS_COUNT + 1))
            echo -e "  ${GREEN}✔ PASS${NC} [${actual}] ${desc}"
            return
        fi
    done

    FAIL_COUNT=$((FAIL_COUNT + 1))
    echo -e "  ${RED}✗ FAIL${NC} [expected ${expected_list// //}, got ${actual}] ${desc}"
}

# Header untuk setiap kelompok pengujian
test_header() {
    echo ""
    echo -e "${CYAN}${BOLD}─── $1 ───${NC}"
}

# ============================================================================
# LANGKAH 0: Setup — Login dan simpan token
# ============================================================================

echo ""
echo -e "${BOLD}╔═══════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BOLD}║    Skrip Pengujian Otorisasi API KampusLMS — Minggu 6      ║${NC}"
echo -e "${BOLD}║    Base URL: ${API}${NC}"
echo -e "${BOLD}╚═══════════════════════════════════════════════════════════════╝${NC}"

echo ""
echo -e "${YELLOW}▸ Mendapatkan token autentikasi...${NC}"

TOKEN_DOSEN1=$(get_token "$DOSEN1_EMAIL" "$DOSEN1_PASS")
if [[ -z "$TOKEN_DOSEN1" ]]; then
    echo -e "${RED}✗ Gagal login sebagai dosen1 (${DOSEN1_EMAIL}). Pastikan server berjalan dan data seeder tersedia.${NC}"
    exit 1
fi
echo -e "  ${GREEN}✔${NC} Token Dosen 1 (Dr. Budi Santoso): ${TOKEN_DOSEN1:0:20}..."

sleep 1  # Jeda antar login agar tidak kena rate limit

TOKEN_DOSEN2=$(get_token "$DOSEN2_EMAIL" "$DOSEN2_PASS")
if [[ -z "$TOKEN_DOSEN2" ]]; then
    echo -e "${RED}✗ Gagal login sebagai dosen2 (${DOSEN2_EMAIL}).${NC}"
    exit 1
fi
echo -e "  ${GREEN}✔${NC} Token Dosen 2 (Prof. Siti Rahayu): ${TOKEN_DOSEN2:0:20}..."

sleep 1

TOKEN_MAHASISWA=$(get_token "$MAHASISWA_EMAIL" "$MAHASISWA_PASS")
if [[ -z "$TOKEN_MAHASISWA" ]]; then
    echo -e "${RED}✗ Gagal login sebagai mahasiswa (${MAHASISWA_EMAIL}).${NC}"
    exit 1
fi
echo -e "  ${GREEN}✔${NC} Token Mahasiswa (Andi Pratama): ${TOKEN_MAHASISWA:0:20}..."

# ============================================================================
# PENGUJIAN 1: GET /me — Profil
# Kondisi Prompt C:
#   1. Tanpa token → 401
#   4. Token benar → 200
# ============================================================================

test_header "1. GET /me — Profil Pengguna"

# Kondisi 1: Tanpa token sama sekali → harus 401
STATUS=$(http_status GET "${API}/me")
assert_status "Tanpa token → 401" "401" "$STATUS"

# Kondisi 4: Token dosen (hak yang benar) → 200
STATUS=$(http_status GET "${API}/me" "$TOKEN_DOSEN1")
assert_status "Token dosen → 200" "200" "$STATUS"

# Kondisi 4: Token mahasiswa (hak yang benar) → 200
STATUS=$(http_status GET "${API}/me" "$TOKEN_MAHASISWA")
assert_status "Token mahasiswa → 200" "200" "$STATUS"

# ============================================================================
# PENGUJIAN 2: GET /courses — Daftar Mata Kuliah
# Kondisi Prompt C:
#   1. Tanpa token → 401
#   4. Token benar → 200
# ============================================================================

test_header "2. GET /courses — Daftar Mata Kuliah"

# Kondisi 1: Tanpa token → 401
STATUS=$(http_status GET "${API}/courses")
assert_status "Tanpa token → 401" "401" "$STATUS"

# Kondisi 4: Token dosen → 200 (MK yang diajar)
STATUS=$(http_status GET "${API}/courses" "$TOKEN_DOSEN1")
assert_status "Token dosen → 200" "200" "$STATUS"

# Kondisi 4: Token mahasiswa → 200 (MK yang diikuti)
STATUS=$(http_status GET "${API}/courses" "$TOKEN_MAHASISWA")
assert_status "Token mahasiswa → 200" "200" "$STATUS"

# ============================================================================
# PENGUJIAN 3: GET /courses/{id} — Detail Mata Kuliah
# Kondisi Prompt C (lengkap 4):
#   1. Tanpa token → 401
#   2. — (endpoint ini bukan khusus dosen, tapi punya scope)
#   3. Token dosen A mengakses course dosen B → 403
#   4. Token benar → 200
# ============================================================================

test_header "3. GET /courses/${COURSE_ID} — Detail Mata Kuliah"

# Kondisi 1: Tanpa token → 401
STATUS=$(http_status GET "${API}/courses/${COURSE_ID}")
assert_status "Tanpa token → 401" "401" "$STATUS"

# Kondisi 4: Dosen pengampu (pemilik) → 200
STATUS=$(http_status GET "${API}/courses/${COURSE_ID}" "$TOKEN_DOSEN1")
assert_status "Dosen pengampu (dosen1) → 200" "200" "$STATUS"

# Kondisi 3: Dosen lain (bukan pengampu) → 403 (proteksi IDOR)
STATUS=$(http_status GET "${API}/courses/${COURSE_ID}" "$TOKEN_DOSEN2")
assert_status "Dosen lain (dosen2) → 403 (IDOR)" "403" "$STATUS"

# Kondisi 4: Mahasiswa terdaftar → 200
STATUS=$(http_status GET "${API}/courses/${COURSE_ID}" "$TOKEN_MAHASISWA")
assert_status "Mahasiswa terdaftar → 200" "200" "$STATUS"

# ============================================================================
# PENGUJIAN 4: GET /courses/{id}/materials — Daftar Materi
# Kondisi Prompt C (4 kondisi):
#   1. Tanpa token → 401
#   3. Token dosen A → course dosen B → 403
#   4. Token benar → 200
# ============================================================================

test_header "4. GET /courses/${COURSE_ID}/materials — Daftar Materi"

# Kondisi 1: Tanpa token → 401
STATUS=$(http_status GET "${API}/courses/${COURSE_ID}/materials")
assert_status "Tanpa token → 401" "401" "$STATUS"

# Kondisi 4: Dosen pengampu → 200
STATUS=$(http_status GET "${API}/courses/${COURSE_ID}/materials" "$TOKEN_DOSEN1")
assert_status "Dosen pengampu (dosen1) → 200" "200" "$STATUS"

# Kondisi 3: Dosen lain → 403
STATUS=$(http_status GET "${API}/courses/${COURSE_ID}/materials" "$TOKEN_DOSEN2")
assert_status "Dosen lain (dosen2) → 403" "403" "$STATUS"

# Kondisi 4: Mahasiswa terdaftar → 200
STATUS=$(http_status GET "${API}/courses/${COURSE_ID}/materials" "$TOKEN_MAHASISWA")
assert_status "Mahasiswa terdaftar → 200" "200" "$STATUS"

# ============================================================================
# PENGUJIAN 5: GET /courses/{id}/assignments — Daftar Tugas
# Kondisi Prompt C (4 kondisi):
#   1. Tanpa token → 401
#   3. Token dosen A → course dosen B → 403
#   4. Token benar → 200
# ============================================================================

test_header "5. GET /courses/${COURSE_ID}/assignments — Daftar Tugas"

# Kondisi 1: Tanpa token → 401
STATUS=$(http_status GET "${API}/courses/${COURSE_ID}/assignments")
assert_status "Tanpa token → 401" "401" "$STATUS"

# Kondisi 4: Dosen pengampu → 200
STATUS=$(http_status GET "${API}/courses/${COURSE_ID}/assignments" "$TOKEN_DOSEN1")
assert_status "Dosen pengampu (dosen1) → 200" "200" "$STATUS"

# Kondisi 3: Dosen lain → 403
STATUS=$(http_status GET "${API}/courses/${COURSE_ID}/assignments" "$TOKEN_DOSEN2")
assert_status "Dosen lain (dosen2) → 403" "403" "$STATUS"

# Kondisi 4: Mahasiswa terdaftar → 200
STATUS=$(http_status GET "${API}/courses/${COURSE_ID}/assignments" "$TOKEN_MAHASISWA")
assert_status "Mahasiswa terdaftar → 200" "200" "$STATUS"

# Bonus: Dengan filter ?status=published → 200
STATUS=$(http_status GET "${API}/courses/${COURSE_ID}/assignments?status=published" "$TOKEN_DOSEN1")
assert_status "Filter ?status=published → 200" "200" "$STATUS"

# ============================================================================
# PENGUJIAN 6: POST /assignments — Buat Tugas Baru
# Kondisi Prompt C (lengkap 4):
#   1. Tanpa token → 401
#   2. Mahasiswa buat tugas → 403
#   3. Dosen A buat tugas di course dosen B → 403
#   4. Dosen pengampu buat tugas → 201
# ============================================================================

test_header "6. POST /assignments — Buat Tugas Baru"

ASSIGNMENT_DATA='{"course_id":'"${COURSE_ID}"',"title":"Tugas Test Otorisasi","instructions":"Uji coba skrip.","due_at":"2099-12-31 23:59:00","max_score":100,"status":"draft"}'

# Kondisi 1: Tanpa token → 401
STATUS=$(http_status POST "${API}/assignments" "" "$ASSIGNMENT_DATA")
assert_status "Tanpa token → 401" "401" "$STATUS"

# Kondisi 2: Mahasiswa → 403 (mahasiswa tidak boleh buat tugas)
STATUS=$(http_status POST "${API}/assignments" "$TOKEN_MAHASISWA" "$ASSIGNMENT_DATA")
assert_status "Mahasiswa membuat tugas → 403" "403" "$STATUS"

# Kondisi 3: Dosen lain (dosen2) untuk course milik dosen1 → 403
STATUS=$(http_status POST "${API}/assignments" "$TOKEN_DOSEN2" "$ASSIGNMENT_DATA")
assert_status "Dosen lain (dosen2) buat tugas di course dosen1 → 403" "403" "$STATUS"

# Kondisi 4: Dosen pengampu → 201
STATUS=$(http_status POST "${API}/assignments" "$TOKEN_DOSEN1" "$ASSIGNMENT_DATA")
assert_status "Dosen pengampu (dosen1) buat tugas → 201" "201" "$STATUS"

# Ambil ID tugas baru untuk test update/delete nanti
NEW_ASSIGNMENT_RESPONSE=$(curl -s -X POST "${API}/assignments" \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer ${TOKEN_DOSEN1}" \
    -d '{"course_id":'"${COURSE_ID}"',"title":"Tugas Untuk Dihapus","instructions":"Akan dihapus.","due_at":"2099-12-31 23:59:00","status":"draft"}' 2>/dev/null)
NEW_ASSIGNMENT_ID=$(echo "$NEW_ASSIGNMENT_RESPONSE" | grep -o '"id":[0-9]*' | head -1 | grep -o '[0-9]*' || true)

if [[ -z "$NEW_ASSIGNMENT_ID" ]]; then
    echo -e "  ${YELLOW}⚠ Tidak bisa membuat tugas baru untuk test update/delete. Menggunakan ASSIGNMENT_ID=${ASSIGNMENT_ID}${NC}"
    NEW_ASSIGNMENT_ID="$ASSIGNMENT_ID"
fi

# ============================================================================
# PENGUJIAN 7: PUT/PATCH /assignments/{id} — Update Tugas
# Kondisi Prompt C (lengkap 4):
#   1. Tanpa token → 401
#   2. Mahasiswa update tugas → 403
#   3. Dosen A update tugas dosen B → 403
#   4. Dosen pemilik update → 200
# ============================================================================

test_header "7. PUT /assignments/${NEW_ASSIGNMENT_ID} — Update Tugas"

UPDATE_DATA='{"title":"Tugas Diperbarui","max_score":95}'

# Kondisi 1: Tanpa token → 401
STATUS=$(http_status PUT "${API}/assignments/${NEW_ASSIGNMENT_ID}" "" "$UPDATE_DATA")
assert_status "Tanpa token → 401" "401" "$STATUS"

# Kondisi 2: Mahasiswa → 403
STATUS=$(http_status PUT "${API}/assignments/${NEW_ASSIGNMENT_ID}" "$TOKEN_MAHASISWA" "$UPDATE_DATA")
assert_status "Mahasiswa update tugas → 403" "403" "$STATUS"

# Kondisi 3: Dosen lain (dosen2) → 403
STATUS=$(http_status PUT "${API}/assignments/${NEW_ASSIGNMENT_ID}" "$TOKEN_DOSEN2" "$UPDATE_DATA")
assert_status "Dosen lain (dosen2) update tugas dosen1 → 403" "403" "$STATUS"

# Kondisi 4: Dosen pemilik → 200
STATUS=$(http_status PUT "${API}/assignments/${NEW_ASSIGNMENT_ID}" "$TOKEN_DOSEN1" "$UPDATE_DATA")
assert_status "Dosen pengampu (dosen1) update tugas → 200" "200" "$STATUS"

# ============================================================================
# PENGUJIAN 8: DELETE /assignments/{id} — Hapus Tugas
# Kondisi Prompt C (lengkap 4):
#   1. Tanpa token → 401
#   2. Mahasiswa hapus tugas → 403
#   3. Dosen A hapus tugas dosen B → 403
#   4. Dosen pemilik hapus → 204
# ============================================================================

test_header "8. DELETE /assignments/${NEW_ASSIGNMENT_ID} — Hapus Tugas"

# Kondisi 1: Tanpa token → 401
STATUS=$(http_status DELETE "${API}/assignments/${NEW_ASSIGNMENT_ID}")
assert_status "Tanpa token → 401" "401" "$STATUS"

# Kondisi 2: Mahasiswa → 403
STATUS=$(http_status DELETE "${API}/assignments/${NEW_ASSIGNMENT_ID}" "$TOKEN_MAHASISWA")
assert_status "Mahasiswa hapus tugas → 403" "403" "$STATUS"

# Kondisi 3: Dosen lain (dosen2) → 403
STATUS=$(http_status DELETE "${API}/assignments/${NEW_ASSIGNMENT_ID}" "$TOKEN_DOSEN2")
assert_status "Dosen lain (dosen2) hapus tugas dosen1 → 403" "403" "$STATUS"

# Kondisi 4: Dosen pemilik → 204
STATUS=$(http_status DELETE "${API}/assignments/${NEW_ASSIGNMENT_ID}" "$TOKEN_DOSEN1")
assert_status "Dosen pengampu (dosen1) hapus tugas → 204" "204" "$STATUS"

# ============================================================================
# PENGUJIAN 9: GET /assignments/{id}/submissions — Daftar Submission
# Kondisi Prompt C (lengkap 4):
#   1. Tanpa token → 401
#   2. Mahasiswa lihat semua submission → 403
#   3. Dosen A lihat submission dosen B → 403
#   4. Dosen pemilik → 200
# ============================================================================

test_header "9. GET /assignments/${ASSIGNMENT_ID}/submissions — Daftar Submission"

# Kondisi 1: Tanpa token → 401
STATUS=$(http_status GET "${API}/assignments/${ASSIGNMENT_ID}/submissions")
assert_status "Tanpa token → 401" "401" "$STATUS"

# Kondisi 2: Mahasiswa → 403 (hanya dosen yang boleh lihat semua submission)
STATUS=$(http_status GET "${API}/assignments/${ASSIGNMENT_ID}/submissions" "$TOKEN_MAHASISWA")
assert_status "Mahasiswa lihat daftar submission → 403" "403" "$STATUS"

# Kondisi 3: Dosen lain (dosen2) → 403
STATUS=$(http_status GET "${API}/assignments/${ASSIGNMENT_ID}/submissions" "$TOKEN_DOSEN2")
assert_status "Dosen lain (dosen2) lihat submission dosen1 → 403" "403" "$STATUS"

# Kondisi 4: Dosen pemilik → 200
STATUS=$(http_status GET "${API}/assignments/${ASSIGNMENT_ID}/submissions" "$TOKEN_DOSEN1")
assert_status "Dosen pengampu (dosen1) lihat submission → 200" "200" "$STATUS"

# ============================================================================
# PENGUJIAN 10: POST /assignments/{id}/submissions — Upload Submission
# Kondisi Prompt C:
#   1. Tanpa token → 401
#   2. Dosen upload submission → 403 (dosen bukan mahasiswa)
#   3. Dosen lain → 403
#   4. Mahasiswa terdaftar → 422 (file wajib) / 201 (jika file dikirim)
#
# Catatan: Endpoint ini sebenarnya memerlukan multipart/form-data dengan file.
#          Untuk uji OTORISASI saja, kita kirim POST tanpa file — middleware
#          auth:sanctum dan pengecekan role terjadi SEBELUM validasi file.
#          Jika lolos auth, Laravel mengembalikan 422 (file required).
# ============================================================================

test_header "10. POST /assignments/${ASSIGNMENT_ID}/submissions — Upload Submission"

# Kondisi 1: Tanpa token → 401
STATUS=$(http_status POST "${API}/assignments/${ASSIGNMENT_ID}/submissions")
assert_status "Tanpa token → 401" "401" "$STATUS"

# Kondisi 2: Dosen upload → 403 (dosen tidak boleh submit)
STATUS=$(http_status POST "${API}/assignments/${ASSIGNMENT_ID}/submissions" "$TOKEN_DOSEN1" '{"note":"test"}')
assert_status "Dosen upload submission → 403" "403" "$STATUS"

# Kondisi 3: Dosen lain → 403
STATUS=$(http_status POST "${API}/assignments/${ASSIGNMENT_ID}/submissions" "$TOKEN_DOSEN2" '{"note":"test"}')
assert_status "Dosen lain (dosen2) upload submission → 403" "403" "$STATUS"

# Kondisi 4: Mahasiswa terdaftar → 422 (file wajib, tapi auth berhasil)
#            atau 201 jika validasi file tidak wajib
STATUS=$(http_status POST "${API}/assignments/${ASSIGNMENT_ID}/submissions" "$TOKEN_MAHASISWA" '{"note":"test"}')
assert_status_any "Mahasiswa upload submission → auth lolos (422 file wajib / 201 berhasil)" "$STATUS" 201 422


# ============================================================================
# PENGUJIAN 11: PUT /submissions/{id}/grade — Berikan/Perbarui Nilai (Upsert)
# Kondisi Prompt C (lengkap 4):
#   1. Tanpa token → 401
#   2. Mahasiswa menilai → 403
#   3. Dosen A menilai submission dosen B → 403
#   4. Dosen pemilik → 201 (baru) atau 200 (update)
# ============================================================================

test_header "11. PUT /submissions/${SUBMISSION_ID}/grade — Penilaian (Upsert)"

GRADE_DATA='{"score":85,"feedback":"Penilaian dari skrip pengujian."}'

# Kondisi 1: Tanpa token → 401
STATUS=$(http_status PUT "${API}/submissions/${SUBMISSION_ID}/grade" "" "$GRADE_DATA")
assert_status "Tanpa token → 401" "401" "$STATUS"

# Kondisi 2: Mahasiswa → 403 (mahasiswa tidak bisa menilai)
STATUS=$(http_status PUT "${API}/submissions/${SUBMISSION_ID}/grade" "$TOKEN_MAHASISWA" "$GRADE_DATA")
assert_status "Mahasiswa menilai → 403" "403" "$STATUS"

# Kondisi 3: Dosen lain (dosen2) → 403
STATUS=$(http_status PUT "${API}/submissions/${SUBMISSION_ID}/grade" "$TOKEN_DOSEN2" "$GRADE_DATA")
assert_status "Dosen lain (dosen2) menilai submission dosen1 → 403" "403" "$STATUS"

# Kondisi 4: Dosen pemilik → 201 (baru) atau 200 (update)
STATUS=$(http_status PUT "${API}/submissions/${SUBMISSION_ID}/grade" "$TOKEN_DOSEN1" "$GRADE_DATA")
assert_status_any "Dosen pengampu menilai → 201 (baru) atau 200 (update)" "$STATUS" 201 200

# Bonus: Upsert — panggil lagi, harus 200 (update)
STATUS=$(http_status PUT "${API}/submissions/${SUBMISSION_ID}/grade" "$TOKEN_DOSEN1" '{"score":90,"feedback":"Nilai diperbarui oleh skrip."}')
assert_status "Upsert kedua kali → 200" "200" "$STATUS"

# ============================================================================
# PENGUJIAN 12: GET /notifications — Daftar Notifikasi
# Kondisi Prompt C:
#   1. Tanpa token → 401
#   4. Token benar → 200
# ============================================================================

test_header "12. GET /notifications — Daftar Notifikasi"

# Kondisi 1: Tanpa token → 401
STATUS=$(http_status GET "${API}/notifications")
assert_status "Tanpa token → 401" "401" "$STATUS"

# Kondisi 4: Token dosen → 200
STATUS=$(http_status GET "${API}/notifications" "$TOKEN_DOSEN1")
assert_status "Token dosen → 200" "200" "$STATUS"

# Kondisi 4: Token mahasiswa → 200
STATUS=$(http_status GET "${API}/notifications" "$TOKEN_MAHASISWA")
assert_status "Token mahasiswa → 200" "200" "$STATUS"

# ============================================================================
# PENGUJIAN 13: POST /notifications/{id}/read — Tandai Dibaca
# Kondisi Prompt C:
#   1. Tanpa token → 401
#   3. Dosen baca notifikasi milik mahasiswa → 404 (not found di scope-nya)
#   4. Pemilik baca notifikasinya → 200
# ============================================================================

test_header "13. POST /notifications/{id}/read — Tandai Notifikasi Dibaca"

FAKE_NOTIF_ID="00000000-0000-0000-0000-000000000000"

# Kondisi 1: Tanpa token → 401
STATUS=$(http_status POST "${API}/notifications/${FAKE_NOTIF_ID}/read")
assert_status "Tanpa token → 401" "401" "$STATUS"

# Kondisi tambahan: Token valid, notifikasi tidak ditemukan → 404
STATUS=$(http_status POST "${API}/notifications/${FAKE_NOTIF_ID}/read" "$TOKEN_DOSEN1")
assert_status "Notifikasi tidak ditemukan → 404" "404" "$STATUS"

# Coba ambil notifikasi asli milik mahasiswa (jika ada)
NOTIF_RESPONSE=$(curl -s -X GET "${API}/notifications" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${TOKEN_MAHASISWA}" 2>/dev/null)
NOTIF_ID=$(echo "$NOTIF_RESPONSE" | grep -o '"id":"[^"]*' | head -1 | grep -o '[^"]*$' || true)

if [[ -n "$NOTIF_ID" ]]; then
    # Kondisi 3: Dosen membaca notifikasi milik mahasiswa → 404
    STATUS=$(http_status POST "${API}/notifications/${NOTIF_ID}/read" "$TOKEN_DOSEN1")
    assert_status "Dosen baca notifikasi milik mahasiswa → 404" "404" "$STATUS"

    # Kondisi 4: Mahasiswa baca notifikasinya sendiri → 200
    STATUS=$(http_status POST "${API}/notifications/${NOTIF_ID}/read" "$TOKEN_MAHASISWA")
    assert_status "Mahasiswa baca notifikasinya sendiri → 200" "200" "$STATUS"
else
    echo -e "  ${YELLOW}⚠ Tidak ada notifikasi di database. Test 13d dan 13e dilewati.${NC}"
fi

# ============================================================================
# PENGUJIAN 14: POST /auth/login — Login
# Catatan: Diletakkan di akhir agar tidak menghabiskan rate limit
#          sebelum token diperoleh di awal skrip.
# Kondisi Prompt C:
#   4. Kredensial benar → 200
#   Validasi: password salah → 422, body kosong → 422
# ============================================================================

echo ""
echo -e "${YELLOW}▸ Menunggu reset rate limit login (61 detik)...${NC}"
sleep 61

test_header "14. POST /auth/login — Login"

# Kondisi 4: Login dengan kredensial benar → 200
STATUS=$(http_status POST "${API}/auth/login" "" '{"email":"dosen@kampuslms.test","password":"password"}')
assert_status "Login kredensial benar → 200" "200" "$STATUS"

# Login dengan password salah → 422
STATUS=$(http_status POST "${API}/auth/login" "" '{"email":"dosen@kampuslms.test","password":"salah"}')
assert_status "Login password salah → 422" "422" "$STATUS"

# Login tanpa body → 422
STATUS=$(http_status POST "${API}/auth/login" "" '{}')
assert_status "Login tanpa body → 422" "422" "$STATUS"

# ============================================================================
# PENGUJIAN 15: POST /auth/logout — Logout
# Kondisi Prompt C:
#   1. Tanpa token → 401
#   4. Token valid → 200
# ============================================================================

test_header "15. POST /auth/logout — Logout"

# Kondisi 1: Tanpa token → 401
STATUS=$(http_status POST "${API}/auth/logout")
assert_status "Tanpa token → 401" "401" "$STATUS"

# Buat token sementara untuk dilogout
sleep 1
TEMP_TOKEN=$(get_token "$DOSEN1_EMAIL" "$DOSEN1_PASS")
if [[ -n "$TEMP_TOKEN" ]]; then
    # Kondisi 4: Token valid → 200
    STATUS=$(http_status POST "${API}/auth/logout" "$TEMP_TOKEN")
    assert_status "Token valid → 200" "200" "$STATUS"

    # Bonus: Token yang sudah dilogout → 401
    STATUS=$(http_status POST "${API}/auth/logout" "$TEMP_TOKEN")
    assert_status "Token sudah revoked → 401" "401" "$STATUS"
else
    echo -e "  ${YELLOW}⚠ Gagal mendapatkan token sementara (rate limit?). Test logout dengan token dilewati.${NC}"
fi

# ============================================================================
# RINGKASAN HASIL
# ============================================================================

echo ""
echo -e "${BOLD}╔═══════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BOLD}║                     RINGKASAN HASIL                        ║${NC}"
echo -e "${BOLD}╠═══════════════════════════════════════════════════════════════╣${NC}"
echo -e "${BOLD}║${NC}  Total tes  : ${TOTAL_COUNT}                                          ${BOLD}║${NC}"
echo -e "${BOLD}║${NC}  ${GREEN}✔ Passed${NC}   : ${PASS_COUNT}                                          ${BOLD}║${NC}"
echo -e "${BOLD}║${NC}  ${RED}✗ Failed${NC}   : ${FAIL_COUNT}                                          ${BOLD}║${NC}"
echo -e "${BOLD}╚═══════════════════════════════════════════════════════════════╝${NC}"
echo ""

if [[ "$FAIL_COUNT" -gt 0 ]]; then
    echo -e "${RED}${BOLD}Ada ${FAIL_COUNT} pengujian yang gagal. Periksa endpoint dan otorisasi Anda.${NC}"
    exit 1
else
    echo -e "${GREEN}${BOLD}Semua pengujian berhasil! Otorisasi API berjalan sesuai kontrak.${NC}"
    exit 0
fi
