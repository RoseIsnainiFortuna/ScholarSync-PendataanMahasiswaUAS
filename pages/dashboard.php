<?php
// ============================================================
// pages/dashboard.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

// ---- Stats ----
$totalMhs    = $conn->query("SELECT COUNT(*) c FROM mahasiswa")->fetch_assoc()['c'];
$aktifMhs    = $conn->query("SELECT COUNT(*) c FROM mahasiswa WHERE status_aktif='Aktif'")->fetch_assoc()['c'];
$totalProdi  = $conn->query("SELECT COUNT(*) c FROM prodi")->fetch_assoc()['c'];
$totalFak    = $conn->query("SELECT COUNT(*) c FROM fakultas")->fetch_assoc()['c'];

// ---- IPK avg per semester (untuk chart) ----
$chartData = $conn->query(
    "SELECT semester, ROUND(AVG(ipk),2) avg_ipk
     FROM perkembangan_semester
     GROUP BY semester ORDER BY semester"
);
$chartLabels = $chartValues = [];
while ($row = $chartData->fetch_assoc()) {
    $chartLabels[] = 'Sem ' . $row['semester'];
    $chartValues[] = (float)$row['avg_ipk'];
}

// ---- Recent students ----
$recent = $conn->query(
    "SELECT m.NIM, m.nama, p.nama_prodi, m.angkatan, m.status_aktif
     FROM mahasiswa m
     JOIN prodi p ON m.id_prodi = p.id_prodi
     ORDER BY m.NIM DESC LIMIT 5"
);

// ---- Activity placeholder data ----
$activities = [
    ['icon'=>'bi-shield-check','color'=>'text-success','title'=>'Database Sync Complete','sub'=>'All student records synchronized.','time'=>'14 MINUTES AGO'],
    ['icon'=>'bi-person-plus','color'=>'text-primary','title'=>'New Student Registered','sub'=>'Registration via admin portal.','time'=>'2 HOURS AGO'],
    ['icon'=>'bi-gear','color'=>'text-muted','title'=>'System Maintenance','sub'=>'Scheduled at 02:00 AM.','time'=>'5 HOURS AGO'],
];

require_once __DIR__ . '/../includes/header.php';
?>

<!-- ===== Dashboard ===== -->
<div class="page-header d-flex flex-wrap align-items-center gap-3">
    <div class="flex-grow-1">
        <h1>Academic Dashboard</h1>
        <p>Real-time overview of university performance and metrics.</p>
    </div>
    <?php if (isAdmin()): ?>
    <a href="<?= BASE_URL ?>/pages/tambah_mahasiswa.php" class="btn btn-primary-custom d-flex align-items-center gap-2">
        <i class="bi bi-plus-circle-fill"></i> New Registration
    </a>
    <?php endif; ?>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div class="stat-icon bg-light rounded-3 p-2"><i class="bi bi-people fs-5 text-primary-custom"></i></div>
                <span class="badge text-bg-success-subtle text-success fw-semibold" style="font-size:.7rem">+12.5%</span>
            </div>
            <div class="stat-label mt-3">Total Students</div>
            <div class="stat-value"><?= number_format($totalMhs) ?> <small class="fs-6 fw-normal text-muted">Active</small></div>
            <div class="progress mt-2" style="height:4px">
                <div class="progress-bar bg-primary" style="width:78%"></div>
            </div>
            <div class="text-muted mt-1" style="font-size:.72rem">78% of total capacity reached</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div class="stat-icon bg-light rounded-3 p-2"><i class="bi bi-grid fs-5 text-primary-custom"></i></div>
                <span class="badge bg-light text-muted fw-semibold border" style="font-size:.7rem">Sem. 2</span>
            </div>
            <div class="stat-label mt-3">Active Programs</div>
            <div class="stat-value"><?= $totalProdi ?> <small class="fs-6 fw-normal text-muted">Prodi</small></div>
            <div class="text-muted mt-2" style="font-size:.78rem">
                <?= $totalFak ?> Faculties / Departments<br>
                <a href="<?= BASE_URL ?>/pages/management.php" class="text-primary-custom fw-semibold" style="font-size:.78rem">
                    View curriculum status →
                </a>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-light rounded-3 p-2 mb-3"><i class="bi bi-diagram-3 fs-5 text-primary-custom"></i></div>
            <div class="stat-label">Student Status</div>
            <div class="d-flex align-items-center justify-content-between mt-2">
                <span class="text-muted small">Active</span>
                <strong><?= $aktifMhs ?></strong>
            </div>
            <div class="progress mb-2" style="height:4px">
                <div class="progress-bar bg-primary" style="width:<?= $totalMhs > 0 ? round($aktifMhs/$totalMhs*100) : 0 ?>%"></div>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted small">Others</span>
                <strong><?= $totalMhs - $aktifMhs ?></strong>
            </div>
            <div class="progress" style="height:4px">
                <div class="progress-bar bg-secondary" style="width:<?= $totalMhs > 0 ? round(($totalMhs-$aktifMhs)/$totalMhs*100) : 0 ?>%"></div>
            </div>
        </div>
    </div>
</div>

<!-- Chart + Activity -->
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-7">
        <div class="chart-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h6 class="fw-bold mb-0">Academic Performance Trend</h6>
                    <small class="text-muted">Average IPK per Semester</small>
                </div>
            </div>
            <canvas id="dashChart" height="200"></canvas>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="chart-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0">Recent Activity</h6>
                <a href="#" class="small text-primary-custom">See all</a>
            </div>
            <?php foreach ($activities as $act): ?>
            <div class="d-flex gap-3 mb-3">
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:36px;height:36px">
                    <i class="bi <?= $act['icon'] ?> <?= $act['color'] ?> small"></i>
                </div>
                <div>
                    <div class="fw-semibold small"><?= $act['title'] ?></div>
                    <div class="text-muted" style="font-size:.75rem"><?= $act['sub'] ?></div>
                    <div class="text-muted" style="font-size:.68rem"><?= $act['time'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Recent Students Table -->
<div class="content-card">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="fw-bold mb-0">Recent Registrations</h6>
        <a href="<?= BASE_URL ?>/pages/data_mahasiswa.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table table-clean mb-0">
            <thead>
                <tr>
                    <th>NIM</th><th>Name</th><th>Study Program</th><th>Year</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $recent->fetch_assoc()): ?>
                <tr>
                    <td class="font-monospace small"><?= htmlspecialchars($row['NIM']) ?></td>
                    <td><a href="<?= BASE_URL ?>/pages/semester_progress.php?nim=<?= urlencode($row['NIM']) ?>">
                        <?= htmlspecialchars($row['nama']) ?></a></td>
                    <td class="text-muted small"><?= htmlspecialchars($row['nama_prodi']) ?></td>
                    <td><?= htmlspecialchars($row['angkatan']) ?></td>
                    <td><?php
                        $s = $row['status_aktif'];
                        $cls = ['Aktif'=>'aktif','Lulus'=>'lulus','DO'=>'do'][$s] ?? 'aktif';
                        echo "<span class='badge-$cls'>$s</span>";
                    ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
renderBarChart('dashChart',
    <?= json_encode($chartLabels) ?>,
    <?= json_encode($chartValues) ?>
);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
