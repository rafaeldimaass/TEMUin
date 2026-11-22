<?php
session_start();

// 1. Proteksi akses: khusus mahasiswa yang sudah login
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'mahasiswa') {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/koneksi.php';

$userId = (int) $_SESSION['user_id'];
$errorMsg = '';
$successMsg = '';

// 2. Ambil daftar tim yang diketuai mahasiswa ini
$timSaya = [];
try {
    $stmtTim = $pdo->prepare("
        SELECT id, nama_tim, nama_lomba
        FROM tim_lomba
        WHERE ketua_id = :ketua_id
        ORDER BY id DESC
    ");
    $stmtTim->execute([':ketua_id' => $userId]);
    $timSaya = $stmtTim->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMsg = "Gagal memuat data tim: " . $e->getMessage();
}

// 3. Ambil dosen_id dari GET saat pertama masuk, atau POST saat form dikirim
$dosenId = filter_input(INPUT_GET, 'dosen_id', FILTER_VALIDATE_INT)
    ?: (filter_input(INPUT_POST, 'dosen_id', FILTER_VALIDATE_INT) ?: 0);

// Ambil detail dosen tujuan (pastikan role = 'dosen')
$dosenDipilih = null;
if ($dosenId > 0) {
    try {
        $stmtDosen = $pdo->prepare("
        SELECT
        u.id,
        u.nama,
        u.email,
        pd.foto_profil AS profil_dosen
        FROM users u
        LEFT JOIN profil_dosen pd ON pd.user_id = u.id
        WHERE u.id = :id
        AND u.role = 'dosen'
        LIMIT 1
        ");        
        $stmtDosen->execute([':id' => $dosenId]);
        $fetchedDosen = $stmtDosen->fetch(PDO::FETCH_ASSOC);
        if ($fetchedDosen) {
            $dosenDipilih = $fetchedDosen;
        }
    } catch (PDOException $e) {
        $errorMsg = "Gagal memuat data dosen: " . $e->getMessage();
    }
}

if (!$dosenDipilih && $errorMsg === '') {
    $errorMsg = "Dosen pembimbing tidak ditemukan. Silakan pilih dosen dari daftar dosen pembimbing.";
}

// 4. Proses submit form pengajuan bimbingan (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $timId = filter_input(INPUT_POST, 'tim_id', FILTER_VALIDATE_INT);
    $postDosenId = filter_input(INPUT_POST, 'dosen_id', FILTER_VALIDATE_INT);
    $proposalInput = trim((string) ($_POST['proposal'] ?? ''));

    // Validasi kepemilikan tim agar mahasiswa hanya bisa mengajukan untuk tim miliknya
    $timValid = false;
    foreach ($timSaya as $t) {
        if ((int) $t['id'] === $timId) {
            $timValid = true;
            break;
        }
    }

    // Validasi dosen berdasarkan $dosenDipilih yang sudah diverifikasi dari DB
    $dosenValid = $dosenDipilih && $postDosenId && ((int) $dosenDipilih['id'] === $postDosenId);

    if (!$timId || !$timValid) {
        $errorMsg = "Pilih tim yang valid terlebih dahulu.";
    } elseif (!$dosenValid) {
        $errorMsg = "Dosen pembimbing tidak valid.";
    } elseif ($proposalInput === '') {
        $errorMsg = "Link atau catatan proposal/dokumen wajib diisi.";
    } else {
        try {
            // Cek apakah tim ini sudah pernah mengajukan ke dosen yang sama (kecuali jika sebelumnya ditolak)
            $stmtCek = $pdo->prepare("
                SELECT id, status
                FROM pengajuan_bimbingan
                WHERE tim_id = :tim_id AND dosen_id = :dosen_id
                ORDER BY id DESC
                LIMIT 1
            ");
            $stmtCek->execute([
                ':tim_id'   => $timId,
                ':dosen_id' => (int) $dosenDipilih['id'],
            ]);
            $existing = $stmtCek->fetch(PDO::FETCH_ASSOC);

            if ($existing && strtolower((string) $existing['status']) !== 'ditolak') {
                $errorMsg = "Tim ini sudah memiliki pengajuan bimbingan ke dosen tersebut dengan status: " . htmlspecialchars($existing['status']) . ".";
            } else {
                $stmtInsert = $pdo->prepare("
                    INSERT INTO pengajuan_bimbingan (tim_id, dosen_id, proposal, status)
                    VALUES (:tim_id, :dosen_id, :proposal, 'menunggu')
                ");
                $stmtInsert->execute([
                    ':tim_id'   => $timId,
                    ':dosen_id' => (int) $dosenDipilih['id'],
                    ':proposal' => $proposalInput,
                ]);

                header("Location: " . basename(__FILE__) . "?dosen_id=" . (int) $dosenDipilih['id'] . "&submitted=1");
                exit;
            }
        } catch (PDOException $e) {
            $errorMsg = "Gagal menyimpan pengajuan bimbingan: " . $e->getMessage();
        }
    }
}

if (isset($_GET['submitted']) && $_GET['submitted'] === '1') {
    $successMsg = "Pengajuan bimbingan berhasil dikirim dan menunggu tinjauan dosen.";
}

// 5. Ambil riwayat pengajuan bimbingan milik tim mahasiswa ini
$riwayatPengajuan = [];
try {
    $stmtRiwayat = $pdo->prepare("
        SELECT
            pb.id,
            pb.proposal,
            pb.status,
            pb.created_at,
            tl.nama_tim,
            tl.nama_lomba,
            ud.nama AS nama_dosen
        FROM pengajuan_bimbingan pb
        JOIN tim_lomba tl ON pb.tim_id = tl.id
        LEFT JOIN users ud ON pb.dosen_id = ud.id
        WHERE tl.ketua_id = :ketua_id
        ORDER BY pb.created_at DESC
    ");
    $stmtRiwayat->execute([':ketua_id' => $userId]);
    $riwayatPengajuan = $stmtRiwayat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMsg = $errorMsg ?: "Gagal memuat riwayat pengajuan: " . $e->getMessage();
}

$fotoProfil = trim((string) ($dosenDipilih['profil_dosen'] ?? ''));
if ($fotoProfil !== '') {
    if (preg_match('#^https?://#i', $fotoProfil)) {
        $fotoUrl = $fotoProfil;
    } elseif (str_starts_with($fotoProfil, 'assets/')) {
        $fotoUrl = '../' . $fotoProfil;
    } elseif (str_starts_with($fotoProfil, 'uploads/')) {
        $fotoUrl = '../assets/' . $fotoProfil;
    } elseif (str_starts_with($fotoProfil, '/')) {
        $fotoUrl = '..' . $fotoProfil;
    } else {
        $fotoUrl = '../assets/uploads/profile/' . ltrim($fotoProfil, '/');
    }
    } else {
    $fotoUrl = '';
}

$page_title = "Ajukan Bimbingan | TEMUIN";
require_once __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/pages/tim.css">

<div class="page-head">
    <div>
        <h1>Ajukan Bimbingan</h1>
        <p>Pilih tim dan dosen pembimbing, lalu lampirkan tautan proposal atau dokumen lomba.</p>
    </div>
</div>

<?php if ($errorMsg !== ''): ?>
    <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($errorMsg) ?>
    </div>
<?php elseif ($successMsg !== ''): ?>
    <div class="alert alert-success" role="alert">
        <?= htmlspecialchars($successMsg) ?>
    </div>
<?php endif; ?>


<!-- <div class="team-header-card"> -->
    <form method="POST" class="tim-form">

        <?php if ($dosenDipilih): ?>
            <input type="hidden" name="dosen_id" value="<?= (int) $dosenDipilih['id'] ?>">
            <div class="form-group">
                <label><b>Dosen Pembimbing Tujuan</b></label>
                <div style="margin-bottom: 10px;">
                    <b><?= htmlspecialchars($dosenDipilih['nama']) ?></b>
                    <?php if (!empty($dosenDipilih['email'])): ?>
                        <small style="color: #666;">(<?= htmlspecialchars($dosenDipilih['email']) ?>)</small>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Informasi Utama Dosen -->
            <div class="team-header-card">
    <div class="team-img">
        <?php if (!empty($dosenDipilih['profil_dosen'])): ?>
            <img
                src="../assets/uploads/profile/<?= htmlspecialchars(ltrim($dosenDipilih['profil_dosen'], '/')) ?>"
                alt="<?= htmlspecialchars($dosenDipilih['nama']) ?>"
                style="width: 70px; height: 70px; object-fit: cover; border-radius: 50%;"
            >
        <?php else: ?>
            <span style="font-weight: bold; color: #1f2d6b; font-size: 1.5rem;">
                <?= htmlspecialchars(strtoupper(substr($dosenDipilih['nama'], 0, 2))) ?>
            </span>
        <?php endif; ?>
    </div>

                <!-- Detail dosen -->
                <div class="team-detail-info">
                    <div class="badge">
                        <span class="badge badge-green">Pembimbing</span>
                        <?php if (!empty($dosenDipilih['email'])): ?>
                            <span class="badge badge-blue"><?= htmlspecialchars($dosenDipilih['email']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
        <?php endif; ?>

        <div class="tim-input-group">
            <label for="tim_id"><b>Pilih Tim</b></label>
            <select name="tim_id" id="tim_id" required class="tim-form-control" required style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                <option value="">-- Pilih Tim Anda --</option>
                <?php foreach ($timSaya as $tim): ?>
                    <option value="<?= (int) $tim['id'] ?>" <?= (($_POST['tim_id'] ?? '') == $tim['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tim['nama_tim']) ?> (<?= htmlspecialchars($tim['nama_lomba']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (count($timSaya) === 0): ?>
                <small style="color: #666;">Kamu belum terdaftar sebagai ketua tim lomba.</small>
            <?php endif; ?>
        </div>

        <div class="tim-input-group">
            <label for="proposal"><b>Link Proposal / Dokumen</b></label>
            <input
                type="text"
                name="proposal"
                id="proposal"
                placeholder="Link proposal tim kamu yaa"
                value="<?= htmlspecialchars($_POST['proposal'] ?? '') ?>"
                required
            >
        </div>

        <div class="card-actions" style="justify-content: flex-end; gap: 10px;">
            <a href="../dashboard.php" class="btn btn-ghost">Kembali</a>
            <button type="submit" class="btn btn-orange" <?= (!$dosenDipilih || count($timSaya) === 0) ? 'disabled' : '' ?>>
                Kirim Pengajuan
            </button>
        </div>
    </form>
<!-- </div> -->

<div class="table-card" style="margin-top: 24px;">
    <div class="table-card-header">
        <h3 class="table-card-title">Riwayat Pengajuan Bimbingan</h3>
        <span class="table-card-count"><?= count($riwayatPengajuan) ?> Pengajuan</span>
    </div>

    <div class="table-responsive">
        <table class="table-admin">
            <thead>
                <tr>
                    <th>Nama Tim & Nama Lomba</th>
                    <th>Dosen Pembimbing</th>
                    <th>Proposal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($riwayatPengajuan) > 0): ?>
                    <?php foreach ($riwayatPengajuan as $row):
                        $st = strtolower(trim((string) ($row['status'] ?? 'menunggu')));
                        $prop = trim((string) ($row['proposal'] ?? ''));
                    ?>
                        <tr>
                            <td>
                                <b><?= htmlspecialchars($row['nama_tim']) ?></b><br>
                                <small style="color: #666;"><?= htmlspecialchars($row['nama_lomba']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($row['nama_dosen'] ?? '-') ?></td>
                            <td>
                                <?php if (filter_var($prop, FILTER_VALIDATE_URL)): ?>
                                    <a href="<?= htmlspecialchars($prop) ?>" target="_blank" rel="noopener noreferrer" class="btn-link">
                                        Lihat Proposal
                                    </a>
                                <?php else: ?>
                                    <?= htmlspecialchars($prop) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($st === 'diterima'): ?>
                                    <span class="badge badge-green">Diterima</span>
                                <?php elseif ($st === 'ditolak'): ?>
                                    <span class="badge">Ditolak</span>
                                <?php else: ?>
                                    <span class="badge badge-blue">Menunggu</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px; color: #666;">
                            Belum ada pengajuan bimbingan yang dikirim.
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