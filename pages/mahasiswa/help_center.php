<?php
// ============================================================
// pages/mahasiswa/help_center.php — Help Center / Kontak
// ============================================================
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireMahasiswa();

$pageTitle  = 'Help Center';
$activePage = 'help';

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- ===== Help Center ===== -->
<div class="page-header">
    <h1>Help Center</h1>
    <p>Hubungi kami untuk mendapatkan bantuan dan informasi lebih lanjut.</p>
</div>

<div class="row g-3">
    <!-- Contact Information -->
    <div class="col-12 col-lg-6">
        <div class="content-card">
            <h6 class="fw-bold mb-4">
                <i class="bi bi-telephone-fill text-primary-custom me-2"></i> Informasi Kontak
            </h6>

            <!-- Kantor Pusat -->
            <div class="contact-item mb-4 pb-4 border-bottom">
                <div class="d-flex gap-3">
                    <div class="contact-icon d-flex align-items-center justify-content-center bg-light rounded-3 p-3" style="width:50px; height:50px; flex-shrink:0">
                        <i class="bi bi-building text-primary-custom fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1">Kantor Pusat</h6>
                        <p class="mb-1 text-muted small">
                            <i class="bi bi-geo-alt me-2"></i>
                            Jl. Kampus No. 1, Universitas Merdeka<br>
                            Bandung, Jawa Barat 40123<br>
                            Indonesia
                        </p>
                        <p class="mb-0 small">
                            <i class="bi bi-telephone me-2"></i>
                            <strong>Telepon:</strong> +62 274-1234-5678
                        </p>
                    </div>
                </div>
            </div>

            <!-- Kantor Akademik -->
            <div class="contact-item mb-4 pb-4 border-bottom">
                <div class="d-flex gap-3">
                    <div class="contact-icon d-flex align-items-center justify-content-center bg-light rounded-3 p-3" style="width:50px; height:50px; flex-shrink:0">
                        <i class="bi bi-book-fill text-success fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1">Kantor Akademik</h6>
                        <p class="mb-1 text-muted small">
                            <i class="bi bi-geo-alt me-2"></i>
                            Gedung Akademik, Lantai 2<br>
                            Kampus Universitas Merdeka<br>
                            Bandung
                        </p>
                        <p class="mb-1 small">
                            <i class="bi bi-telephone me-2"></i>
                            <strong>Telepon:</strong> +62 274-8765-4321
                        </p>
                        <p class="mb-0 small">
                            <i class="bi bi-clock me-2"></i>
                            <strong>Jam Kerja:</strong> Senin - Jumat, 08:00 - 16:00
                        </p>
                    </div>
                </div>
            </div>

            <!-- Kantor Mahasiswa -->
            <div class="contact-item mb-4 pb-4 border-bottom">
                <div class="d-flex gap-3">
                    <div class="contact-icon d-flex align-items-center justify-content-center bg-light rounded-3 p-3" style="width:50px; height:50px; flex-shrink:0">
                        <i class="bi bi-people-fill text-info fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1">Kantor Kemahasiswaan</h6>
                        <p class="mb-1 text-muted small">
                            <i class="bi bi-geo-alt me-2"></i>
                            Gedung Mahasiswa, Lantai 1<br>
                            Kampus Universitas Merdeka<br>
                            Bandung
                        </p>
                        <p class="mb-1 small">
                            <i class="bi bi-telephone me-2"></i>
                            <strong>Telepon:</strong> +62 274-5555-8888
                        </p>
                        <p class="mb-0 small">
                            <i class="bi bi-clock me-2"></i>
                            <strong>Jam Kerja:</strong> Senin - Jumat, 09:00 - 16:30
                        </p>
                    </div>
                </div>
            </div>

            <!-- Layanan Konseling -->
            <div class="contact-item">
                <div class="d-flex gap-3">
                    <div class="contact-icon d-flex align-items-center justify-content-center bg-light rounded-3 p-3" style="width:50px; height:50px; flex-shrink:0">
                        <i class="bi bi-chat-dots-fill text-warning fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1">Layanan Konseling Akademik</h6>
                        <p class="mb-1 text-muted small">
                            <i class="bi bi-telephone me-2"></i>
                            <strong>Hotline:</strong> +62 274-1111-2222
                        </p>
                        <p class="mb-0 small">
                            <i class="bi bi-envelope me-2"></i>
                            <strong>Email:</strong> konseling@unimerdeka.ac.id
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ & Other Info -->
    <div class="col-12 col-lg-6">
        <div class="content-card">
            <h6 class="fw-bold mb-4">
                <i class="bi bi-question-circle text-primary-custom me-2"></i> Pertanyaan Umum
            </h6>

            <div class="accordion" id="faqAccordion">
                <!-- FAQ 1 -->
                <div class="accordion-item border">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            Bagaimana cara menghubungi dosen pembimbing akademik?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body small">
                            Anda dapat menghubungi dosen pembimbing akademik melalui kantor akademik atau membuat janji temu di jam konsultasi yang telah ditentukan. 
                            Informasi lengkap tersedia di portal akademik.
                        </div>
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="accordion-item border">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            Bagaimana cara mengajukan cuti akademik?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body small">
                            Ajukan permohonan cuti akademik melalui kantor akademik dengan melengkapi formulir yang disediakan. 
                            Proses persetujuan biasanya memakan waktu 3-5 hari kerja.
                        </div>
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="accordion-item border">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            Siapa yang harus saya hubungi jika ada masalah akademik?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body small">
                            Hubungi kantor akademik atau layanan konseling akademik. Tim kami siap membantu menyelesaikan masalah Anda dengan cepat dan profesional.
                        </div>
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="accordion-item border">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            Bagaimana cara mengakses data nilai dan transkrip akademik?
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body small">
                            Data nilai dapat diakses melalui portal akademik dengan login menggunakan NIM dan password Anda. 
                            Transkrip akademik resmi dapat diminta melalui kantor akademik.
                        </div>
                    </div>
                </div>

                <!-- FAQ 5 -->
                <div class="accordion-item border-0">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                            Bagaimana cara mengubah data pribadi di sistem?
                        </button>
                    </h2>
                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body small">
                            Anda dapat mengubah data pribadi melalui halaman "Student Data" di dashboard. 
                            Untuk perubahan data resmi lainnya, hubungi kantor akademik.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email & Website -->
        <div class="content-card mt-3">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-link-45deg text-primary-custom me-2"></i> Informasi Lainnya
            </h6>

            <div class="list-group list-group-flush">
                <a href="mailto:info@unimerdeka.ac.id" class="list-group-item list-group-item-action d-flex gap-3 px-0 py-3">
                    <i class="bi bi-envelope text-primary-custom fs-5 flex-shrink-0"></i>
                    <div>
                        <div class="fw-semibold">Email Umum</div>
                        <div class="text-muted small">info@unimerdeka.ac.id</div>
                    </div>
                </a>

                <a href="https://www.unimerdeka.ac.id" target="_blank" rel="noopener noreferrer" class="list-group-item list-group-item-action d-flex gap-3 px-0 py-3">
                    <i class="bi bi-globe text-primary-custom fs-5 flex-shrink-0"></i>
                    <div>
                        <div class="fw-semibold">Website Universitas</div>
                        <div class="text-muted small">www.unimerdeka.ac.id</div>
                    </div>
                </a>

                <a href="https://portal.unimerdeka.ac.id" target="_blank" rel="noopener noreferrer" class="list-group-item list-group-item-action d-flex gap-3 px-0 py-3 border-0">
                    <i class="bi bi-window text-primary-custom fs-5 flex-shrink-0"></i>
                    <div>
                        <div class="fw-semibold">Portal Akademik</div>
                        <div class="text-muted small">portal.unimerdeka.ac.id</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
