<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder terpisah untuk akun demo.
 *
 * Digunakan di produksi untuk mengganti password akun demo:
 *   php artisan db:seed --class=DemoAccountSeeder --force
 *
 * Idempoten: menggunakan updateOrCreate, aman dijalankan ulang.
 */
class DemoAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Password default: 'password'
        // Di produksi, ganti password via environment variable
        // atau ubah langsung di sini sebelum deploy.
        $password = env('DEMO_PASSWORD', 'password');

        User::updateOrCreate(
            ['email' => 'admin@kampuslms.test'],
            [
                'name'              => 'Admin KampusLMS',
                'password'          => Hash::make($password),
                'role'              => 'admin',
                'nim_nip'           => 'ADM000001',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'dosen@kampuslms.test'],
            [
                'name'              => 'Dr. Budi Santoso',
                'password'          => Hash::make($password),
                'role'              => 'dosen',
                'nim_nip'           => '1980010001',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'mahasiswa@kampuslms.test'],
            [
                'name'              => 'Andi Pratama',
                'password'          => Hash::make($password),
                'role'              => 'mahasiswa',
                'nim_nip'           => '2401010001',
                'email_verified_at' => now(),
            ]
        );
    }
}
