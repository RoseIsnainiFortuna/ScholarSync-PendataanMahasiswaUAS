<?php
// ============================================================
// pages/mahasiswa/form.php — Form Lengkapi Data Mahasiswa
// ============================================================
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireMahasiswa();

$pageTitle  = 'Lengkapi Data';
$activePage = 'form';

$nimSession = sessionNIM();
$success = '';
$error = '';
$warning = '';

$hasFakultasColumn = false;
$columnCheck = $conn->query("SHOW COLUMNS FROM mahasiswa LIKE 'id_fakultas'");
if ($columnCheck && $columnCheck->num_rows > 0) {
    $hasFakultasColumn = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = $nimSession ?? trim($_POST['NIM'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $id_fakultas = (int)($_POST['id_fakultas'] ?? 0);
    $id_prodi = (int)($_POST['id_prodi'] ?? 0);
    $angkatan = (int)($_POST['angkatan'] ?? date('Y'));
    $status_aktif = $_POST['status_aktif'] ?? 'Aktif';
    $ipk = floatval($_POST['ipk'] ?? 0.00);

    if ($nim === '' || $nama === '') {
        $error = 'NIM dan Nama wajib diisi.';
    } elseif ($id_fakultas <= 0) {
        $error = 'Fakultas harus dipilih.';
    } elseif ($id_prodi <= 0) {
        $error = 'Program studi harus dipilih.';
    } elseif ($ipk < 0.00 || $ipk > 4.00) { // <--- TAMBAHKAN VALIDASI DI SINI
        $error = 'IPK harus berada di antara 0.00 sampai 4.00.';
    } else {
        $stmt = $conn->prepare("INSERT INTO mahasiswa (NIM, nama, id_prodi, angkatan, status_aktif, ipk)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE nama = VALUES(nama), id_prodi = VALUES(id_prodi), 
            angkatan = VALUES(angkatan), status_aktif = VALUES(status_aktif), ipk = VALUES(ipk)");
        // types: NIM(s), nama(s), id_prodi(i), angkatan(i), status_aktif(s), ipk(f)
        $stmt->bind_param('ssiisd', $nim, $nama, $id_prodi, $angkatan, $status_aktif, $ipk);
        if ($stmt->execute()) {
                if (empty($nimSession) && $nim !== '') {
                    $_SESSION['NIM'] = $nim;
                    if (!empty($_SESSION['user_id'])) {
                        $u = $conn->prepare('UPDATE users SET NIM = ? WHERE id_users = ?');
                        $u->bind_param('si', $nim, $_SESSION['user_id']);
                        $u->execute();
                        $u->close();
                    }
                }
                $_SESSION['nama'] = $nama;

                // Proses upload foto jika ada
                $croppedFotoData = trim($_POST['foto_cropped'] ?? '');
                $photoSaved = false;
                $safeUploadNIM = preg_replace('/[^A-Za-z0-9_-]/', '', $nim);
                if ($safeUploadNIM === '') {
                    $warning = 'NIM tidak valid untuk menyimpan foto profil.';
                }

                if ($croppedFotoData !== '' && $safeUploadNIM !== '') {
                    if (preg_match('/^data:(image\/(?:jpeg|png));base64,(.+)$/', $croppedFotoData, $matches)) {
                        $mimeType = $matches[1];
                        $decoded = base64_decode($matches[2]);
                        $allowed = ['image/jpeg' => '.jpg', 'image/png' => '.png'];

                        if ($decoded === false) {
                            $warning = 'Foto crop tidak dapat diproses.';
                        } elseif (!isset($allowed[$mimeType])) {
                            $warning = 'Format foto tidak valid. Hanya JPG/PNG yang diperbolehkan.';
                        } elseif (strlen($decoded) > 2 * 1024 * 1024) {
                            $warning = 'Ukuran foto terlalu besar. Maksimal 2MB.';
                        } else {
                            $uploadDir = __DIR__ . '/../../uploads/mahasiswa';
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0755, true);
                            }
                            $ext = $allowed[$mimeType];
                            $destPath = $uploadDir . '/' . $safeUploadNIM . $ext;

                            foreach (['.jpg', '.jpeg', '.png'] as $oldExt) {
                                if ($oldExt !== $ext) {
                                    @unlink($uploadDir . '/' . $safeUploadNIM . $oldExt);
                                }
                            }

                            if (file_put_contents($destPath, $decoded) === false) {
                                $warning = 'Foto gagal disimpan. Pastikan folder upload dapat ditulisi.';
                            } else {
                                $photoSaved = true;
                            }
                        }
                    } else {
                        $warning = 'Data foto crop tidak valid.';
                    }
                }

                if (!$photoSaved && isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE && $safeUploadNIM !== '') {
                    if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                        $warning = 'Foto tidak dapat diunggah. Silakan coba lagi.';
                    } else {
                        $tmpName = $_FILES['foto']['tmp_name'];
                        $fileSize = $_FILES['foto']['size'];
                        $mimeType = mime_content_type($tmpName);
                        $allowed = ['image/jpeg' => '.jpg', 'image/png' => '.png'];

                        if (!isset($allowed[$mimeType])) {
                            $warning = 'Format foto tidak valid. Hanya JPG/PNG yang diperbolehkan.';
                        } elseif ($fileSize > 2 * 1024 * 1024) {
                            $warning = 'Ukuran foto terlalu besar. Maksimal 2MB.';
                        } else {
                            $uploadDir = __DIR__ . '/../../uploads/mahasiswa';
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0755, true);
                            }
                            $ext = $allowed[$mimeType];
                            $destPath = $uploadDir . '/' . $safeUploadNIM . $ext;

                            foreach (['.jpg', '.jpeg', '.png'] as $oldExt) {
                                if ($oldExt !== $ext) {
                                    @unlink($uploadDir . '/' . $safeUploadNIM . $oldExt);
                                }
                            }

                            if (!move_uploaded_file($tmpName, $destPath)) {
                                $warning = 'Foto gagal disimpan. Pastikan folder upload dapat ditulisi.';
                            }
                        }
                    }
                }

                header('Location: ' . BASE_URL . '/pages/mahasiswa/dashboard.php');
                exit();
            } else {
                $error = 'Terjadi kesalahan saat menyimpan data: ' . htmlspecialchars($stmt->error);

                // Tambahkan debug detail (hanya tampil jika ?debug=1 atau user adalah admin)
                $debugHtml = '';
                if ((isset($_GET['debug']) && $_GET['debug'] === '1') || (function_exists('isAdmin') && isAdmin())) {
                    $postDump = htmlspecialchars(var_export($_POST, true));
                    $sessDump = htmlspecialchars(var_export($_SESSION ?? [], true));
                    $stmtErr  = htmlspecialchars($stmt->error);
                    $debugHtml = '<div class="alert alert-danger mt-2"><strong>Debug info:</strong><pre style="white-space:pre-wrap;">'
                        ."POST:\n".$postDump."\n\nSESSION:\n".$sessDump."\n\nstmt->error:\n".$stmtErr.'</pre></div>';
                }
            }
        $stmt->close();
    }
}

// Ambil data existing untuk prefill
$prefill = ['NIM' => $nimSession ?? '', 'nama' => '', 'id_fakultas' => 0, 'id_prodi' => 0, 'angkatan' => date('Y'), 'status_aktif' => 'Aktif', 'ipk' => '0.00'];
if ($prefill['NIM']) {
    if ($hasFakultasColumn) {
        $s = $conn->prepare("SELECT NIM, nama, id_fakultas, id_prodi, angkatan, status_aktif, ipk FROM mahasiswa WHERE NIM = ? LIMIT 1");
    } else {
        $s = $conn->prepare("SELECT NIM, nama, id_prodi, angkatan, status_aktif, ipk FROM mahasiswa WHERE NIM = ? LIMIT 1");
    }
    $s->bind_param('s', $prefill['NIM']);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    if ($row) {
        if (!$hasFakultasColumn) {
            $row['id_fakultas'] = 0;
        }
        $prefill = array_merge($prefill, $row);
    }
    $s->close();
}

// Ambil list fakultas dan prodi
$fakultasList = $conn->query("SELECT id_fakultas, nama_fakultas FROM fakultas ORDER BY nama_fakultas");
$prodiList = $conn->query("SELECT p.id_prodi, p.nama_prodi, p.id_fakultas, f.nama_fakultas FROM prodi p JOIN fakultas f ON p.id_fakultas=f.id_fakultas ORDER BY f.nama_fakultas, p.nama_prodi");

$photoUrl = $prefill['NIM'] ? mahasiswaPhotoUrl($prefill['NIM']) : null;

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- ===== Form Lengkapi Data ===== -->
<div class="page-header">
    <h1>Lengkapi Data Pribadi</h1>
    <p>Isi informasi akademik dan kontak Anda dengan lengkap.</p>
</div>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
    <i class="bi bi-check-circle fs-5"></i>
    <div>
        <strong>Berhasil!</strong> <?= htmlspecialchars($success) ?>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($warning): ?>
<div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
    <i class="bi bi-exclamation-triangle fs-5"></i>
    <div>
        <strong>Perhatian:</strong> <?= htmlspecialchars($warning) ?>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
    <i class="bi bi-exclamation-triangle fs-5"></i>
    <div>
        <strong>Error!</strong> <?= htmlspecialchars($error) ?>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (!empty($debugHtml)): ?>
    <?= $debugHtml /* intentionally not escaped for readable debug */ ?>
<?php endif; ?>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="content-card">
            <h6 class="fw-bold mb-4">
                <i class="bi bi-pencil-square text-primary-custom me-2"></i> Form Data Mahasiswa
            </h6>

            <form method="POST" action="" enctype="multipart/form-data">
                <!-- NIM dan Nama -->
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">NIM <span class="text-danger">*</span></label>
                        <input type="text" name="NIM" class="form-control" 
                               value="<?= htmlspecialchars($prefill['NIM']) ?>" 
                               <?= $nimSession ? 'readonly' : '' ?> 
                               required>
                        <small class="text-muted">Nomor Induk Mahasiswa (tidak dapat diubah)</small>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" 
                               value="<?= htmlspecialchars($prefill['nama']) ?>" 
                               required>
                        <small class="text-muted">Nama lengkap sesuai identitas</small>
                    </div>
                </div>
        
                <!-- Foto Profil -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Foto Profil</label>
                    <input type="file" id="fotoInput" name="foto" class="form-control" accept="image/jpeg,image/png">
                    <input type="hidden" name="foto_cropped" id="fotoCroppedData" value="">
                    <div class="form-text">Unggah foto JPG/PNG maksimal 2MB. Atur ukuran dan crop sebelum submit.</div>
                    <?php if (!empty($photoUrl)): ?>
                    <div class="mt-3">
                        <img src="<?= htmlspecialchars($photoUrl) ?>" alt="Foto Profil" class="img-thumbnail" style="max-width:180px; height:auto;">
                    </div>
                    <?php endif; ?>
                </div>

                <div id="photoCropPanel" class="mb-3 d-none">
                    <div class="photo-crop-preview mb-2 position-relative">
                        <canvas id="photoCropCanvas" width="280" height="280" class="w-100 rounded"></canvas>
                        <span class="photo-crop-hint position-absolute top-50 start-50 translate-middle text-white small px-2 py-1 bg-dark bg-opacity-50 rounded">Geser dan zoom untuk menyesuaikan</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label mb-1">Zoom</label>
                            <input type="range" id="photoZoom" class="form-range" min="1" max="3" step="0.05" value="1">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label mb-1">Putar</label>
                            <input type="range" id="photoRotate" class="form-range" min="0" max="360" step="1" value="0">
                        </div>
                    </div>
                    <div class="form-text mt-2">Drag foto untuk memindahkan area crop. Zoom dan rotate tersedia.</div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Fakultas</label>
                        <select id="id_fakultas" name="id_fakultas" class="form-select" required>
                            <option value="">Pilih Fakultas</option>
                            <?php while ($fakultas = $fakultasList->fetch_assoc()): ?>
                            <option value="<?= $fakultas['id_fakultas'] ?>" <?= $prefill['id_fakultas'] == $fakultas['id_fakultas'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($fakultas['nama_fakultas']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                        <small class="text-muted">Pilih fakultas terlebih dahulu</small>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Program Studi</label>
                        <select id="id_prodi" name="id_prodi" class="form-select" required>
                            <option value="">Pilih Program Studi</option>
                            <?php
                            $currentFaculty = '';
                            while ($prodi = $prodiList->fetch_assoc()):
                                $dataFak = (string)$prodi['id_fakultas'];
                                if ($currentFaculty !== $prodi['nama_fakultas']):
                                    if ($currentFaculty !== ''): ?>
                                    </optgroup>
                                    <?php endif; ?>
                                    <optgroup label="<?= htmlspecialchars($prodi['nama_fakultas']) ?>">
                                    <?php $currentFaculty = $prodi['nama_fakultas'];
                                endif; ?>
                                <option value="<?= $prodi['id_prodi'] ?>" data-fakultas="<?= htmlspecialchars($dataFak) ?>" <?= $prefill['id_prodi'] == $prodi['id_prodi'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($prodi['nama_prodi']) ?>
                                </option>
                            <?php endwhile; if ($currentFaculty !== ''): ?>
                                    </optgroup>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted">Program studi Anda</small>
                    </div>
                    <div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <label for="angkatan" class="form-label fw-semibold">Tahun Angkatan <span class="text-danger">*</span></label>
        <input type="number" id="angkatan" name="angkatan" class="form-control" value="<?= htmlspecialchars($prefill['angkatan'] ?? date('Y')) ?>" required>
    </div>
    
    <div class="col-12 col-md-4">
        <label for="status_aktif" class="form-label fw-semibold">Status Mahasiswa <span class="text-danger">*</span></label>
        <select class="form-select" id="status_aktif" name="status_aktif" required>
            <option value="Aktif" <?= (isset($prefill['status_aktif']) && $prefill['status_aktif'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
            <option value="Lulus" <?= (isset($prefill['status_aktif']) && $prefill['status_aktif'] == 'Lulus') ? 'selected' : '' ?>>Lulus</option>
            <option value="DO" <?= (isset($prefill['status_aktif']) && $prefill['status_aktif'] == 'DO') ? 'selected' : '' ?>>DO</option>
        </select>
    </div>

    <div class="col-12 col-md-4">
        <label for="ipk" class="form-label fw-semibold">IPK Terakhir <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0.00" max="4.00" id="ipk" name="ipk" 
               class="form-control" 
               placeholder="Contoh: 3.50"
               value="<?= htmlspecialchars($prefill['ipk'] ?? '0.00') ?>" required>
        <small class="text-muted">Gunakan titik (.) untuk desimal</small>
    </div>
</div>

                <!-- Buttons -->
                <div class="d-flex gap-2 pt-3">
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-check-lg me-1"></i> Simpan Data
                    </button>
                    <a href="<?= BASE_URL ?>/pages/mahasiswa/dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Info sidebar -->
    <div class="col-12 col-lg-4">
        <div class="content-card bg-light-custom">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-info-circle text-info me-2"></i> Informasi
            </h6>
            <div class="small text-muted">
                <p class="mb-2">
                    <strong>Mengapa data penting?</strong><br>
                    Data Anda akan membantu admin mengelola database akademik dengan lebih baik.
                </p>
                <p class="mb-2">
                    <strong>Keamanan Data</strong><br>
                    Data Anda dilindungi dan hanya dapat diakses oleh Anda dan admin terkait.
                </p>
                <p class="mb-0">
                    <strong>Perubahan Data</strong><br>
                    Anda dapat mengubah data kapan saja dengan kembali ke form ini.
                </p>
            </div>
        </div>

        <div class="content-card mt-3">
            <h6 class="fw-bold mb-2 d-flex align-items-center gap-2">
                <i class="bi bi-bookmark-check text-success"></i> Checklist
            </h6>
            <div class="small">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" <?= !empty($prefill['nama']) ? 'checked' : '' ?> disabled>
                    <label class="form-check-label">Nama lengkap terisi</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" <?= !empty($prefill['id_prodi']) ? 'checked' : '' ?> disabled>
                    <label class="form-check-label">Program studi dipilih</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" <?= !empty($prefill['angkatan']) ? 'checked' : '' ?> disabled>
                    <label class="form-check-label">Angkatan diatur</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" <?= !empty($prefill['status_aktif']) ? 'checked' : '' ?> disabled>
                    <label class="form-check-label">Status aktif disetel</label>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('id_fakultas').addEventListener('change', function() {
    const fakultasId = this.value;
    const prodiSelect = document.getElementById('id_prodi');
    const groups = prodiSelect.getElementsByTagName('optgroup');
    const options = prodiSelect.querySelectorAll('option:not([value=""])');

    // Reset pilihan prodi ke default kosong
    prodiSelect.value = "";

    if (fakultasId === "") {
        // Jika tidak ada fakultas yang dipilih, sembunyikan semua optgroup
        for (let group of groups) {
            group.style.display = 'none';
        }
    } else {
        // Tampilkan prodi yang id_fakultas-nya cocok, sembunyikan yang lain
        for (let group of groups) {
            let hasVisibleOption = false;
            const groupOptions = group.getElementsByTagName('option');
            
            for (let option of groupOptions) {
                if (option.getAttribute('data-fakultas') === fakultasId) {
                    option.style.display = '';
                    hasVisibleOption = true;
                } else {
                    option.style.display = 'none';
                }
            }

            if (hasVisibleOption) {
                group.style.display = '';
            } else {
                group.style.display = 'none';
            }
        }
    }
});

// Jalankan fungsi sekali saat halaman pertama kali dimuat (untuk keperluan prefill data edit)
window.addEventListener('DOMContentLoaded', (event) => {
    const fakultasSelect = document.getElementById('id_fakultas');
    if(fakultasSelect.value !== "") {
        fakultasSelect.dispatchEvent(new Event('change'));
        // Kembalikan ke nilai prefill prodi semula setelah difilter
        document.getElementById('id_prodi').value = "<?= $prefill['id_prodi'] ?>";
    }
});
</script>
</body>
</html>
