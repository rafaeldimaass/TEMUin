<?php
session_start();

// Cek apakah user sudah login dan perannya adalah dosen
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen') {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/koneksi.php';

$dosenId = $_SESSION['user_id'];
$namaDosen = $_SESSION['nama'] ?? 'Dosen';

// 1. Proses aksi Terima / Tolak pengajuan bimbingan via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pengajuanId = filter_input(INPUT_POST, 'pengajuan_id', FILTER_VALIDATE_INT);
    $aksi = $_POST['aksi'] ?? '';

    $statusBaru = null;
    if ($aksi === 'terima') {
        $statusBaru = 'diterima';
    } elseif ($aksi === 'tolak') {
        $statusBaru = 'ditolak';
    }

    if ($pengajuanId && $statusBaru) {
        try {
            $stmtUpdate = $pdo->prepare("
                UPDATE pengajuan_bimbingan
                SET status = :status
                WHERE id = :id AND dosen_id = :dosen_id
            ");
            $stmtUpdate->execute([
                ':status'   => $statusBaru,
                ':id'       => $pengajuanId,
                ':dosen_id' => $dosenId,
            ]);
            header("Location: list-bimbingan.php?status_updated=1");
            exit;
        } catch (PDOException $e) {
            $errorMsg = "Gagal memperbarui status pengajuan: " . $e->getMessage();
        }
    }
}

$page_title = "Pengajuan Bimbingan | TEMUIN";
require_once __DIR__ . '/../includes/header.php';

// 2. Ambil data ketersediaan dosen (fallback aman jika kolom/tabel belum lengkap)
$statusKetersediaan = 'terbuka';
$kuotaTersisa = 3;
try {
    $stmtDosen = $pdo->prepare("SELECT status_bimbingan, kuota_bimbingan FROM profil_dosen WHERE user_id = ?");
    $stmtDosen->execute([$dosenId]);
    $dataDosen = $stmtDosen->fetch(PDO::FETCH_ASSOC);
    if ($dataDosen) {
        $statusKetersediaan = $dataDosen['status_bimbingan'] ?? 'terbuka';
        $kuotaTersisa = $dataDosen['kuota_bimbingan'] ?? 3;
    }
} catch (PDOException $e) {
    // Gunakan nilai default jika belum ada di database
}

// 3. Ambil daftar pengajuan bimbingan untuk dosen yang sedang login
$daftarPengajuan = [];
$jumlahMenunggu = 0;
try {
    $stmt = $pdo->prepare("
        SELECT
            pb.id,
            pb.tim_id,
            pb.dosen_id,
            pb.proposal,
            pb.status,
            pb.created_at,
            tl.nama_tim,
            tl.nama_lomba,
            u.nama AS nama_ketua,
            pm.foto_profil AS foto_ketua
        FROM pengajuan_bimbingan pb
        JOIN tim_lomba tl ON pb.tim_id = tl.id
        LEFT JOIN users u ON tl.ketua_id = u.id
        LEFT JOIN profil_mahasiswa pm ON pm.user_id = tl.ketua_id
        WHERE pb.dosen_id = :dosen_id
        ORDER BY pb.created_at DESC
    ");
    $stmt->execute([':dosen_id' => $dosenId]);
    $daftarPengajuan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($daftarPengajuan as $row) {
        $st = strtolower(trim((string)($row['status'] ?? 'menunggu')));
        if ($st === 'menunggu' || $st === 'pending') {
            $jumlahMenunggu++;
        }
    }
} catch (PDOException $e) {
    $errorMsg = "Gagal memuat data pengajuan bimbingan: " . $e->getMessage();
}

$avatarColors = ['avatar-blue', 'avatar-pink', 'avatar-orange'];
?>
<link rel="stylesheet" href="../assets/css/pages/tim.css">
<link rel="stylesheet" href="../assets/css/components.css">

<div class="page-head">
    <div>
        <h1>Kelola Pengajuan Bimbingan</h1>
        <p>Silakan tinjau proposal dan tentukan apakah Anda bersedia menjadi dosen pembimbing untuk tim-tim berikut</p>
    </div>
</div>

<?php if (!empty($errorMsg)): ?>
    <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($errorMsg) ?>
    </div>
<?php elseif (isset($_GET['status_updated'])): ?>
    <div class="alert alert-success" role="alert">
        Status pengajuan bimbingan berhasil diperbarui.
    </div>
<?php endif; ?>

<div class="btn-management-tim">
    <a class="btn btn-navy" href="../bimbingan/list-bimbingan.php">Tim Bimbingan Saya</a>
</div>

<div class="team-header-card">
  <!-- Bagian Kiri: Info Status Ketersediaan Dosen -->
  <div class="team-info">
    <div class="badge-group">
      <span class="badge badge-blue">Dosen Pembimbing</span>
      <?php if ($statusKetersediaan === 'terbuka'): ?>
        <span class="badge badge-green">Menerima Bimbingan</span>
      <?php else: ?>
        <span class="badge">Menutup Bimbingan</span>
      <?php endif; ?>
    </div>

    <h2 class="team-title">Status Ketersediaan: <?= ($statusKetersediaan === 'terbuka') ? 'Terbuka' : 'Ditutup' ?></h2>
    <p class="team-desc">Kuota bimbingan tersisa: <?= (int)$kuotaTersisa ?> Tim. Anda <?= ($statusKetersediaan === 'terbuka') ? 'masih dapat menerima pengajuan baru.' : 'sedang tidak menerima pengajuan baru.' ?></p>
  </div>

  <div class="action-group">
    <button type="button" class="btn-close-vacancy"><?= ($statusKetersediaan === 'terbuka') ? 'Tutup Penerimaan' : 'Buka Penerimaan' ?></button>
    <a href="#" class="btn btn-ghost">Edit Preferensi</a>
  </div>
</div>

<div class="table-card">
  <!-- Header Card -->
  <div class="table-card-header">
    <h3 class="table-card-title">Daftar Pengajuan Tim</h3>
    <span class="table-card-count">Menunggu Tinjauan: <?= $jumlahMenunggu ?> Tim</span>
  </div>

  <div class="table-responsive">
    <table class="table-admin">
      <thead>
        <tr>
          <th>Nama Tim &amp; Ketua</th>
          <th>Kompetisi yang Diikuti</th>
          <th>Proposal/Dokumen</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($daftarPengajuan) > 0): ?>
          <?php foreach ($daftarPengajuan as $index => $item):
            $namaTim = $item['nama_tim'] ?? 'Tim';
            $namaKetua = $item['nama_ketua'] ?? '-';
            $fotoKetua = trim((string)($item['foto_ketua'] ?? ''));
            $namaLomba = $item['nama_lomba'] ?? '-';
            $proposal = trim((string)($item['proposal'] ?? ''));
            $status = strtolower(trim((string)($item['status'] ?? 'menunggu')));
            $avatarClass = $avatarColors[$index % count($avatarColors)];

            $kataTim = preg_split('/\s+/', trim($namaTim));
            $inisial = strtoupper(
                substr($kataTim[0] ?? 'T', 0, 1) .
                (isset($kataTim[1]) ? substr($kataTim[1], 0, 1) : substr($kataTim[0] ?? 'M', 1, 1))
            );
          ?>
            <tr>
              <td>
                <div class="profile-cell">
                  <?php if ($fotoKetua !== ''): ?>
                    <img
                      src="../assets/uploads/profile/<?= htmlspecialchars($fotoKetua) ?>"
                      alt="Foto Profil <?= htmlspecialchars($namaKetua) ?>"
                      class="avatar"
                      style="object-fit: cover;"
                    >
                  <?php else: ?>
                    <div class="avatar <?= $avatarClass ?>"><?= htmlspecialchars($inisial) ?></div>
                  <?php endif; ?>
                  <span class="applicant-name">
                    <?= htmlspecialchars($namaTim) ?><br>
                    <small style="color: #666; font-size: 0.85em;">Ketua: <?= htmlspecialchars($namaKetua) ?></small>
                  </span>
                </div>
              </td>
              <td class="role-cell"><?= htmlspecialchars($namaLomba) ?></td>
              <td>
                <?php if ($proposal !== ''): ?>
                  <a href="<?= htmlspecialchars($proposal) ?>" target="_blank" rel="noopener noreferrer" class="btn-link">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                    </svg>
                    Lihat Proposal
                  </a>
                <?php else: ?>
                  <span style="color: #888; font-size: 0.9em;">Belum ada proposal</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($status === 'menunggu' || $status === 'pending'): ?>
                  <form method="POST" class="action-cell" style="margin: 0;">
                    <input type="hidden" name="pengajuan_id" value="<?= (int)$item['id'] ?>">
                    <button type="submit" name="aksi" value="terima" class="btn btn-navy">Terima</button>
                    <button type="submit" name="aksi" value="tolak" class="btn-reject">Tolak</button>
                  </form>
                <?php elseif ($status === 'diterima'): ?>
                  <span class="badge badge-green">Diterima</span>
                <?php elseif ($status === 'ditolak'): ?>
                  <span class="badge">Ditolak</span>
                <?php else: ?>
                  <span class="badge"><?= htmlspecialchars(ucfirst($status)) ?></span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="4" style="text-align: center; padding: 20px; color: #666;">
              Belum ada pengajuan bimbingan yang masuk.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>