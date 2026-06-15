<?php
// ============================================================
// pages/mahasiswa/logout.php — Logout dengan Konfirmasi Modal
// ============================================================
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireMahasiswa();

$pageTitle  = 'Konfirmasi Logout';
$activePage = 'logout';

// Jika ada parameter confirm=yes, lakukan logout
if ($_GET['confirm'] === 'yes') {
    session_start();
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . '/pages/login_mahasiswa.php');
    exit;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Modal Konfirmasi Logout -->
<div class="modal d-block" id="logoutModal" tabindex="-1" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle text-warning fs-5"></i>
                    Konfirmasi Logout
                </h5>
                <button type="button" class="btn-close" onclick="goBack()"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">
                    <strong>Apakah Anda yakin ingin keluar dari aplikasi?</strong><br>
                    <small class="text-muted">Anda akan diarahkan ke halaman login setelah logout.</small>
                </p>
            </div>
            <div class="modal-footer border-0 gap-2">
                <button type="button" class="btn btn-outline-secondary" onclick="goBack()">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
                <a href="<?= BASE_URL ?>/pages/mahasiswa/logout.php?confirm=yes" class="btn btn-danger">
                    <i class="bi bi-box-arrow-right me-1"></i> Exit
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Backdrop untuk modal -->
<div class="modal-backdrop fade show" id="backdrop"></div>

<script>
    function goBack() {
        window.history.back();
    }

    // Jika modal ditutup tanpa diklik tombol, kembali ke halaman sebelumnya
    document.querySelector('.btn-close').addEventListener('click', function() {
        goBack();
    });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
