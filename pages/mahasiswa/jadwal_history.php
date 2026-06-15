<?php
// ============================================================
// pages/mahasiswa/jadwal_history.php — Jadwal & History
// ============================================================
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireMahasiswa();

$pageTitle  = 'Jadwal & History';
$activePage = 'jadwal';

$nim = sessionNIM();

// Menggunakan data array placeholder yang sama dengan dashboard (Berisi seluruh jadwal)
$jadwal = [
    ['day' => 'Senin', 'course' => 'Database', 'time' => '12:15 - 13:55', 'room' => 'R. KULIAH HU 207', 'icon' => 'bi-laptop'],
    ['day' => 'Selasa', 'course' => 'Object Oriented Programming', 'time' => '12:15 - 13:55', 'room' => 'R. KULIAH HU 208', 'icon' => 'bi-code-slash'],
    ['day' => 'Selasa', 'course' => 'Algorithm & Data Structure Analysis', 'time' => '14:15 - 15:55', 'room' => 'R. KULIAH CU 205', 'icon' => 'bi-code-slash'],
    ['day' => 'Rabu', 'course' => 'Web Programming Practicum-1', 'time' => '07:15 - 10:55', 'room' => 'Laboratorium RPL 7', 'icon' => 'bi-code-slash'],
    ['day' => 'Rabu', 'course' => 'Database Practicum', 'time' => '12:15 - 15:55', 'room' => 'Laboratorium RPL 2', 'icon' => 'bi-laptop'],
    ['day' => 'Kamis', 'course' => 'Object Oriented Programming Practicum-1', 'time' => '07:15 - 10:55', 'room' => 'Laboratorium RPL 4', 'icon' => 'bi-code-slash'],
    ['day' => 'Kamis', 'course' => 'Data Structure Practicum', 'time' => '12:15 - 15:55', 'room' => 'Laboratorium RPL 4', 'icon' => 'bi-code-slash'],
];

// Ambil history (jika tabel ada di database)
$history = [];
if ($result2 = $conn->query("SHOW TABLES LIKE 'history_studi'")) {
    if ($result2->num_rows) {
        $h = $conn->prepare("SELECT tanggal, keterangan FROM history_studi WHERE NIM = ? ORDER BY tanggal DESC LIMIT 50");
        $h->bind_param('s', $nim);
        $h->execute();
        $history = $h->get_result()->fetch_all(MYSQLI_ASSOC);
        $h->close();
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>Jadwal & History Studi</h1>
    <p>Lihat jadwal kuliah dan riwayat kegiatan akademik Anda.</p>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="content-card">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-calendar2-week text-primary-custom me-2"></i> Jadwal Kuliah
            </h6>

            <?php if (!empty($jadwal)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Hari</th>
                            <th>Mata Kuliah</th>
                            <th>Jam / Ruang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jadwal as $r): ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark"><?= htmlspecialchars($r['day']) ?></span>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($r['course']) ?></strong>
                            </td>
                            <td>
                                <div class="small text-muted mb-1">
                                    <i class="bi bi-clock me-1"></i><?= htmlspecialchars($r['time']) ?>
                                </div>
                                <div class="small text-muted" style="font-size: 0.78rem;">
                                    <i class="bi <?= $r['icon'] ?> me-1"></i><?= htmlspecialchars($r['room']) ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info text-center py-4">
                <i class="bi bi-calendar-x fs-5 mb-2 d-block"></i>
                <p class="mb-0">Belum ada jadwal studi yang tersimpan.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="content-card">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-clock-history text-primary-custom me-2"></i> History Studi
            </h6>

            <?php if (!empty($history)): ?>
            <div class="history-timeline" style="max-height:500px; overflow-y:auto">
                <?php foreach ($history as $idx => $h): ?>
                <div class="d-flex gap-3 pb-3 <?= $idx < count($history) - 1 ? 'mb-3 border-bottom' : '' ?>">
                    <div class="timeline-dot" style="width:12px; height:12px; background:#0d6efd; border-radius:50%; flex-shrink:0; margin-top:3px"></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small mb-1">
                            <i class="bi bi-calendar-event me-1"></i><?= htmlspecialchars($h['tanggal']) ?>
                        </div>
                        <div class="text-muted small"><?= htmlspecialchars($h['keterangan']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-info text-center py-4">
                <i class="bi bi-inbox fs-5 mb-2 d-block"></i>
                <p class="mb-0">Belum ada riwayat studi yang tersimpan.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-3 mt-3">
    <div class="col-12">
        <a href="<?= BASE_URL ?>/pages/mahasiswa/dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>