<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

// Halaman utama: pilih role
?><!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terpal University — Pilih Akses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="text-center mb-4">
        <h1>Terpal University</h1>
        <p class="text-muted">Selamat datang. Pilih akses Anda untuk melanjutkan.</p>
    </div>

    <?php if (isLoggedIn()): ?>
    <div class="alert alert-info text-center">
        Anda sedang login sebagai <strong><?= htmlspecialchars(sessionName()) ?></strong> dengan peran <strong><?= htmlspecialchars($_SESSION['role']) ?></strong>.
        <?php if (isMahasiswa()): ?>
            <br>Silakan lanjut ke <a href="<?= BASE_URL ?>/pages/mahasiswa/dashboard.php">dashboard mahasiswa</a>.
        <?php else: ?>
            <br>Silakan lanjut ke <a href="<?= BASE_URL ?>/pages/dashboard.php">dashboard admin</a>.
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="<?= BASE_URL ?>/pages/login.php" class="card shadow-sm text-decoration-none text-dark" style="width:18rem;">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-person-badge fs-1 mb-3"></i>
                        <h5 class="card-title">Login Admin</h5>
                        <p class="card-text text-muted small">Masuk ke dashboard admin untuk mengelola mahasiswa, prodi, dan laporan.</p>
                    </div>
                </a>

                <a href="<?= BASE_URL ?>/pages/login_mahasiswa.php" class="card shadow-sm text-decoration-none text-dark" style="width:18rem;">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-mortarboard fs-1 mb-3"></i>
                        <h5 class="card-title">Login Mahasiswa</h5>
                        <p class="card-text text-muted small">Masuk atau daftar sebagai mahasiswa untuk melengkapi data dan melihat progress studi.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
exit;
