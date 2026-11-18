<?php
session_start();

// 1. Proteksi Akses: Hanya mahasiswa yang boleh masuk
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['mahasiswa', 'dosen'])){
    header('Location: ../auth/login.php');
    exit;
}

// 2. Panggil koneksi database
require_once '../includes/koneksi.php';
$page_title = "Info Lomba";
require_once '../includes/header.php';

$namaMahasiswa = $_SESSION['nama'] ?? 'Mahasiswa';

try {
    $stmt =$pdo->query("SELECT * FROM info_lomba ORDER BY tanggal_penutupan ASC");
    $list_lomba =$stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {$list_lomba = [];
}
?>

<!-- ======================= INFO LOMBA ======================= -->
    <section class="block" aria-labelledby="judul-lomba">
      <div class="sec-head">
        <h2 id="judul-lomba">Info lomba</h2>
      </div>
      
      <div class="grid">
        <?php if (count($list_lomba) > 0): ?>
          <?php foreach ($list_lomba as$lomba):
          // Hitung sisa hari pendaftaran
            $today = new DateTime();$deadline = new DateTime($lomba['tanggal_penutupan']);$sisa_hari = $today->diff($deadline)->format('%r%a');
            // Pecah kategori berdasarkan koma jika ada lebih dari satu
            $kategori_arr = !empty($lomba['kategori']) ? explode(',', $lomba['kategori']) : [];
        ?>

        <article class="card">
          <div class="poster" style="--a:#1e40af; --b:#3b82f6;">
            <?php if (!empty($lomba['poster'])): ?>
                <img src="../assets/uploads/poster/<?= htmlspecialchars($lomba['poster']) ?>" alt="Poster Lomba" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">
            <?php else: ?>
                <span><?= strtoupper(substr($lomba['judul'], 0, 2)) ?></span>
            <?php endif; ?>
          </div>
          <div class="body">
            <div class="tags">
              <?php foreach ($kategori_arr as$kat): ?>
                    <?php if (trim($kat) !== ''): ?>
                        <span class="tag cat"><?= htmlspecialchars(trim($kat)) ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <h3><?= htmlspecialchars($lomba['judul']) ?></h3>
            <p class="desc"><?= htmlspecialchars($lomba['deskripsi']) ?></p>
            <div class="meta">
              <div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Pendaftaran sampai <b><?= date('d M Y', strtotime($lomba['tanggal_penutupan'])) ?></b> 
                <?= ($sisa_hari > 0) ? "($sisa_hari hari lagi)" : "(Ditutup hari ini)" ?></span>
              </div>
            </div>
            <div class="card-actions">
              <a class="btn btn-ghost btn-block" href="<?= !empty($lomba['link_detail']) ? htmlspecialchars($lomba['link_detail']) : '#' ?>">Detail Lomba</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align: center; width: 100%; color: #666;">Belum ada info lomba yang dipublikasikan.</p>
    <?php endif; ?>
      </div>
    </section>

 <?php
  require_once '../includes/footer.php';
 ?>