<?php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['mahasiswa', 'dosen'])){
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/koneksi.php';
$page_title = "List Tim | TEMUIN";
require_once __DIR__ . '/../includes/header.php';

$namaMahasiswa = $_SESSION['nama'] ?? 'Mahasiswa';

$q = $_GET['q'] ?? '';
$sort = $_GET['sort'] ?? 'terdekat';
$order = ($sort === 'terlama') ? 'DESC' : 'ASC';

$sql = "SELECT t.*, u.nama AS nama_ketua 
        FROM tim_lomba t 
        JOIN users u ON t.ketua_id = u.id 
        WHERE t.status_tim = 'open'";
$params = [];

if (!empty($q)) {
    $sql .= " AND (t.nama_tim ILIKE ? OR t.nama_lomba ILIKE ? OR t.skills_dibutuhkan ILIKE ?)";
    $params = ["%$q%", "%$q%", "%$q%"];
}
$sql .= " ORDER BY t.tanggal_penutupan $order";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tim_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] !== 'dosen'): ?>
        <a class="btn btn-navy" href="../tim/kelola-tim.php">Kelola Tim saya</a>
    <?php endif; ?>
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
        <?php if(count($tim_list) > 0): ?>
          <?php foreach ($tim_list as $tim):
          // Kalkulasi sisa hari pendaftaran
                $today = new DateTime();
                $deadline = new DateTime($tim['tanggal_penutupan']);
                $sisa_hari = $today->diff($deadline)->format('%r%a');
                $status_teks = ($sisa_hari <= 3) ? "Segera ditutup" : "Menerima anggota";
                $status_class = ($sisa_hari <= 3) ? "warn" : "";
                // Pecah string skills dari database menjadi array
                $skills = explode(',', $tim['skills_dibutuhkan']);
            ?>
        <!-- ================= CARD ================= -->
        <article class="card">
          <div class="poster" style="--a:#1f2d6b; --b:#44639a;">
            <span class="status <?= $status_class ?>"><?= $status_teks ?></span>
            <?php if (!empty($tim['gambar'])): ?>
        <!-- Menampilkan gambar dari database -->
        <img src="../<?= htmlspecialchars($tim['gambar']) ?>" alt="Poster Tim" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">
    <?php else: ?>
        <!-- Fallback ke inisial jika gambar tidak ada -->
        <span><?= strtoupper(substr($tim['nama_lomba'], 0, 2)) ?></span>
    <?php endif; ?>
          </div>

          <div class="body">
            <h3><?= htmlspecialchars($tim['nama_lomba']) ?></h3>
            <p class="desc">Tim <?= htmlspecialchars($tim['nama_tim']) ?>, ketua <?= htmlspecialchars($tim['nama_ketua']) ?></p>

            <div class="tags">
              <?php foreach($skills as $skill): ?>
              <?php if(trim($skill) !== ''): ?>
              <span class="tag tag-role"><?= htmlspecialchars(trim($skill)) ?></span>
              <?php endif; ?>
              <?php endforeach; ?>
            </div>
            <div class="meta">
              <div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Daftar sampai <b><?= date('d M', strtotime($tim['tanggal_penutupan'])) ?></b> 
                <?= ($sisa_hari > 0) ? "($sisa_hari hari lagi)" : "(Ditutup hari ini)" ?></span>
              </div>
              <div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Mencari <?= $tim['jumlah_anggota'] ?> anggota</span>
              </div>
            </div>
            <div class="cta">
              <a class="btn btn-ghost" href="apply-tim.php?id=<?= $tim['id'] ?>" style="width: 100%;">Detail</a>
              <a class="btn btn-orange" href="apply-tim.php?id=<?= $tim['id'] ?>" style="width: 100%;">Gabung</a>
            </div>
          </div>
        </article>

        <?php endforeach; ?>
        <?php else: ?>
            <p class="no-data">Tidak ada tim yang ditemukan.</p>
        <?php endif; ?>
      </div>

</section>
<?php
require_once '../includes/footer.php';
?>