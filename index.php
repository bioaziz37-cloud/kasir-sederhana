<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['status_login']) && $_SESSION['status_login'] == true) {
    header("Location: admin/dashboard.php");
    exit;
}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");

    if (mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);

        if (password_verify($password, $user['password']) || $password == $user['password']) {
            $_SESSION['status_login'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'] ?? 'Administrator';

            header("Location: admin/dashboard.php");
            exit;
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Username tidak ditemukan di sistem!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#35271f">
    <link rel="icon" type="image/png" href="logo/LOGO.png">
    <link rel="apple-touch-icon" href="logo/LOGO.png">
    <title>Masuk - RPL Coffee &amp; Eatery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
            --coffee: #30271f;
            --terracotta: #8a4f31;
            --terracotta-dark: #68391f;
            --paper: #f4f2ee;
            --ink: #302921;
            --muted: #81776d;
        }

        * {
            box-sizing: border-box;
        }

        body {
            display: grid;
            min-height: 100vh;
            margin: 0;
            padding: clamp(16px, 4vw, 48px);
            place-items: center;
            background:
                radial-gradient(ellipse at 12% 18%, rgba(205, 168, 126, 0.18), transparent 30%),
                var(--paper);
            color: var(--ink);
            font-family: Inter, "Segoe UI", sans-serif;
        }

        .login-shell {
            display: grid;
            width: min(1050px, 100%);
            min-height: 620px;
            grid-template-columns: 0.95fr 1.05fr;
            overflow: hidden;
            border: 1px solid rgba(75, 58, 44, 0.09);
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 28px 80px rgba(57, 43, 31, 0.15);
        }

        .login-story {
            position: relative;
            display: flex;
            min-height: 100%;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            padding: clamp(32px, 5vw, 58px);
            background:
                radial-gradient(ellipse at 85% 20%, rgba(195, 129, 78, 0.22), transparent 38%),
                linear-gradient(150deg, #423127, var(--coffee) 72%);
            color: #fffaf3;
        }

        .login-story::after {
            position: absolute;
            right: -138px;
            bottom: 92px;
            width: 360px;
            height: 360px;
            border: 1px solid rgba(231, 190, 143, 0.16);
            border-radius: 50%;
            box-shadow:
                0 0 0 24px rgba(231, 190, 143, 0.035),
                0 0 0 52px rgba(231, 190, 143, 0.03),
                0 0 0 82px rgba(231, 190, 143, 0.025);
            content: "";
            pointer-events: none;
        }

        .story-brand,
        .story-copy,
        .story-footer {
            position: relative;
            z-index: 1;
        }

        .story-brand {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .brand-mark {
            display: grid;
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            place-items: center;
            border-radius: 50%;
            object-fit: contain;
        }

        .brand-name {
            display: block;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.11em;
        }

        .brand-caption {
            display: block;
            margin-top: 3px;
            color: rgba(255, 250, 243, 0.58);
            font-size: 0.68rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .story-copy {
            max-width: 380px;
            margin: 70px 0 54px;
        }

        .story-kicker,
        .form-kicker {
            color: #d3a578;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .story-copy h1 {
            margin: 17px 0 18px;
            font-family: "DM Serif Display", Georgia, serif;
            font-size: clamp(2.5rem, 4.2vw, 3.8rem);
            font-weight: 400;
            letter-spacing: -0.035em;
            line-height: 1.08;
        }

        .story-copy p {
            max-width: 315px;
            margin: 0;
            color: rgba(255, 250, 243, 0.68);
            font-size: 0.92rem;
            line-height: 1.8;
        }

        .story-footer {
            display: flex;
            align-items: center;
            gap: 11px;
            color: rgba(255, 250, 243, 0.58);
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .story-footer::before {
            width: 25px;
            height: 1px;
            background: #bd8658;
            content: "";
        }

        .login-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(32px, 6vw, 78px);
        }

        .login-content {
            width: min(100%, 365px);
        }

        .form-kicker {
            color: var(--terracotta);
        }

        .login-heading {
            margin: 12px 0 8px;
            color: var(--ink);
            font-size: clamp(1.9rem, 3vw, 2.3rem);
            font-weight: 600;
            letter-spacing: -0.055em;
        }

        .login-subheading {
            margin: 0 0 30px;
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.65;
        }

        .login-alert {
            margin-bottom: 22px;
            border: 0;
            border-radius: 10px;
            font-size: 0.82rem;
        }

        .login-alert.alert-danger {
            background: #f8ece7;
            color: #8d4936;
        }

        .login-alert.alert-success {
            background: #edf2e9;
            color: #536747;
        }

        .login-alert.alert-warning {
            background: #f8f1e3;
            color: #786038;
        }

        .login-label {
            margin-bottom: 8px;
            color: #4a4036;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .login-input {
            min-height: 49px;
            border: 1px solid #e5ddd3;
            border-radius: 10px;
            background: #fffdfa;
            color: var(--ink);
            font-size: 0.88rem;
        }

        .login-input::placeholder {
            color: #aaa197;
        }

        .login-input:focus {
            border-color: #c4936d;
            box-shadow: 0 0 0 3px rgba(164, 94, 55, 0.11);
        }

        .password-control {
            position: relative;
        }

        .password-control .login-input {
            padding-right: 88px;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 12px;
            padding: 5px 3px;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: var(--terracotta);
            font-size: 0.74rem;
            font-weight: 600;
        }

        .password-toggle:hover {
            color: var(--terracotta-dark);
        }

        .password-toggle:focus-visible {
            border-radius: 4px;
            outline: 2px solid #c4936d;
            outline-offset: 3px;
        }

        .login-submit {
            min-height: 49px;
            margin-top: 8px;
            border: 1px solid var(--terracotta);
            border-radius: 10px;
            background: var(--terracotta);
            box-shadow: 0 7px 16px rgba(138, 79, 49, 0.18);
            font-size: 0.88rem;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .login-submit:hover,
        .login-submit:focus-visible {
            border-color: var(--terracotta-dark);
            background: var(--terracotta-dark);
        }

        .login-note {
            margin: 24px 0 0;
            color: #9b938a;
            font-size: 0.74rem;
            line-height: 1.65;
            text-align: center;
        }

        @media (max-width: 760px) {
            body {
                padding: 16px;
            }

            .login-shell {
                width: min(480px, 100%);
                min-height: 0;
                grid-template-columns: 1fr;
                border-radius: 18px;
            }

            .login-story {
                min-height: 245px;
                padding: 26px 30px;
            }

            .login-story::after {
                right: -180px;
                bottom: -180px;
                width: 330px;
                height: 330px;
            }

            .brand-mark {
                width: 42px;
                height: 42px;
                flex-basis: 42px;
            }

            .story-copy {
                margin: 35px 0 0;
            }

            .story-copy h1 {
                max-width: 330px;
                margin: 11px 0 8px;
                font-size: 2rem;
            }

            .story-copy p,
            .story-footer {
                display: none;
            }

            .login-panel {
                padding: 38px 30px 34px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <section class="login-story" aria-label="RPL Coffee & Eatery">
            <div class="story-brand">
                <img class="brand-mark" src="logo/LOGO.png" alt="Logo RPL Coffee &amp; Eatery">
                <span>
                    <span class="brand-name">RPL COFFEE &amp; EATERY</span>
                    <span class="brand-caption">Kopi · Makanan · Cerita</span>
                </span>
            </div>

            <div class="story-copy">
                <span class="story-kicker">behind the cash register</span>
                <h1>Pesanan beres. Suasana tetap chill.</h1>
                <p>Transaksi, menu, dan pesanan harian ada di satu tempat.</p>
            </div>

            <div class="story-footer">Panel operasional</div>
        </section>

        <section class="login-panel" aria-labelledby="login-title">
            <div class="login-content">
                <span class="form-kicker">Selamat datang</span>
                <h2 class="login-heading" id="login-title">Masuk ke panel</h2>
                <p class="login-subheading">Gunakan akun Anda untuk melanjutkan ke ruang kerja.</p>

                <?php if (isset($error)) : ?>
                    <div class="alert alert-danger login-alert" role="alert">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'logout') : ?>
                    <div class="alert alert-success login-alert" role="status">
                        Anda berhasil keluar dari sistem.
                    </div>
                <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] == 'belum_login') : ?>
                    <div class="alert alert-warning login-alert" role="alert">
                        Silakan masuk terlebih dahulu untuk mengakses panel.
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label login-label">Username</label>
                        <input type="text" class="form-control login-input" id="username" name="username"
                            required autocomplete="username" placeholder="Masukkan username">
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label login-label">Password</label>
                        <div class="password-control">
                            <input type="password" class="form-control login-input" id="password" name="password"
                                required autocomplete="current-password" placeholder="Masukkan password">
                            <button class="password-toggle" type="button" id="password-toggle"
                                aria-controls="password" aria-label="Tampilkan password">Tampilkan</button>
                        </div>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary login-submit w-100">Masuk</button>
                </form>

                <p class="login-note">Akses ini hanya untuk staf RPL Coffee &amp; Eatery.</p>
            </div>
        </section>
    </main>

    <script>
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('password-toggle');

        passwordToggle.addEventListener('click', function () {
            const shouldShowPassword = passwordInput.type === 'password';
            passwordInput.type = shouldShowPassword ? 'text' : 'password';
            passwordToggle.textContent = shouldShowPassword ? 'Sembunyikan' : 'Tampilkan';
            passwordToggle.setAttribute(
                'aria-label',
                shouldShowPassword ? 'Sembunyikan password' : 'Tampilkan password'
            );
        });
    </script>
</body>
</html>
