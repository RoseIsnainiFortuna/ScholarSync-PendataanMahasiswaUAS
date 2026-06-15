<?php
// ============================================================
// pages/mahasiswa/index.php — Redirect to Dashboard
// ============================================================
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireMahasiswa();

// Redirect ke dashboard
header('Location: ' . BASE_URL . '/pages/mahasiswa/dashboard.php');
exit;

