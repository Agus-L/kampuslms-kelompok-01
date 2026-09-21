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

// ── Filter Pengguna Berdasarkan Peran & Scroll Bar Alfabet ──
function setupUserFilter() {
    const filterButtons = document.querySelectorAll('.user-filter-bar .filter-btn');
    const userRows = document.querySelectorAll('.data-table .user-row');
    const alphabetHeaders = document.querySelectorAll('.data-table .alphabet-header-row');
    const alphabetNavItems = document.querySelectorAll('.alphabet-nav-item');
    const emptyRow = document.getElementById('empty-filter-row');

    if (!userRows.length) return;

    // Handler klik pada item scroll bar alfabet (Lompat cepat ke bagian huruf)
    alphabetNavItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const letter = item.getAttribute('data-nav-letter');
            const targetHeader = document.querySelector(`.alphabet-header-row[data-letter="${letter}"]`);

            if (targetHeader && targetHeader.style.display !== 'none') {
                targetHeader.scrollIntoView({ behavior: 'smooth', block: 'start' });

                // Highlight sekejap pada badge huruf yang dituju
                const badge = targetHeader.querySelector('.alphabet-badge');
                if (badge) {
                    badge.classList.add('badge-highlight');
                    setTimeout(() => badge.classList.remove('badge-highlight'), 1200);
                }
            }
        });
    });

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            const selectedRole = button.getAttribute('data-filter');

            // Update status active pada tombol filter
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            let visibleIndex = 0;
            const visibleLetters = new Set();

            userRows.forEach(row => {
                const rowRole = row.getAttribute('data-role');
                const rowLetter = row.getAttribute('data-letter');
                const isMatch = (selectedRole === 'all' || rowRole === selectedRole);

                if (isMatch) {
                    row.style.display = '';
                    visibleIndex++;
                    if (rowLetter) {
                        visibleLetters.add(rowLetter);
                    }
                    const numberCell = row.querySelector('.row-number');
                    if (numberCell) {
                        numberCell.textContent = visibleIndex;
                    }
                } else {
                    row.style.display = 'none';
                }
            });

            // Tampilkan hanya header abjad yang memiliki data pengguna aktif
            alphabetHeaders.forEach(header => {
                const letter = header.getAttribute('data-letter');
                header.style.display = visibleLetters.has(letter) ? '' : 'none';
            });

            // Update status aktif/disabled pada scroll bar alfabet di sebelah kanan
            alphabetNavItems.forEach(item => {
                const letter = item.getAttribute('data-nav-letter');
                const hasData = visibleLetters.has(letter);
                if (hasData) {
                    item.classList.add('has-data');
                    item.classList.remove('disabled');
                    item.removeAttribute('disabled');
                    item.setAttribute('title', 'Lompat ke huruf ' + letter);
                } else {
                    item.classList.remove('has-data');
                    item.classList.add('disabled');
                    item.setAttribute('disabled', 'true');
                    item.setAttribute('title', 'Tidak ada data berawalan ' + letter);
                }
            });

            if (emptyRow) {
                emptyRow.style.display = visibleIndex === 0 ? '' : 'none';
            }
        });
    });
}

function initApp() {
    setupThemeToggle();
    setupUserFilter();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}
