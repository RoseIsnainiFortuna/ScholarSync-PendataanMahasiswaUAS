<?php
// ============================================================
// pages/mahasiswa/student_data.php — Data Pribadi & Daftar Mahasiswa
// ============================================================
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireMahasiswa();

$pageTitle  = 'Student Data';
$activePage = 'data';

$nimSession = sessionNIM();

// ---- Ambil data pribadi mahasiswa ----
$myData = [];
$photoUrl = null;
if ($nimSession) {
    $s = $conn->prepare("SELECT m.*, p.nama_prodi FROM mahasiswa m 
                         LEFT JOIN prodi p ON m.id_prodi = p.id_prodi 
                         WHERE m.NIM = ? LIMIT 1");
    $s->bind_param('s', $nimSession);
    $s->execute();
    $myData = $s->get_result()->fetch_assoc() ?: [];
    $s->close();
    if (!empty($myData['NIM'])) {
        $photoUrl = mahasiswaPhotoUrl($myData['NIM']);
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- ===== Student Data Page ===== -->
<div class="page-header">
    <h1>Data Pribadi & Daftar Mahasiswa</h1>
    <p>Lihat informasi akademik Anda dan data mahasiswa lainnya.</p>
</div>

<div class="row g-3">
    <!-- Bagian Kiri: Data Pribadi Mahasiswa -->
    <div class="col-12 col-lg-4">
        <div class="content-card">
            <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-person-vcard text-primary-custom"></i> Data Pribadi Anda
            </h6>

            <?php if (!empty($myData['nama'])): ?>
            <div class="student-profile">
                <!-- Avatar -->
                <div class="d-flex justify-content-center mb-3">
                    <?php if (!empty($photoUrl)): ?>
                        <div class="avatar-large overflow-hidden">
                            <img src="<?= htmlspecialchars($photoUrl) ?>" alt="Foto Profil" class="w-100 h-100" style="object-fit:cover;">
                        </div>
                    <?php else: ?>
                        <div class="avatar-large d-flex align-items-center justify-content-center">
                            <?= strtoupper(substr($myData['nama'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="text-center mb-3 pb-3 border-bottom">
                    <div class="fw-bold fs-5 mb-1"><?= htmlspecialchars($myData['nama']) ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($myData['NIM']) ?></div>
                </div>

                <!-- Details -->
                <div class="student-details mb-3">
                    <div class="detail-item mb-3">
                        <label class="text-muted small fw-semibold d-block mb-1">Program Studi</label>
                        <div><?= htmlspecialchars($myData['nama_prodi'] ?? 'N/A') ?></div>
                    </div>

                    <div class="detail-item mb-3">
                        <label class="text-muted small fw-semibold d-block mb-1">Angkatan</label>
                        <div><?= $myData['angkatan'] ?? 'N/A' ?></div>
                    </div>

                    <div class="detail-item mb-3">
                        <label class="text-muted small fw-semibold d-block mb-1">Status</label>
                        <div>
                            <?php
                                $s = $myData['status_aktif'] ?? 'Aktif';
                            // Pemetaan ringkas: Aktif & Lulus ikut CSS Anda, DO dipaksa pakai kelas 'do' (merah)
                            $cls = [
                                'Aktif' => 'aktif',
                                'Lulus' => 'lulus',
                                'DO'    => 'do'
                            ];
                            $badgeClass = $cls[$s] ?? 'aktif';
                            ?>
                            <span class="badge-<?= $badgeClass ?>"><?= htmlspecialchars($s) ?></span>
                        </div>
                    </div>

                </div>
            </div>

            <a href="<?= BASE_URL ?>/pages/mahasiswa/form.php" class="btn btn-primary-custom w-100 mt-3">
                <i class="bi bi-pencil me-1"></i> Edit Data
            </a>

            <?php else: ?>
            <div class="alert alert-warning d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle"></i>
                <div>
                    <strong>Data belum lengkap!</strong> Silakan lengkapi profil Anda terlebih dahulu.
                </div>
            </div>
            <a href="<?= BASE_URL ?>/pages/mahasiswa/form.php" class="btn btn-primary-custom w-100">
                <i class="bi bi-pencil me-1"></i> Lengkapi Data
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bagian Kanan: Daftar Mahasiswa Lain -->
    <div class="col-12 col-lg-8">
        <div class="content-card">
            <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-people text-primary-custom"></i> Daftar Mahasiswa
            </h6>

            <?php
            // Query untuk ambil semua mahasiswa (exclude current user)
            $students = $conn->query("
                SELECT m.NIM, m.nama, p.nama_prodi, m.angkatan, m.status_aktif 
                FROM mahasiswa m
                LEFT JOIN prodi p ON m.id_prodi = p.id_prodi
                WHERE m.NIM != '$nimSession'
                ORDER BY m.nama ASC
            ");

            if ($students && $students->num_rows > 0):
            ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:25%">Nama Lengkap</th>
                            <th style="width:35%">Program Studi</th>
                            <th style="width:15%">Angkatan</th>
                            <th style="width:25%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($student = $students->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($student['nama']) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($student['NIM']) ?></div>
                            </td>
                            <td>
                                <div class="small"><?= htmlspecialchars($student['nama_prodi'] ?? 'N/A') ?></div>
                            </td>
                            <td>
                                <div class="small"><?= $student['angkatan'] ?? 'N/A' ?></div>
                            </td>
                            <td>
                                <span class="badge bg-<?php
                                    $statusColor = match($student['status_aktif'] ?? 'Aktif') {
                                        'Aktif' => 'success',
                                        'Lulus' => 'info',
                                        'DO' => 'danger',
                                        default => 'secondary'
                                    };
                                    echo $statusColor;
                                ?>">
                                    <?= htmlspecialchars($student['status_aktif'] ?? 'Aktif') ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <?php else: ?>
            <div class="alert alert-info text-center py-4">
                <i class="bi bi-info-circle fs-5 mb-2 d-block"></i>
                <p class="mb-0">Belum ada data mahasiswa lain untuk ditampilkan.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
