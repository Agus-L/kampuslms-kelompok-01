<x-layout title="404 - Halaman Tidak Ditemukan">
    <div style="text-align: center; padding: 4rem 1rem; min-height: 50vh;">
        <h1 style="font-size: 5rem; font-weight: bold; color: #dc2626; margin: 0;">404</h1>
        <h2 style="font-size: 2rem; color: #1f2937; margin-top: 10px;">Halaman Tidak Ditemukan</h2>
        <p style="color: #4b5563; margin-bottom: 2rem;">
            Maaf, halaman yang kamu cari tidak ada atau alamat rutenya salah.
        </p>
        <a href="{{ route('courses.index') }}" 
           style="background-color: #4f46e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Kembali ke Daftar Mata Kuliah
        </a>
    </div>
</x-layout>