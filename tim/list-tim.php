<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa'){
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/koneksi.php';
$page_title = "Buat Tim | TEMUIN";
require_once __DIR__ . '/../includes/header.php';

$namaMahasiswa = $_SESSION['nama'] ?? 'Mahasiswa';
?>

<link rel="stylesheet" href="../assets/css/pages/tim.css">
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/components.css">

<div class="page-head">
    <div>
        <h1>Temuin Tim</h1>
        <p>Temukan tim yang cocok dengan minat dan bakatmu, atau kelola timmu sendiri.</p>
    </div>
    </div>
   <div class="btn-management-tim">
        <a class="btn btn-navy" href="../tim/kelola-tim.php">Kelola Tim saya</a>
    </div>

<div class="toolbar">
    <form action="" method="GET">
        <!-- Input Pencarian -->
          <label class="field-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
            </svg>
            <input name="q" type="search" placeholder="Cari nama tim, lomba, atau peran...">
          </label>

          <!-- Dropdown Urutkan -->
          <label class="sort">
            <select name="sort">
              <option value="terdekat">Tenggat Terdekat</option>
              <option value="terlama">Tenggat Terlama</option>
            </select>
          </label>

          <!-- Tombol Cari -->
          <button class="btn btn-navy toolbar-btn" type="submit">Terapkan</button>
    </form>
</div>

<section class="list-section">
    <!-- Grid Container -->
      <div class="list-grid">
        <!-- ================= CARD ================= -->
        <article class="card">
          <div class="poster" style="--a:#1f2d6b; --b:#44639a;">
            <span class="status">Menerima anggota</span>
            <span>JF</span>
          </div>

          <div class="body">
            <h3>JTI FEST</h3>
            <p class="desc">Tim Garuda Digital, ketua Syahrur R.</p>
            
            <div class="tags">
              <span class="tag role">UI/UX Designer</span>
              <span class="tag role">Programmer</span>
            </div>
            <div class="meta">
              <div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Daftar sampai <b>29 Okt</b> (10 hari lagi)</span>
              </div>
              <div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Mencari 2 anggota lagi</span>
              </div>
            </div>
            <div class="cta">
              <a class="btn btn-ghost" href="detail-tim.php?id=1" style="width: 100%;">Detail</a>
              <a class="btn btn-orange" href="#" style="width: 100%;">Gabung</a>
            </div>
          </div>
        </article>

        <article class="card">
          <div class="poster" style="--a:#7c2d12; --b:#f6ad55;">
            <span class="status warn">Segera ditutup</span>
            <span>LK</span>
          </div>
          <div class="body">
            <h3>Lomba Karya Tulis</h3>
            <p class="desc">Tim Literasi Muda, ketua Camelia A.</p>
            
            <div class="tags">
              <span class="tag role">Penulis</span>
            </div>

            <div class="meta">
              <div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Daftar sampai <b>21 Okt</b> (2 hari lagi)</span>
              </div>
              <div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Mencari 1 anggota lagi</span>
              </div>
            </div>
            
            <div class="cta">
              <a class="btn btn-ghost" href="detail-tim.php?id=2" style="width: 100%;">Detail</a>
              <a class="btn btn-orange" href="#" style="width: 100%;">Gabung</a>
            </div>
          </div>
        </article>
      </div>

</section>
<?php
require_once '../includes/footer.php';
?>