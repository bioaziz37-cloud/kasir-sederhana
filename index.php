<?php
require_once __DIR__ . '/koneksi.php';

if (isset($_SESSION['status_login']) && $_SESSION['status_login'] === true) {
    header("Location: admin/dashboard.php");
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (!verify_csrf()) {
        $error = "Sesi kedaluwarsa atau token tidak valid. Silakan muat ulang halaman.";
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $error = "Username dan password wajib diisi.";
        } else {
            $stmt = mysqli_prepare($koneksi, "SELECT id, username, password, nama_lengkap FROM users WHERE username = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if ($user = mysqli_fetch_assoc($result)) {
                    if (password_verify($password, $user['password'])) {
                        session_regenerate_id(true);
                        $_SESSION['status_login'] = true;
                        $_SESSION['user_id'] = (int) $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['nama_lengkap'] = $user['nama_lengkap'] ?: 'Administrator';

                        header("Location: admin/dashboard.php");
                        exit;
                    } else {
                        $error = "Password yang Anda masukkan salah.";
                    }
                } else {
                    $error = "Akun dengan username tersebut tidak ditemukan.";
                }
                mysqli_stmt_close($stmt);
            } else {
                $error = "Terjadi gangguan sistem saat memverifikasi akun.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - RPL Coffee &amp; Eatery</title>
    <link rel="icon" type="image/png" href="logo/LOGO.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-page: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --border-focus: #0f172a;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --accent: #1e293b;
            --accent-hover: #0f172a;
            --danger-bg: #fef2f2;
            --danger-text: #991b1b;
            --danger-border: #fecaca;
            --success-bg: #f0fdf4;
            --success-text: #166534;
            --success-border: #bbf7d0;
            --font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bg-page);
            color: var(--text-main);
            font-family: var(--font-family);
            -webkit-font-smoothing: antialiased;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            padding: 36px 32px;
        }

        .brand-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .brand-logo {
            width: 40px;
            height: 40px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid var(--border);
            padding: 4px;
            background: #ffffff;
        }

        .brand-meta h1 {
            font-size: 0.95rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-main);
            letter-spacing: -0.01em;
        }

        .brand-meta p {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin: 2px 0 0 0;
        }

        .divider {
            height: 1px;
            background: var(--border);
            margin: 20px 0 24px 0;
        }

        .form-heading {
            margin-bottom: 20px;
        }

        .form-heading h2 {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0 0 4px 0;
            letter-spacing: -0.02em;
        }

        .form-heading p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 0;
        }

        .form-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .form-control {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.9rem;
            color: var(--text-main);
            background-color: #ffffff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-control:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08);
            outline: none;
        }

        .password-wrap {
            position: relative;
        }

        .password-wrap .form-control {
            padding-right: 76px;
        }

        .btn-toggle-pass {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 8px;
            cursor: pointer;
            border-radius: 4px;
        }

        .btn-toggle-pass:hover {
            color: var(--text-main);
            background: #f1f5f9;
        }

        .btn-submit {
            width: 100%;
            background: var(--accent);
            color: #ffffff;
            border: 1px solid var(--accent);
            border-radius: 8px;
            padding: 11px 16px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease;
            margin-top: 8px;
        }

        .btn-submit:hover {
            background: var(--accent-hover);
        }

        .alert-box {
            font-size: 0.82rem;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
        }

        .alert-box.danger {
            background-color: var(--danger-bg);
            color: var(--danger-text);
            border: 1px solid var(--danger-border);
        }

        .alert-box.success {
            background-color: var(--success-bg);
            color: var(--success-text);
            border: 1px solid var(--success-border);
        }

        .alert-box.warning {
            background-color: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .footer-note {
            margin-top: 24px;
            font-size: 0.75rem;
            color: var(--text-muted);
            text-align: center;
        }
    </style>
</head>
<body>

    <main class="login-card">
        <header class="brand-header">
            <img src="logo/LOGO.png" alt="Logo" class="brand-logo">
            <div class="brand-meta">
                <h1>RPL Coffee &amp; Eatery</h1>
                <p>Sistem Kasir &amp; Pengelolaan</p>
            </div>
        </header>

        <div class="divider"></div>

        <div class="form-heading">
            <h2>Masuk</h2>
            <p>Masukkan username dan password staf untuk melanjutkan.</p>
        </div>

        <?php if (!empty($error)) : ?>
            <div class="alert-box danger" role="alert">
                <?= e($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'logout') : ?>
            <div class="alert-box success" role="status">
                Anda telah berhasil keluar dari akun.
            </div>
        <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'belum_login') : ?>
            <div class="alert-box warning" role="alert">
                Silakan masuk terlebih dahulu untuk mengakses sistem.
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <?= csrf_field(); ?>

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username"
                       value="<?= isset($_POST['username']) ? e($_POST['username']) : ''; ?>"
                       required autocomplete="username" autofocus placeholder="Contoh: admin">
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="password-wrap">
                    <input type="password" class="form-control" id="password" name="password"
                           required autocomplete="current-password" placeholder="Masukkan password">
                    <button type="button" class="btn-toggle-pass" id="btn-toggle-pass" aria-label="Tampilkan atau sembunyikan password">Tampilkan</button>
                </div>
            </div>

            <button type="submit" name="login" class="btn-submit">Masuk</button>
        </form>

        <div class="footer-note">
            Akses terbatas hanya untuk staf operasional.
        </div>
    </main>

    <script>
        const passInput = document.getElementById('password');
        const toggleBtn = document.getElementById('btn-toggle-pass');

        if (toggleBtn && passInput) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passInput.type === 'password';
                passInput.type = isPassword ? 'text' : 'password';
                toggleBtn.textContent = isPassword ? 'Tutup' : 'Tampilkan';
            });
        }
    </script>
</body>
</html>
