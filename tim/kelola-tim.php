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
<link rel="stylesheet" href="../assets/css/components.css">

<div class="page-head">
    <div>
        <h1>Kelola Temuin Tim Saya</h1>
        <p>Silakan accept atau reject calon anggota tim berdasarkan seleksi portofolio</p>
    </div>
    </div>
  <div class="btn-management-tim">
        <a class="btn btn-navy" href="../tim/list-tim.php">List Tim</a>
    </div>

   <div class="team-header-card">
  
  <!-- Bagian Kiri: Info Tim -->
  <div class="team-info">
    <!-- Badge Status -->
    <div class="badge-group">
      <span class="badge badge-blue">Ketua Tim</span>
      <span class="badge badge-green">Merekrut</span>
    </div>
    
    <!-- Judul & Deskripsi -->
    <h2 class="team-title">Tim Aurora</h2>
    <p class="team-desc">Kompetisi Hackathon & Inovasi Teknologi Nasional 2026</p>
  </div>

  <div class="action-group">
    <button class="btn-close-vacancy">Tutup Lowongan</button>
    <a href="#" class="btn btn-ghost">Edit Info Tim</a>
  </div>
  
</div>

<div class="table-card">
  
  <!-- Header Card -->
  <div class="table-card-header">
    <h3 class="table-card-title">Daftar Pelamar</h3>
    <span class="table-card-count">Menunggu Tinjauan: 3 Orang</span>
  </div>

  
  <div class="table-responsive">
    <table class="applicant-table">
      <thead>
        <tr>
          <th>Profil Pelamar</th>
          <th>Posisi yang Dilamar</th>
          <th>Portofolio</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        
        <!-- Pelamar 1 -->
        <tr>
          <td>
            <div class="profile-cell">
              <div class="avatar avatar-blue">SR</div>
              <span class="applicant-name">Syahrur Ramadhan</span>
            </div>
          </td>
          <td class="role-cell">Web Developer</td>
          <td>
            <a href="#" class="btn-link">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
              </svg>
              Lihat Portofolio
            </a>
          </td>
          <td>
            <div class="action-cell">
              <button class="btn btn-navy">Terima</button>
              <button class="btn-reject">Tolak</button>
            </div>
          </td>
        </tr>

        <!-- Pelamar 2 -->
        <tr>
          <td>
            <div class="profile-cell">
              <div class="avatar avatar-pink">NR</div>
              <span class="applicant-name">Nurfakiyah Rahmadhani</span>
            </div>
          </td>
          <td class="role-cell">UI/UX Designer</td>
          <td>
            <a href="#" class="btn-link">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
              </svg>
              Lihat Portofolio
            </a>
          </td>
          <td>
            <div class="action-cell">
              <button class="btn btn-navy">Terima</button>
              <button class="btn-reject">Tolak</button>
            </div>
          </td>
        </tr>

        <!-- Pelamar 3 -->
        <tr>
          <td>
            <div class="profile-cell">
              <div class="avatar avatar-orange">RD</div>
              <span class="applicant-name">Raffael Dimas Cahyo Laksono</span>
            </div>
          </td>
          <td class="role-cell">Video Editor</td>
          <td>
            <a href="#" class="btn-link">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
              </svg>
              Lihat Portofolio
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
