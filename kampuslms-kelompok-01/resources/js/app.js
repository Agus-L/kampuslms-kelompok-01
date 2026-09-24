import './bootstrap';

// ── Dark / Light Mode Switcher ──
function setupThemeToggle() {
    const themeToggleBtn = document.getElementById('theme-toggle');
    const themeLabel = document.getElementById('theme-label');
    if (!themeToggleBtn) return;

    function getActiveTheme() {
        return document.documentElement.getAttribute('data-theme') || 'dark';
    }

    function syncToggleUI(theme) {
        const isLight = theme === 'light';
        themeToggleBtn.setAttribute('aria-checked', isLight ? 'true' : 'false');
        themeToggleBtn.setAttribute(
            'title',
            isLight ? 'Beralih ke Mode Gelap' : 'Beralih ke Mode Terang'
        );
        if (themeLabel) {
            themeLabel.textContent = isLight ? 'Mode Terang' : 'Mode Gelap';
        }
        if (isLight) {
            themeToggleBtn.classList.add('is-light');
        } else {
            themeToggleBtn.classList.remove('is-light');
        }
    }

    function toggleTheme() {
        const currentTheme = getActiveTheme();
        const targetTheme = currentTheme === 'dark' ? 'light' : 'dark';

        document.documentElement.setAttribute('data-theme', targetTheme);
        localStorage.setItem('theme', targetTheme);
        syncToggleUI(targetTheme);
    }

    // Sinkronisasi awal tampilan tombol & label dengan tema yang aktif
    syncToggleUI(getActiveTheme());

    themeToggleBtn.addEventListener('click', toggleTheme);
    if (themeLabel) {
        themeLabel.addEventListener('click', toggleTheme);
    }
}

// ── Navigasi Cepat Abjad ──
function setupUserFilter() {
    const alphabetNavItems = document.querySelectorAll('.alphabet-nav-item');

    // Handler klik pada item scroll bar alfabet (Lompat cepat ke bagian huruf)
    alphabetNavItems.forEach(item => {
        item.addEventListener('click', (e) => {
            const letter = item.getAttribute('data-nav-letter');
            const targetPage = item.getAttribute('data-target-page');
            const currentPage = item.getAttribute('data-current-page');
            const targetHeader = document.querySelector(`.alphabet-header-row[data-letter="${letter}"]`);

            // Jika huruf berada di halaman saat ini: scroll langsung tanpa reload
            if (targetHeader && targetHeader.style.display !== 'none' && targetPage === currentPage) {
                e.preventDefault();
                targetHeader.scrollIntoView({ behavior: 'smooth', block: 'start' });

                // Highlight sekejap pada badge huruf yang dituju
                const badge = targetHeader.querySelector('.alphabet-badge');
                if (badge) {
                    badge.classList.add('badge-highlight');
                    setTimeout(() => badge.classList.remove('badge-highlight'), 1200);
                }
            }
            // Jika huruf berada di halaman lain, browser akan membuka halaman tersebut dan auto-scroll via hash
        });
    });

    // Otomatis scroll ke huruf jika URL memuat anchor #letter-X
    if (window.location.hash && window.location.hash.startsWith('#letter-')) {
        const hashLetter = window.location.hash.replace('#letter-', '');
        const targetHeader = document.querySelector(`.alphabet-header-row[data-letter="${hashLetter}"]`);
        if (targetHeader) {
            setTimeout(() => {
                targetHeader.scrollIntoView({ behavior: 'smooth', block: 'start' });
                const badge = targetHeader.querySelector('.alphabet-badge');
                if (badge) {
                    badge.classList.add('badge-highlight');
                    setTimeout(() => badge.classList.remove('badge-highlight'), 1200);
                }
            }, 200);
        }
    }
}

// ── Delete Confirmation Modal ──
function setupDeleteModals() {
    // Buka modal saat tombol trigger diklik
    document.querySelectorAll('[data-modal-target]').forEach(trigger => {
        trigger.addEventListener('click', () => {
            const modalId = trigger.getAttribute('data-modal-target');
            const modal = document.getElementById(modalId);
            if (modal) {
                // Pindahkan modal ke body agar tidak terjebak di dalam
                // parent yang punya overflow:hidden atau backdrop-filter
                // (yang membuat position:fixed jadi relatif terhadap parent)
                document.body.appendChild(modal);
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                // Focus pada tombol Batal agar keyboard-accessible
                const cancelBtn = modal.querySelector('[data-modal-close]');
                if (cancelBtn) cancelBtn.focus();
            }
        });
    });

    // Tutup modal saat tombol Batal diklik
    document.querySelectorAll('[data-modal-close]').forEach(closeBtn => {
        closeBtn.addEventListener('click', () => {
            const modalId = closeBtn.getAttribute('data-modal-close');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
            }
        });
    });

    // Tutup modal saat klik area gelap overlay (di luar kartu modal)
    document.querySelectorAll('.delete-modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('is-open');
                overlay.setAttribute('aria-hidden', 'true');
            }
        });
    });

    // Tutup modal saat tekan tombol Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.delete-modal-overlay.is-open');
            if (openModal) {
                openModal.classList.remove('is-open');
                openModal.setAttribute('aria-hidden', 'true');
            }
        }
    });
}

function initApp() {
    setupThemeToggle();
    setupUserFilter();
    setupDeleteModals();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}
