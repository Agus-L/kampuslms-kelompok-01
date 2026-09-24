{{--
    Delete Confirmation Modal Component
    ────────────────────────────────────

    Komponen modal konfirmasi penghapusan yang reusable.
    Menggantikan dialog confirm() bawaan browser yang polos
    dengan modal custom yang sesuai design system Kampus LMS.

    Props:
      - id       : ID unik modal (wajib, agar bisa ada banyak modal di satu halaman)
      - action   : URL tujuan form DELETE (contoh: route('courses.destroy', $course))
      - itemName : Nama item yang akan dihapus (ditampilkan di pesan konfirmasi)

    Penggunaan:
      <button type="button" class="btn btn-delete" data-modal-target="delete-modal-{{ $course->id }}">
          🗑️ Hapus
      </button>
      <x-delete-modal
          id="delete-modal-{{ $course->id }}"
          :action="route('courses.destroy', $course)"
          :itemName="$course->name"
      />

    KEAMANAN:
      - Form menggunakan @csrf + @method('DELETE') — sesuai konvensi RESTful Laravel
      - Token CSRF melindungi dari serangan Cross-Site Request Forgery
      - @method('DELETE') mengirim field _method=DELETE agar Laravel me-route ke destroy()
--}}

@props(['id', 'action', 'itemName'])

<div id="{{ $id }}" class="delete-modal-overlay" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">
    <div class="delete-modal-card">
        {{-- Icon Warning --}}
        <div class="delete-modal-icon">
            <span class="delete-modal-icon-inner">⚠️</span>
        </div>

        {{-- Judul --}}
        <h3 id="{{ $id }}-title" class="delete-modal-title">Konfirmasi Penghapusan</h3>

        {{-- Pesan --}}
        <p class="delete-modal-message">
            Apakah Anda yakin ingin menghapus
            <strong class="delete-modal-item-name">{{ $itemName }}</strong>?
        </p>

        {{-- Peringatan --}}
        <p class="delete-modal-warning">
            ⛔ Tindakan ini tidak dapat dibatalkan.
        </p>

        {{-- Tombol Aksi --}}
        <div class="delete-modal-actions">
            <button type="button" class="btn btn-cancel" data-modal-close="{{ $id }}">
                ✕ Batal
            </button>
            <form action="{{ $action }}" method="POST" class="delete-modal-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-confirm-delete">
                    🗑️ Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>
