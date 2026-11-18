<?php
session_start();


if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['mahasiswa', 'dosen'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/koneksi.php';
$page_title = "Info Lomba";
require_once '../includes/header.php';

$namaMahasiswa = $_SESSION['nama'] ?? 'Mahasiswa';

$q = $_GET['q'] ?? '';
$sql = "SELECT u.id, u.nama, pd.bidang_kepakaran 
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


<div class="page-head">
    <div>
        <h1>Profile Dosen</h1>
        <p>Temukan dosen sesuai dengan bidang lombamu yaa!</p>
    </div>
</div>

<div class="toolbar">
    <form action="" method="GET">
        <!-- Input Pencarian -->
        <label class="field-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.3-4.3" />
            </svg>
            <input name="q" type="search" placeholder="Cari nama dosen, bisa melalui bidangnya">
        </label>

        <!-- Tombol Cari -->
        <button class="btn btn-navy toolbar-btn" type="submit">Terapkan</button>
    </form>
</div>

<div class="profile-section">
    <div class="card-grid">
        <?php if (count($dosen_list) > 0): ?>
            <?php foreach ($dosen_list as $dosen): ?>
                <div class="card">
                    <div class="card-image-placeholder">
                        <span>Foto dosen, blurrrr</span>
                    </div>
                    <div class="card-content">
                        <p class="dosen-name"><?= htmlspecialchars($dosen['nama']) ?></p>
                        <p class="dosen-desc">Keahlian: <?= htmlspecialchars($dosen['bidang_kepakaran'] ?? 'Belum mengatur bidang kepakaran') ?></p>
                        <a href="form-pengajuan.php?dosen_id=<?= $dosen['id'] ?>" class="btn btn-orange" style="display:block; text-align:center; text-decoration:none;">Ajukan Bimbingan</a>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-data">
                <p>Tidak ada dosen yang ditemukan.</p>
            </div>
    </div>
</div>
<?php endif; ?>

<?php
require_once '../includes/footer.php';
?>