<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Idempoten: aman dijalankan ulang setelah migrate:fresh.
     */
    public function run(): void
    {
        $this->call([
            KampusLMSSeeder::class,
        ]);
    }
}
