<?php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['mahasiswa', 'dosen'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/koneksi.php';
$page_title = "Info Dosen";
require_once '../includes/header.php';

$namaMahasiswa = $_SESSION['nama'] ?? 'Mahasiswa';

$q = $_GET['q'] ?? '';
$sql = "SELECT u.id, u.nama, pd.foto_profil, pd.bidang_kepakaran, pd.pengalaman
        FROM users u 
        JOIN profil_dosen pd ON u.id = pd.user_id 
        WHERE u.role = 'dosen'";
$params = [];

if (!empty($q)) {
    $sql .= " AND (u.nama ILIKE ? OR pd.bidang_kepakaran ILIKE ?)";
    $params = ["%$q%", "%$q%"];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$dosen_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/pages/dosen.css">
<link rel="stylesheet" href="../assets/css/pages/tim.css">
<link rel="stylesheet" href="../assets/css/global.css">

<!-- ======================= INFO DOSEN ======================= -->
<section class="block" aria-labelledby="judul-dosen">
    <div class="page-head">
            <h1>Profile Dosen</h2>
            <!-- <p>Temukan dosen sesuai dengan bidang lombamu yaa!</p> -->
    </div>

    <div class="toolbar">
        <form action="" method="GET">
            <!-- Input Pencarian -->
            <label class="field-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
                <input name="q" type="search" value="<?= htmlspecialchars($q) ?>" placeholder="Cari nama dosen, bisa melalui bidangnya">
            </label>

            <!-- Tombol Cari -->
            <button class="btn btn-navy toolbar-btn" type="submit">Terapkan</button>
        </form>
    </div>

    <div class="grid">
        <?php if (count($dosen_list) > 0): ?>
            <?php foreach ($dosen_list as $dosen): ?>
                <?php
                    $fotoProfil = trim($dosen['foto_profil'] ?? '');
                    $srcFoto = $fotoProfil !== '' ? '../assets/uploads/profile/' . basename($fotoProfil) : '';
                    $keahlian_arr = !empty($dosen['bidang_kepakaran']) ? explode(',', $dosen['bidang_kepakaran']) : [];
                    $inisial = strtoupper(mb_substr(trim($dosen['nama']), 0, 2));
                ?>
                <article class="card">
                    <div class="poster" style="--a:#1e40af; --b:#3b82f6;">
                        <?php if ($srcFoto !== ''): ?>
                            <img src="<?= htmlspecialchars($srcFoto) ?>"
                                 alt="Foto <?= htmlspecialchars($dosen['nama']) ?>"
                                 style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <span style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center;"><?= htmlspecialchars($inisial) ?></span>
                        <?php else: ?>
                            <span><?= htmlspecialchars($inisial) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="body">
                        <div class="tags">
                            <?php if (count($keahlian_arr) > 0): ?>
                                <?php foreach ($keahlian_arr as $ahli): ?>
                                    <?php if (trim($ahli) !== ''): ?>
                                        <span class="tag cat"><?= htmlspecialchars(trim($ahli)) ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="tag cat">Belum mengatur bidang kepakaran</span>
                            <?php endif; ?>
                        </div>

                        <h3><?= htmlspecialchars($dosen['nama']) ?></h3>
                        <p class="desc"><?= htmlspecialchars(!empty($dosen['pengalaman']) ? $dosen['pengalaman'] : 'Siap membimbing mahasiswa untuk persiapan lomba dan riset.') ?></p>

                        <div class="card-actions">
                        <a class="btn btn-orange btn-block" href="bimbingan.php?dosen_id=<?= (int) $dosen['id'] ?>">Ajukan Bimbingan</a>                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; width: 100%; color: #666;">Tidak ada dosen yang ditemukan.</p>
        <?php endif; ?>
    </div>
</section>

<?php
require_once '../includes/footer.php';
?>