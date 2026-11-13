<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="app-sidebar" id="app-sidebar">
    <div class="sidebar-header">
        <a href="dashboard.php" class="brand-link">
            <img src="../logo/LOGO.png" alt="Logo" class="brand-img">
            <div class="brand-text">
                <span class="brand-title">RPL Coffee</span>
                <span class="brand-subtitle">Point of Sale</span>
            </div>
        </a>
        <button type="button" class="sidebar-toggle" id="sidebar-toggle" aria-label="Alihkan sidebar">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </button>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Navigasi Utama</div>
        <ul class="nav-list">
            <li>
                <a href="dashboard.php" class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                    <svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                        <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                        <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                        <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                    </svg>
                    <span class="nav-label">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="kasir.php" class="nav-link <?= $currentPage === 'kasir.php' ? 'active' : ''; ?>">
                    <svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <span class="nav-label">Halaman Kasir</span>
                </a>
            </li>
            <li>
                <a href="menu.php" class="nav-link <?= $currentPage === 'menu.php' ? 'active' : ''; ?>">
                    <svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                    <span class="nav-label">Kelola Menu</span>
                </a>
            </li>
            <li>
                <a href="riwayat.php" class="nav-link <?= $currentPage === 'riwayat.php' ? 'active' : ''; ?>">
                    <svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span class="nav-label">Riwayat Transaksi</span>
                </a>
            </li>
        </ul>

        <div class="nav-section-title mt-4">Akun</div>
        <ul class="nav-list">
            <li>
                <button type="button" class="nav-link text-danger border-0 bg-transparent w-100 text-start" data-bs-toggle="modal" data-bs-target="#modalLogout">
                    <svg class="nav-icon text-danger" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span class="nav-label">Keluar</span>
                </button>
            </li>
        </ul>
    </nav>
</aside>

<div class="modal fade" id="modalLogout" tabindex="-1" aria-labelledby="modalLogoutLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold text-dark" id="modalLogoutLabel">Konfirmasi Keluar</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body py-3">
                <p class="text-secondary small mb-0">Apakah Anda yakin ingin mengakhiri sesi dan keluar?</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
                <a href="../logout.php" class="btn btn-sm btn-dark">Ya, Keluar</a>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const sidebar = document.getElementById('app-sidebar');
        const toggle = document.getElementById('sidebar-toggle');
        if (!sidebar || !toggle) return;

        const storageKey = 'app-sidebar-collapsed';
        const isCollapsed = localStorage.getItem(storageKey) === 'true';

        if (isCollapsed) {
            sidebar.classList.add('is-collapsed');
            document.body.classList.add('sidebar-is-collapsed');
        }

        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('is-collapsed');
            document.body.classList.toggle('sidebar-is-collapsed');
            const state = sidebar.classList.contains('is-collapsed');
            localStorage.setItem(storageKey, String(state));
        });
    })();
</script>
