<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['mahasiswa'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/koneksi.php';

// Tentukan tab aktif (default ke 'lamaran' jika tidak ada di URL)
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'lamaran';

$page_title = ($tab === 'tim' ? "Riwayat Pembuatan Tim" : "Riwayat Lamaran") . " | TEMUIN";
require_once '../includes/header.php';

$user_id = $_SESSION['user_id'];
$riwayat_lamaran = [];
$riwayat_tim = [];
$error_msg = null;

try {
    if ($tab === 'tim') {
        // Logika untuk mengambil data riwayat pembuatan tim (dimana user sebagai ketua)
        $stmt = $pdo->prepare("
            SELECT t.*, 
                   (SELECT COUNT(*) FROM lamaran_anggota WHERE tim_id = t.id) AS total_pelamar,
                   (SELECT COUNT(*) FROM lamaran_anggota WHERE tim_id = t.id AND status = 'diterima') AS total_diterima
            FROM tim_lomba t
            WHERE t.ketua_id = ?
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $riwayat_tim = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Logika awal: Ambil daftar riwayat lamaran beserta data tim dan nomor WhatsApp ketua
        $stmt = $pdo->prepare("
            SELECT l.*, t.kategori, t.nama_lomba, t.nama_tim, t.status_tim, u.nama AS nama_ketua, u.no_wa AS whatsapp_ketua 
            FROM lamaran_anggota l
            JOIN tim_lomba t ON l.tim_id = t.id
            JOIN users u ON t.ketua_id = u.id
            WHERE l.kandidat_id = ?
            ORDER BY l.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $riwayat_lamaran = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $error_msg = "Gagal memuat data: " . $e->getMessage();
}
?>

<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/pages/tim.css">
<link rel="stylesheet" href="../assets/css/pages/riwayat.css">
<link rel="stylesheet" href="../assets/css/global.css">

<div class="page-head">
        <div class="page-title-riwayat">
        <h1><?= $tab === 'tim' ? 'Riwayat Pembuatan Tim' : 'Riwayat Lamaran Tim' ?></h1>
        <p><?= $tab === 'tim' ? 'Kelola dan pantau tim lomba yang telah kamu bentuk.' : 'Kelola dan pantau status lamaran yang telah kamu kirimkan ke berbagai tim lomba.' ?></p>
        </div>
        
    <!-- Toggle Switch / Tab Navigation -->
    <div class="toggle-switch-container">
        <a href="?tab=lamaran" class="toggle-btn <?= $tab === 'lamaran' ? 'active' : '' ?>">Riwayat Lamaran</a>
        <a href="?tab=tim" class="toggle-btn <?= $tab === 'tim' ? 'active' : '' ?>">Riwayat Pembuatan Tim</a>
    </div>
    </div>

<div class="riwayat-container">
    <?php if (isset($error_msg)): ?>
        <div style="padding: 12px; background: #fee2e2; color: #dc2626; border-radius: 8px; margin-bottom: 16px;">
            <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>

    <?php if ($tab === 'tim'): ?>
        <?php if (!empty($riwayat_tim)): ?>
            <div class="table-responsive">
                <table class="riwayat-table">
                    <thead>
                        <tr>
                            <th>Nama Tim & Lomba</th>
                            <th>Kategori</th>
                            <th>Status Tim</th>
                            <th>Total Pelamar</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($riwayat_tim as $tim): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($tim['nama_tim']) ?></strong><br>
                                    <small style="color: #64748b;"><?= htmlspecialchars($tim['nama_lomba']) ?></small>
                                </td>
                                <td><span class="badge badge-blue"><?= htmlspecialchars($tim['kategori']) ?></span></td>
                                <td>
                                    <span class="badge-status <?= $tim['status_tim'] == 'Open' ? 'status-diterima' : 'status-ditolak' ?>">
                                        <?= htmlspecialchars($tim['status_tim']) ?>
                                    </span>
                                </td>
                                <td><?= $tim['total_pelamar'] ?> Pelamar (<?= $tim['total_diterima'] ?> Diterima)</td>
                                <td><?= date('d M Y', strtotime($tim['created_at'])) ?></td>
                                <td>
                                    <a href="../tim/kelola-tim.php?= $tim['id'] ?>" class="btn-action btn-navy">Kelola Tim</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>Anda belum pernah membuat atau membentuk tim lomba.</p>
                <a href="buat-tim.php" class="btn btn-navy" style="padding: 10px 16px; display: inline-block;">Buat Tim Baru Sekarang</a>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <?php if (!empty($riwayat_lamaran)): ?>
            <div class="table-responsive">
                <table class="riwayat-table">
                    <thead>
                        <tr>
                            <th>Informasi</th>
                            <th>Posisi</th>
                            <th>Ketua Tim</th>
                            <th>Tanggal Kirim</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($riwayat_lamaran as $lamaran): ?>
                            <?php 
                                $status = strtolower($lamaran['status']);

                                // Format nomor WhatsApp Ketua Tim
                                $wa_raw = preg_replace('/[^0-9]/', '', $lamaran['whatsapp_ketua'] ?? '');
                                if (substr($wa_raw, 0, 1) === '0') {
                                    $wa_ketua = '62' . substr($wa_raw, 1);
                                } else {
                                    $wa_ketua = $wa_raw;
                                }

                                $pesan = "Halo kak " . $lamaran['nama_ketua'] . ",\n"
                                . " saya melihat lamaran saya diterima di tim " . $lamaran['nama_tim'] . "\n"
                                . " Boleh lanjut diskusi teknisnya?";

                                $link_wa = !empty($wa_ketua) 
                                ? "https://wa.me/{$wa_ketua}?text=" . urldecode($pesan) : '#';
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($lamaran['nama_tim']) ?></strong><br>
                                    <small style="color: #64748b;"><?= htmlspecialchars($lamaran['nama_lomba']) ?></small>
                                </td>
                                <td><span class="badge badge-blue"><?= htmlspecialchars($lamaran['posisi_dilamar']) ?></span></td>
                                <td><?= htmlspecialchars($lamaran['nama_ketua']) ?></td>
                                <td><?= date('d M Y', strtotime($lamaran['created_at'])) ?></td>
                                <td>
                                    <?php if ($status === 'diterima'): ?>
                                        <span class="badge-status status-diterima">Diterima🎉</span>
                                    <?php elseif ($status === 'ditolak'): ?>
                                        <span class="badge-status status-ditolak">Ditolak</span>
                                    <?php else: ?>
                                        <span class="badge-status status-pending">Menunggu</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($status === 'diterima' && !empty($wa_ketua)): ?>
                                        <a href="<?= htmlspecialchars($link_wa, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="bgn-action btn-wa" style="padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; font-weight: 500; display: inline-block; background: #25d366; color: #fff;">Chat WhatsApp</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>Anda belum pernah mengirim lamaran ke tim manapun.</p>
                <a href="list-tim.php" class="btn btn-navy" style="padding: 10px 16px; display: inline-block;">Cari Tim yang Membuka Lowongan</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>