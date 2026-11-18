<?php
session_start();

// 1. Proteksi Akses: Hanya dosen yang boleh masuk
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen') {
  header('Location: ../auth/login.php');
  exit;
}

// 2. Panggil koneksi database
require_once '../includes/koneksi.php';
$page_title = "Dashboard Dosen Pembimbing";
require_once '../includes/header.php';


$namaDosen = $_SESSION['nama'] ?? 'Dosen';
try {
  $stmtTim = $pdo->query("SELECT * FROM tim ORDER BY id DESC LIMIT 4");
  $listTeams = $stmtTim->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $listTeams = []; // Fallback jika tabel belum ada
}
?>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/pages/dashboard.css">

<!-- ======================= HERO SECTION ======================= -->
<section class="hero" aria-labelledby="judul-hero">
  <div>
    <h1 id="judul-hero">Halo, <?= htmlspecialchars($namaDosen) ?>! Selamat Datang di sistem pembentukan calon juara!</h1>
    <p class="lead">Silakan cek halaman kelola tim yang mengajukan bimbingan yaa, Terima kasih.</p>
  </div>
</section>
  

 <!-- ======================= TIM MENCARI ANGGOTA ======================= -->
<section class="block" aria-labelledby="judul-tim">
  <div class="page-head">
    <h1>Tim yang sedang mencari anggota</h1>
    <a href="../tim/list-tim.php">Lihat semua</a>
  </div>

  <div class="grid">
    <!-- Card -->
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
        </div>
        <div class="card-actions">
          <a class="btn btn-ghost" href="#">Detail</a>
          <a class="btn btn-orange" href="#">Gabung</a>
        </div>
      </div>
    </article>
  </div>
</section>

<!-- ======================= INFO LOMBA ======================= -->
<section class="block" aria-labelledby="judul-lomba">
  <div class="page-head">
    <h1>Info lomba</h1>
    <a href="lomba.php">Lihat semua</a>
  </div>

  <div class="grid">

    <!-- Card 1 -->
    <article class="card">
      <div class="poster" style="--a:#1e40af; --b:#3b82f6;">
        <span>PI</span>
      </div>
      <div class="body">
        <div class="tags">
          <span class="tag cat">Hackathon</span>
          <span class="tag cat">Business Plan</span>
        </div>
        <h3>PLAY IT! PLUS+</h3>
        <p class="desc">Saatnya wujudkan inovasi masa depan lewat PLAY IT! PLUS Hackathon Deeptech! Kompetisi ini menantang tim terbaik untuk menciptaka...</p>
        <div class="meta">
          <div>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
            <span>Pendaftaran sampai <b>29 Okt</b> (10 hari lagi)</span>
          </div>
        </div>
        <div class="card-actions">
          <a class="btn btn-ghost" href="#">Detail Lomba</a>
        </div>
      </div>
    </article>

    <!-- Card 2 -->
    <article class="card">
      <div class="poster" style="--a:#1e3a8a; --b:#4b5563;">
        <span>JF</span>
      </div>
      <div class="body">
        <div class="tags">
          <span class="tag cat">Hackathon</span>
        </div>
        <h3>JTI FEST</h3>
        <p class="desc">Kompetisi inovasi solusi digital dengan seleksi proposal, bootcamp, dan final di Malang.</p>
        <div class="meta">
          <div>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
            <span>Pendaftaran sampai <b>29 Okt</b> (10 hari lagi)</span>
          </div>
        </div>
        <div class="card-actions">
          <a class="btn btn-ghost" href="#">Detail Lomba</a>
        </div>
      </div>
    </article>

    <!-- Card 3 -->
    <article class="card">
      <div class="poster" style="--a:#7c2d12; --b:#f6ad55;">
        <span>LK</span>
      </div>
      <div class="body">
        <div class="tags">
          <span class="tag cat">Karya Tulis</span>
        </div>
        <h3>Lomba Karya Tulis Ilmiah Nasional</h3>
        <p class="desc">Tulis gagasan ilmiahmu tentang isu pembangunan berkelanjutan dan bersaing di tingkat nasional.</p>
        <div class="meta">
          <div>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
            <span>Pendaftaran sampai <b>7 Nov</b> (19 hari lagi)</span>
          </div>
        </div>
        <div class="card-actions">
          <a class="btn btn-ghost" href="#">Detail Lomba</a>
        </div>
      </div>
    </article>
    </div>
    </section>
 <?php
  require_once '../includes/footer.php';
 ?>