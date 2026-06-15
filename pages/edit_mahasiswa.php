<?php
// ============================================================
// pages/edit_mahasiswa.php  — UPDATE student
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pageTitle  = 'Edit Student';
$activePage = 'data';
$errors     = [];

// Load existing data
$nim = htmlspecialchars(trim($_GET['nim'] ?? ''), ENT_QUOTES, 'UTF-8');
if ($nim === '') {
    header('Location: ' . BASE_URL . '/pages/data_mahasiswa.php');
    exit;
}

// Mengambil data mahasiswa dan id_fakultas lewat JOIN prodi karena tabel mahasiswa tidak punya kolom id_fakultas
$stmt = $conn->prepare(
    "SELECT m.*, p.id_prodi, p.id_fakultas FROM mahasiswa m JOIN prodi p ON m.id_prodi=p.id_prodi WHERE m.NIM=? LIMIT 1"
);
$stmt->bind_param('s', $nim);
$stmt->execute();
$mhs = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$mhs) {
    $_SESSION['flash'] = ['type'=>'danger','msg'=>'Mahasiswa tidak ditemukan.'];
    header('Location: ' . BASE_URL . '/pages/data_mahasiswa.php');
    exit;
}

// Prefill from DB
$old = [
    'nama'       => $mhs['nama'],
    'id_fakultas'=> $mhs['id_fakultas'] ?? 0,
    'id_prodi'   => $mhs['id_prodi'],
    'angkatan'   => $mhs['angkatan'],
    'status'     => $mhs['status_aktif'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = htmlspecialchars(trim($_POST['nama']       ?? ''), ENT_QUOTES, 'UTF-8');
    $id_fakultas= (int)($_POST['id_fakultas'] ?? 0);
    $id_prodi   = (int)($_POST['id_prodi']  ?? 0);
    $angkatan   = (int)($_POST['angkatan']  ?? 0);
    $status     = htmlspecialchars($_POST['status_aktif'] ?? '', ENT_QUOTES, 'UTF-8');
    $old        = compact('nama','id_fakultas','id_prodi','angkatan','status');

    // Validate
    if ($nama === '')        $errors['nama']        = 'Nama wajib diisi.';
    if ($id_fakultas === 0)  $errors['id_fakultas'] = 'Pilih fakultas.';
    if ($id_prodi === 0)     $errors['id_prodi']    = 'Pilih program studi.';
    if ($angkatan < 2000 || $angkatan > (int)date('Y'))
                             $errors['angkatan'] = 'Tahun angkatan tidak valid.';
    $validStatus = ['Aktif','Lulus','DO'];
    if (!in_array($status, $validStatus)) $errors['status_aktif'] = 'Status tidak valid.';

    if (empty($errors)) {
        // PERBAIKAN UTAMA: Menghapus id_fakultas dari klausa UPDATE tabel mahasiswa
        $upd = $conn->prepare(
            "UPDATE mahasiswa SET nama=?, id_prodi=?, angkatan=?, status_aktif=? WHERE NIM=?"
        );
        // Sesuaikan bind_param menjadi 'siiss' (string, integer, integer, string, string)
        $upd->bind_param('siiss', $nama, $id_prodi, $angkatan, $status, $nim);

        if ($upd->execute()) {
            $_SESSION['flash'] = ['type'=>'success','msg'=>"Data $nama ($nim) berhasil diperbarui."];
            header('Location: ' . BASE_URL . '/pages/data_mahasiswa.php');
            exit;
        } else {
            $errors['_global'] = 'Gagal memperbarui data.';
        }
        $upd->close();
    }
}

$fakultasList = $conn->query("SELECT id_fakultas, nama_fakultas FROM fakultas ORDER BY nama_fakultas");
// Memperbaiki reuse objek result dengan membuat query prodi yang bersih
$prodiList = $conn->query("SELECT p.id_prodi, p.nama_prodi, p.id_fakultas, f.nama_fakultas FROM prodi p JOIN fakultas f ON p.id_fakultas=f.id_fakultas ORDER BY f.nama_fakultas, p.nama_prodi");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-1">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/pages/data_mahasiswa.php">Student Data</a></li>
            <li class="breadcrumb-item active">Edit Student</li>
        </ol>
    </nav>
    <h1>Edit Student</h1>
    <p>NIM: <strong><?= htmlspecialchars($nim) ?></strong></p>
</div>

<?php if (isset($errors['_global'])): ?>
<div class="alert alert-danger"><?= $errors['_global'] ?></div>
<?php endif; ?>

<div class="row justify-content-center">
<div class="col-12 col-lg-7">
<div class="content-card">
    <form method="POST" action="" data-validate="true" novalidate>

        <div class="mb-3">
            <label class="form-label">NIM</label>
            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($nim) ?>" readonly>
            <small class="text-muted">NIM tidak dapat diubah.</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" id="nama" name="nama"
                   class="form-control <?= isset($errors['nama'])?'is-invalid':'' ?>"
                   value="<?= htmlspecialchars($old['nama']) ?>"
                   data-required="Nama wajib diisi." data-min-length="3">
            <?php if (isset($errors['nama'])): ?>
            <div class="invalid-feedback"><?= $errors['nama'] ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="id_fakultas" class="form-label">Fakultas <span class="text-danger">*</span></label>
            <select id="id_fakultas" name="id_fakultas" class="form-select <?= isset($errors['id_fakultas'])?'is-invalid':'' ?>" required>
                <option value="0">— Pilih Fakultas —</option>
                <?php while ($f = $fakultasList->fetch_assoc()): ?>
                <option value="<?= $f['id_fakultas'] ?>" <?= $old['id_fakultas'] == $f['id_fakultas'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['nama_fakultas']) ?>
                </option>
                <?php endwhile; ?>
            </select>
            <?php if (isset($errors['id_fakultas'])): ?>
            <div class="invalid-feedback"><?= $errors['id_fakultas'] ?></div>
            <?php endif; ?>
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
                    <?= $old['id_prodi'] == $p['id_prodi'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nama_prodi']) ?>
                </option>
                <?php endwhile; if ($lastFak !== '') echo '</optgroup>'; ?>
            </select>
            <?php if (isset($errors['id_prodi'])): ?>
            <div class="invalid-feedback"><?= $errors['id_prodi'] ?></div>
            <?php endif; ?>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6">
                <label for="angkatan" class="form-label">Tahun Angkatan <span class="text-danger">*</span></label>
                <input type="number" id="angkatan" name="angkatan"
                       class="form-control <?= isset($errors['angkatan'])?'is-invalid':'' ?>"
                       value="<?= $old['angkatan'] ?>"
                       min="2000" max="<?= date('Y') ?>"
                       data-required="Angkatan wajib diisi."
                       data-min="2000" data-max="<?= date('Y') ?>">
                <?php if (isset($errors['angkatan'])): ?>
                <div class="invalid-feedback"><?= $errors['angkatan'] ?></div>
                <?php endif; ?>
            </div>
            <div class="col-6">
                <label for="status_aktif" class="form-label">Status <span class="text-danger">*</span></label>
                <select id="status_aktif" name="status_aktif"
                        class="form-select <?= isset($errors['status_aktif'])?'is-invalid':'' ?>"
                        data-required="Status wajib dipilih.">
                    <?php foreach (['Aktif','Lulus','DO'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $old['status']===$opt?'selected':'' ?>>
                        <?= $opt ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary-custom px-4"
                    onclick="return confirm('Simpan perubahan data <?= htmlspecialchars(addslashes($mhs['nama'])) ?>?')">
                <i class="bi bi-save me-1"></i> Update Student
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