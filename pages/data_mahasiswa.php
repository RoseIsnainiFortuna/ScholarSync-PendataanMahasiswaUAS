<?php
// ============================================================
// pages/data_mahasiswa.php  — Student Data (READ + search + pagination)
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle  = 'Student Data';
$activePage = 'data';

// ---- Filter & Pagination ----
$search    = trim($_GET['q']     ?? '');
$filterFak = (int)($_GET['fak']  ?? 0);
$filterPro = (int)($_GET['pro']  ?? 0);
$perPage   = 8;
$page      = max(1, (int)($_GET['page'] ?? 1));
$offset    = ($page - 1) * $perPage;

// Build WHERE
$where  = "WHERE 1=1";
$params = [];
$types  = '';

if ($search !== '') {
    $where   .= " AND (m.NIM LIKE ? OR m.nama LIKE ?)";
    $like     = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}
if ($filterPro > 0) {
    $where   .= " AND m.id_prodi = ?";
    $params[] = $filterPro;
    $types   .= 'i';
} elseif ($filterFak > 0) {
    $where   .= " AND f.id_fakultas = ?";
    $params[] = $filterFak;
    $types   .= 'i';
}

// Count
$countSQL  = "SELECT COUNT(*) c FROM mahasiswa m JOIN prodi p ON m.id_prodi=p.id_prodi JOIN fakultas f ON p.id_fakultas=f.id_fakultas $where";
$stmtCount = $conn->prepare($countSQL);
if ($params) $stmtCount->bind_param($types, ...$params);
$stmtCount->execute();
$totalRows = $stmtCount->get_result()->fetch_assoc()['c'];
$stmtCount->close();
$totalPages = max(1, (int)ceil($totalRows / $perPage));
$page = min($page, $totalPages);

// Data
$dataSQL  = "SELECT m.NIM, m.nama, p.nama_prodi, f.nama_fakultas, m.angkatan, m.status_aktif
             FROM mahasiswa m
             JOIN prodi p ON m.id_prodi=p.id_prodi
             JOIN fakultas f ON p.id_fakultas=f.id_fakultas
             $where ORDER BY m.NIM DESC LIMIT ? OFFSET ?";
$stmtData = $conn->prepare($dataSQL);
$allTypes = $types . 'ii';
$allParams = array_merge($params, [$perPage, $offset]);
$stmtData->bind_param($allTypes, ...$allParams);
$stmtData->execute();
$rows = $stmtData->get_result();
$stmtData->close();

// Fakultas dropdown
$fakultasList = $conn->query("SELECT * FROM fakultas ORDER BY nama_fakultas");
// Prodi dropdown (all, filtered by fak if set)
$prodiSQL  = $filterFak > 0 ? "SELECT * FROM prodi WHERE id_fakultas=$filterFak ORDER BY nama_prodi"
                             : "SELECT * FROM prodi ORDER BY nama_prodi";
$prodiList = $conn->query($prodiSQL);

// Stats
$stats = $conn->query(
    "SELECT status_aktif, COUNT(*) c FROM mahasiswa GROUP BY status_aktif"
)->fetch_all(MYSQLI_ASSOC);
$statMap = array_column($stats, 'c', 'status_aktif');

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page header -->
<div class="page-header d-flex flex-wrap align-items-center gap-3">
    <div class="flex-grow-1">
        <h1>Student Management</h1>
        <p>Manage university student records and academic statuses.</p>
    </div>
    <?php if (isAdmin()): ?>
    <a href="<?= BASE_URL ?>/pages/tambah_mahasiswa.php" class="btn btn-primary-custom d-flex align-items-center gap-2">
        <i class="bi bi-person-plus-fill"></i> Add Student
    </a>
    <?php endif; ?>
</div>

<!-- Flash -->
<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show alert-auto-dismiss" role="alert">
    <i class="bi bi-<?= $flash['type']==='success'?'check-circle':'exclamation-triangle' ?> me-1"></i>
    <?= htmlspecialchars($flash['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Stat mini-cards -->
<div class="row g-3 mb-4">
    <?php
    $miniStats = [
        ['label'=>'Total Enrolled', 'value'=>$totalRows, 'badge'=>'+2.4%', 'bclass'=>'text-success'],
        ['label'=>'Active Status',  'value'=>($statMap['Aktif']??0), 'badge'=>round(($statMap['Aktif']??0)/max(1,$totalRows)*100).'%', 'bclass'=>'text-muted'],
        ['label'=>'Graduated',      'value'=>($statMap['Lulus']??0), 'badge'=>round(($statMap['Lulus']??0)/max(1,$totalRows)*100).'%', 'bclass'=>'text-primary'],
        ['label'=>'Dropout',       'value'=>($statMap['DO']??0), 'badge'=>round(($statMap['DO']??0)/max(1,$totalRows)*100).'%', 'bclass'=>'text-danger'],   
    ];
    foreach ($miniStats as $ms):
    ?>
    <div class="col-6 col-md-3">
        <div class="stat-card py-3">
            <div class="stat-label"><?= $ms['label'] ?></div>
            <div class="d-flex align-items-baseline gap-2">
                <div class="stat-value" style="font-size:1.6rem"><?= number_format($ms['value']) ?></div>
                <?php if ($ms['badge']): ?>
                <span class="small <?= $ms['bclass'] ?>"><?= $ms['badge'] ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Table card -->
<div class="content-card">
    <!-- Search & Filter bar -->
    <form method="GET" action="" class="row g-2 mb-3" id="filterForm">
        <div class="col-12 col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                       class="form-control border-start-0 ps-0"
                       placeholder="Filter by Name or NIM..."
                       id="searchInput">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="fak" class="form-select" onchange="this.form.submit()">
                <option value="0">Faculty</option>
                <?php $fakultasList->data_seek(0); while ($f = $fakultasList->fetch_assoc()): ?>
                <option value="<?= $f['id_fakultas'] ?>" <?= $filterFak==$f['id_fakultas']?'selected':'' ?>>
                    <?= htmlspecialchars($f['nama_fakultas']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select name="pro" class="form-select" onchange="this.form.submit()">
                <option value="0">Prodi</option>
                <?php while ($pr = $prodiList->fetch_assoc()): ?>
                <option value="<?= $pr['id_prodi'] ?>" <?= $filterPro==$pr['id_prodi']?'selected':'' ?>>
                    <?= htmlspecialchars($pr['nama_prodi']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-12 col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm px-3"><i class="bi bi-funnel"></i></button>
            <a href="<?= BASE_URL ?>/pages/data_mahasiswa.php" class="btn btn-outline-secondary btn-sm px-2"><i class="bi bi-x"></i></a>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-clean mb-0">
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Name</th>
                    <th>Study Program (Prodi)</th>
                    <th>Year</th>
                    <th>Status</th>
                    <?php if (isAdmin()): ?><th class="text-end">Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows->num_rows === 0): ?>
                <tr><td colspan="6" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-3 d-block mb-2"></i> No records found.
                </td></tr>
                <?php else: while ($row = $rows->fetch_assoc()): ?>
                <tr>
                    <td class="font-monospace small"><?= htmlspecialchars($row['NIM']) ?></td>
                    <td>
                        <a href="<?= BASE_URL ?>/pages/semester_progress.php?nim=<?= urlencode($row['NIM']) ?>">
                            <?= htmlspecialchars($row['nama']) ?>
                        </a>
                    </td>
                    <td class="text-muted small"><?= htmlspecialchars($row['nama_prodi']) ?></td>
                    <td><?= htmlspecialchars($row['angkatan']) ?></td>
                    <td><?php
                        $s = $row['status_aktif'];
                    // Kita langsung petakan status ke kelas warna bawaan Bootstrap
                        $bgCls = ['Aktif'=>'bg-success', 'Cuti'=>'bg-warning', 'Probation'=>'bg-warning', 'Lulus'=>'bg-primary', 'DO'=>'bg-danger'][$s] ?? 'bg-success';
                        echo "<span class='badge $bgCls text-white px-2 py-1'>$s</span>";
                    ?></td>
                    <?php if (isAdmin()): ?>
                    <td class="text-end">
                        <a href="<?= BASE_URL ?>/pages/edit_mahasiswa.php?nim=<?= urlencode($row['NIM']) ?>"
                           class="btn btn-sm btn-outline-secondary me-1" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/pages/hapus_mahasiswa.php?nim=<?= urlencode($row['NIM']) ?>"
                           class="btn btn-sm btn-outline-danger btn-delete"
                           data-name="<?= htmlspecialchars($row['nama']) ?>"
                           title="Hapus">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mt-3 gap-2">
        <small class="text-muted">
            Showing <?= min(($page-1)*$perPage+1, $totalRows) ?>–<?= min($page*$perPage, $totalRows) ?>
            of <?= number_format($totalRows) ?> entries
        </small>
        <?php if ($totalPages > 1): ?>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $page<=1?'disabled':'' ?>">
                    <a class="page-link" href="?q=<?= urlencode($search) ?>&fak=<?= $filterFak ?>&pro=<?= $filterPro ?>&page=<?= $page-1 ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php for ($p = max(1,$page-2); $p <= min($totalPages,$page+2); $p++): ?>
                <li class="page-item <?= $p==$page?'active':'' ?>">
                    <a class="page-link" href="?q=<?= urlencode($search) ?>&fak=<?= $filterFak ?>&pro=<?= $filterPro ?>&page=<?= $p ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>">
                    <a class="page-link" href="?q=<?= urlencode($search) ?>&fak=<?= $filterFak ?>&pro=<?= $filterPro ?>&page=<?= $page+1 ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<script>
// Live search with debounce (addEventListener — bukan onclick)
(function(){
    const input = document.getElementById('searchInput');
    const form  = document.getElementById('filterForm');
    if (!input || !form) return;
    let timer;
    input.addEventListener('input', function(){
        clearTimeout(timer);
        timer = setTimeout(function(){ form.submit(); }, 500);
    });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
