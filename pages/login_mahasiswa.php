<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Jika sudah login sebagai mahasiswa, langsung ke dashboard
if (isLoggedIn() && isMahasiswa()) {
    header('Location: ' . BASE_URL . '/pages/mahasiswa/dashboard.php');
    exit;
}

$error = '';
$success = '';
$info = '';
$showForm = true;

// Jika sudah login sebagai admin, beri pesan agar logout dulu untuk menggunakan akses mahasiswa
if (isLoggedIn() && isAdmin()) {
    $info = 'Anda sedang login sebagai admin. Silakan logout terlebih dahulu jika ingin menggunakan akses mahasiswa.';
    $showForm = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Username dan password wajib diisi.';
        } else {
            $stmt = $conn->prepare("SELECT id_users, username, password, role, NIM FROM users WHERE username = ? LIMIT 1");
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, $user['password']) && $user['role'] === 'mahasiswa') {
                session_regenerate_id(true);
                $_SESSION['user_id']  = $user['id_users'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];
                $_SESSION['NIM']      = $user['NIM'];

                if (!empty($user['NIM'])) {
                    $s2 = $conn->prepare("SELECT nama FROM mahasiswa WHERE NIM = ? LIMIT 1");
                    $s2->bind_param('s', $user['NIM']);
                    $s2->execute();
                    $m = $s2->get_result()->fetch_assoc();
                    $_SESSION['nama'] = $m['nama'] ?? $user['username'];
                    $s2->close();
                }

                header('Location: ' . BASE_URL . '/pages/mahasiswa/dashboard.php');
                exit;
            } else {
                $error = 'Login gagal. Pastikan Anda menggunakan akun mahasiswa.';
            }
        }

    } elseif ($action === 'register') {
        $r_username = trim($_POST['reg_username'] ?? '');
        $r_password = $_POST['reg_password'] ?? '';
        $r_password2 = $_POST['reg_password2'] ?? '';
        $r_nim = trim($_POST['reg_NIM'] ?? '');

        if ($r_username === '' || $r_password === '' || $r_password2 === '') {
            $error = 'Lengkapi semua field pendaftaran.';
        } elseif ($r_password !== $r_password2) {
            $error = 'Konfirmasi password tidak cocok.';
        } elseif (strlen($r_password) < 4) {
            $error = 'Password minimal 4 karakter.';
        } elseif ($r_nim !== '' && (mb_strlen($r_nim) < 3 || mb_strlen($r_nim) > 20)) {
            $error = 'NIM harus berisi 3–20 karakter jika diisi.';
        } else {
            // Cek username atau NIM sudah terdaftar
            $q = $conn->prepare("SELECT id_users FROM users WHERE username = ? OR (NIM <> '' AND NIM = ?) LIMIT 1");
            $q->bind_param('ss', $r_username, $r_nim);
            $q->execute();
            $exists = $q->get_result()->fetch_assoc();
            $q->close();

            if ($exists) {
                $error = 'Username atau NIM sudah terdaftar.';
            } else {
                $hash = password_hash($r_password, PASSWORD_DEFAULT);
                
                // Jika NIM diberikan, buat entry di mahasiswa terlebih dahulu
                if ($r_nim !== '') {
                    $m = $conn->prepare("INSERT INTO mahasiswa (NIM, nama, id_fakultas, id_prodi, angkatan, status_aktif) 
                        VALUES (?, ?, 1, 1, ?, 'Aktif') ON DUPLICATE KEY UPDATE nama = VALUES(nama), id_fakultas = VALUES(id_fakultas), id_prodi = VALUES(id_prodi)");
                    $displayName = $r_username;
                    $currentYear = date('Y');
                    $m->bind_param('ssi', $r_nim, $displayName, $currentYear);
                    if (!$m->execute()) {
                        $error = 'Gagal membuat data mahasiswa: ' . htmlspecialchars($m->error);
                        $m->close();
                        $q->close();
                    } else {
                        $m->close();
                        // Setelah mahasiswa berhasil, buat akun user dengan NIM
                        $ins = $conn->prepare("INSERT INTO users (username, password, role, NIM) VALUES (?, ?, 'mahasiswa', ?)");
                        $ins->bind_param('sss', $r_username, $hash, $r_nim);
                        if ($ins->execute()) {
                            $newId = $ins->insert_id;
                            $ins->close();
                            
                            // Auto-login setelah registrasi
                            session_regenerate_id(true);
                            $_SESSION['user_id'] = $newId;
                            $_SESSION['username'] = $r_username;
                            $_SESSION['role'] = 'mahasiswa';
                            $_SESSION['NIM'] = $r_nim;
                            $_SESSION['nama'] = $r_username;

                            header('Location: ' . BASE_URL . '/pages/mahasiswa/dashboard.php');
                            exit;
                        } else {
                            $error = 'Gagal membuat akun: ' . htmlspecialchars($ins->error);
                            $ins->close();
                        }
                    }
                } else {
                    // Jika NIM kosong, buat akun tanpa NIM
                    $ins = $conn->prepare("INSERT INTO users (username, password, role, NIM) VALUES (?, ?, 'mahasiswa', NULL)");
                    $ins->bind_param('ss', $r_username, $hash);
                    if ($ins->execute()) {
                        $newId = $ins->insert_id;
                        $ins->close();
                        
                        // Auto-login setelah registrasi
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $newId;
                        $_SESSION['username'] = $r_username;
                        $_SESSION['role'] = 'mahasiswa';
                        $_SESSION['NIM'] = '';
                        $_SESSION['nama'] = $r_username;

                        header('Location: ' . BASE_URL . '/pages/mahasiswa/dashboard.php');
                        exit;
                    } else {
                        $error = 'Gagal membuat akun: ' . htmlspecialchars($ins->error);
                        $ins->close();
                    }
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Mahasiswa — Terpal University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <style>.tab-card .nav-link{cursor:pointer}</style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card p-3 tab-card">
                <?php if ($showForm): ?>
                <ul class="nav nav-tabs mb-3" id="authTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">Sign In</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">Daftar</button>
                    </li>
                </ul>
                <?php endif; ?>

                <?php if ($info): ?>
                    <div class="alert alert-info">
                        <?= htmlspecialchars($info) ?>
                        <div class="mt-2">
                            <a href="<?= BASE_URL ?>/pages/logout.php" class="btn btn-sm btn-outline-secondary">Logout</a>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if ($showForm): ?>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="login" role="tabpanel">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="login">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button class="btn btn-primary w-100">Masuk</button>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="register" role="tabpanel">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="register">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="reg_username" class="form-control" value="<?= htmlspecialchars($_POST['reg_username'] ?? '') ?>" required>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="reg_password" class="form-control" required>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Konfirmasi Password</label>
                                    <input type="password" name="reg_password2" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">NIM (opsional)</label>
                                <input type="text" name="reg_NIM" class="form-control" value="<?= htmlspecialchars($_POST['reg_NIM'] ?? '') ?>">
                                <div class="form-text">Jika memiliki NIM, masukkan agar akun terhubung dengan data mahasiswa.</div>
                            </div>
                            <button class="btn btn-success w-100">Daftar dan Masuk</button>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <p class="mb-3">Silakan logout terlebih dahulu dari akun admin, lalu kembali ke halaman utama untuk memilih akses mahasiswa.</p>
                    <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-secondary">Kembali ke Halaman Pilihan</a>
                </div>
                <?php endif; ?>

                <div class="mt-3 text-center small text-muted">
                    Jika Anda admin, gunakan halaman <a href="<?= BASE_URL ?>/pages/login.php">admin login</a>.
                    <br>
                    <a href="<?= BASE_URL ?>/index.php">Kembali ke Halaman Pilihan</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
                