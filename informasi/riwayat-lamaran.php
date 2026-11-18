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
<link rel="stylesheet" href="../assets/css/global.css">

<style>
    .riwayat-container { max: width 100px; margin: 30px auto; padding: 0 16px; }
    .riwayat-header { margin-bottom: 24px; }
    .riwayat-header h1 { font-size: 26px; color: #1e293b; margin-bottom: 6px; }
    .riwayat-header p { color: #64748b; font-size: 14px; }

    /* Styling Toggle / Tab Switcher */
    .toggle-switch-container {
        display: inline-flex;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 99px;
        padding: 4px;
        margin-bottom: 24px;
    }
    .toggle-btn {
        padding: 8px 20px;
        border-radius: 99px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        color: #64748b;
        transition: all 0.2s ease;
    }
    .toggle-btn.active {
        background: #0284c7; /* Warna biru utama (bisa disesuaikan) */
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(2, 132, 199, 0.3);
    }
    .toggle-btn:hover:not(.active) {
        color: #1e293b;
    }

    .table-responsive { width: 100%; overflow-x: auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,.05); border: 1px solid #e2e8f0; }
    .riwayat-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
    .riwayat-table th { background: #f8fafc; color: #475569; font-weight: 600; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; }
    .riwayat-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
    .riwayat-table tr:last-child td { border-bottom: none; }

    .badge-status { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
    .status-pending { background: #fef3c7; color: #d97706; }
    .status-diterima { background: #d1fae5; color: #059669; }
    .status-ditolak { background: #fee2e2; color: #dc2626; }

    .btn-action { padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; font-weight: 500; display: inline-block; margin-right: 4px; margin-bottom: 4px; }
    .btn-detail { background: #1e293b; color: #fff; }
    .btn-detail:hover { background: #0f172a; color: #fff; }
    .btn-wa { background: #25d366; color: #fff; }
    .btn-wa:hover { background: #1eae54; color: #fff; }

    .empty-state { text-align: center; padding: 48px 20px; background: #fff; border-radius: 12px; border: 1px dashed #cbd5e1; }
    .empty-state p { color: #64748b; margin-bottom: 16px; }
</style>

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
                                    <a href="detail-tim.php?id=<?= $tim['id'] ?>" class="btn-action btn-detail">Kelola Tim</a>
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
                                $link_wa = !empty($wa_ketua) ? "https://wa.me/{$wa_ketua}?text=Halo%20kak%20" . urlencode($lamaran['nama_ketua']) . ",%20saya%20melihat%20lamaran%20saya%20diterima%20di%20tim%20" . urlencode($lamaran['nama_tim']) . "." : '#';
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
                <a href="list-tim.php" class="btn-action btn-detail" style="padding: 10px 16px; display: inline-block;">Cari Tim yang Membuka Lowongan</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>