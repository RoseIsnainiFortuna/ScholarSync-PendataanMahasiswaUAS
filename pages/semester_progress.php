<?php
// ============================================================
// pages/semester_progress.php  — Semester Progress detail
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle  = 'Semester Progress';
$activePage = 'progress';

// NIM dari GET (admin bisa lihat siapapun, mahasiswa hanya dirinya)
$nimReq = htmlspecialchars(trim($_GET['nim'] ?? ''), ENT_QUOTES, 'UTF-8');

if (!isAdmin()) {
    $nimReq = $_SESSION['NIM'] ?? '';
}

// ---- Daftar mahasiswa (dropdown untuk admin) ----
$mahasiswaList = null;
if (isAdmin()) {
    $mahasiswaList = $conn->query(
        "SELECT m.NIM, m.nama, p.nama_prodi FROM mahasiswa m JOIN prodi p ON m.id_prodi=p.id_prodi ORDER BY m.nama"
    );
}

// ---- Load selected student ----
$mhs = null;
$progressRows = [];
$chartLabels = $chartValues = [];

if ($nimReq !== '') {
    // PERBAIKAN 1: Menambahkan kolom m.ipk ke dalam query SELECT utama mahasiswa
    $s = $conn->prepare(
        "SELECT m.NIM, m.nama, m.angkatan, m.status_aktif, m.ipk, p.nama_prodi, f.nama_fakultas
         FROM mahasiswa m
         JOIN prodi p ON m.id_prodi=p.id_prodi
         JOIN fakultas f ON p.id_fakultas=f.id_fakultas
         WHERE m.NIM=? LIMIT 1"
    );
    $s->bind_param('s', $nimReq);
    $s->execute();
    $mhs = $s->get_result()->fetch_assoc();
    $s->close();

    if ($mhs) {
        // PERBAIKAN 2: Gunakan nilai IPK global mahasiswa sebagai fallback tren chart grafiknya
        $chartLabels[]  = 'Current';
        $chartValues[]  = (float)($mhs['ipk'] ?? 0.00);
    }
}

// ---- Handle Add progress (Ditantang/Dimatikan karena menggunakan input profil langsung) ----
$progErrors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAdmin() && isset($_POST['action']) && $_POST['action'] === 'add_progress') {
    $pNim      = htmlspecialchars(trim($_POST['nim_prog'] ?? ''), ENT_QUOTES, 'UTF-8');
    $pSem      = (int)($_POST['semester'] ?? 0);
    $pIpk      = (float)str_replace(',', '.', $_POST['ipk'] ?? '');

    if ($pNim === '')          $progErrors['nim_prog'] = 'Pilih mahasiswa.';
    if ($pSem < 1 || $pSem > 14) $progErrors['semester'] = 'Semester 1–14.';
    if ($pIpk < 0 || $pIpk > 4)  $progErrors['ipk']      = 'IPK 0.00–4.00.';

    if (empty($progErrors)) {
        // Mengubah nilai IPK di tabel mahasiswa secara langsung
        $ins = $conn->prepare("UPDATE mahasiswa SET ipk = ? WHERE NIM = ?");
        $ins->bind_param('ds', $pIpk, $pNim);
        if ($ins->execute()) {
            $_SESSION['flash'] = ['type'=>'success','msg'=>"IPK Mahasiswa berhasil diperbarui menjadi $pIpk."];
            header("Location: " . BASE_URL . "/pages/semester_progress.php?nim=" . urlencode($pNim));
            exit;
        }
        $ins->close();
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header d-flex flex-wrap align-items-center gap-3">
    <div class="flex-grow-1">
        <h1>Semester Progress</h1>
        <p>Track GPA trends and academic milestones per student.</p>
    </div>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show alert-auto-dismiss" role="alert">
    <?= htmlspecialchars($flash['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isAdmin()): ?>
<div class="content-card mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-12 col-md-6">
            <label class="form-label">Select Student</label>
            <select name="nim" class="form-select" onchange="this.form.submit()">
                <option value="">— Choose a student —</option>
                <?php while ($m = $mahasiswaList->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($m['NIM']) ?>"
                    <?= $nimReq === $m['NIM'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['NIM']) ?> — <?= htmlspecialchars($m['nama']) ?>
                    (<?= htmlspecialchars($m['nama_prodi']) ?>)
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary-custom">Load</button>
        </div>
    </form>
</div>
<?php endif; ?>

<?php if ($mhs): ?>
<div class="content-card mb-4">
    <nav aria-label="breadcrumb" class="mb-2">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/pages/data_mahasiswa.php">Students</a></li>
            <li class="breadcrumb-item"><?= htmlspecialchars($mhs['nama_prodi']) ?></li>
            <li class="breadcrumb-item active">Progress Detail</li>
        </ol>
    </nav>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-bold mb-0"><?= htmlspecialchars($mhs['nama']) ?></h2>
            <div class="text-muted small">
                Student ID: <?= htmlspecialchars($mhs['NIM']) ?> •
                <?= htmlspecialchars($mhs['nama_prodi']) ?> •
                Angkatan <?= htmlspecialchars($mhs['angkatan']) ?>
            </div>
        </div>
        <?php if (isAdmin()): ?>
        <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addProgressModal">
            <i class="bi bi-pencil-square me-1"></i> Update IPK
        </button>
        <?php endif; ?>
    </div>
</div>

<?php
// PERBAIKAN 3: Ubah nilai statis kalkulasi agar langsung mengambil data $mhs['ipk']
$latestIpk  = (float)($mhs['ipk'] ?? 0.00);
?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="stat-card">
            <div class="stat-label">Cumulative GPA (IPK)</div>
            <div class="stat-value"><?= number_format($latestIpk, 2) ?></div>
            <div class="small text-muted">
                <i class="bi bi-check-circle-fill text-success"></i> Realtime data profil
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-card">
            <div class="stat-label">Semesters Recorded</div>
            <div class="stat-value">1</div>
            <div class="progress mt-2" style="height:4px">
                <div class="progress-bar bg-primary" style="width: 12.5%"></div>
            </div>
            <div class="text-muted small mt-1">Aktif berjalan</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card" style="background:linear-gradient(135deg,var(--primary),#2563a8);color:#fff">
            <div class="stat-label" style="color:rgba(255,255,255,.7)">Academic Status</div>
            <div class="stat-value" style="font-size:1.3rem"><?= htmlspecialchars($mhs['status_aktif']) ?></div>
            <?php if ($latestIpk >= 3.5): ?>
            <span class="badge bg-warning text-dark mt-1">DEAN'S LIST</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="chart-card">
            <h6 class="fw-bold mb-1">GPA (IPK) Historical Trends</h6>
            <canvas id="ipkChart" height="220"></canvas>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="content-card h-100">
            <h6 class="fw-bold mb-3">Semester Breakdown</h6>
            <div class="table-responsive">
                <table class="table table-clean mb-0 small">
                    <thead>
                        <tr><th>Status Terkini</th><th>IPK</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-semibold">IPK Kumulatif</td>
                            <td>
                                <span class="fw-bold"><?= number_format($latestIpk, 2) ?></span>
                                <div class="progress mt-1" style="height:3px;width:80px">
                                    <div class="progress-bar bg-primary" style="width:<?= $latestIpk/4*100 ?>%"></div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
renderIPKChart('ipkChart',
    <?= json_encode($chartLabels) ?>,
    <?= json_encode($chartValues) ?>
);
</script>

<?php if (isAdmin() && $mhs): ?>
<div class="modal fade" id="addProgressModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold">Update IPK Mahasiswa</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" data-validate="true" novalidate>
                    <input type="hidden" name="action" value="add_progress">
                    <input type="hidden" name="nim_prog" value="<?= htmlspecialchars($nimReq) ?>">
                    <input type="hidden" name="semester" value="1">

                    <div class="mb-4">
                        <label class="form-label">IPK Baru (0.00–4.00) <span class="text-danger">*</span></label>
                        <input type="number" name="ipk" class="form-control"
                               min="0" max="4" step="0.01" placeholder="3.50" value="<?= $latestIpk ?>"
                               data-required="IPK wajib diisi." data-min="0" data-max="4">
                        <div class="field-error" id="err_ipk"></div>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-custom">Save IPK</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php endif; ?> <?php require_once __DIR__ . '/../includes/footer.php'; ?>