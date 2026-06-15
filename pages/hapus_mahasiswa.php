<?php
// ============================================================
// pages/hapus_mahasiswa.php  — DELETE student
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$nim = htmlspecialchars(trim($_GET['nim'] ?? ''), ENT_QUOTES, 'UTF-8');

if ($nim === '') {
    header('Location: ' . BASE_URL . '/pages/data_mahasiswa.php');
    exit;
}

// Fetch nama for flash message
$stmt = $conn->prepare("SELECT nama FROM mahasiswa WHERE NIM=? LIMIT 1");
$stmt->bind_param('s', $nim);
$stmt->execute();
$mhs = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$mhs) {
    $_SESSION['flash'] = ['type'=>'warning','msg'=>'Mahasiswa tidak ditemukan.'];
    header('Location: ' . BASE_URL . '/pages/data_mahasiswa.php');
    exit;
}

// Delete (FK cascade hapus perkembangan_semester, SET NULL pada users)
$del = $conn->prepare("DELETE FROM mahasiswa WHERE NIM=?");
$del->bind_param('s', $nim);

if ($del->execute()) {
    $_SESSION['flash'] = ['type'=>'success','msg'=>"Mahasiswa {$mhs['nama']} ($nim) berhasil dihapus."];
} else {
    $_SESSION['flash'] = ['type'=>'danger','msg'=>'Gagal menghapus: ' . htmlspecialchars($conn->error)];
}
$del->close();

header('Location: ' . BASE_URL . '/pages/data_mahasiswa.php');
exit;
