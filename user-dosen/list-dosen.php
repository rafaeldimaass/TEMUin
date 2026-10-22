<?php
session_start();

// 1. Proteksi Akses: Hanya mahasiswa yang boleh masuk
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../auth/login.php');
    exit;
}

// 2. Panggil koneksi database
require_once '../includes/koneksi.php';
$page_title = "Info Lomba";
require_once '../includes/header.php';


$namaMahasiswa = $_SESSION['nama'] ?? 'Mahasiswa';
?>

<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/pages/dosen.css">


    <div class="page-head">
    <div>
        <h1>Profile Dosen</h1>
        <p>Temukan dosen sesuai dengan bidang lombamu yaa!</p>
    </div>
    </div>

    <div class="toolbar">
    <form action="" method="GET">
        <!-- Input Pencarian -->
          <label class="field-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
            </svg>
            <input name="q" type="search" placeholder="Cari nama dosen, bisa melalui bidangnya">
          </label>

          <!-- Tombol Cari -->
          <button class="btn btn-navy toolbar-btn" type="submit">Terapkan</button>
    </form>
</div>

    <div class="profile-section">
            <div class="card-grid">
                <!-- Card 1 -->
                <div class="card">
                    <div class="card-image-placeholder">
                        <span>Foto dosen, blurrrr</span>
                    </div>
                    <div class="card-content">
                        <p class="dosen-name">Dr. Siti Rahma, S.Kom</p>
                        <p class="dosen-desc">Keahlian: UI/UX • Pengalaman: 5 tahun mentoring kompetisi</p>
                        <span class="btn btn-orange">Ajukan Bimbingan</span>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="card">
                    <div class="card-image-placeholder">
                        <span>Foto dosen, blurrrr</span>
                    </div>
                    <div class="card-content">
                        <p class="dosen-name">Dr. Siti Rahma, S.Kom</p>
                        <p class="dosen-desc">Keahlian: UI/UX • Pengalaman: 5 tahun mentoring kompetisi</p>
                        <span class="btn btn-orange">Ajukan Bimbingan</span>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="card">
                    <div class="card-image-placeholder">
                        <span>Foto dosen, blurrrr</span>
                    </div>
                    <div class="card-content">
                        <p class="dosen-name">Dr. Siti Rahma, S.Kom</p>
                        <p class="dosen-desc">Keahlian: UI/UX • Pengalaman: 5 tahun mentoring kompetisi</p>
                        <span class="btn btn-orange">Ajukan Bimbingan</span>
                    </div>
                </div>
            </div>
        </div>
</div>

 <?php
  require_once '../includes/footer.php';
 ?>