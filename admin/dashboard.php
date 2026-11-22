<?php
session_start();

// 1. Proteksi Akses: Hanya dosen yang boleh masuk
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../auth/login.php');
  exit;
}

// 2. Panggil koneksi database
require_once '../includes/koneksi.php';
$page_title = "Dashboard Admin TEMUin";
require_once '../includes/sidebar.php';

$namaAdmin = $_SESSION['nama'] ?? 'Admin';

$stmtTim = $pdo->query(
  'SELECT t.nama_tim, t.nama_lomba, t.kategori, t.status_tim, u.nama AS nama_ketua
   FROM tim_lomba AS t
   JOIN users AS u ON u.id = t.ketua_id
   ORDER BY t.nama_tim'
);
$daftarTim = $stmtTim->fetchAll(PDO::FETCH_ASSOC);

$stmtLomba = $pdo->query(
  'SELECT u.nama AS nama_pembuat,
          il.poster,
          il.judul,
          il.kategori,
          il.link_detail
   FROM info_lomba AS il
   LEFT JOIN users AS u ON u.id = il.admin_id
   ORDER BY il.judul'
);
$daftarLomba = $stmtLomba->fetchAll(PDO::FETCH_ASSOC);

$totalTim = (int) $pdo->query('SELECT COUNT(*) FROM tim_lomba')->fetchColumn();
$totalLomba = (int) $pdo->query('SELECT COUNT(*) FROM info_lomba')->fetchColumn();

$stmtMenunggu = $pdo->prepare(
  'SELECT COUNT(*) FROM tim_lomba WHERE status_tim = :status'
);
$stmtMenunggu->execute(['status' => 'Menunggu Tinjauan']);
$jumlahMenunggu = (int) $stmtMenunggu->fetchColumn();

?>

<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/pages/admin.css">
<link rel="stylesheet" href="../assets/css/pages/dashboard.css">
<link rel="stylesheet" href="../assets/css/pages/tim.css">
<link rel="stylesheet" href="../assets/css/global.css">


<section class="hero" aria-labelledby="judul-hero">
  <div>
    <h1 id="judul-hero">Halo, <?= htmlspecialchars($namaAdmin ?? 'Admin') ?>! Selamat Datang di Dashboard Admin TEMUin.</h1>
    <p class="lead">Pantau ringkasan statistik sistem, moderasi konten tim, dan cetak laporan rekapitulasi di sini.</p>
  </div>
</section>

<div class="page-head">
  <div>
    <h1>Moderasi Konten & Pelaporan</h1>
    <p>Tinjau postingan tim baru, tetapkan filter untuk rekapitulasi, dan kelola pelanggaran.</p>
  </div>
</div>



<!-- Section Analytics (Overview) -->
<section class="admin-analytics">
  <div class="section-header">
    <h2>Overview Sistem</h2>
    <div class="admin-actions">
      <!-- Tombol untuk menuju menu laporan[cite: 4] -->
      <a href="cetak-laporan.php" class="btn btn-navy">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
          <path d="M17 17h2a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h2"></path>
          <polyline points="17 9 12 14 7 9"></polyline>
          <path d="M12 14V4"></path>
        </svg>
        Cetak Rekapitulasi
      </a>
    </div>
  </div>

  <div class="analytics-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 1rem;">

    <!-- Card Total Tim -->
    <div class="analytic-card" style="background: #fff; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
      <h3 style="color: #64748b; font-size: 0.9rem; margin-bottom: 0.5rem;">Total Tim Terbuat</h3>
      <div style="display: flex; align-items: center; justify-content: space-between;">
        <span style="font-size: 2rem; font-weight: 700; color: #0f172a;"><?= $totalTim ?></span>
        <span style="color: #10b981; font-size: 0.85rem; background: #d1fae5; padding: 4px 8px; border-radius: 20px;">+12%</span>
      </div>
    </div>

    <!-- Card Postingan Info Lomba -->
    <div class="analytic-card" style="background: #fff; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
      <h3 style="color: #64748b; font-size: 0.9rem; margin-bottom: 0.5rem;">Postingan Info Lomba</h3>
      <div style="display: flex; align-items: center; justify-content: space-between;">
        <span style="font-size: 2rem; font-weight: 700; color: #0f172a;"><?= $totalLomba ?></span>
        <span style="color: #10b981; font-size: 0.85rem; background: #d1fae5; padding: 4px 8px; border-radius: 20px;">+5%</span>
      </div>
    </div>

    <!-- Card Menunggu Moderasi -->
    <div class="analytic-card" style="background: #fff; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid #fee2e2;">
      <h3 style="color: #64748b; font-size: 0.9rem; margin-bottom: 0.5rem;">Menunggu Moderasi</h3>
      <div style="display: flex; align-items: center; justify-content: space-between;">
        <span style="font-size: 2rem; font-weight: 700; color: #ef4444;"><?= $jumlahMenunggu ?></span>
        <a href="moderasi.php" style="font-size: 0.85rem; color: #2563eb; text-decoration: none; font-weight: 500;">Tinjau Sekarang &rarr;</a>
      </div>
    </div>

  </div>

  <div class="table-card">
    <!-- Header Card -->
    <div class="table-card-header">
      <h3 class="table-card-title">Aktivitas Terbaru</h3>
      <span class="table-card-count"><?= $jumlahMenunggu ?> Menunggu Tinjauan</span>
    </div>

    <div class="table-responsive">
      <table class="table-admin">
        <thead>
          <tr>
            <th>Ketua Tim</th>
            <th>Nama Tim</th>
            <th>Nama Lomba</th>
            <th>Kategori Kompetisi</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>

          <?php foreach ($daftarTim as $tim): ?>
            <tr>
              <td>
                <div class="applicant-name">
                  <p><?= htmlspecialchars($tim['nama_tim'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
              </td>
              <td>
                <div class="applicant-name">
                  <p><?= htmlspecialchars($tim['nama_lomba'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
              </td>
              <td class="role-cell">
                <div class="profile-cell">
                  <!-- <div class="avatar avatar-blue"><?= htmlspecialchars(strtoupper(substr($tim['nama_ketua'] ?? '', 0, 2)), ENT_QUOTES, 'UTF-8') ?></div> -->
                  <span class="applicant-name"><?= htmlspecialchars($tim['nama_ketua'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
              </td>
              <td><span class="badge badge-blue"><?= htmlspecialchars($tim['kategori'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
              <td><span class="badge badge-green"><?= htmlspecialchars($tim['status_tim'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
              <td>
                <div class="action-buttons">
                  <a href="#" class="btn-action btn-navy">
                    <i class="fas fa-eye"></i> Lihat
                  </a>
                  <a href="#" class="btn-action btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data tim ini?');">
                    <i class="fas fa-trash"></i> Hapus
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>

        </tbody>
      </table>
    </div>
  </div>

  <div class="table-card">
    <!-- Header Card -->
    <div class="table-card-header">
      <h3 class="table-card-title">Aktivitas Terbaru Info Lomba</h3>
    </div>

    <div class="table-responsive">
      <table class="table-admin">
        <thead>
          <tr>
            <th>Nama Pembuat Info Lomba</th>
            <th>Poster</th>
            <th>Nama Lomba</th>
            <th>Kategori Lomba</th>
            <th>Link Pendaftaran</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>

          <?php foreach ($daftarLomba as $lomba): ?>
            <tr>
              <td>
                <div class="td-admin">
                  <p><?= htmlspecialchars($lomba['nama_pembuat'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
              </td>
              <td>
                <?php if (!empty($lomba['poster'])): ?>
                  <img
                    src="../assets/uploads/poster/<?= htmlspecialchars($lomba['poster'], ENT_QUOTES, 'UTF-8') ?>"
                    alt="Poster <?= htmlspecialchars($lomba['nama_lomba'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    style="width: 80px; height: auto;">
                <?php else: ?>
                  -
                <?php endif; ?>
              </td>
              <td>
                <div class="td-admin">
                  <p><?= htmlspecialchars($lomba['judul'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
              </td>

              <td><span class="badge badge-blue"><?= htmlspecialchars($tim['kategori'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
              <td>
                <?php if (!empty($lomba['link_detail'])): ?>
                  <a href="<?= htmlspecialchars($lomba['link_detail'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Buka link</a>
                <?php else: ?>
                  -
                <?php endif; ?>
              </td>
              <td>
                <div class="action-cell">
                  <a class="btn-reject" href="moderasi.php">Hapus</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>

        </tbody>
      </table>
    </div>
  </div>
</section>

<?php
require_once '../includes/footer.php';
?>