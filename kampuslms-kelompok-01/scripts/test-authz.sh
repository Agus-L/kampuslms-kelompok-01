#!/usr/bin/env bash
# ============================================================================
# scripts/test-authz.sh — Skrip Pengujian Otorisasi (Policy) KampusLMS
# ============================================================================
#
# Deliverable BUILD No. 7 — Minggu 7 (Milestone M2)
# Modul: 03-modul-minggu-04-07.md § 7.3 Build
#
# Skrip ini KHUSUS menguji OTORISASI (Policy) — membuktikan bahwa setiap
# titik rawan IDOR dari minggu 5 sudah ditutup oleh Policy di minggu 7.
#
# Fokus pengujian (sesuai Prompt D § 7.2):
#   ✦ Mahasiswa mengakses submission mahasiswa lain (IDOR) → 403
#   ✦ Dosen mengakses mata kuliah dosen lain (isolasi kepemilikan) → 403
#   ✦ Mahasiswa mengakses mata kuliah yang tidak diikutinya → 403
#   ✦ Pengubahan role lewat request (mass assignment / privilege escalation) → ditolak
#   ✦ Akses tanpa autentikasi → 401
#   ✦ Audit trail submission (tidak boleh dihapus oleh siapa pun) → ditolak
#   ✦ Penilaian hanya oleh dosen pengampu mata kuliah → 403 untuk selain dosen pengampu
#   ✦ Proteksi middleware role pada route Web → 403
#
# Akun demo dari seeder:
#   - admin@kampuslms.test      (admin)
#   - dosen@kampuslms.test      (dosen1 — mengajar MK 1 & 2)
#   - dosen2@kampuslms.test     (dosen2 — mengajar MK 3 & 4)
#   - mahasiswa@kampuslms.test  (mahasiswa demo 1)
#   - mahasiswa2@kampuslms.test (mahasiswa demo 2 — tidak terdaftar di semua MK)
#
# Prasyarat:
#   - Server Laravel berjalan (php artisan serve)
#   - Database di-seed: php artisan migrate:fresh --seed
#   - curl tersedia di PATH
#
# Penggunaan:
#   bash scripts/test-authz.sh
#   BASE_URL=http://localhost:8000 bash scripts/test-authz.sh
#
# ============================================================================

set -uo pipefail

# ── Konfigurasi ──────────────────────────────────────────────────────────────

BASE_URL="${BASE_URL:-http://localhost:8000}"
API="${BASE_URL}/api/v1"

# Akun demo dari seeder (KampusLMSSeeder)
ADMIN_EMAIL="admin@kampuslms.test"
ADMIN_PASS="password"

DOSEN1_EMAIL="dosen@kampuslms.test"
DOSEN1_PASS="password"

DOSEN2_EMAIL="dosen2@kampuslms.test"
DOSEN2_PASS="password"

MAHASISWA_EMAIL="mahasiswa@kampuslms.test"
MAHASISWA_PASS="password"

MAHASISWA2_EMAIL="mahasiswa2@kampuslms.test"
MAHASISWA2_PASS="password"

# ── Warna output ─────────────────────────────────────────────────────────────

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
BOLD='\033[1m'
DIM='\033[2m'
NC='\033[0m'

# ── Counter ──────────────────────────────────────────────────────────────────

PASS_COUNT=0
FAIL_COUNT=0
SKIP_COUNT=0
TOTAL_COUNT=0

# ── Fungsi Helper ────────────────────────────────────────────────────────────

# HTTP request → status code saja
http_status() {
    local method="$1"
    local url="$2"
    local token="${3:-}"
    local data="${4:-}"
    local content_type="${5:-application/json}"

    local cmd=(curl -s -o /dev/null -w '%{http_code}' -X "$method" "$url"
        -H "Accept: application/json"
        -H "Content-Type: ${content_type}")

    if [[ -n "$token" ]]; then
        cmd+=(-H "Authorization: Bearer ${token}")
    fi

    if [[ -n "$data" ]]; then
        cmd+=(-d "$data")
    fi

    "${cmd[@]}" 2>/dev/null || echo "000"
}

# HTTP request → response body
http_body() {
    local method="$1"
    local url="$2"
    local token="${3:-}"
    local data="${4:-}"

    local cmd=(curl -s -X "$method" "$url"
        -H "Accept: application/json"
        -H "Content-Type: application/json")

    if [[ -n "$token" ]]; then
        cmd+=(-H "Authorization: Bearer ${token}")
    fi

    if [[ -n "$data" ]]; then
        cmd+=(-d "$data")
    fi

    "${cmd[@]}" 2>/dev/null
}

# Login → token
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

    echo "$response" | grep -o '"token":"[^"]*' | grep -o '[^"]*$' || true
}

# Assert: status code yang diharapkan
assert_status() {
    local desc="$1"
    local expected="$2"
    local actual="$3"

    TOTAL_COUNT=$((TOTAL_COUNT + 1))

    if [[ "$actual" == "$expected" ]]; then
        PASS_COUNT=$((PASS_COUNT + 1))
        echo -e "  ${GREEN}✔ PASS${NC} [${actual}] ${desc}"
    else
        FAIL_COUNT=$((FAIL_COUNT + 1))
        echo -e "  ${RED}✗ FAIL${NC} [expected ${expected}, got ${actual}] ${desc}"
    fi
}

# Assert: beberapa kemungkinan status code
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

# Skip test
skip_test() {
    local desc="$1"
    local reason="$2"
    TOTAL_COUNT=$((TOTAL_COUNT + 1))
    SKIP_COUNT=$((SKIP_COUNT + 1))
    echo -e "  ${YELLOW}⊘ SKIP${NC} ${desc} — ${reason}"
}

# Header kelompok test
section_header() {
    echo ""
    echo -e "${CYAN}${BOLD}━━━ $1 ━━━${NC}"
}

# Sub-header
policy_header() {
    echo -e "  ${MAGENTA}▸ $1${NC}"
}

# ============================================================================
# SETUP: Login ke akun demo & ambil token
# ============================================================================

echo ""
echo -e "${BOLD}╔══════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BOLD}║  scripts/test-authz.sh — Pengujian Otorisasi (Policy)          ║${NC}"
echo -e "${BOLD}║  Milestone M2 — Minggu 7 — KampusLMS                           ║${NC}"
echo -e "${BOLD}║  Base URL: ${API}${NC}"
echo -e "${BOLD}╚══════════════════════════════════════════════════════════════════╝${NC}"

echo ""
echo -e "${YELLOW}▸ Login ke akun-akun demo...${NC}"

TOKEN_ADMIN=$(get_token "$ADMIN_EMAIL" "$ADMIN_PASS")
if [[ -z "$TOKEN_ADMIN" ]]; then
    echo -e "${RED}✗ Gagal login admin. Pastikan server berjalan & database sudah di-seed.${NC}"
    exit 1
fi
echo -e "  ${GREEN}✔${NC} Admin       (Admin KampusLMS)    : ${TOKEN_ADMIN:0:15}..."

sleep 1

TOKEN_DOSEN1=$(get_token "$DOSEN1_EMAIL" "$DOSEN1_PASS")
if [[ -z "$TOKEN_DOSEN1" ]]; then
    echo -e "${RED}✗ Gagal login dosen1.${NC}"
    exit 1
fi
echo -e "  ${GREEN}✔${NC} Dosen 1     (Dr. Budi Santoso)   : ${TOKEN_DOSEN1:0:15}..."

sleep 1

TOKEN_DOSEN2=$(get_token "$DOSEN2_EMAIL" "$DOSEN2_PASS")
if [[ -z "$TOKEN_DOSEN2" ]]; then
    echo -e "${RED}✗ Gagal login dosen2.${NC}"
    exit 1
fi
echo -e "  ${GREEN}✔${NC} Dosen 2     (Prof. Siti Rahayu)  : ${TOKEN_DOSEN2:0:15}..."

sleep 1

TOKEN_MHS=$(get_token "$MAHASISWA_EMAIL" "$MAHASISWA_PASS")
if [[ -z "$TOKEN_MHS" ]]; then
    echo -e "${RED}✗ Gagal login mahasiswa demo 1.${NC}"
    exit 1
fi
echo -e "  ${GREEN}✔${NC} Mahasiswa 1 (Andi Pratama)        : ${TOKEN_MHS:0:15}..."

sleep 1

TOKEN_MHS2=$(get_token "$MAHASISWA2_EMAIL" "$MAHASISWA2_PASS")
if [[ -z "$TOKEN_MHS2" ]]; then
    echo -e "  ${YELLOW}⚠${NC} Mahasiswa demo 2 gagal login, pengujian unenrolled akan menggunakan ID statis."
    TOKEN_MHS2=""
else
    echo -e "  ${GREEN}✔${NC} Mahasiswa 2 (Mahasiswa Lain)     : ${TOKEN_MHS2:0:15}..."
fi

# ── Discover resource IDs dari seeder ────────────────────────────────────────

echo ""
echo -e "${YELLOW}▸ Menemukan ID resource dari database...${NC}"

DOSEN1_COURSE_ID=1
DOSEN2_COURSE_ID=3
DOSEN1_ASSIGNMENT_ID=1
DOSEN2_ASSIGNMENT_ID=7

# Cari submission ID
MHS_SUBMISSION_RESPONSE=$(http_body GET "${API}/assignments/${DOSEN1_ASSIGNMENT_ID}/submissions" "$TOKEN_DOSEN1")
MHS_DEMO_SUBMISSION_ID=""
OTHER_SUBMISSION_ID=""

if command -v php >/dev/null 2>&1; then
    MHS_DEMO_SUBMISSION_ID=$(php -r '$data = json_decode(file_get_contents("php://stdin"), true)["data"] ?? []; foreach($data as $s) { if (($s["student"]["email"] ?? "") === "mahasiswa@kampuslms.test") { echo $s["id"]; break; } }' <<< "$MHS_SUBMISSION_RESPONSE" 2>/dev/null || true)
    OTHER_SUBMISSION_ID=$(php -r '$data = json_decode(file_get_contents("php://stdin"), true)["data"] ?? []; foreach($data as $s) { if (($s["student"]["email"] ?? "") !== "mahasiswa@kampuslms.test") { echo $s["id"]; break; } }' <<< "$MHS_SUBMISSION_RESPONSE" 2>/dev/null || true)
fi

if [[ -z "$MHS_DEMO_SUBMISSION_ID" ]]; then
    MHS_DEMO_SUBMISSION_ID=1
fi
if [[ -z "$OTHER_SUBMISSION_ID" ]]; then
    OTHER_SUBMISSION_ID=2
fi

echo -e "  ${GREEN}✔${NC} Submission mahasiswa demo : ID ${MHS_DEMO_SUBMISSION_ID}"
echo -e "  ${GREEN}✔${NC} Submission mahasiswa lain : ID ${OTHER_SUBMISSION_ID}"

# Cari course yang TIDAK diikuti oleh mahasiswa 2
UNENROLLED_COURSE_ID=""
if [[ -n "$TOKEN_MHS2" ]]; then
    for cid in 1 2 3 4 5; do
        st=$(http_status GET "${API}/courses/${cid}" "$TOKEN_MHS2")
        if [[ "$st" == "403" ]]; then
            UNENROLLED_COURSE_ID="$cid"
            break
        fi
    done
fi
if [[ -z "$UNENROLLED_COURSE_ID" ]]; then
    UNENROLLED_COURSE_ID=5
fi
echo -e "  ${GREEN}✔${NC} Course tidak diikuti mhs2 : ID ${UNENROLLED_COURSE_ID}"


# ╔══════════════════════════════════════════════════════════════════╗
# ║  PENGUJIAN OTORISASI PER POLICY                                ║
# ╚══════════════════════════════════════════════════════════════════╝

# ============================================================================
# A. CoursePolicy — Otorisasi Mata Kuliah
# ============================================================================

section_header "A. CoursePolicy — Otorisasi Mata Kuliah"

policy_header "A1. Tanpa autentikasi → 401"
STATUS=$(http_status GET "${API}/courses")
assert_status "GET /courses tanpa token" "401" "$STATUS"
STATUS=$(http_status GET "${API}/courses/${DOSEN1_COURSE_ID}")
assert_status "GET /courses/{id} tanpa token" "401" "$STATUS"

policy_header "A2. viewAny — Daftar MK (disaring di query, bukan Policy)"
STATUS=$(http_status GET "${API}/courses" "$TOKEN_ADMIN")
assert_status "Admin melihat daftar MK → 200" "200" "$STATUS"
STATUS=$(http_status GET "${API}/courses" "$TOKEN_DOSEN1")
assert_status "Dosen melihat daftar MK (hanya miliknya) → 200" "200" "$STATUS"
STATUS=$(http_status GET "${API}/courses" "$TOKEN_MHS")
assert_status "Mahasiswa melihat daftar MK (enrolled) → 200" "200" "$STATUS"

policy_header "A3. view — Detail MK: isolasi kepemilikan dosen"
STATUS=$(http_status GET "${API}/courses/${DOSEN1_COURSE_ID}" "$TOKEN_DOSEN1")
assert_status "Dosen1 akses MK sendiri (ID ${DOSEN1_COURSE_ID}) → 200" "200" "$STATUS"
STATUS=$(http_status GET "${API}/courses/${DOSEN1_COURSE_ID}" "$TOKEN_DOSEN2")
assert_status "Dosen2 akses MK dosen1 (ID ${DOSEN1_COURSE_ID}) → 403 ★ IDOR" "403" "$STATUS"
STATUS=$(http_status GET "${API}/courses/${DOSEN2_COURSE_ID}" "$TOKEN_DOSEN2")
assert_status "Dosen2 akses MK sendiri (ID ${DOSEN2_COURSE_ID}) → 200" "200" "$STATUS"
STATUS=$(http_status GET "${API}/courses/${DOSEN2_COURSE_ID}" "$TOKEN_DOSEN1")
assert_status "Dosen1 akses MK dosen2 (ID ${DOSEN2_COURSE_ID}) → 403 ★ IDOR" "403" "$STATUS"

policy_header "A4. view — Mahasiswa: hanya MK yang diikuti"
STATUS=$(http_status GET "${API}/courses/${DOSEN1_COURSE_ID}" "$TOKEN_MHS")
assert_status "Mahasiswa 1 akses MK yang diikuti (ID ${DOSEN1_COURSE_ID}) → 200" "200" "$STATUS"

if [[ -n "$TOKEN_MHS2" ]]; then
    STATUS=$(http_status GET "${API}/courses/${UNENROLLED_COURSE_ID}" "$TOKEN_MHS2")
    assert_status "Mahasiswa 2 akses MK yang TIDAK diikutinya (ID ${UNENROLLED_COURSE_ID}) → 403 ★ IDOR" "403" "$STATUS"
fi

policy_header "A5. view — Admin: akses semua MK"
STATUS=$(http_status GET "${API}/courses/${DOSEN1_COURSE_ID}" "$TOKEN_ADMIN")
assert_status "Admin akses MK dosen1 → 200" "200" "$STATUS"
STATUS=$(http_status GET "${API}/courses/${DOSEN2_COURSE_ID}" "$TOKEN_ADMIN")
assert_status "Admin akses MK dosen2 → 200" "200" "$STATUS"

# ============================================================================
# B. MaterialPolicy — Otorisasi Materi Kuliah
# ============================================================================

section_header "B. MaterialPolicy — Otorisasi Materi Kuliah"

policy_header "B1. Tanpa autentikasi → 401"
STATUS=$(http_status GET "${API}/courses/${DOSEN1_COURSE_ID}/materials")
assert_status "GET /courses/{id}/materials tanpa token" "401" "$STATUS"

policy_header "B2. Isolasi kepemilikan dosen"
STATUS=$(http_status GET "${API}/courses/${DOSEN1_COURSE_ID}/materials" "$TOKEN_DOSEN1")
assert_status "Dosen1 akses materi MK sendiri → 200" "200" "$STATUS"
STATUS=$(http_status GET "${API}/courses/${DOSEN1_COURSE_ID}/materials" "$TOKEN_DOSEN2")
assert_status "Dosen2 akses materi MK dosen1 → 403 ★ IDOR" "403" "$STATUS"

policy_header "B3. Mahasiswa: materi MK yang diikuti vs tidak diikuti"
STATUS=$(http_status GET "${API}/courses/${DOSEN1_COURSE_ID}/materials" "$TOKEN_MHS")
assert_status "Mahasiswa akses materi MK enrolled → 200" "200" "$STATUS"

if [[ -n "$TOKEN_MHS2" ]]; then
    STATUS=$(http_status GET "${API}/courses/${UNENROLLED_COURSE_ID}/materials" "$TOKEN_MHS2")
    assert_status "Mahasiswa akses materi MK yang TIDAK diikutinya → 403 ★ IDOR" "403" "$STATUS"
fi

policy_header "B4. Admin akses materi"
STATUS=$(http_status GET "${API}/courses/${DOSEN1_COURSE_ID}/materials" "$TOKEN_ADMIN")
assert_status "Admin akses materi MK → 200" "200" "$STATUS"

# ============================================================================
# C. AssignmentPolicy — Otorisasi Penugasan
# ============================================================================

section_header "C. AssignmentPolicy — Otorisasi Penugasan"

policy_header "C1. viewAny — Daftar tugas per MK"
STATUS=$(http_status GET "${API}/courses/${DOSEN1_COURSE_ID}/assignments" "$TOKEN_DOSEN1")
assert_status "Dosen1 lihat tugas MK sendiri → 200" "200" "$STATUS"
STATUS=$(http_status GET "${API}/courses/${DOSEN1_COURSE_ID}/assignments" "$TOKEN_DOSEN2")
assert_status "Dosen2 lihat tugas MK dosen1 → 403 ★ IDOR" "403" "$STATUS"
STATUS=$(http_status GET "${API}/courses/${DOSEN1_COURSE_ID}/assignments" "$TOKEN_MHS")
assert_status "Mahasiswa lihat tugas MK enrolled → 200" "200" "$STATUS"

if [[ -n "$TOKEN_MHS2" ]]; then
    STATUS=$(http_status GET "${API}/courses/${UNENROLLED_COURSE_ID}/assignments" "$TOKEN_MHS2")
    assert_status "Mahasiswa lihat tugas MK TIDAK diikuti → 403 ★ IDOR" "403" "$STATUS"
fi

policy_header "C2. create — Buat tugas baru"
ASSIGNMENT_DATA='{"course_id":'${DOSEN1_COURSE_ID}',"title":"Test Authz Tugas","instructions":"Test otorisasi.","due_at":"2099-12-31 23:59:00","max_score":100,"status":"draft"}'

STATUS=$(http_status POST "${API}/assignments" "$TOKEN_DOSEN1" "$ASSIGNMENT_DATA")
assert_status "Dosen1 buat tugas di MK sendiri → 201" "201" "$STATUS"

ASSIGNMENT_DATA_D2='{"course_id":'${DOSEN1_COURSE_ID}',"title":"Test Authz D2","instructions":"Test.","due_at":"2099-12-31 23:59:00","max_score":100,"status":"draft"}'
STATUS=$(http_status POST "${API}/assignments" "$TOKEN_DOSEN2" "$ASSIGNMENT_DATA_D2")
assert_status "Dosen2 buat tugas di MK dosen1 → 403 ★ IDOR" "403" "$STATUS"

STATUS=$(http_status POST "${API}/assignments" "$TOKEN_MHS" "$ASSIGNMENT_DATA")
assert_status "Mahasiswa buat tugas → 403" "403" "$STATUS"

STATUS=$(http_status POST "${API}/assignments" "" "$ASSIGNMENT_DATA")
assert_status "Tanpa token buat tugas → 401" "401" "$STATUS"

policy_header "C3. update/delete — Tugas milik dosen lain"
NEW_ASGN_RESPONSE=$(http_body POST "${API}/assignments" "$TOKEN_DOSEN1" '{"course_id":'${DOSEN1_COURSE_ID}',"title":"Tugas Untuk Update Test","instructions":"Test.","due_at":"2099-12-31 23:59:00","status":"draft"}')
NEW_ASGN_ID=$(echo "$NEW_ASGN_RESPONSE" | grep -o '"id":[0-9]*' | head -1 | grep -o '[0-9]*' || true)

if [[ -n "$NEW_ASGN_ID" ]]; then
    STATUS=$(http_status PUT "${API}/assignments/${NEW_ASGN_ID}" "$TOKEN_DOSEN2" '{"title":"Diubah Dosen2"}')
    assert_status "Dosen2 update tugas dosen1 (ID ${NEW_ASGN_ID}) → 403 ★ IDOR" "403" "$STATUS"

    STATUS=$(http_status PUT "${API}/assignments/${NEW_ASGN_ID}" "$TOKEN_MHS" '{"title":"Diubah Mhs"}')
    assert_status "Mahasiswa update tugas → 403" "403" "$STATUS"

    STATUS=$(http_status DELETE "${API}/assignments/${NEW_ASGN_ID}" "$TOKEN_DOSEN2")
    assert_status "Dosen2 hapus tugas dosen1 (ID ${NEW_ASGN_ID}) → 403 ★ IDOR" "403" "$STATUS"

    STATUS=$(http_status DELETE "${API}/assignments/${NEW_ASGN_ID}" "$TOKEN_MHS")
    assert_status "Mahasiswa hapus tugas → 403" "403" "$STATUS"

    # Cleanup: dosen1 hapus tugas test-nya sendiri
    STATUS=$(http_status DELETE "${API}/assignments/${NEW_ASGN_ID}" "$TOKEN_DOSEN1")
    assert_status "Dosen1 hapus tugas sendiri (cleanup) → 204" "204" "$STATUS"
else
    skip_test "Update/delete tugas" "Gagal membuat tugas test"
fi

# ============================================================================
# D. SubmissionPolicy — Otorisasi Submission (Anti-IDOR Utama)
# ============================================================================

section_header "D. SubmissionPolicy — Otorisasi Submission (Anti-IDOR)"

policy_header "D1. viewAny — Daftar submission"
STATUS=$(http_status GET "${API}/assignments/${DOSEN1_ASSIGNMENT_ID}/submissions" "$TOKEN_DOSEN1")
assert_status "Dosen1 lihat submission tugas MK sendiri → 200" "200" "$STATUS"
STATUS=$(http_status GET "${API}/assignments/${DOSEN1_ASSIGNMENT_ID}/submissions" "$TOKEN_DOSEN2")
assert_status "Dosen2 lihat submission tugas MK dosen1 → 403 ★ IDOR" "403" "$STATUS"
STATUS=$(http_status GET "${API}/assignments/${DOSEN1_ASSIGNMENT_ID}/submissions" "$TOKEN_MHS")
assert_status "Mahasiswa lihat daftar semua submission → 403" "403" "$STATUS"
STATUS=$(http_status GET "${API}/assignments/${DOSEN1_ASSIGNMENT_ID}/submissions" "")
assert_status "Tanpa token lihat submission → 401" "401" "$STATUS"

policy_header "D2. create — Kumpul tugas: hanya mahasiswa terdaftar"
STATUS=$(http_status POST "${API}/assignments/${DOSEN1_ASSIGNMENT_ID}/submissions" "$TOKEN_DOSEN1" '{"note":"test"}')
assert_status "Dosen submit tugas → 403 (dosen tidak boleh submit)" "403" "$STATUS"
STATUS=$(http_status POST "${API}/assignments/${DOSEN1_ASSIGNMENT_ID}/submissions" "" '{"note":"test"}')
assert_status "Tanpa token submit tugas → 401" "401" "$STATUS"

policy_header "D3. ★★★ IDOR KRITIS: Mahasiswa akses submission mahasiswa lain ★★★"

# 1. Mahasiswa akses submission MILIK SENDIRI → HARUS 200
STATUS=$(http_status GET "${API}/submissions/${MHS_DEMO_SUBMISSION_ID}" "$TOKEN_MHS")
assert_status "Mahasiswa akses submission milik sendiri (API) → 200" "200" "$STATUS"

STATUS=$(http_status GET "${BASE_URL}/mahasiswa/submissions/${MHS_DEMO_SUBMISSION_ID}" "$TOKEN_MHS")
assert_status "Mahasiswa akses submission milik sendiri (Web) → 200" "200" "$STATUS"

# 2. Mahasiswa akses submission MAHASISWA LAIN → HARUS 403 ★ IDOR BLOCKED
STATUS=$(http_status GET "${API}/submissions/${OTHER_SUBMISSION_ID}" "$TOKEN_MHS")
assert_status "Mahasiswa akses submission mahasiswa lain (API) → 403 ★ IDOR BLOCKED" "403" "$STATUS"

STATUS=$(http_status GET "${BASE_URL}/mahasiswa/submissions/${OTHER_SUBMISSION_ID}" "$TOKEN_MHS")
assert_status "Mahasiswa akses submission mahasiswa lain (Web) → 403 ★ IDOR BLOCKED" "403" "$STATUS"

# 3. Dosen akses submission:
# Dosen1 akses submission di MK sendiri → 200
STATUS=$(http_status GET "${API}/submissions/${MHS_DEMO_SUBMISSION_ID}" "$TOKEN_DOSEN1")
assert_status "Dosen1 akses submission di MK sendiri (API) → 200" "200" "$STATUS"

STATUS=$(http_status GET "${BASE_URL}/dosen/submissions/${MHS_DEMO_SUBMISSION_ID}" "$TOKEN_DOSEN1")
assert_status "Dosen1 akses submission di MK sendiri (Web) → 200" "200" "$STATUS"

# Dosen2 akses submission di MK Dosen1 → 403 ★ IDOR BLOCKED
STATUS=$(http_status GET "${API}/submissions/${MHS_DEMO_SUBMISSION_ID}" "$TOKEN_DOSEN2")
assert_status "Dosen2 akses submission di MK dosen1 (API) → 403 ★ IDOR BLOCKED" "403" "$STATUS"

STATUS=$(http_status GET "${BASE_URL}/dosen/submissions/${MHS_DEMO_SUBMISSION_ID}" "$TOKEN_DOSEN2")
assert_status "Dosen2 akses submission di MK dosen1 (Web) → 403 ★ IDOR BLOCKED" "403" "$STATUS"

# Admin akses submission (audit) → 200
STATUS=$(http_status GET "${API}/submissions/${MHS_DEMO_SUBMISSION_ID}" "$TOKEN_ADMIN")
assert_status "Admin akses submission (audit) → 200" "200" "$STATUS"

# Tanpa token akses detail submission → 401
STATUS=$(http_status GET "${API}/submissions/${MHS_DEMO_SUBMISSION_ID}")
assert_status "Tanpa token akses detail submission → 401" "401" "$STATUS"

policy_header "D4. delete — Submission tidak boleh dihapus (academic audit trail)"
STATUS=$(http_status DELETE "${BASE_URL}/mahasiswa/submissions/${MHS_DEMO_SUBMISSION_ID}" "$TOKEN_MHS")
assert_status_any "Mahasiswa hapus submission (Web) → ditolak (405/403/404)" "$STATUS" 405 403 404
STATUS=$(http_status DELETE "${API}/submissions/${MHS_DEMO_SUBMISSION_ID}" "$TOKEN_MHS")
assert_status_any "Mahasiswa hapus submission (API) → ditolak (405/403/404)" "$STATUS" 405 403 404
STATUS=$(http_status DELETE "${API}/submissions/${MHS_DEMO_SUBMISSION_ID}" "$TOKEN_DOSEN1")
assert_status_any "Dosen hapus submission → ditolak (405/403/404)" "$STATUS" 405 403 404
STATUS=$(http_status DELETE "${API}/submissions/${MHS_DEMO_SUBMISSION_ID}" "$TOKEN_ADMIN")
assert_status_any "Admin hapus submission → ditolak (405/403/404)" "$STATUS" 405 403 404

# ============================================================================
# E. GradePolicy — Otorisasi Penilaian
# ============================================================================

section_header "E. GradePolicy — Otorisasi Penilaian"

GRADE_DATA='{"score":85,"feedback":"Test otorisasi penilaian."}'

policy_header "E1. create — Hanya dosen pengampu yang boleh menilai"
STATUS=$(http_status PUT "${API}/submissions/${MHS_DEMO_SUBMISSION_ID}/grade" "$TOKEN_DOSEN1" "$GRADE_DATA")
assert_status_any "Dosen1 menilai submission di MK sendiri → 200/201" "$STATUS" 200 201

STATUS=$(http_status PUT "${API}/submissions/${MHS_DEMO_SUBMISSION_ID}/grade" "$TOKEN_DOSEN2" "$GRADE_DATA")
assert_status "Dosen2 menilai submission di MK dosen1 → 403 ★ IDOR" "403" "$STATUS"

STATUS=$(http_status PUT "${API}/submissions/${MHS_DEMO_SUBMISSION_ID}/grade" "$TOKEN_MHS" "$GRADE_DATA")
assert_status "Mahasiswa menilai → 403" "403" "$STATUS"

STATUS=$(http_status PUT "${API}/submissions/${MHS_DEMO_SUBMISSION_ID}/grade" "$TOKEN_ADMIN" "$GRADE_DATA")
assert_status "Admin menilai → 403 (hanya dosen yang boleh)" "403" "$STATUS"

STATUS=$(http_status PUT "${API}/submissions/${MHS_DEMO_SUBMISSION_ID}/grade" "" "$GRADE_DATA")
assert_status "Tanpa token menilai → 401" "401" "$STATUS"

policy_header "E2. Upsert — Dosen update nilai yang sudah ada"
STATUS=$(http_status PUT "${API}/submissions/${MHS_DEMO_SUBMISSION_ID}/grade" "$TOKEN_DOSEN1" '{"score":92,"feedback":"Nilai diperbarui."}')
assert_status "Dosen1 update nilai (upsert kedua) → 200" "200" "$STATUS"

# ============================================================================
# F. Keamanan Tambahan — Mass Assignment & Role Escalation
# ============================================================================

section_header "F. Keamanan Tambahan — Mass Assignment & Privilege Escalation"

policy_header "F1. Pengubahan role lewat request (mass assignment)"
STATUS=$(http_status PUT "${API}/submissions/${MHS_DEMO_SUBMISSION_ID}/grade" "$TOKEN_MHS" '{"score":100,"feedback":"hacked","role":"admin"}')
assert_status "Mahasiswa kirim role=admin di body request → 403 (Policy)" "403" "$STATUS"

policy_header "F2. Token invalid / expired"
STATUS=$(http_status GET "${API}/courses" "invalid-token-12345")
assert_status "Token palsu → 401" "401" "$STATUS"
STATUS=$(http_status GET "${API}/me" "invalid-token-12345")
assert_status "Token palsu pada /me → 401" "401" "$STATUS"

policy_header "F3. Dosen mengakses daftar submission tugas MK dosen lain"
STATUS=$(http_status GET "${API}/assignments/${DOSEN2_ASSIGNMENT_ID}/submissions" "$TOKEN_DOSEN1")
assert_status "Dosen1 lihat submission tugas MK dosen2 → 403 ★ IDOR" "403" "$STATUS"
STATUS=$(http_status GET "${API}/assignments/${DOSEN1_ASSIGNMENT_ID}/submissions" "$TOKEN_DOSEN2")
assert_status "Dosen2 lihat submission tugas MK dosen1 → 403 ★ IDOR" "403" "$STATUS"

# ============================================================================
# G. Route Web — Pengujian Middleware role + Policy
# ============================================================================

section_header "G. Route Web — Middleware role + Otorisasi"

policy_header "G1. Akses halaman terproteksi tanpa login → 302/401"
STATUS=$(http_status GET "${BASE_URL}/admin/users")
assert_status_any "GET /admin/users tanpa login → 302/401" "$STATUS" 302 401
STATUS=$(http_status GET "${BASE_URL}/dosen/courses")
assert_status_any "GET /dosen/courses tanpa login → 302/401" "$STATUS" 302 401
STATUS=$(http_status GET "${BASE_URL}/mahasiswa/courses")
assert_status_any "GET /mahasiswa/courses tanpa login → 302/401" "$STATUS" 302 401
STATUS=$(http_status GET "${BASE_URL}/dashboard")
assert_status_any "GET /dashboard tanpa login → 302/401" "$STATUS" 302 401

policy_header "G2. Isolasi Role Middleware (Cross-role access rejection)"
STATUS=$(http_status GET "${BASE_URL}/admin/users" "$TOKEN_MHS")
assert_status "Mahasiswa akses route /admin/users → 403" "403" "$STATUS"

STATUS=$(http_status GET "${BASE_URL}/admin/users" "$TOKEN_DOSEN1")
assert_status "Dosen akses route /admin/users → 403" "403" "$STATUS"

STATUS=$(http_status GET "${BASE_URL}/dosen/courses" "$TOKEN_MHS")
assert_status "Mahasiswa akses route /dosen/courses → 403" "403" "$STATUS"

STATUS=$(http_status GET "${BASE_URL}/mahasiswa/courses" "$TOKEN_DOSEN1")
assert_status "Dosen akses route /mahasiswa/courses → 403" "403" "$STATUS"

policy_header "G3. Isolasi Dosen di Web Route Model Binding (Policy view)"
STATUS=$(http_status GET "${BASE_URL}/dosen/courses/${DOSEN1_COURSE_ID}" "$TOKEN_DOSEN1")
assert_status "Dosen1 akses detail MK sendiri di web → 200" "200" "$STATUS"

STATUS=$(http_status GET "${BASE_URL}/dosen/courses/${DOSEN2_COURSE_ID}" "$TOKEN_DOSEN1")
assert_status "Dosen1 akses detail MK dosen2 di web → 403 ★ IDOR" "403" "$STATUS"

# ============================================================================
# H. Isolasi Cross-Role — Ringkasan Bukti
# ============================================================================

section_header "H. Isolasi Cross-Role — Ringkasan Bukti"

echo -e "  ${DIM}Ringkasan tes yang sudah dijalankan:${NC}"
echo -e "  ${DIM}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "  ${DIM}│ Skenario                        │ Policy yang Menutup     │${NC}"
echo -e "  ${DIM}│ Dosen A → MK Dosen B             │ CoursePolicy->view()    │${NC}"
echo -e "  ${DIM}│ Dosen A → Materi MK Dosen B      │ MaterialPolicy / scope  │${NC}"
echo -e "  ${DIM}│ Dosen A → Tugas MK Dosen B       │ AssignmentPolicy        │${NC}"
echo -e "  ${DIM}│ Dosen A → Submission MK Dosen B   │ SubmissionPolicy        │${NC}"
echo -e "  ${DIM}│ Dosen A → Nilai MK Dosen B        │ GradePolicy             │${NC}"
echo -e "  ${DIM}│ Mahasiswa → Submission Mhs Lain   │ SubmissionPolicy->view()│${NC}"
echo -e "  ${DIM}│ Mahasiswa → MK Tidak Diikuti      │ CoursePolicy->view()    │${NC}"
echo -e "  ${DIM}│ Mahasiswa → Membuat Tugas          │ AssignmentPolicy/abort  │${NC}"
echo -e "  ${DIM}│ Mahasiswa → Menilai                │ GradePolicy->create()   │${NC}"
echo -e "  ${DIM}│ Hapus Submission                   │ SubmissionPolicy false  │${NC}"
echo -e "  ${DIM}│ Cross-Role Route Access            │ Middleware role         │${NC}"
echo -e "  ${DIM}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"


# ============================================================================
# RINGKASAN HASIL
# ============================================================================

echo ""
echo -e "${BOLD}╔══════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BOLD}║                       RINGKASAN HASIL                           ║${NC}"
echo -e "${BOLD}╠══════════════════════════════════════════════════════════════════╣${NC}"
printf "${BOLD}║${NC}  Total tes  : %-4s                                             ${BOLD}║${NC}\n" "$TOTAL_COUNT"
printf "${BOLD}║${NC}  ${GREEN}✔ Passed${NC}   : %-4s                                             ${BOLD}║${NC}\n" "$PASS_COUNT"
printf "${BOLD}║${NC}  ${RED}✗ Failed${NC}   : %-4s                                             ${BOLD}║${NC}\n" "$FAIL_COUNT"
printf "${BOLD}║${NC}  ${YELLOW}⊘ Skipped${NC}  : %-4s                                             ${BOLD}║${NC}\n" "$SKIP_COUNT"
echo -e "${BOLD}╚══════════════════════════════════════════════════════════════════╝${NC}"
echo ""

echo -e "${DIM}Skrip ini membuktikan bahwa setiap Policy di docs/keamanan.md${NC}"
echo -e "${DIM}benar-benar menutup titik rawan IDOR yang didaftarkan di minggu 5.${NC}"
echo ""

if [[ "$FAIL_COUNT" -gt 0 ]]; then
    echo -e "${RED}${BOLD}⚠ Ada ${FAIL_COUNT} pengujian yang GAGAL.${NC}"
    echo -e "${RED}Periksa Policy dan controller Anda. IDOR mungkin masih terbuka.${NC}"
    exit 1
else
    echo -e "${GREEN}${BOLD}✔ Semua pengujian otorisasi BERHASIL!${NC}"
    echo -e "${GREEN}Seluruh Policy berfungsi. Tidak ada IDOR terbuka.${NC}"
    exit 0
fi
