<?php
// ============================================================
// pages/mahasiswa/management.php — Academic Management (Evaluasi Pembelajaran)
// ============================================================
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireMahasiswa();

$pageTitle  = 'Academic Management';
$activePage = 'management';

$nimSession = sessionNIM();

// ---- Ambil data evaluasi pembelajaran ----
$evaluations = [];
if ($nimSession) {
    $eval = $conn->query("
        SELECT ps.semester, ps.ipk, 
               (SELECT COUNT(*) FROM perkembangan_semester WHERE NIM = '$nimSession' AND semester <= ps.semester) as completed_sems
        FROM perkembangan_semester ps
        WHERE ps.NIM = '$nimSession'
        ORDER BY ps.semester
    ");
    
    while ($row = $eval->fetch_assoc()) {
        $evaluations[] = $row;
    }
}

// ---- Ambil rata-rata IPK dan statistik ----
$stats = $conn->query("
    SELECT 
        COUNT(*) as total_sems,
        ROUND(AVG(ipk), 2) as avg_ipk,
        MAX(ipk) as max_ipk,
        MIN(ipk) as min_ipk
    FROM perkembangan_semester
    WHERE NIM = '$nimSession'
")->fetch_assoc();

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- ===== Academic Management - Student ===== -->
<div class="page-header">
    <h1>Evaluasi Pembelajaran</h1>
    <p>Pantau perkembangan akademik dan analisis performa studi Anda.</p>
</div>

<!-- Navigation cards -->
<div class="row g-3 mb-4">
    <?php
    $modules = [
        ['icon'=>'bi-grid-1x2-fill','color'=>'bg-light','title'=>'Dashboard','sub'=>'Kembali','href'=>BASE_URL.'/pages/mahasiswa/dashboard.php'],
        ['icon'=>'bi-people-fill','color'=>'bg-light','title'=>'Data Pribadi','sub'=>'Lihat profil','href'=>BASE_URL.'/pages/mahasiswa/student_data.php'],
        ['icon'=>'bi-graph-up-arrow','color'=>'bg-light','title'=>'Semester Progress','sub'=>'Performa semester','href'=>BASE_URL.'/pages/semester_progress.php'],
        ['icon'=>'bi-award','color'=>'bg-primary-custom text-white','title'=>'Evaluasi Pembelajaran','sub'=>'Halaman ini','href'=>'#'],
    ];
    foreach ($modules as $mod):
    ?>
    <div class="col-6 col-md-3">
        <a href="<?= $mod['href'] ?>" class="text-decoration-none">
            <div class="stat-card d-flex flex-column gap-2 p-3">
                <div class="rounded-3 d-inline-flex align-items-center justify-content-center p-2 <?= $mod['color'] ?>" style="width:40px;height:40px">
                    <i class="bi <?= $mod['icon'] ?>"></i>
                </div>
                <div class="fw-semibold small text-dark"><?= $mod['title'] ?></div>
                <div class="text-muted" style="font-size:.75rem"><?= $mod['sub'] ?></div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<!-- Statistics Cards -->
<?php if (!empty($stats) && $stats['total_sems'] > 0): ?>
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-light rounded-3 p-2"><i class="bi bi-mortarboard fs-5 text-primary-custom"></i></div>
            <div class="stat-label mt-3">Total Semester</div>
            <div class="stat-value"><?= $stats['total_sems'] ?> <small class="fs-6 fw-normal text-muted">Semester</small></div>
            <div class="text-muted mt-2" style="font-size:.78rem">Sudah menjalani <?= $stats['total_sems'] ?> semester akademik</div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-light rounded-3 p-2"><i class="bi bi-star-fill fs-5 text-warning"></i></div>
            <div class="stat-label mt-3">IPK Rata-rata</div>
            <div class="stat-value"><?= number_format($stats['avg_ipk'], 2) ?> <small class="fs-6 fw-normal text-muted">/4.00</small></div>
            <div class="progress mt-2" style="height:4px">
                <div class="progress-bar bg-warning" style="width:<?= ($stats['avg_ipk'] / 4) * 100 ?>%"></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-light rounded-3 p-2"><i class="bi bi-graph-up fs-5 text-success"></i></div>
            <div class="stat-label mt-3">IPK Tertinggi</div>
            <div class="stat-value"><?= number_format($stats['max_ipk'], 2) ?> <small class="fs-6 fw-normal text-muted">/4.00</small></div>
            <div class="text-muted mt-2" style="font-size:.78rem">Prestasi terbaik Anda</div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-light rounded-3 p-2"><i class="bi bi-exclamation-circle fs-5 text-danger"></i></div>
            <div class="stat-label mt-3">IPK Terendah</div>
            <div class="stat-value"><?= number_format($stats['min_ipk'], 2) ?> <small class="fs-6 fw-normal text-muted">/4.00</small></div>
            <div class="text-muted mt-2" style="font-size:.78rem">Performa paling rendah</div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Evaluasi Per Semester -->
<div class="row g-3">
    <div class="col-12">
        <div class="content-card">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-calendar-check text-primary-custom me-2"></i> Evaluasi Per Semester
            </h6>

            <?php if (!empty($evaluations)): ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:20%">Semester</th>
                            <th style="width:20%">IPK</th>
                            <th style="width:30%">Grafik</th>
                            <th style="width:30%">Performa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $prevIpk = null;
                        foreach ($evaluations as $eval):
                            $trend = 'stable';
                            if ($prevIpk !== null) {
                                $trend = $eval['ipk'] > $prevIpk ? 'naik' : ($eval['ipk'] < $prevIpk ? 'turun' : 'stabil');
                            }
                            $prevIpk = $eval['ipk'];
                        ?>
                        <tr>
                            <td>
                                <strong>Semester <?= $eval['semester'] ?></strong>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-custom"><?= number_format($eval['ipk'], 2) ?></div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px">
                                        <div class="progress-bar" style="width:<?= ($eval['ipk'] / 4) * 100 ?>%; background:<?= $eval['ipk'] >= 3.5 ? '#28a745' : ($eval['ipk'] >= 3.0 ? '#ffc107' : '#dc3545') ?>"></div>
                                    </div>
                                    <small><?= number_format(($eval['ipk'] / 4) * 100, 0) ?>%</small>
                                </div>
                            </td>
                            <td>
                                <?php if ($eval['ipk'] >= 3.5): ?>
                                    <span class="badge bg-success-subtle text-success">Sangat Baik</span>
                                <?php elseif ($eval['ipk'] >= 3.0): ?>
                                    <span class="badge bg-warning-subtle text-warning">Baik</span>
                                <?php elseif ($eval['ipk'] >= 2.5): ?>
                                    <span class="badge bg-info-subtle text-info">Cukup</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger">Perlu Ditingkatkan</span>
                                <?php endif; ?>
                                
                                <?php if ($trend === 'naik'): ?>
                                    <span class="text-success small ms-2"><i class="bi bi-arrow-up"></i> Naik</span>
                                <?php elseif ($trend === 'turun'): ?>
                                    <span class="text-danger small ms-2"><i class="bi bi-arrow-down"></i> Turun</span>
                                <?php elseif ($trend === 'stabil'): ?>
                                    <span class="text-muted small ms-2"><i class="bi bi-dash"></i> Stabil</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php else: ?>
            <div class="alert alert-info text-center py-4">
                <i class="bi bi-info-circle fs-5 mb-2 d-block"></i>
                <p class="mb-0">Belum ada data evaluasi pembelajaran. Silakan hubungi admin untuk input data.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Rekomendasi -->
<?php if (!empty($evaluations)): ?>
<div class="row g-3 mt-3">
    <div class="col-12">
        <div class="content-card bg-light-custom">
            <h6 class="fw-bold mb-2">
                <i class="bi bi-lightbulb text-warning me-2"></i> Rekomendasi
            </h6>
            <div class="small text-muted">
                <?php 
                $lastIpk = end($evaluations)['ipk'] ?? 0;
                if ($lastIpk >= 3.7):
                    echo 'Prestasi luar biasa! Pertahankan performa Anda dan terus tingkatkan kompetensi akademik.';
                elseif ($lastIpk >= 3.5):
                    echo 'Performa bagus! Fokus pada mata kuliah yang sulit dan manfaatkan jam belajar tambahan.';
                elseif ($lastIpk >= 3.0):
                    echo 'Cukup baik, namun ada ruang untuk peningkatan. Identifikasi mata kuliah yang lemah dan cari bantuan tutor.';
                elseif ($lastIpk >= 2.5):
                    echo 'Nilai Anda sudah melewati ambang minimum, tapi cobalah tingkatkan usaha belajar untuk hasil yang lebih baik.';
                else:
                    echo 'Anda perlu meningkatkan performa akademik. Hubungi advisor atau dosen untuk konsultasi belajar.';
                endif;
                ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
