<?php
// ============================================================
// pages/mahasiswa/rencana_studi.php — Rencana Studi
// ============================================================
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireMahasiswa();

$pageTitle  = 'Rencana Studi';
$activePage = 'rencana';

$nim = sessionNIM();
$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isi = trim($_POST['rencana'] ?? '');
    if ($isi === '') {
        $message = 'Rencana studi tidak boleh kosong.';
        $messageType = 'warning';
    } else {
        // Tabel rencana_studi diharapkan ada; simpan/replace berdasarkan NIM
        $stmt = $conn->prepare("INSERT INTO rencana_studi (NIM, isi, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE isi = VALUES(isi), updated_at = NOW()");
        $stmt->bind_param('ss', $nim, $isi);
        if ($stmt->execute()) {
            $message = 'Rencana studi berhasil disimpan!';
            $messageType = 'success';
        } else {
            $message = 'Gagal menyimpan: ' . htmlspecialchars($stmt->error);
            $messageType = 'danger';
        }
        $stmt->close();
    }
}

// Ambil rencana existing
$current = '';
$s = $conn->prepare("SELECT isi FROM rencana_studi WHERE NIM = ? LIMIT 1");
$s->bind_param('s', $nim);
$s->execute();
$r = $s->get_result()->fetch_assoc();
if ($r) $current = $r['isi'];
$s->close();

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- ===== Rencana Studi Page ===== -->
<div class="page-header">
    <h1>Rencana Studi</h1>
    <p>Tulis dan kelola rencana studi akademik Anda untuk semester yang akan datang.</p>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?> alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
    <i class="bi bi-<?= $messageType === 'success' ? 'check-circle' : ($messageType === 'danger' ? 'exclamation-triangle' : 'info-circle') ?> fs-5"></i>
    <div>
        <?= htmlspecialchars($message) ?>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="content-card">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-file-earmark-text text-primary-custom me-2"></i> Form Rencana Studi
            </h6>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Rencana Studi Anda</label>
                    <textarea name="rencana" class="form-control" rows="10" placeholder="Tuliskan rencana studi Anda untuk semester mendatang&#10;&#10;Contoh:&#10;- Fokus pada mata kuliah fundamental&#10;- Meningkatkan IPK menjadi 3.5&#10;- Aktif dalam penelitian&#10;- Praktik coding rutin"><?= htmlspecialchars($current) ?></textarea>
                    <small class="text-muted">Tuliskan rencana, target, dan strategi studi Anda.</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-check-lg me-1"></i> Simpan Rencana
                    </button>
                    <a href="<?= BASE_URL ?>/pages/mahasiswa/dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar Tips -->
    <div class="col-12 col-lg-4">
        <div class="content-card bg-light-custom">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-lightbulb text-warning me-2"></i> Tips Rencana Studi
            </h6>
            <div class="small text-muted">
                <div class="mb-3">
                    <strong class="text-dark">1. Tetapkan Tujuan Jelas</strong><br>
                    Tentukan target IPK atau pencapaian akademik spesifik untuk semester ini.
                </div>
                <div class="mb-3">
                    <strong class="text-dark">2. Identifikasi Prioritas</strong><br>
                    Mata kuliah mana yang memerlukan fokus lebih? Tuliskan alasannya.
                </div>
                <div class="mb-3">
                    <strong class="text-dark">3. Strategi Belajar</strong><br>
                    Bagaimana Anda akan belajar? Kelompok studi, konsultasi dosen, dll.
                </div>
                <div>
                    <strong class="text-dark">4. Review Berkala</strong><br>
                    Tinjau kembali rencana Anda dan sesuaikan dengan progres.
                </div>
            </div>
        </div>

        <div class="content-card mt-3">
            <h6 class="fw-bold mb-2 d-flex align-items-center gap-2">
                <i class="bi bi-info-circle text-info"></i> Info
            </h6>
            <div class="small text-muted">
                Rencana studi yang Anda buat akan membantu Anda tetap fokus dan termotivasi dalam menjalani perkuliahan.
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
