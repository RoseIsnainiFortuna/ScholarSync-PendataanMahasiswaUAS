<?php
// ============================================================
// pages/login.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

$info = '';
$showForm = true;

if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: ' . BASE_URL . '/pages/dashboard.php');
        exit;
    }

    if (isMahasiswa()) {
        $info = 'Anda sedang login sebagai mahasiswa. Jika ingin mengakses panel admin, silakan logout terlebih dahulu.';
        $showForm = false;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        // Ambil user dari DB (prepared statement)
        $stmt = $conn->prepare("SELECT id_users, username, password, role, NIM FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id_users'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['NIM']      = $user['NIM'];

            // Jika mahasiswa, ambil nama
            if ($user['role'] === 'mahasiswa' && $user['NIM']) {
                $s2 = $conn->prepare("SELECT nama FROM mahasiswa WHERE NIM = ? LIMIT 1");
                $s2->bind_param('s', $user['NIM']);
                $s2->execute();
                $mhs = $s2->get_result()->fetch_assoc();
                $_SESSION['nama'] = $mhs['nama'] ?? $user['username'];
                $s2->close();
            }

            // Redirect ke halaman sesuai role
            if ($user['role'] === 'mahasiswa') {
                header('Location: ' . BASE_URL . '/pages/mahasiswa/dashboard.php');
            } else {
                header('Location: ' . BASE_URL . '/pages/dashboard.php');
            }
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — Terpal University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="login-page">
    <div class="login-card">

        <!-- Icon -->
        <div class="login-icon-wrap">
            <i class="bi bi-bank2 fs-3 text-white"></i>
        </div>
        <h1 class="text-center fw-bold mb-1" style="font-size:1.6rem;color:var(--primary)">Terpal University</h1>
        <p class="text-center text-muted mb-4" style="font-size:.82rem">Pilihan akses admin hanya untuk staf yang mengelola data sistem.</p>

        <!-- Info Alert -->
        <?php if ($info): ?>
        <div class="alert alert-info alert-dismissible fade show py-2" role="alert">
            <i class="bi bi-info-circle me-1"></i>
            <?= htmlspecialchars($info) ?>
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Error Alert -->
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show alert-auto-dismiss py-2" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
        <div class="alert alert-warning py-2" role="alert">
            <i class="bi bi-shield-exclamation me-1"></i> Anda harus login terlebih dahulu.
        </div>
        <?php endif; ?>

        <?php if ($showForm): ?>
        <form method="POST" action="" data-validate="true" novalidate>
            <!-- Username -->
            <div class="mb-3">
                <label class="form-label text-uppercase" style="font-size:.7rem;letter-spacing:.06em">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted">
                        <i class="bi bi-person"></i>
                    </span>
                    <input type="text" name="username" id="username"
                           class="form-control border-start-0 ps-0"
                           placeholder="Enter admin username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                           data-required="Username wajib diisi.">
                </div>
                <div class="field-error" id="err_username"></div>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <div class="d-flex justify-content-between">
                    <label class="form-label text-uppercase" style="font-size:.7rem;letter-spacing:.06em">Password</label>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" name="password" id="password"
                           class="form-control border-start-0 border-end-0 ps-0"
                           placeholder="••••••••"
                           data-required="Password wajib diisi." data-min-length="4">
                    <button type="button" class="input-group-text bg-white toggle-password" data-target="#password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="field-error" id="err_password"></div>
            </div>

            <button type="submit" class="btn btn-primary-custom w-100 py-2">
                <i class="bi bi-box-arrow-in-right me-1"></i> Login
            </button>
        </form>
        <?php else: ?>
        <div class="text-center py-4">
            <p class="mb-3">Silakan <a href="<?= BASE_URL ?>/pages/logout.php" class="fw-semibold">logout</a> terlebih dahulu untuk menggunakan akses yang berbeda.</p>
            <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-secondary">Kembali ke halaman utama</a>
        </div>
        <?php endif; ?>

        <hr class="my-4">
        <div class="text-center mb-3">
            <a href="<?= BASE_URL ?>/index.php" class="text-decoration-none">&larr; Kembali ke Halaman Pilihan</a>
        </div>

        <div class="text-center text-muted" style="font-size:.75rem">
            <i class="bi bi-shield-check me-1 text-success"></i> System Online &nbsp;|&nbsp;
            <i class="bi bi-lock-fill me-1"></i> Secure Session
        </div>

        <p class="text-center mt-3 text-muted" style="font-size:.72rem">
            &copy; <?= date('Y') ?> Terpal University. All rights reserved.
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
