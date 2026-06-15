<?php
/**
 * generate_password.php
 * Jalankan SEKALI via browser: http://localhost/scholarsync/generate_password.php
 * Lalu copy hash-nya ke database.sql atau langsung ke DB.
 * HAPUS file ini setelah selesai!
 */
$plain = 'admin123';
$hash  = password_hash($plain, PASSWORD_DEFAULT);
echo "<pre>Password : $plain\nHash     : $hash\n\n";
echo "UPDATE users SET password='$hash' WHERE username='admin';</pre>";
