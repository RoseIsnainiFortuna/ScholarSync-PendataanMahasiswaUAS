<?php
// ============================================================
// pages/mahasiswa/dashboard.php — Dashboard khusus Mahasiswa
// ============================================================
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireMahasiswa();

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

$nimSession = sessionNIM();
$usernameSession = sessionName();

// ---- Ambil data mahasiswa ----
$studentData = [];
if ($nimSession) {
    $s = $conn->prepare("SELECT NIM, nama, id_prodi, angkatan, status_aktif, ipk FROM mahasiswa WHERE NIM = ? LIMIT 1");
    $s->bind_param('s', $nimSession);
    $s->execute();
    $studentData = $s->get_result()->fetch_assoc() ?: [];
    $s->close();
}

$isFormFilled = !empty($studentData['nama']);

// ---- IPK Stats ----
// Mengambil langsung nilai IPK dari tabel mahasiswa yang sudah ditarik di atas
$lastSemester = 0; // Anda bisa membiarkannya 0 atau sesuaikan nanti
$gpaStats = [
    'ipk' => $studentData['ipk'] ?? 0.00
];

$photoUrl = $nimSession ? mahasiswaPhotoUrl($nimSession) : null;

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- ===== Dashboard Mahasiswa ===== -->
<div class="page-header d-flex flex-wrap align-items-center gap-3">
    <?php if (!empty($photoUrl)): ?>
    <div class="avatar-small overflow-hidden">
        <img src="<?= htmlspecialchars($photoUrl) ?>" alt="Foto Profil" class="w-100 h-100" style="object-fit:cover;">
    </div>
    <?php else: ?>
    <div class="avatar-small d-flex align-items-center justify-content-center">
        <?= strtoupper(substr($usernameSession, 0, 1)) ?>
    </div>
    <?php endif; ?>
    <div class="flex-grow-1">
        <h1>Selamat Datang, <?= htmlspecialchars($usernameSession) ?></h1>
        <p>Kelola data akademik dan pantau progress studi Anda.</p>
    </div>
    <?php if (!$isFormFilled): ?>
    <a href="<?= BASE_URL ?>/pages/mahasiswa/form.php" class="btn btn-primary-custom d-flex align-items-center gap-2">
        <i class="bi bi-pencil-square"></i> Lengkapi Data
    </a>
    <?php endif; ?>
</div>

<?php if (!$isFormFilled): ?>
<!-- Alert untuk lengkapi form -->
<div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
    <i class="bi bi-exclamation-triangle fs-5"></i>
    <div>
        <strong>Data Anda belum lengkap!</strong> Silakan lengkapi profil Anda untuk akses penuh ke semua fitur.
        <a href="<?= BASE_URL ?>/pages/mahasiswa/form.php" class="alert-link fw-bold">Lengkapi sekarang</a>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <?php if ($isFormFilled): ?>
    <div class="col-12 col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-light rounded-3 p-2 mb-2"><i class="bi bi-person fs-5 text-primary-custom"></i></div>
            <div class="stat-label">Nama Lengkap</div>
            <div class="stat-value"><?= htmlspecialchars($studentData['nama']) ?></div>
            <div class="text-muted mt-1" style="font-size:.78rem">
                <strong>NIM:</strong> <?= htmlspecialchars($studentData['NIM']) ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-light rounded-3 p-2 mb-2"><i class="bi bi-book fs-5 text-primary-custom"></i></div>
            <div class="stat-label">Program Studi</div>
            <div class="stat-value">
                <?php
                $prodiName = 'Unknown';
                if (!empty($studentData['id_prodi'])) {
                    $p = $conn->prepare("SELECT nama_prodi FROM prodi WHERE id_prodi = ? LIMIT 1");
                    $p->bind_param('i', $studentData['id_prodi']);
                    $p->execute();
                    $pr = $p->get_result()->fetch_assoc();
                    $prodiName = $pr['nama_prodi'] ?? 'Unknown';
                    $p->close();
                }
                echo htmlspecialchars($prodiName);
                ?>
            </div>
            <div class="text-muted mt-1" style="font-size:.78rem">
                <strong>Angkatan:</strong> <?= $studentData['angkatan'] ?? 'N/A' ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-light rounded-3 p-2 mb-2"><i class="bi bi-star-fill fs-5 text-warning"></i></div>
            <div class="stat-label">IPK Terakhir</div>
            <div class="stat-value"><?= number_format($gpaStats['ipk'] ?? 0, 2) ?></div>
            <div class="text-muted mt-1" style="font-size:.78rem">
                <strong>Semester:</strong> <?= $lastSemester ?>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="col-12">
        <div class="stat-card p-4 text-center">
            <div class="mb-3">
                <i class="bi bi-inbox fs-1 text-muted"></i>
            </div>
            <h5 class="text-muted">Data Belum Dilengkapi</h5>
            <p class="text-muted small">Lengkapi profil Anda untuk melihat statistik akademik dan informasi detail.</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Main Content -->
<div class="row g-3">
    <!-- Jadwal & History -->
    <div class="col-12 col-lg-6">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-calendar2-week text-primary-custom me-2"></i> Jadwal & History
                </h6>
                <a href="<?= BASE_URL ?>/pages/mahasiswa/jadwal_history.php" class="btn btn-sm btn-outline-primary">
                    Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="schedule-container">
                <?php
                // Ambil jadwal (placeholder - bisa disesuaikan dengan database)
                $schedules = [
                    ['day' => 'Senin', 'course' => 'Database', 'time' => '12:15 - 13:55', 'room' => 'R. KULIAH HU 207', 'icon' => 'bi-laptop'],
                    ['day' => 'Selasa', 'course' => 'Object Oriented Programming', 'time' => '12:15 - 13:55', 'room' => 'R. KULIAH HU 208', 'icon' => 'bi-code-slash'],
                    ['day' => 'Selasa', 'course' => 'Algorithm & Data Structure Analysis', 'time' => '14:15 - 15:55', 'room' => 'R. KULIAH CU 205', 'icon' => 'bi-code-slash'],
                    ['day' => 'Rabu', 'course' => 'Web Programming Practicum-1', 'time' => '07:15 - 10:55', 'room' => 'Laboratorium RPL 7', 'icon' => 'bi-code-slash'],
                    ['day' => 'Rabu', 'course' => 'Database Practicum', 'time' => '12:15 - 15:55', 'room' => 'Laboratorium RPL 2', 'icon' => 'bi-laptop'],
                    ['day' => 'Kamis', 'course' => 'Object Oriented Programming Practicum-1', 'time' => '07:15 - 10:55', 'room' => 'Laboratorium RPL 4', 'icon' => 'bi-code-slash'],
                    ['day' => 'Kamis', 'course' => 'Data Structure Practicum', 'time' => '12:15 - 15:55', 'room' => 'Laboratorium RPL 4', 'icon' => 'bi-code-slash'],
                ];
                $limitedSchedules = array_slice($schedules, 0, 4);
                foreach ($limitedSchedules as $sched):
                ?>
                <div class="schedule-item d-flex gap-3 pb-3 mb-3 border-bottom">
                    <div class="schedule-day">
                        <div class="fw-semibold small text-muted"><?= $sched['day'] ?></div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold mb-1"><?= $sched['course'] ?></div>
                        <div class="small text-muted mb-1">
                            <i class="bi bi-clock me-1"></i><?= $sched['time'] ?>
                        </div>
                        <div class="small text-muted">
                            <i class="bi <?= $sched['icon'] ?> me-1"></i><?= $sched['room'] ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($schedules)): ?>
                <p class="text-muted text-center py-4">Tidak ada jadwal untuk ditampilkan</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Rencana Studi -->
    <div class="col-12 col-lg-6">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-file-earmark-check text-primary-custom me-2"></i> Rencana Studi
                </h6>
                <a href="<?= BASE_URL ?>/pages/mahasiswa/rencana_studi.php" class="btn btn-sm btn-outline-primary">
                    Lihat Rencana <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="study-plan-container">
                <?php
                // Ambil rencana studi dari database
                $studyPlans = [];
                if ($nimSession) {
                    $sp = $conn->query("SELECT * FROM perkembangan_semester WHERE NIM = '$nimSession' ORDER BY semester LIMIT 4");
                    while ($plan = $sp->fetch_assoc()) {
                        $studyPlans[] = $plan;
                    }
                }
                
                if (!empty($studyPlans)):
                    foreach ($studyPlans as $plan):
                ?>
                <div class="study-plan-item d-flex justify-content-between align-items-center pb-2 mb-2 border-bottom">
                    <div>
                        <div class="fw-semibold small">Semester <?= $plan['semester'] ?></div>
                        <div class="text-muted small">IPK: <strong><?= number_format($plan['ipk'], 2) ?></strong></div>
                    </div>
                    <div class="progress-bar-small" style="width:60px; height:6px; background:#e9ecef; border-radius:3px; overflow:hidden">
                        <div style="width:<?= ($plan['ipk'] / 4) * 100 ?>%; height:100%; background:#0d6efd; transition:width 0.3s"></div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <p class="text-muted text-center py-4">Belum ada data rencana studi</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row g-3 mt-3">
    <div class="col-12">
        <div class="content-card">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-lightning text-warning me-2"></i> Aksi Cepat
            </h6>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= BASE_URL ?>/pages/mahasiswa/form.php" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i> Edit Profil
                </a>
                <a href="<?= BASE_URL ?>/pages/mahasiswa/student_data.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-eye me-1"></i> Lihat Data Pribadi
                </a>
                <a href="<?= BASE_URL ?>/pages/semester_progress.php" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-graph-up me-1"></i> Progress Akademik
                </a>
                <a href="<?= BASE_URL ?>/pages/mahasiswa/management.php" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-award me-1"></i> Evaluasi Pembelajaran
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
