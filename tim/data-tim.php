<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/koneksi.php';
$page_title = "Data Tim | TEMUIN";
require_once __DIR__ . '/../includes/sidebar.php';

$namaAdmin = $_SESSION['nama'] ?? 'admin';

$stmtTim = $pdo->query(
  'SELECT t.id, t.nama_tim, t.nama_lomba, t.kategori, t.status_tim, u.nama AS nama_ketua
   FROM tim_lomba AS t
   JOIN users AS u ON u.id = t.ketua_id
   ORDER BY t.nama_tim'
);
$daftarTim = $stmtTim->fetchAll(PDO::FETCH_ASSOC);

$totalTim = (int) $pdo->query('SELECT COUNT(*) FROM tim_lomba')->fetchColumn();
$totalRekrut = (int) $pdo->query('SELECT COUNT(*) FROM tim_lomba')->fetchColumn();
$timTerbentuk = (int) $pdo->query('SELECT COUNT(*) FROM tim_lomba')->fetchColumn();
?>

<link rel="stylesheet" href="../assets/css/pages/tim.css">
<link rel="stylesheet" href="../assets/css/pages/dashboard.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/pages/admin.css">

<div class="page-head">
    <div>
        <h1>Data Tim Lomba</h1>
        <p>Pantau status pembentukan tim, progres rekrutmen anggota, dan detail informasi kompetisi.</p>
    </div>
</div>

<section class="admin-content">
<div class="analytics-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 1rem;">

    <div class="analytic-card" style="background: #fff; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
      <h3 style="color: #64748b; font-size: 0.9rem; margin-bottom: 0.5rem;">Total Tim Terbuat</h3>
      <div style="display: flex; align-items: center; justify-content: space-between;">
        <span style="font-size: 2rem; font-weight: 700; color: #0f172a;"><?= $totalTim ?></span>
      </div>
    </div>

    <div class="analytic-card" style="background: #fff; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
      <h3 style="color: #64748b; font-size: 0.9rem; margin-bottom: 0.5rem;">Sedang Merekrut</h3>
      <div style="display: flex; align-items: center; justify-content: space-between;">
        <span style="font-size: 2rem; font-weight: 700; color: #0f172a;"><?= $totalRekrut?></span>
      </div>
    </div>

    <div class="analytic-card" style="background: #fff; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
      <h3 style="color: #64748b; font-size: 0.9rem; margin-bottom: 0.5rem;">Tim Terbentuk</h3>
      <div style="display: flex; align-items: center; justify-content: space-between;">
        <span style="font-size: 2rem; font-weight: 700; color: #0f172a;"><?= $timTerbentuk ?></span>
      </div>
    </div>
</div>

<div class="table-card">

  <div class="table-responsive">
    <table class="table-admin">
      <thead>
        <tr>
          <th>Informasi Tim</th>
          <th>Ketua Tim</th>
          <th>Kategori Kompetisi</th>
          <th>Status Rekrutmen</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$daftarTim): ?>
          <tr><td colspan="5" style="text-align: center;">Belum ada data tim.</td></tr>
        <?php else: ?>
          <?php foreach ($daftarTim as $tim): ?>
            <tr>
              <td>
                <?= htmlspecialchars($tim['nama_tim'] ?? '', ENT_QUOTES, 'UTF-8') ?>
              </td>
              <td>
                <div class="profile-cell">
                  <div class="avatar avatar-blue"><?= htmlspecialchars(strtoupper(substr($tim['nama_ketua'] ?? '', 0, 2)), ENT_QUOTES, 'UTF-8') ?></div>
                  <div>
                    <span class="applicant-name"><?= htmlspecialchars($tim['nama_ketua'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    <!-- <span class="team-subtext"></span> -->
                  </div>
                </div>
              </td>
              <td><span class="badge badge-blue"><?= htmlspecialchars($tim['kategori'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
              <td><span class="badge badge-green"><?= htmlspecialchars($tim['status_tim'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
              <td>
                <div class="action-buttons">
                                <a href="edit-tim.php?id=<?= urlencode($tim['id']) ?>" class="btn-action btn-navy">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="hapus-tim.php?id=<?= urlencode($tim['id']) ?>" class="btn-action btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data dosen ini?');">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </div>
              </td>
            </tr>
           <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
          </section>
<?php
require_once '../includes/footer.php';
?>