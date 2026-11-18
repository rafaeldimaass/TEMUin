<?php
session_start();

// 1. Proteksi Akses: Hanya mahasiswa yang boleh masuk
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
  header('Location: ../auth/login.php');
  exit;
}

// 2. Panggil koneksi database
require_once '../includes/koneksi.php';
$page_title = "Dashboard Mahasiswa";
require_once '../includes/header.php';


$namaMahasiswa = $_SESSION['nama'] ?? 'Mahasiswa';
$user_id = $_SESSION['user_id'];

// 3. Ambil data profil mahasiswa untuk rekomendasi dan foto profil
try {
  $stmtProfil = $pdo->prepare("SELECT skill, foto_profil FROM profil_mahasiswa WHERE user_id = ?");
  $stmtProfil->execute([$user_id]);
  $profil = $stmtProfil->fetch(PDO::FETCH_ASSOC);
  $user_skills = $profil['skill'] ?? '';
  $user_foto_profil = $profil['foto_profil'] ?? '';
} catch (PDOException $e) {
  $user_skills = '';
  $user_foto_profil = '';
}

// 4. Ambil Data Tim yang Buka (Status 'open') beserta Nama Ketua dan Foto Profil Ketua
try {
  $stmtTim = $pdo->query("SELECT t.*, u.nama AS nama_ketua, pm.foto_profil AS foto_profil
    FROM tim_lomba t
    LEFT JOIN users u ON t.ketua_id = u.id
    LEFT JOIN profil_mahasiswa pm ON pm.user_id = u.id
    WHERE t.status_tim = 'open'
    ORDER BY t.tanggal_penutupan ASC
    LIMIT 4");
  $listTeams = $stmtTim->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $listTeams = [];
}

// 5. Ambil Data Info Lomba dari Database
try {
  $stmtLomba = $pdo->query("SELECT * FROM info_lomba ORDER BY tanggal_penutupan ASC LIMIT 3");
  $listLomba = $stmtLomba->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $listLomba = [];
}

?>
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/pages/dashboard.css">

<!-- ======================= HERO SECTION ======================= -->
<section class="hero" aria-labelledby="judul-hero">
  <div>
    <h1 id="judul-hero">Halo, <?= htmlspecialchars($namaMahasiswa) ?>! Lomba apa yang mau kamu ikuti?</h1>
    <p class="lead">Gabung ke tim yang sedang mencari anggota, atau buat timmu sendiri dan ajak dosen pembimbing.</p>

    <form class="search-form" role="search" id="searchForm">
      <label class="field">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8" />
          <path d="m21 21-4.3-4.3" />
        </svg>
        <input id="q" type="search" placeholder="Cari lomba, tim, atau peran (mis. UI/UX)">
      </label>
      <button class="btn btn-orange" type="submit">Cari</button>
    </form>
  </div>

  <div class="hero-stats">
    <!-- Stat 1: Tim mencari anggota -->
    <div class="stat">
      <div class="ic">
        <!-- Ikon Users -->
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
          <circle cx="9" cy="7" r="4" />
          <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
          <path d="M16 3.13a4 4 0 0 1 0 7.75" />
        </svg>
      </div>
      <div>
        <b><?= count($listTeams) ?></b>
        <span>tim mencari anggota</span>
      </div>
    </div>

    <!-- Stat 2: Lomba sedang dibuka -->
    <div class="stat">
      <div class="ic">
        <!-- Ikon Trophy -->
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6" />
          <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18" />
          <path d="M4 22h16" />
          <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22" />
          <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22" />
          <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z" />
        </svg>
      </div>
      <div>
        <b><?= count($listLomba) ?></b>
        <span>lomba sedang dibuka</span>
      </div>
    </div>
  </div>
</section>

<!-- ======================= TIM MENCARI ANGGOTA ======================= -->
<section class="block" aria-labelledby="judul-tim">
  <div class="page-head">
    <h1>Tim yang sedang mencari anggota</h1>
    <a href="../tim/list-tim.php">Lihat semua</a>
  </div>

  <div class="grid">
    <?php if (count($listTeams) > 0): ?>
      <?php foreach ($listTeams as $team):
        $skills_req = explode(',', $team['skills_dibutuhkan'] ?? '');
      ?>
        <!-- Card -->
        <article class="card">
          <div class="poster" style="--a:#1f2d6b; --b:#44639a;">
            <span class="status">Menerima anggota</span>
            <?php if (!empty($team['gambar'])): ?>
              <img src="../<?= htmlspecialchars($team['gambar']) ?>" alt="Poster" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">
            <?php else: ?>
              <span><?= strtoupper(substr($team['nama_lomba'], 0, 2)) ?></span>
            <?php endif; ?>
          </div>

          <div class="body">
            <h3><?= htmlspecialchars($team['nama_lomba']) ?></h3>
            <div class="team-meta-info" style="display: flex; align-items: center; gap: 6px; margin: 4px 0 8px; font-size: 15px; font-weight: 700;">
            <span style="font-size: 17px;">👥</span>
            <span>Nama Tim: <b><?= htmlspecialchars($team['nama_tim']) ?></b></span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: var(--muted);">
              <?php if (!empty($team['foto_profil'])): ?>
                <img src="../assets/uploads/profile/<?= htmlspecialchars($team['foto_profil']) ?>" alt="Foto Profil" style="width: 22px; height: 22px; border-radius: 50%; object-fit: cover;">
              <?php else: ?>
                <!-- Fallback inisial nama jika ketua belum upload foto profil -->
                <div style="width: 22px; height: 22px; border-radius: 50%; background: var(--yellow); color: var(--navy-900); display: grid; place-items: center; font-size: 10px; font-weight: 700;">
                  <?= strtoupper(substr($team['nama_ketua'], 0, 2)) ?>
                </div>
              <?php endif; ?>
              <span>Ketua: <b><?= htmlspecialchars($team['nama_ketua']) ?></b></span>
            </div>
            <div class="tags">
              <?php foreach ($skills_req as $s): ?>
                <?php if (trim($s) !== ''): ?>
                  <span class="tag tag-role"><?= htmlspecialchars(trim($s)) ?></span>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
            <div class="card-actions">
              <a class="btn btn-ghost" href="../tim/apply-tim.php?id=<?= $team['id'] ?>" style="width: 100%;">Detail</a>
              <a class="btn btn-orange" href="../tim/apply-tim.php?id=<?= $team['id'] ?>" style="width: 100%;">Gabung</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p>Belum ada tim yang membuka rekrutmen saat ini.</p>
    <?php endif; ?>
  </div>
</section>

<!-- ======================= INFO LOMBA ======================= -->
<section class="block" aria-labelledby="judul-lomba">
  <div class="page-head">
    <h1>Info lomba</h1>
    <a href="lomba.php">Lihat semua</a>
  </div>

  <div class="grid">
    <?php if (count($listLomba) > 0): ?>
      <?php foreach ($listLomba as $lomba):
        $today = new DateTime();
        $deadline = new DateTime($lomba['tanggal_penutupan']);
        $sisa_hari = $today->diff($deadline)->format('%r%a');
        $kategori_arr = !empty($lomba['kategori']) ? explode(',', $lomba['kategori']) : [];
      ?>
        <article class="card">
          <div class="poster" style="--a:#1e40af; --b:#3b82f6;">
            <?php if (!empty($lomba['poster'])): ?>
              <img src="../assets/uploads/poster/<?= htmlspecialchars($lomba['poster']) ?>" alt="Poster" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">
            <?php else: ?>
              <span><?= strtoupper(substr($lomba['judul'], 0, 2)) ?></span>
            <?php endif; ?>
          </div>

          <div class="body">
            <div class="tags">
              <?php foreach ($kategori_arr as $kat): ?>
                <?php if (trim($kat) !== ''): ?>
                  <span class="tag cat"><?= htmlspecialchars(trim($kat)) ?></span>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>

            <h3><?= htmlspecialchars($lomba['judul']) ?></h3>
            <p class="desc"><?= htmlspecialchars($lomba['deskripsi']) ?></p>

            <div class="meta">
              <div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                  <line x1="16" y1="2" x2="16" y2="6" />
                  <line x1="8" y1="2" x2="8" y2="6" />
                  <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                <span>Pendaftaran sampai <b><?= date('d M Y', strtotime($lomba['tanggal_penutupan'])) ?></b>
                  <?= ($sisa_hari > 0) ? "($sisa_hari hari lagi)" : "(Ditutup hari ini)" ?>
                </span>
              </div>
            </div>

            <div class="card-actions">
              <a class="btn btn-ghost" href="<?= !empty($lomba['link_detail']) ? htmlspecialchars($lomba['link_detail']) : '#' ?>" target="_blank">Detail Lomba</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p>Belum ada info lomba yang tersedia.</p>
    <?php endif; ?>
  </div>
</section>

<?php
require_once '../includes/footer.php';
?>