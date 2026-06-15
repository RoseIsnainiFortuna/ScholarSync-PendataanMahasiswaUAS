<?php
require_once __DIR__ . '/includes/config.php';

$fakultas = [
    'Fakultas Biologi',
    'Fakultas Farmasi',
    'Fakultas Geografi',
    'Fakultas Kedokteran, Kesehatan Masyarakat, dan Keperawatan (FKKMK)',
    'Fakultas Kedokteran Gigi',
    'Fakultas Kedokteran Hewan',
    'Fakultas Matematika dan Ilmu Pengetahuan Alam (FMIPA)',
    'Fakultas Pertanian',
    'Fakultas Kehutanan',
    'Fakultas Teknologi Pertanian',
    'Fakultas Teknik',
    'Fakultas Ekonomika dan Bisnis (FEB)',
    'Fakultas Hukum',
    'Fakultas Ilmu Budaya (FIB)',
    'Fakultas Ilmu Sosial dan Ilmu Politik (FISIPOL)',
    'Fakultas Filsafat',
    'Fakultas Psikologi',
    'Sekolah Vokasi',
];

$prodiData = [
    'Fakultas Biologi' => ['Biologi'],
    'Fakultas Farmasi' => ['Farmasi'],
    'Fakultas Geografi' => ['Geografi Lingkungan', 'Kartografi dan Penginderaan Jauh', 'Pembangunan Wilayah'],
    'Fakultas Kedokteran, Kesehatan Masyarakat, dan Keperawatan (FKKMK)' => ['Kedokteran', 'Ilmu Keperawatan', 'Gizi Kesehatan'],
    'Fakultas Kedokteran Gigi' => ['Kedokteran Gigi', 'Hygiene Gigi'],
    'Fakultas Kedokteran Hewan' => ['Kedokteran Hewan'],
    'Fakultas Matematika dan Ilmu Pengetahuan Alam (FMIPA)' => ['Matematika', 'Fisika', 'Kimia', 'Ilmu Komputer', 'Statistika', 'Geofisika', 'Elektronika dan Instrumentasi', 'Aktuaria'],
    'Fakultas Pertanian' => ['Agronomi', 'Ilmu Tanah', 'Proteksi Tanaman', 'Mikrobiologi Pertanian', 'Penyuluhan dan Komunikasi Pertanian', 'Ekonomi Pertanian dan Agribisnis', 'Akuakultur (Budidaya Perikanan)', 'Teknologi Hasil Perikanan', 'Manajemen Sumberdaya Akuatik'],
    'Fakultas Kehutanan' => ['Kehutanan'],
    'Fakultas Teknologi Pertanian' => ['Teknik Pertanian', 'Teknologi Pangan dan Hasil Pertanian', 'Teknik Industri Pertanian'],
    'Fakultas Teknik' => ['Teknik Sipil', 'Teknik Mesin', 'Teknik Elektro', 'Teknik Kimia', 'Arsitektur', 'Perencanaan Wilayah dan Kota', 'Teknik Geologi', 'Teknik Geodesi', 'Teknik Nuklir', 'Teknologi Informasi', 'Teknik Industri', 'Teknik Fisika'],
    'Fakultas Ekonomika dan Bisnis (FEB)' => ['Akuntansi', 'Ilmu Ekonomi', 'Manajemen'],
    'Fakultas Hukum' => ['Ilmu Hukum'],
    'Fakultas Ilmu Budaya (FIB)' => ['Sastra Indonesia', 'Sastra Inggris', 'Sastra Arab/Semit', 'Sastra Jepang', 'Sastra Korea', 'Sastra Prancis', 'Sejarah', 'Arkeologi', 'Antropologi', 'Pariwisata', 'Sastra Nusantara'],
    'Fakultas Ilmu Sosial dan Ilmu Politik (FISIPOL)' => ['Ilmu Hubungan Internasional', 'Ilmu Komunikasi', 'Sosiologi', 'Manajemen dan Kebijakan Publik (Administrasi Negara)', 'Politik dan Pemerintahan', 'Pembangunan Sosial dan Kesejahteraan'],
    'Fakultas Filsafat' => ['Ilmu Filsafat'],
    'Fakultas Psikologi' => ['Psikologi'],
    'Sekolah Vokasi' => ['Bahasa Jepang', 'Sistem Informasi Geografis', 'Akuntansi Sektor Publik', 'Bahasa Inggris', 'Bisnis Perjalanan Wisata', 'Manajemen dan Penilaian Properti', 'Manajemen Informasi Kesehatan', 'Pembangunan Ekonomi Kewilayahan', 'Pengelolaan Arsip dan Rekaman Informasi', 'Pengelolaan Hutan', 'Pengembangan Produk Agroindustri', 'Perbankan', 'Teknologi Veteriner', 'Teknik Pengelolaan dan Pemeliharaan Infrastruktur Sipil', 'Teknik Pengelolaan dan Perawatan Alat Berat', 'Teknik Rekayasa Pelaksanaan Bangunan Sipil', 'Teknologi Rekayasa Elektro', 'Teknologi Rekayasa Instrumentasi dan Kontrol', 'Teknologi Rekayasa Internet', 'Teknologi Rekayasa Mesin', 'Teknologi Rekayasa Perangkat Lunak', 'Teknologi Survei dan Pemetaan Dasar'],
];

function findOrCreateFakultas($conn, $nama) {
    $stmt = $conn->prepare('SELECT id_fakultas FROM fakultas WHERE nama_fakultas = ? LIMIT 1');
    $stmt->bind_param('s', $nama);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result) {
        return (int)$result['id_fakultas'];
    }

    $insert = $conn->prepare('INSERT INTO fakultas (nama_fakultas) VALUES (?)');
    $insert->bind_param('s', $nama);
    if (!$insert->execute()) {
        $insert->close();
        throw new RuntimeException('Gagal membuat fakultas: ' . $conn->error);
    }
    $insert->close();

    return (int)$conn->insert_id;
}

function findOrCreateProdi($conn, $nama, $fkId) {
    $stmt = $conn->prepare('SELECT id_prodi FROM prodi WHERE nama_prodi = ? AND id_fakultas = ? LIMIT 1');
    $stmt->bind_param('si', $nama, $fkId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result) {
        return (int)$result['id_prodi'];
    }

    $insert = $conn->prepare('INSERT INTO prodi (nama_prodi, id_fakultas) VALUES (?, ?)');
    $insert->bind_param('si', $nama, $fkId);
    if (!$insert->execute()) {
        $insert->close();
        throw new RuntimeException('Gagal membuat prodi: ' . $conn->error);
    }
    $insert->close();

    return (int)$conn->insert_id;
}

$report = [];
$conn->begin_transaction();
try {
    foreach ($fakultas as $namaFakultas) {
        $id = findOrCreateFakultas($conn, $namaFakultas);
        $report[] = "Fakultas tersimpan: {$namaFakultas} (ID {$id})";
    }

    foreach ($prodiData as $namaFakultas => $prodis) {
        $fkId = findOrCreateFakultas($conn, $namaFakultas);
        foreach ($prodis as $prodiNama) {
            $id = findOrCreateProdi($conn, $prodiNama, $fkId);
            $report[] = "Prodi tersimpan: {$prodiNama} (Fakultas: {$namaFakultas}, ID {$id})";
        }
    }

    $conn->commit();
} catch (Throwable $ex) {
    $conn->rollback();
    http_response_code(500);
    echo '<h1>Seed gagal</h1>';
    echo '<pre>' . htmlspecialchars($ex->getMessage()) . '</pre>';
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Seed Fakultas & Prodi</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; }
        h1 { margin-bottom: 1rem; }
        .success { color: #1a3a5c; }
        .item { margin-bottom: .35rem; }
        .note { margin-top: 1rem; color: #555; }
    </style>
</head>
<body>
    <h1>Seed Fakultas dan Prodi berhasil</h1>
    <div class="success">
        <p>Semua fakultas dan prodi yang diperlukan telah ditambahkan ke database.</p>
    </div>
    <div>
        <?php foreach ($report as $line): ?>
            <div class="item"><?= htmlspecialchars($line) ?></div>
        <?php endforeach; ?>
    </div>
    <div class="note">
        <p>Buka kembali halaman form mahasiswa atau refresh browser Anda untuk melihat daftar lengkap fakultas/prodi.</p>
    </div>
</body>
</html>
