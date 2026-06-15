<?php
// ============================================================
// pages/management.php  — Academic Management (Fakultas + Prodi CRUD)
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pageTitle  = 'Academic Management';
$activePage = 'management';
$errors     = [];
$tab        = $_GET['tab'] ?? 'fakultas'; // 'fakultas' | 'prodi'

// ---- CREATE Fakultas ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_fak') {
    $nama = htmlspecialchars(trim($_POST['nama_fakultas'] ?? ''), ENT_QUOTES, 'UTF-8');
    if ($nama === '') { $errors['nama_fakultas'] = 'Nama fakultas wajib diisi.'; }
    else {
        $ins = $conn->prepare("INSERT INTO fakultas (nama_fakultas) VALUES (?)");
        $ins->bind_param('s', $nama);
        $ins->execute(); $ins->close();
        $_SESSION['flash'] = ['type'=>'success','msg'=>"Fakultas \"$nama\" ditambahkan."];
        header('Location: ' . BASE_URL . '/pages/management.php?tab=fakultas'); exit;
    }
}

// ---- DELETE Fakultas ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'del_fak') {
    $id = (int)$_POST['id_fak'];
    $del = $conn->prepare("DELETE FROM fakultas WHERE id_fakultas=?");
    $del->bind_param('i', $id); $del->execute(); $del->close();
    $_SESSION['flash'] = ['type'=>'success','msg'=>'Fakultas dihapus.'];
    header('Location: ' . BASE_URL . '/pages/management.php?tab=fakultas'); exit;
}

// ---- CREATE Prodi ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_prodi') {
    $namaP  = htmlspecialchars(trim($_POST['nama_prodi'] ?? ''), ENT_QUOTES, 'UTF-8');
    $fakId  = (int)($_POST['id_fakultas'] ?? 0);
    if ($namaP === '')  $errors['nama_prodi']   = 'Nama prodi wajib.';
    if ($fakId === 0)   $errors['id_fakultas2']  = 'Pilih fakultas.';
    if (empty($errors)) {
        $ins = $conn->prepare("INSERT INTO prodi (nama_prodi, id_fakultas) VALUES (?,?)");
        $ins->bind_param('si', $namaP, $fakId); $ins->execute(); $ins->close();
        $_SESSION['flash'] = ['type'=>'success','msg'=>"Prodi \"$namaP\" ditambahkan."];
        header('Location: ' . BASE_URL . '/pages/management.php?tab=prodi'); exit;
    }
    $tab = 'prodi';
}

// ---- DELETE Prodi ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'del_prodi') {
    $id = (int)$_POST['id_prodi'];
    $del = $conn->prepare("DELETE FROM prodi WHERE id_prodi=?");
    $del->bind_param('i', $id); $del->execute(); $del->close();
    $_SESSION['flash'] = ['type'=>'success','msg'=>'Prodi dihapus.'];
    header('Location: ' . BASE_URL . '/pages/management.php?tab=prodi'); exit;
}

// Load data
$fakultasList = $conn->query("SELECT * FROM fakultas ORDER BY nama_fakultas");
$prodiList    = $conn->query("SELECT p.*, f.nama_fakultas FROM prodi p JOIN fakultas f ON p.id_fakultas=f.id_fakultas ORDER BY f.nama_fakultas, p.nama_prodi");
$fakultasDrop = $conn->query("SELECT * FROM fakultas ORDER BY nama_fakultas");

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Academic Management</h1>
    <p>Configure faculties, study programs, and institutional frameworks.</p>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show alert-auto-dismiss">
    <?= htmlspecialchars($flash['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Navigation cards -->
<div class="row g-3 mb-4">
    <?php
    $modules = [
        ['icon'=>'bi-grid-1x2-fill','color'=>'bg-primary-custom text-white','title'=>'Dashboard','sub'=>'View metrics','href'=>BASE_URL.'/pages/dashboard.php'],
        ['icon'=>'bi-people-fill','color'=>'bg-light','title'=>'Student Management','sub'=>'Manage records','href'=>BASE_URL.'/pages/data_mahasiswa.php'],
        ['icon'=>'bi-graph-up-arrow','color'=>'bg-light','title'=>'Semester Progress','sub'=>'Track GPA','href'=>BASE_URL.'/pages/semester_progress.php'],
        ['icon'=>'bi-bank','color'=>'bg-light','title'=>'Academic Management','sub'=>'Current page','href'=>'#'],
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

<!-- Tabs -->
<ul class="nav nav-tabs mb-3" id="mgmtTab">
    <li class="nav-item">
        <a class="nav-link <?= $tab==='fakultas'?'active':'' ?>"
           href="?tab=fakultas">
            <i class="bi bi-building me-1"></i> Fakultas
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab==='prodi'?'active':'' ?>"
           href="?tab=prodi">
            <i class="bi bi-journal-bookmark me-1"></i> Program Studi
        </a>
    </li>
</ul>

<?php if ($tab === 'fakultas'): ?>
<!-- ===== FAKULTAS ===== -->
<div class="row g-3">
    <!-- Add form -->
    <div class="col-12 col-md-4">
        <div class="content-card">
            <h6 class="fw-bold mb-3">Tambah Fakultas</h6>
            <form method="POST" data-validate="true" novalidate>
                <input type="hidden" name="action" value="add_fak">
                <div class="mb-3">
                    <label class="form-label">Nama Fakultas <span class="text-danger">*</span></label>
                    <input type="text" name="nama_fakultas"
                           class="form-control <?= isset($errors['nama_fakultas'])?'is-invalid':'' ?>"
                           placeholder="Contoh: Fakultas Teknik"
                           data-required="Nama fakultas wajib.">
                    <?php if (isset($errors['nama_fakultas'])): ?>
                    <div class="invalid-feedback"><?= $errors['nama_fakultas'] ?></div>
                    <?php else: ?>
                    <div class="field-error" id="err_nama_fakultas"></div>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary-custom w-100">
                    <i class="bi bi-plus-circle me-1"></i> Tambah
                </button>
            </form>
        </div>
    </div>
    <!-- List -->
    <div class="col-12 col-md-8">
        <div class="content-card">
            <h6 class="fw-bold mb-3">Daftar Fakultas (<?= $fakultasList->num_rows ?>)</h6>
            <div class="table-responsive">
                <table class="table table-clean mb-0">
                    <thead><tr><th>#</th><th>Nama Fakultas</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                        <?php $no=1; while ($f = $fakultasList->fetch_assoc()): ?>
                        <tr>
                            <td class="text-muted small"><?= $no++ ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($f['nama_fakultas']) ?></td>
                            <td class="text-end">
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="action" value="del_fak">
                                    <input type="hidden" name="id_fak" value="<?= $f['id_fakultas'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-delete"
                                            data-name="<?= htmlspecialchars($f['nama_fakultas']) ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ===== PRODI ===== -->
<div class="row g-3">
    <div class="col-12 col-md-4">
        <div class="content-card">
            <h6 class="fw-bold mb-3">Tambah Program Studi</h6>
            <form method="POST" data-validate="true" novalidate>
                <input type="hidden" name="action" value="add_prodi">
                <div class="mb-3">
                    <label class="form-label">Fakultas <span class="text-danger">*</span></label>
                    <select name="id_fakultas" class="form-select <?= isset($errors['id_fakultas2'])?'is-invalid':'' ?>"
                            data-required="Pilih fakultas.">
                        <option value="">— Pilih —</option>
                        <?php while ($f = $fakultasDrop->fetch_assoc()): ?>
                        <option value="<?= $f['id_fakultas'] ?>"><?= htmlspecialchars($f['nama_fakultas']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    <?php if (isset($errors['id_fakultas2'])): ?>
                    <div class="invalid-feedback"><?= $errors['id_fakultas2'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Prodi <span class="text-danger">*</span></label>
                    <input type="text" name="nama_prodi"
                           class="form-control <?= isset($errors['nama_prodi'])?'is-invalid':'' ?>"
                           placeholder="Contoh: Teknik Informatika"
                           data-required="Nama prodi wajib.">
                    <?php if (isset($errors['nama_prodi'])): ?>
                    <div class="invalid-feedback"><?= $errors['nama_prodi'] ?></div>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary-custom w-100">
                    <i class="bi bi-plus-circle me-1"></i> Tambah
                </button>
            </form>
        </div>
    </div>
    <div class="col-12 col-md-8">
        <div class="content-card">
            <h6 class="fw-bold mb-3">Daftar Program Studi (<?= $prodiList->num_rows ?>)</h6>
            <div class="table-responsive">
                <table class="table table-clean mb-0">
                    <thead><tr><th>#</th><th>Prodi</th><th>Fakultas</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                        <?php $no=1; while ($p = $prodiList->fetch_assoc()): ?>
                        <tr>
                            <td class="text-muted small"><?= $no++ ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($p['nama_prodi']) ?></td>
                            <td class="text-muted small"><?= htmlspecialchars($p['nama_fakultas']) ?></td>
                            <td class="text-end">
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="action" value="del_prodi">
                                    <input type="hidden" name="id_prodi" value="<?= $p['id_prodi'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-delete"
                                            data-name="<?= htmlspecialchars($p['nama_prodi']) ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
