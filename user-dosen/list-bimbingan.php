<?php
session_start();

// Cek apakah user sudah login dan perannya adalah dosen
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen'){
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/koneksi.php';
$page_title = "Pengajuan Bimbingan | TEMUIN";
require_once __DIR__ . '/../includes/header.php';

$namaDosen = $_SESSION['nama'] ?? 'Dosen';
?>
<link rel="stylesheet" href="../assets/css/pages/tim.css">
<link rel="stylesheet" href="../assets/css/components.css">

<div class="page-head">
    <div>
        <h1>Kelola Pengajuan Bimbingan</h1>
        <p>Silakan tinjau proposal dan tentukan apakah Anda bersedia menjadi dosen pembimbing untuk tim-tim berikut</p>
    </div>
</div>

<div class="btn-management-tim">
    <a class="btn btn-navy" href="../bimbingan/list-bimbingan.php">Tim Bimbingan Saya</a>
</div>

<div class="team-header-card">
  
  <!-- Bagian Kiri: Info Status Dosen -->
  <div class="team-info">
    <!-- Badge Status -->
    <div class="badge-group">
      <span class="badge badge-blue">Dosen Pembimbing</span>
      <span class="badge badge-green">Menerima Bimbingan</span>
    </div>
    
    <!-- Judul & Deskripsi -->
    <h2 class="team-title">Status Ketersediaan: Terbuka</h2>
    <p class="team-desc">Kuota bimbingan tersisa: 3 Tim. Anda masih dapat menerima pengajuan baru.</p>
  </div>

  <div class="action-group">
    <button class="btn-close-vacancy">Tutup Penerimaan</button>
    <a href="#" class="btn btn-ghost">Edit Preferensi</a>
  </div>
  
</div>

<div class="table-card">
  
  <!-- Header Card -->
  <div class="table-card-header">
    <h3 class="table-card-title">Daftar Pengajuan Tim</h3>
    <span class="table-card-count">Menunggu Tinjauan: 3 Tim</span>
  </div>

  <div class="table-responsive">
    <table class="applicant-table">
      <thead>
        <tr>
          <th>Nama Tim & Ketua</th>
          <th>Kompetisi yang Diikuti</th>
          <th>Proposal/Dokumen</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        
        <!-- Pengajuan 1 -->
        <tr>
          <td>
            <div class="profile-cell">
              <div class="avatar avatar-blue">TA</div>
              <span class="applicant-name">Tim Aurora<br><small style="color: #666; font-size: 0.85em;">Ketua: Syahrur Ramadhan</small></span>
            </div>
          </td>
          <td class="role-cell">Hackathon & Inovasi Teknologi Nasional 2026</td>
          <td>
            <a href="#" class="btn-link">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
              </svg>
              Lihat Proposal
            </a>
          </td>
          <td>
            <div class="action-cell">
              <button class="btn btn-navy">Terima</button>
              <button class="btn-reject">Tolak</button>
            </div>
          </td>
        </tr>

        <!-- Pengajuan 2 -->
        <tr>
          <td>
            <div class="profile-cell">
              <div class="avatar avatar-pink">NX</div>
              <span class="applicant-name">Nexus Squad<br><small style="color: #666; font-size: 0.85em;">Ketua: Nurfakiyah Rahmadhani</small></span>
            </div>
          </td>
          <td class="role-cell">Gemastik 2026 - Divisi Pengembangan Perangkat Lunak</td>
          <td>
            <a href="#" class="btn-link">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
              </svg>
              Lihat Proposal
            </a>
          </td>
          <td>
            <div class="action-cell">
              <button class="btn btn-navy">Terima</button>
              <button class="btn-reject">Tolak</button>
            </div>
          </td>
        </tr>

        <!-- Pengajuan 3 -->
        <tr>
          <td>
            <div class="profile-cell">
              <div class="avatar avatar-orange">IV</div>
              <span class="applicant-name">Invictus<br><small style="color: #666; font-size: 0.85em;">Ketua: Raffael Dimas Cahyo Laksono</small></span>
            </div>
          </td>
          <td class="role-cell">Lomba Inovasi Digital Mahasiswa (LIDM)</td>
          <td>
            <a href="#" class="btn-link">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
              </svg>
              Lihat Proposal
            </a>
          </td>
          <td>
            <div class="action-cell">
              <button class="btn btn-navy">Terima</button>
              <button class="btn-reject">Tolak</button>
            </div>
          </td>
        </tr>

      </tbody>
    </table>
  </div>
</div>
<?php
require_once '../includes/footer.php';
?>