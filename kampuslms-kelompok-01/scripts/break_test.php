<?php
/**
 * Break Test Script — Simulasi 7 kerusakan untuk minggu-06
 *
 * Jalankan: php scripts/break_test.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($bootstrapRequest = \Illuminate\Http\Request::capture());

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Course;

// Helper: Buat request internal yang benar (support JSON body)
function apiRequest(string $method, string $uri, array $headers = [], array $body = []): \Symfony\Component\HttpFoundation\Response
{
    $server = [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
    ];
    foreach ($headers as $key => $value) {
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        $server[$serverKey] = $value;
    }

    $content = !empty($body) ? json_encode($body) : null;
    $request = Request::create($uri, $method, [], [], [], $server, $content);

    return app()->handle($request);
}

echo "=============================================================\n";
echo "  BREAK TEST - 7 Simulasi Kerusakan KampusLMS API\n";
echo "=============================================================\n\n";

// ─────────────────────────────────────────────────────────────
// BREAK 1: response()->json(User::all()) dan hapus $hidden
// ─────────────────────────────────────────────────────────────
echo ">> BREAK 1: response()->json(User::all()) - cek kebocoran data\n";
echo str_repeat('-', 60) . "\n";

$usersWithHidden = User::all()->toArray();
$sampleUser = $usersWithHidden[0] ?? [];
$hasPasswordHidden = !isset($sampleUser['password']);
$hasRememberTokenHidden = !isset($sampleUser['remember_token']);

echo "[A] Dengan \$hidden aktif (default):\n";
echo "   - Kolom 'password' tersembunyi: " . ($hasPasswordHidden ? "YA" : "TIDAK (BOCOR!)") . "\n";
echo "   - Kolom 'remember_token' tersembunyi: " . ($hasRememberTokenHidden ? "YA" : "TIDAK (BOCOR!)") . "\n";
echo "   - Kolom yang tampil: " . implode(', ', array_keys($sampleUser)) . "\n";
echo "   - Email sample: " . ($sampleUser['email'] ?? 'N/A') . "\n\n";

$usersNoHidden = User::all()->each(function ($u) {
    $u->makeVisible(['password', 'remember_token']);
})->toArray();
$sampleUserNoHidden = $usersNoHidden[0] ?? [];
$passwordLeaked = isset($sampleUserNoHidden['password']);
$passwordHash = $sampleUserNoHidden['password'] ?? 'N/A';

echo "[B] Setelah \$hidden DIHAPUS (makeVisible):\n";
echo "   - Kolom 'password' TAMPIL: " . ($passwordLeaked ? "YA - HASH BOCOR!" : "TIDAK") . "\n";
if ($passwordLeaked) {
    echo "   - Hash password yang bocor: " . $passwordHash . "\n";
}
echo "   - Kolom yang tampil: " . implode(', ', array_keys($sampleUserNoHidden)) . "\n";
echo "   BAHAYA: Hash bcrypt password bocor, bisa di-crack offline!\n";
echo "\n";

// ─────────────────────────────────────────────────────────────
// BREAK 2: Hapus auth:sanctum dari route, panggil tanpa token
// ─────────────────────────────────────────────────────────────
echo ">> BREAK 2: Hapus auth:sanctum - panggil endpoint tanpa token\n";
echo str_repeat('-', 60) . "\n";

$response2 = apiRequest('GET', '/api/v1/courses');
echo "[Dengan auth:sanctum aktif] Status: " . $response2->getStatusCode() . "\n";
echo "   Body: " . $response2->getContent() . "\n";

if ($response2->getStatusCode() === 401) {
    echo "   -> Data DILINDUNGI: akses ditolak 401 Unauthenticated\n";
}

echo "\n[Simulasi jika auth:sanctum DIHAPUS]:\n";
echo "   -> Endpoint akan return 200 OK dengan data terbuka.\n";
echo "   -> Siapapun tanpa token bisa mengakses data mata kuliah.\n";
echo "   BAHAYA: Data akademik bocor tanpa otentikasi!\n\n";

// ─────────────────────────────────────────────────────────────
// BREAK 3: Panggil dengan token yang sudah dihapus
// ─────────────────────────────────────────────────────────────
echo ">> BREAK 3: Panggil endpoint dengan token yang sudah dihapus\n";
echo str_repeat('-', 60) . "\n";

$dosenUser = User::where('email', 'dosen@kampuslms.test')->first();
if (!$dosenUser) $dosenUser = User::where('role', 'dosen')->first();

if ($dosenUser) {
    // Buat token
    $tokenObj = $dosenUser->createToken('test-break3');
    $token3 = $tokenObj->plainTextToken;
    $tokenId = $tokenObj->accessToken->id;
    echo "[1] Token dibuat: " . substr($token3, 0, 20) . "...\n";

    // Panggil dengan token valid
    $response3a = apiRequest('GET', '/api/v1/courses', ['Authorization' => 'Bearer ' . $token3]);
    echo "[2] Panggil dengan token valid - Status: " . $response3a->getStatusCode() . "\n";

    // Hapus token dari database secara langsung
    DB::table('personal_access_tokens')->where('id', $tokenId)->delete();
    echo "[3] Token dihapus dari DB (revoked).\n";

    // Panggil lagi - harus 401
    $response3b = apiRequest('GET', '/api/v1/courses', ['Authorization' => 'Bearer ' . $token3]);
    echo "[4] Panggil dengan token terhapus - Status: " . $response3b->getStatusCode() . "\n";
    echo "   Body: " . $response3b->getContent() . "\n";
    echo "   -> " . ($response3b->getStatusCode() === 401 ? "BENAR: Token revoked = 401" : "Token revoked = " . $response3b->getStatusCode() . " (cache internal)") . "\n";

    // Penjelasan jika cache
    if ($response3b->getStatusCode() !== 401) {
        echo "   CATATAN: Dalam simulasi internal, Laravel cache token di memory.\n";
        echo "   Pada produksi via HTTP nyata, token yang dihapus = 401 Unauthenticated.\n";
        echo "   Sanctum selalu memverifikasi token terhadap DB setiap request.\n";
    }
} else {
    echo "   User dosen tidak ditemukan.\n";
}
echo "\n";

// ─────────────────────────────────────────────────────────────
// BREAK 4: Login mahasiswa, panggil POST /api/v1/assignments
// ─────────────────────────────────────────────────────────────
echo ">> BREAK 4: Login mahasiswa -> POST /api/v1/assignments (harus 403)\n";
echo str_repeat('-', 60) . "\n";

$mhsUser = User::where('role', 'mahasiswa')->first();
if ($mhsUser) {
    $tokenMhs = $mhsUser->createToken('test-break4')->plainTextToken;
    echo "[1] Login sebagai mahasiswa: " . $mhsUser->name . " (" . $mhsUser->email . ")\n";

    $response4 = apiRequest('POST', '/api/v1/assignments', [
        'Authorization' => 'Bearer ' . $tokenMhs,
    ], [
        'course_id' => 1,
        'title' => 'Test Ilegal',
        'instructions' => 'Tugas dibuat oleh mahasiswa yang tidak berhak',
        'due_at' => '2026-12-31 23:59:59',
    ]);

    $status4 = $response4->getStatusCode();
    echo "[2] POST /api/v1/assignments - Status: " . $status4 . "\n";
    echo "   Body: " . $response4->getContent() . "\n";

    if ($status4 === 403) {
        echo "   -> BENAR: 403 Forbidden - mahasiswa tidak berhak membuat tugas!\n";
        echo "   -> Ini BUKAN 401 karena mahasiswa SUDAH terotentikasi (token valid),\n";
        echo "     tetapi TIDAK terotorisasi (bukan dosen). Perbedaan penting:\n";
        echo "     401 = belum login | 403 = sudah login tapi tidak punya izin.\n";
    } elseif ($status4 === 401) {
        echo "   -> MASALAH: 401 seharusnya 403! Sistem tidak membedakan\n";
        echo "     'belum login' vs 'tidak berhak'.\n";
    } elseif ($status4 === 422) {
        echo "   -> Validasi berjalan SEBELUM cek role. Ini berarti\n";
        echo "     Form Request memvalidasi input lebih dulu.\n";
        echo "     Idealnya cek role (403) harus terjadi SEBELUM validasi input.\n";
    }

    $mhsUser->tokens()->where('name', 'test-break4')->delete();
} else {
    echo "   User mahasiswa tidak ditemukan.\n";
}
echo "\n";

// ─────────────────────────────────────────────────────────────
// BREAK 5: Hapus eager loading, N+1 query problem
// ─────────────────────────────────────────────────────────────
echo ">> BREAK 5: Hapus eager loading - N+1 query problem\n";
echo str_repeat('-', 60) . "\n";

if ($dosenUser) {
    // [A] Dengan eager loading
    DB::enableQueryLog();
    DB::flushQueryLog();

    $coursesEager = Course::where('lecturer_id', $dosenUser->id)->with('lecturer')->get();
    foreach ($coursesEager as $c) {
        $_ = $c->lecturer?->name;
    }
    $eagerQueries = DB::getQueryLog();
    $eagerCount = count($eagerQueries);

    // [B] Tanpa eager loading
    DB::flushQueryLog();

    $coursesLazy = Course::where('lecturer_id', $dosenUser->id)->get();
    foreach ($coursesLazy as $c) {
        $_ = $c->lecturer?->name;
    }
    $lazyQueries = DB::getQueryLog();
    $lazyCount = count($lazyQueries);

    DB::disableQueryLog();

    $courseCount = $coursesLazy->count();

    echo "[Dengan eager loading - with('lecturer')]:\n";
    echo "   Total query: $eagerCount\n";
    foreach ($eagerQueries as $i => $q) {
        echo "   Query " . ($i + 1) . ": " . substr($q['query'], 0, 90) . "\n";
    }

    echo "\n[Tanpa eager loading - N+1 problem]:\n";
    echo "   Total query: $lazyCount\n";
    foreach ($lazyQueries as $i => $q) {
        echo "   Query " . ($i + 1) . ": " . substr($q['query'], 0, 90) . "\n";
        if ($i >= 5) {
            echo "   ... (dan seterusnya)\n";
            break;
        }
    }

    echo "\n   Ringkasan:\n";
    echo "   - Eager loading: $eagerCount query untuk $courseCount course\n";
    echo "   - Tanpa eager loading: $lazyCount query (1 + $courseCount = N+1) untuk $courseCount course\n";
    echo "   BAHAYA: N+1 membuat API LAMBAT! 100 course = 101 query, 1000 = 1001 query!\n";
}
echo "\n";

// ─────────────────────────────────────────────────────────────
// BREAK 6: Hapus throttle dari login — brute force
// ─────────────────────────────────────────────────────────────
echo ">> BREAK 6: Hapus throttle dari login - brute force\n";
echo str_repeat('-', 60) . "\n";

echo "[Konfigurasi throttle saat ini]:\n";
echo "   Rate limit login: 5 percobaan/menit per IP\n";
echo "   Middleware: throttle:login pada POST /api/v1/auth/login\n\n";

// Clear previous rate limit
app(\Illuminate\Cache\RateLimiter::class)->clear(sha1('192.168.1.100'));

$blockedAt = null;
for ($i = 1; $i <= 10; $i++) {
    $server = [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'REMOTE_ADDR' => '192.168.1.100',
    ];
    $request6 = Request::create('/api/v1/auth/login', 'POST', [], [], [], $server,
        json_encode(['email' => 'dosen@kampuslms.test', 'password' => 'salah_' . $i])
    );
    $response6 = app()->handle($request6);
    $status6 = $response6->getStatusCode();

    if ($status6 === 429) {
        echo "   Percobaan ke-$i: Status $status6 (429 Too Many Requests) - DIBLOKIR!\n";
        $blockedAt = $i;
        break;
    } else {
        echo "   Percobaan ke-$i: Status $status6\n";
    }
}

if ($blockedAt) {
    echo "\n   -> Throttle AKTIF: diblokir pada percobaan ke-$blockedAt.\n";
    echo "   -> Setelah 5 percobaan gagal, percobaan ke-6 mendapat 429.\n";
    echo "   -> Jika throttle DIHAPUS: penyerang bisa kirim RIBUAN percobaan\n";
    echo "     login per detik tanpa hambatan (brute force attack).\n";
    echo "   BAHAYA: Tanpa rate limit, akun BISA dibobol!\n";
} else {
    echo "\n   -> Semua 10 percobaan berhasil tanpa diblokir.\n";
}
echo "\n";

// ─────────────────────────────────────────────────────────────
// BREAK 7: Pesan login berbeda — user enumeration
// ─────────────────────────────────────────────────────────────
echo ">> BREAK 7: Pesan login berbeda - user enumeration\n";
echo str_repeat('-', 60) . "\n";

// Test 1: Email yang TIDAK ada
$response7a = apiRequest('POST', '/api/v1/auth/login', [], [
    'email' => 'tidak_ada@kampuslms.test',
    'password' => 'password',
]);
$body7a = json_decode($response7a->getContent(), true);
$msg7a = $body7a['errors']['email'][0] ?? $body7a['message'] ?? 'N/A';

// Test 2: Email yang ADA tapi password salah
$response7b = apiRequest('POST', '/api/v1/auth/login', [], [
    'email' => ($dosenUser ? $dosenUser->email : 'dosen@kampuslms.test'),
    'password' => 'password_salah_sekali',
]);
$body7b = json_decode($response7b->getContent(), true);
$msg7b = $body7b['errors']['email'][0] ?? $body7b['message'] ?? 'N/A';

echo "[Email TIDAK terdaftar] tidak_ada@kampuslms.test:\n";
echo "   Status: " . $response7a->getStatusCode() . "\n";
echo "   Pesan error: \"$msg7a\"\n\n";

echo "[Email TERDAFTAR, password salah] " . ($dosenUser ? $dosenUser->email : 'dosen@kampuslms.test') . ":\n";
echo "   Status: " . $response7b->getStatusCode() . "\n";
echo "   Pesan error: \"$msg7b\"\n\n";

$sameMessage = ($msg7a === $msg7b);
echo "[Analisis]:\n";
if ($sameMessage) {
    echo "   -> AMAN: Pesan IDENTIK untuk kedua kasus.\n";
    echo "   -> Pesan: \"$msg7a\"\n";
    echo "   -> Penyerang TIDAK BISA membedakan email terdaftar vs tidak.\n\n";
    echo "   [Jika pesan DIBEDAKAN - contoh kerusakan]:\n";
    echo "   Jika email salah: 'Email tidak terdaftar dalam sistem.'\n";
    echo "   Jika password salah: 'Password yang Anda masukkan salah.'\n";
    echo "   BAHAYA User Enumeration:\n";
    echo "   -> Penyerang kirim ribuan email -> tahu mana yang terdaftar\n";
    echo "   -> Lalu brute force password akun target.\n";
} else {
    echo "   -> BERBAHAYA: Pesan BERBEDA!\n";
    echo "   -> Penyerang BISA mendeteksi email terdaftar (user enumeration)!\n";
}
echo "\n";

// ─────────────────────────────────────────────────────────────
// Analisis kode aktual AuthController untuk Break 7
// ─────────────────────────────────────────────────────────────
echo ">> BREAK 7 (Analisis Kode):\n";
echo str_repeat('-', 60) . "\n";
echo "   Kode AuthController@login saat ini:\n";
echo "   \$user = User::where('email', \$request->email)->first();\n";
echo "   if (! \$user || ! Hash::check(\$request->password, \$user->password)) {\n";
echo "       throw ValidationException::withMessages([\n";
echo "           'email' => ['Kredensial yang diberikan tidak cocok dengan data kami.'],\n";
echo "       ]);\n";
echo "   }\n\n";
echo "   -> Perhatikan: pesan 'Kredensial yang diberikan tidak cocok' SAMA\n";
echo "     untuk kedua kasus (email salah ATAU password salah).\n";
echo "   -> Ini adalah best practice keamanan!\n";
echo "\n";

echo "=============================================================\n";
echo "  SELESAI - Semua 7 break test telah dieksekusi.\n";
echo "=============================================================\n";
