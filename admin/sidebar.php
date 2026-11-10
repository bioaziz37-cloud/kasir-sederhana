<div class="sidebar" id="admin-sidebar">
    <div class="sidebar-top">
        <div class="brand-title">
            <img class="badge-logo" src="../logo/LOGO.png" alt="Logo RPL Coffee &amp; Eatery">
            <span class="brand-label">RPL COFFEE &amp; EATERY</span>
        </div>
        <button class="sidebar-toggle" id="sidebar-toggle" type="button"
            aria-label="Ciutkan sidebar" aria-expanded="true" aria-controls="admin-sidebar">
            <svg class="toggle-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M14.5 5 7.5 12l7 7"></path>
                <path d="M20 5v14"></path>
            </svg>
        </button>
    </div>
    <div class="sidebar-section-label"><span>MENU UTAMA</span></div>
    <div class="list-group list-group-flush">
        <a href="dashboard.php" title="Dashboard" aria-label="Dashboard" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="3.5" width="7" height="7" rx="1.5"></rect><rect x="13.5" y="3.5" width="7" height="5" rx="1.5"></rect><rect x="13.5" y="11.5" width="7" height="9" rx="1.5"></rect><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"></rect></svg>
            <span class="nav-label">Dashboard</span>
        </a>
        <a href="kasir.php" title="Halaman Kasir" aria-label="Halaman Kasir" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) == 'kasir.php' ? 'active' : ''; ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 10h16v10H4z"></path><path d="M6 10l1.5-5h9L18 10M8 14h2m4 0h2m-8 3h8"></path></svg>
            <span class="nav-label">Halaman Kasir</span>
        </a>
        <a href="menu.php" title="Kelola Menu" aria-label="Kelola Menu" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) == 'menu.php' ? 'active' : ''; ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 6h14M5 12h14M5 18h14"></path><circle cx="3" cy="6" r=".5"></circle><circle cx="3" cy="12" r=".5"></circle><circle cx="3" cy="18" r=".5"></circle></svg>
            <span class="nav-label">Kelola Menu</span>
        </a>
        <a href="riwayat.php" title="Riwayat Transaksi" aria-label="Riwayat Transaksi" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) == 'riwayat.php' ? 'active' : ''; ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"></circle><path d="M12 7v5l3.5 2"></path><path d="M4 4v4h4"></path></svg>
            <span class="nav-label">Riwayat Transaksi</span>
        </a>
        <a href="#" title="Logout" aria-label="Logout" class="list-group-item list-group-item-action text-danger mt-4" data-bs-toggle="modal" data-bs-target="#modalLogout">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M10 5H5v14h5m5-4 4-3-4-3m4 3H9"></path></svg>
            <span class="nav-label">Logout</span>
        </a>
    </div>
</div>

<script>
    (function () {
        const sidebar = document.querySelector('.sidebar');
        const toggle = document.getElementById('sidebar-toggle');

        if (!sidebar || !toggle) {
            return;
        }

        const storageKey = 'rpl-sidebar-collapsed';
        let isCollapsed = false;

        try {
            isCollapsed = localStorage.getItem(storageKey) === 'true';
        } catch (error) {
            isCollapsed = false;
        }

        function setCollapsed(collapsed) {
            sidebar.classList.toggle('is-collapsed', collapsed);
            document.body.classList.toggle('sidebar-collapsed', collapsed);
            toggle.setAttribute('aria-expanded', String(!collapsed));
            toggle.setAttribute('aria-label', collapsed ? 'Perlebar sidebar' : 'Ciutkan sidebar');
            toggle.title = collapsed ? 'Perlebar sidebar' : 'Ciutkan sidebar';
        }

        setCollapsed(isCollapsed);

        toggle.addEventListener('click', function () {
            isCollapsed = !sidebar.classList.contains('is-collapsed');
            setCollapsed(isCollapsed);

            try {
                localStorage.setItem(storageKey, String(isCollapsed));
            } catch (error) {
                // Sidebar tetap dapat digunakan jika penyimpanan browser tidak tersedia.
            }
        });
    })();
</script>

<div class="modal fade" id="modalLogout" tabindex="-1" aria-labelledby="modalLogoutLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark fs-6" id="modalLogoutLabel">Konfirmasi Keluar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-2">
                <p class="mb-0 text-secondary small">Apakah Anda yakin ingin keluar dari sistem aplikasi kasir?</p>
            </div>
            <div class="modal-footer border-0 pb-4 px-4">
                <button type="button" class="btn btn-light btn-sm px-3 text-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="../logout.php" class="btn btn-danger btn-sm px-3">Keluar</a>
            </div>
        </div>
    </div>
</div>
