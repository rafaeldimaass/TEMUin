const togglePassword = document.getElementById('togglePassword');
const passwordInput  = document.getElementById('password');
const eyeIcon        = document.getElementById('eyeIcon');

if (togglePassword) {
    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        if (type === 'text') {
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    });
}

// Toggle sidebar: mengecil di desktop, menjadi off-canvas di mobile.
(() => {
    const toggle = document.getElementById('sidebarToggle');
    const backdrop = document.getElementById('sidebarBackdrop');
    if (!toggle) return;

    const body = document.body;
    const mobileQuery = window.matchMedia('(max-width: 768px)');

    // Preferensi desktop disimpan agar kondisi sidebar tetap sama saat pindah halaman.
    let desktopCollapsed = false;
    try {
        desktopCollapsed = localStorage.getItem('temuin-sidebar-collapsed') === 'true';
    } catch (error) {}

    const syncState = () => {
        const isMobile = mobileQuery.matches;
        const collapsed = isMobile ? body.classList.contains('sidebar-collapsed') : desktopCollapsed;
        toggle.setAttribute('aria-expanded', String(!collapsed));
        toggle.setAttribute('aria-label', collapsed ? 'Buka sidebar' : 'Tutup sidebar');
        toggle.title = collapsed ? 'Buka sidebar' : 'Tutup sidebar';
        const icon = toggle.querySelector('i');
        if (icon) {
            icon.classList.toggle('fa-bars', collapsed);
            icon.classList.toggle('fa-times', !collapsed);
        }
    };

    if (mobileQuery.matches) body.classList.add('sidebar-collapsed');
    else body.classList.toggle('sidebar-collapsed', desktopCollapsed);
    syncState();

    toggle.addEventListener('click', () => {
        if (mobileQuery.matches) {
            body.classList.toggle('sidebar-collapsed');
        } else {
            desktopCollapsed = !desktopCollapsed;
            body.classList.toggle('sidebar-collapsed', desktopCollapsed);
            try {
                localStorage.setItem('temuin-sidebar-collapsed', String(desktopCollapsed));
            } catch (error) {}
        }
        syncState();
    });

    if (backdrop) {
        backdrop.addEventListener('click', () => {
            if (mobileQuery.matches) {
                body.classList.add('sidebar-collapsed');
                syncState();
            }
        });
    }

    mobileQuery.addEventListener('change', (event) => {
        body.classList.toggle('sidebar-collapsed', event.matches || desktopCollapsed);
        syncState();
    });
})();
