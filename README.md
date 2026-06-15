# ScholarSync Academic
**Aplikasi Pendataan Mahasiswa** — UAS Praktikum Pemrograman Web 1 (SVPL214208)
Universitas Gadjah Mada · Sekolah Vokasi · TA Genap 2025/2026

---

## Deskripsi
ScholarSync Academic adalah aplikasi web berbasis PHP untuk manajemen data mahasiswa.
Fitur utama: CRUD mahasiswa, tracking semester IPK per mahasiswa, manajemen fakultas dan prodi, serta autentikasi berbasis session dengan role admin/mahasiswa.

---

## Teknologi
- PHP 8.x + MySQLi (prepared statements)
- MySQL / MariaDB via Laragon
- Bootstrap 5.3 + Bootstrap Icons
- Chart.js 4 (grafik tren IPK)
- Vanilla JavaScript (validasi form, DOM manipulation, event listener)

---

## Struktur Folder
```
scholarsync/
├── assets/
│   ├── css/style.css
│   └── js/app.js
├── includes/
│   ├── config.php       ← koneksi DB (exclude git)
│   ├── auth.php         ← helper session/role
│   ├── header.php       ← sidebar + topbar
│   └── footer.php       ← script tags + closing HTML
├── pages/
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── data_mahasiswa.php
│   ├── tambah_mahasiswa.php
│   ├── edit_mahasiswa.php
│   ├── hapus_mahasiswa.php
│   ├── semester_progress.php
│   └── management.php
├── index.php
├── database.sql
├── generate_password.php  ← HAPUS setelah setup
├── .gitignore
└── README.md
```

---

## Cara Menjalankan

### 1. Prasyarat
- Laragon (atau XAMPP) sudah terinstall dan berjalan
- PHP ≥ 8.0, MySQL/MariaDB aktif

### 2. Clone / salin project
```bash
# Jika pakai Git:
git clone <repo-url> C:/laragon/www/scholarsync

# Atau ekstrak ZIP ke:
C:/laragon/www/scholarsync
```

### 3. Import Database
Buka **phpMyAdmin** → tab **SQL** → paste isi `database.sql` → Execute.

Atau via terminal:
```bash
mysql -u root -p < database.sql
```

### 4. Konfigurasi
Edit `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // sesuaikan password Laragon kamu
define('DB_NAME', 'scholarsync');
define('BASE_URL', 'http://localhost/scholarsync');
```

### 5. Generate Password Admin
Buka browser: `http://localhost/scholarsync/generate_password.php`
Copy hasil hash, jalankan query UPDATE yang ditampilkan di phpMyAdmin.
**Hapus file `generate_password.php` setelah selesai!**

### 6. Akses Aplikasi
`http://localhost/scholarsync`

Login default:
- Username: `admin`
- Password: `admin123`

---

## Fitur Checklist UAS

| Fitur | Status |
|---|---|
| Halaman Beranda / Dashboard | ✅ |
| Halaman Daftar Data + Search + Pagination | ✅ |
| Form Tambah Data (CREATE) | ✅ |
| Form Edit Data (UPDATE) | ✅ |
| Hapus Data + JS confirm() | ✅ |
| Autentikasi Login + session | ✅ |
| Responsif Mobile (Bootstrap) | ✅ |
| password_hash / password_verify | ✅ |
| htmlspecialchars semua output | ✅ |
| Prepared Statement MySQLi | ✅ |
| config.php terpisah | ✅ |
| .gitignore (exclude config.php) | ✅ |

---

## Screenshot
*(Tambahkan screenshot ke folder `assets/img/screenshots/` dan link di sini)*

---

## Author
**[Nama Mahasiswa]** — NIM: [NIM]
Dosen: Achmad Choirudin Emcha, S.Kom., M.Eng.
