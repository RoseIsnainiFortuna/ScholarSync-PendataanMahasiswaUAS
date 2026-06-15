<?php
// ============================================================
// includes/header.php
// Sidebar + Topbar Bootstrap (dipanggil di semua halaman)
// Variabel yang harus di-set sebelum include:
//   $pageTitle  (string) — judul halaman
//   $activePage (string) — 'dashboard'|'data'|'progress'|'management'
// ============================================================
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/config.php';
}
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/auth.php';
}
$pageTitle  = $pageTitle  ?? 'ScholarSync Academic';
$activePage = $activePage ?? '';
$role       = $_SESSION['role'] ?? 'guest';
$username   = htmlspecialchars($_SESSION['username'] ?? '');
$profilePhotoUrl = null;
if (function_exists('isMahasiswa') && isMahasiswa() && function_exists('sessionNIM')) {
    $profilePhotoUrl = mahasiswaPhotoUrl(sessionNIM() ?? '');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — ScholarSync Academic</title>

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<div class="d-flex" id="app-wrapper">
    <nav id="sidebar" class="d-flex flex-column flex-shrink-0">
        <!-- Brand -->
        <div class="sidebar-brand d-flex align-items-center gap-2 px-3 py-3">
            <div class="brand-icon d-flex align-items-center justify-content-center rounded-3">
                <i class="bi bi-bank2 fs-5 text-white"></i>
            </div>
            <div>
                <div class="fw-bold text-dark small"><?= $role === 'admin' ? 'Academic Admin' : htmlspecialchars($_SESSION['nama'] ?? $username) ?></div>
                <div class="text-muted" style="font-size:.7rem">University Portal</div>
            </div>
        </div>

        <hr class="mx-3 mt-0">

        <!-- Nav links -->
        <ul class="nav nav-pills flex-column px-2 gap-1 flex-grow-1">
            <?php 
            // Tentukan URL berdasarkan role
            $dashboardUrl = $role === 'mahasiswa' ? BASE_URL . '/pages/mahasiswa/dashboard.php' : BASE_URL . '/pages/dashboard.php';
            $dataUrl = $role === 'mahasiswa' ? BASE_URL . '/pages/mahasiswa/student_data.php' : BASE_URL . '/pages/data_mahasiswa.php';
            $progressUrl = BASE_URL . '/pages/semester_progress.php'; // Sama untuk semua
            $managementUrl = $role === 'mahasiswa' ? BASE_URL . '/pages/mahasiswa/management.php' : BASE_URL . '/pages/management.php';
            ?>
            <li class="nav-item">
                <a href="<?= $dashboardUrl ?>"
                   class="nav-link d-flex align-items-center gap-2 <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $dataUrl ?>"
                   class="nav-link d-flex align-items-center gap-2 <?= $activePage === 'data' ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i> Student Data
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $progressUrl ?>"
                   class="nav-link d-flex align-items-center gap-2 <?= $activePage === 'progress' ? 'active' : '' ?>">
                    <i class="bi bi-graph-up-arrow"></i> Semester Progress
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $managementUrl ?>"
                   class="nav-link d-flex align-items-center gap-2 <?= $activePage === 'management' ? 'active' : '' ?>">
                    <i class="bi bi-bank"></i> Academic Management
                </a>
            </li>
        </ul>

        <!-- Bottom links -->
        <div class="px-2 pb-3 mt-auto">
            <hr class="mx-1">
            <a href="<?= BASE_URL ?>/pages/mahasiswa/help_center.php" class="nav-link d-flex align-items-center gap-2 text-muted small">
                <i class="bi bi-question-circle"></i> Help Center
            </a>
            <a href="<?= BASE_URL ?>/pages/mahasiswa/logout.php"
               class="nav-link d-flex align-items-center gap-2 text-danger small">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </nav>

    <!-- ===== MAIN CONTENT WRAPPER ===== -->
    <div id="main-content" class="flex-grow-1 d-flex flex-column min-vh-100">

        <!-- Topbar -->
        <nav class="topbar d-flex align-items-center px-3 px-md-4 gap-3">
            <!-- Hamburger (mobile) -->
            <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle">
                <i class="bi bi-list fs-5"></i>
            </button>

            <!-- Search -->
            <div class="flex-grow-1">
                <div class="input-group input-group-sm" style="max-width:400px">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="globalSearch" class="form-control border-start-0 ps-0"
                           placeholder="Search student records, faculty or courses...">
                </div>
            </div>

            <!-- Right side -->
            <div class="d-flex align-items-center gap-3 ms-auto">
                <span class="d-none d-md-block text-muted small">
                    <?= date('F j, Y') ?>
                </span>
                <button class="btn btn-sm btn-outline-secondary position-relative">
                    <i class="bi bi-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.55rem">3</span>
                </button>
                <button class="btn btn-sm btn-outline-secondary" id="themeToggleBtn" type="button" title="Toggle light/dark theme">
                    <i class="bi bi-moon-stars" id="themeToggleIcon"></i>
                </button>
                <button class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-gear"></i>
                </button>
                <div class="d-flex align-items-center gap-2">
                    <?php if (!empty($profilePhotoUrl)): ?>
                        <div class="avatar-circle overflow-hidden rounded-circle"
                             style="width:34px;height:34px;">
                            <img src="<?= htmlspecialchars($profilePhotoUrl) ?>" alt="Foto Profil" style="width:100%;height:100%;object-fit:cover;">
                        </div>
                    <?php else: ?>
                        <div class="avatar-circle d-flex align-items-center justify-content-center bg-primary text-white rounded-circle"
                             style="width:34px;height:34px;font-size:.8rem;font-weight:600">
                            <?= strtoupper(substr($username, 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="d-none d-md-block lh-1">
                        <div class="fw-semibold small"><?= $username ?></div>
                        <div class="text-muted" style="font-size:.68rem"><?= strtoupper($role) ?></div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page body starts here -->
        <div class="page-body flex-grow-1 p-3 p-md-4">
