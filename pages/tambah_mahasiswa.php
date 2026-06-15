<?php
// ============================================================
// pages/tambah_mahasiswa.php  — CREATE student
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pageTitle  = 'Add Student';
$activePage = 'data';
$errors     = [];
$old        = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize all inputs
    $nim        = htmlspecialchars(trim($_POST['nim']        ?? ''), ENT_QUOTES, 'UTF-8');
    $nama       = htmlspecialchars(trim($_POST['nama']       ?? ''), ENT_QUOTES, 'UTF-8');
    $id_prodi   = (int)($_POST['id_prodi']  ?? 0);
    $angkatan   = (int)($_POST['angkatan']  ?? 0);
    $status     = htmlspecialchars($_POST['status_aktif'] ?? '', ENT_QUOTES, 'UTF-8');
    $old        = compact('nim','nama','id_prodi','angkatan','status');

    // Validate
    if ($nim === '') {
        $errors['nim'] = 'NIM wajib diisi.';
    } elseif (mb_strlen($nim) < 3 || mb_strlen($nim) > 20) {
        $errors['nim'] = 'NIM harus berisi 3–20 karakter.';
    }
    if ($nama === '')        $errors['nama']     = 'Nama wajib diisi.';
    if ($id_prodi === 0)     $errors['id_prodi'] = 'Pilih program studi.';
    if ($angkatan < 2000 || $angkatan > (int)date('Y'))
                             $errors['angkatan'] = 'Tahun angkatan tidak valid.';
    $validStatus = ['Aktif','Cuti','Probation','Lulus','DO'];
    if (!in_array($status, $validStatus)) $errors['status_aktif'] = 'Status tidak valid.';

    // Check duplicate NIM
    if (empty($errors['nim'])) {
        $chk = $conn->prepare("SELECT NIM FROM mahasiswa WHERE NIM = ? LIMIT 1");
        $chk->bind_param('s', $nim);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) $errors['nim'] = 'NIM sudah terdaftar.';
        $chk->close();
    }

    if (empty($errors)) {
        $id_fakultas = 1;
        $fak = $conn->prepare("SELECT id_fakultas FROM prodi WHERE id_prodi = ? LIMIT 1");
        $fak->bind_param('i', $id_prodi);
        $fak->execute();
        $fakRow = $fak->get_result()->fetch_assoc();
        if ($fakRow) {
            $id_fakultas = (int)$fakRow['id_fakultas'];
        }
        $fak->close();

        $stmt = $conn->prepare(
            "INSERT INTO mahasiswa (NIM, nama, id_fakultas, id_prodi, angkatan, status_aktif) VALUES (?,?,?,?,?,?)"
        );
        // types: NIM(s), nama(s), id_fakultas(i), id_prodi(i), angkatan(i), status_aktif(s)
        $stmt->bind_param('ssiiis', $nim, $nama, $id_fakultas, $id_prodi, $angkatan, $status);

        if ($stmt->execute()) {
            $_SESSION['flash'] = ['type'=>'success','msg'=>"Mahasiswa $nama ($nim) berhasil ditambahkan."];
            header('Location: ' . BASE_URL . '/pages/data_mahasiswa.php');
            exit;
        } else {
            $errors['_global'] = 'Gagal menyimpan data: ' . htmlspecialchars($conn->error);
        }
        $stmt->close();
    }
}

$fakultasList = $conn->query("SELECT id_fakultas, nama_fakultas FROM fakultas ORDER BY nama_fakultas");
$prodiList = $conn->query("SELECT p.id_prodi, p.nama_prodi, p.id_fakultas, f.nama_fakultas FROM prodi p JOIN fakultas f ON p.id_fakultas=f.id_fakultas ORDER BY f.nama_fakultas, p.nama_prodi");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-1">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/pages/data_mahasiswa.php">Student Data</a></li>
            <li class="breadcrumb-item active">Add Student</li>
        </ol>
    </nav>
    <h1>Add New Student</h1>
    <p>Register a new student record into the system.</p>
</div>

<?php if (isset($errors['_global'])): ?>
<div class="alert alert-danger"><?= $errors['_global'] ?></div>
<?php endif; ?>

<div class="row justify-content-center">
<div class="col-12 col-lg-7">
<div class="content-card">
    <form method="POST" action="" data-validate="true" novalidate>

        <!-- NIM -->
        <div class="mb-3">
            <label for="nim" class="form-label">NIM <span class="text-danger">*</span></label>
            <input type="text" id="nim" name="nim"
                   class="form-control <?= isset($errors['nim'])?'is-invalid':'' ?>"
                   value="<?= htmlspecialchars($old['nim'] ?? '') ?>"
                   placeholder="Contoh: 2024/10001 atau ABC-123"
                   data-required="NIM wajib diisi."
                   data-min-length="3">
            <?php if (isset($errors['nim'])): ?>
            <div class="invalid-feedback"><?= $errors['nim'] ?></div>
            <?php else: ?>
            <div class="field-error" id="err_nim"></div>
            <?php endif; ?>
        </div>

        <!-- Nama -->
        <div class="mb-3">
            <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" id="nama" name="nama"
                   class="form-control <?= isset($errors['nama'])?'is-invalid':'' ?>"
                   value="<?= htmlspecialchars($old['nama'] ?? '') ?>"
                   placeholder="Nama lengkap mahasiswa"
                   data-required="Nama wajib diisi."
                   data-min-length="3">
            <?php if (isset($errors['nama'])): ?>
            <div class="invalid-feedback"><?= $errors['nama'] ?></div>
            <?php else: ?>
            <div class="field-error" id="err_nama"></div>
            <?php endif; ?>
        </div>

        <!-- Prodi -->
        <div class="mb-3">
            <label for="id_fakultas" class="form-label">Fakultas <span class="text-danger">*</span></label>
            <select id="id_fakultas" name="id_fakultas" class="form-select" required>
                <option value="">— Pilih Fakultas —</option>
                <?php $fList = $fakultasList; while ($f = $fList->fetch_assoc()): ?>
                <option value="<?= $f['id_fakultas'] ?>"><?= htmlspecialchars($f['nama_fakultas']) ?></option>
                <?php endwhile; ?>
            </select>

        </div>

        <div class="mb-3">
            <label for="id_prodi" class="form-label">Program Studi <span class="text-danger">*</span></label>
            <select id="id_prodi" name="id_prodi"
                    class="form-select <?= isset($errors['id_prodi'])?'is-invalid':'' ?>"
                    data-required="Pilih program studi.">
                <option value="">— Pilih Prodi —</option>
                <?php
                $lastFak = '';
                while ($p = $prodiList->fetch_assoc()):
                    $dataFak = (int)$p['id_fakultas'];
                    if ($p['nama_fakultas'] !== $lastFak):
                        if ($lastFak !== '') echo '</optgroup>';
                        echo '<optgroup label="' . htmlspecialchars($p['nama_fakultas']) . '">';
                        $lastFak = $p['nama_fakultas'];
                    endif;
                ?>
                <option value="<?= $p['id_prodi'] ?>" data-fakultas="<?= $dataFak ?>"
                    <?= ($old['id_prodi'] ?? 0) == $p['id_prodi'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nama_prodi']) ?>
                </option>
                <?php endwhile; if ($lastFak !== '') echo '</optgroup>'; ?>
            </select>
            <?php if (isset($errors['id_prodi'])): ?>
            <div class="invalid-feedback"><?= $errors['id_prodi'] ?></div>
            <?php else: ?>
            <div class="field-error" id="err_id_prodi"></div>
            <?php endif; ?>
        </div>

        <!-- Angkatan + Status (row) -->
        <div class="row g-3 mb-4">
            <div class="col-6">
                <label for="angkatan" class="form-label">Tahun Angkatan <span class="text-danger">*</span></label>
                <input type="number" id="angkatan" name="angkatan"
                       class="form-control <?= isset($errors['angkatan'])?'is-invalid':'' ?>"
                       value="<?= htmlspecialchars($old['angkatan'] ?? date('Y')) ?>"
                       min="2000" max="<?= date('Y') ?>"
                       data-required="Angkatan wajib diisi."
                       data-min="2000" data-max="<?= date('Y') ?>">
                <?php if (isset($errors['angkatan'])): ?>
                <div class="invalid-feedback"><?= $errors['angkatan'] ?></div>
                <?php else: ?>
                <div class="field-error" id="err_angkatan"></div>
                <?php endif; ?>
            </div>
            <div class="col-6">
                <label for="status_aktif" class="form-label">Status <span class="text-danger">*</span></label>
                <select id="status_aktif" name="status_aktif"
                        class="form-select <?= isset($errors['status_aktif'])?'is-invalid':'' ?>"
                        data-required="Status wajib dipilih.">
                    <?php foreach (['Aktif','Cuti','Probation','Lulus','DO'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= ($old['status'] ?? 'Aktif')===$opt?'selected':'' ?>>
                        <?= $opt ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['status_aktif'])): ?>
                <div class="invalid-feedback"><?= $errors['status_aktif'] ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary-custom px-4">
                <i class="bi bi-save me-1"></i> Save Student
            </button>
            <a href="<?= BASE_URL ?>/pages/data_mahasiswa.php" class="btn btn-outline-secondary px-4">
                Cancel
            </a>
        </div>
    </form>
</div>
</div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
