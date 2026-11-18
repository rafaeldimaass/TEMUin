<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
  header('Location: ../auth/login.php');
  exit;
}

require_once __DIR__ . '/../includes/koneksi.php';
$page_title = "Buat Tim | TEMUIN";
require_once __DIR__ . '/../includes/header.php';

$namaMahasiswa = $_SESSION['nama'] ?? 'Mahasiswa';
$ketua_id = $_SESSION['user_id'];

// 1. Cek apakah user ini adalah ketua dari sebuah tim
$stmtTim = $pdo->prepare("SELECT * FROM tim_lomba WHERE ketua_id = ? LIMIT 1");
$stmtTim->execute([$ketua_id]);
$timData = $stmtTim->fetch(PDO::FETCH_ASSOC);

$pelamar_list = [];
if ($timData) {
  // 2. Ambil data pelamar yang berstatus 
  $sqlPelamar = "SELECT la.id AS lamaran_id,la.tim_id, la.kandidat_id, u.nama, la.posisi_dilamar, la.status, pm.portofolio 
                   FROM lamaran_anggota la 
                   JOIN users u ON la.kandidat_id = u.id 
                   LEFT JOIN profil_mahasiswa pm ON u.id = pm.user_id 
                   WHERE la.tim_id= ?";
  $stmtPelamar = $pdo->prepare($sqlPelamar);
  $stmtPelamar->execute([$timData['id']]);
  $pelamar_list = $stmtPelamar->fetchAll(PDO::FETCH_ASSOC);
}
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

<?php if (!$timData): ?>
  <div class="team-header-card" style="text-align:center; margin-top: 100px;margin-bottom: 110px;">
    <h2>Kamu belum membuat tim lomba apapun.</h2>
    <a href="buat-tim.php" class="btn btn-navy" style="margin-top:15px;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
      Buat Tim Sekarang</a>
  </div>
<?php else: ?>

  <div class="team-header-card">

    <!-- Bagian Kiri: Info Tim -->
    <div class="team-info">
      <!-- Badge Status -->
      <div class="badge-group">
        <span class="badge badge-blue">Ketua Tim</span>
        <span class="badge badge-green"><?= strtoupper($timData['status_tim']) ?></span>
      </div>

      <!-- Judul & Deskripsi -->
      <h2 class="team-title"><?= htmlspecialchars($timData['nama_tim']) ?></h2>
      <p class="team-desc"><?= htmlspecialchars($timData['nama_lomba']) ?></p>
    </div>

    <div class="action-group">
      <button href="proses-tutup-tim.php?id=<?= $timData['id'] ?>" class="btn-close-vacancy" onclick="return confirm('Tutup lowongan?')">Tutup Lowongan</button>
      <a href="edit-tim.php?id=<?= $timData['id'] ?>" class="btn btn-ghost">Edit Info Tim</a>
    </div>
  </div>

  <div class="table-card">

    <!-- Header Card -->
    <div class="table-card-header">
      <h3 class="table-card-title">Daftar Pelamar</h3>
      <span class="table-card-count">Menunggu Tinjauan: <?= count($pelamar_list) ?> Orang</span>
    </div>

    <div class="table-responsive">
      <table class="table-admin">
        <thead>
          <tr>
            <th>Profil Pelamar</th>
            <th>Posisi yang Dilamar</th>
            <th>Link CV/Portofolio</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($pelamar_list) > 0): ?>
            <?php foreach ($pelamar_list as $pelamar): ?>
              <tr>
                <td>
                  <div class="profile-cell">
                    <div class="avatar avatar-blue"><?= strtoupper(substr($pelamar['nama'], 0, 2)) ?></div>
                    <span class="applicant-name"><?= htmlspecialchars($pelamar['nama']) ?></span>
                  </div>
                </td>
                <td class="role-cell"><?= htmlspecialchars($pelamar['posisi_dilamar']) ?>?></td>
                <td>
                  <a href="<?= htmlspecialchars($pelamar['portofolio']) ?>" target="_blank" class="btn-link">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                    </svg>
                    Lihat CV/Portofolio
                  </a>
                </td>
                <td>
                  <form action="proses-update-status.php" method="POST" style="display: flex; gap: 8px; align-items: center;">
                    <input type="hidden" name="lamaran_id" value="<?= $pelamar['lamaran_id'] ?>">
                    <input type="hidden" name="tim_id" value="<?= $pelamar['tim_id'] ?>">
                    <input type="hidden" name="user_id" value="<?= $pelamar['kandidat_id'] ?>">
                    <input type="hidden" name="posisi" value="<?= htmlspecialchars($pelamar['posisi_dilamar']) ?>">

                    <select name="status_baru" class="form-select" style="padding: 6px 10px; border-radius: 8px; border: 1.5px solid var(--line-strong, #cfd8ea); font-size: 13px; font-weight: 500; background: #fff;">
                      <option value="pending" <?= ($pelamar['status'] == 'pending') ? 'selected' : '' ?>>Menunggu</option>
                      <option value="diterima" <?= ($pelamar['status'] == 'diterima') ? 'selected' : '' ?>>Diterima</option>
                      <option value="ditolak" <?= ($pelamar['status'] == 'ditolak') ? 'selected' : '' ?>>Ditolak</option>
                    </select>

                    <button type="submit" class="btn btn-navy" style="height: 36px; padding: 0 14px; font-size: 13px;">Simpan</button>
                  </form>
                </td>
              </tr>

            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" style="text-align:center;">Belum ada pelamar baru.</td>
            </tr>
          <?php endif; ?>

        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
<?php
require_once '../includes/footer.php';
?>