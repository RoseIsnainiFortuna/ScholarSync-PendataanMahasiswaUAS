<?php
// ============================================================
// includes/auth.php
// Helper fungsi autentikasi session
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Pastikan user sudah login sebagai admin.
 * Jika belum, redirect ke halaman login.
 */
function requireAdmin(): void {
    if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        header('Location: ' . BASE_URL . '/pages/login.php?error=unauthorized');
        exit;
    }
}

/**
 * Pastikan user sudah login (admin atau mahasiswa).
 */
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/pages/login.php?error=unauthorized');
        exit;
    }
}

/**
 * Cek apakah sudah login.
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

/**
 * Ambil nama display dari session.
 */
function sessionName(): string {
    return htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? 'User');
}

/**
 * Cek apakah user adalah mahasiswa.
 */
function isMahasiswa(): bool {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'mahasiswa';
}

/**
 * Pastikan user sudah login sebagai mahasiswa.
 */
function requireMahasiswa(): void {
    if (empty($_SESSION['user_id']) || !isMahasiswa()) {
        header('Location: ' . BASE_URL . '/pages/login_mahasiswa.php?error=unauthorized');
        exit;
    }
}

/**
 * Ambil NIM dari session jika tersedia.
 */
function sessionNIM(): ?string {
    return $_SESSION['NIM'] ?? null;
}

function safeNIMFileName(string $nim): string {
    return preg_replace('/[^A-Za-z0-9_-]/', '', $nim);
}

/**
 * Dapatkan URL foto profil mahasiswa jika ada.
 */
function mahasiswaPhotoUrl(string $nim): ?string {
    $safeNIM = safeNIMFileName($nim);
    if ($safeNIM === '') {
        return null;
    }

    $uploadDir = __DIR__ . '/../uploads/mahasiswa';
    foreach (['.jpg', '.jpeg', '.png', '.webp'] as $ext) {
        $filePath = $uploadDir . '/' . $safeNIM . $ext;
        if (file_exists($filePath)) {
            return BASE_URL . '/uploads/mahasiswa/' . $safeNIM . $ext;
        }
    }
    return null;
}
