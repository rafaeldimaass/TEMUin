<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/koneksi.php';
$page_title = "Data Kompetisi";
require_once '../includes/sidebar.php';

$q = trim($_GET['q'] ?? '');


$info_lomba = [];
$error_message = '';

try {
    $query = 'SELECT id, judul, kategori, deskripsi, poster, link_detail, tanggal_penutupan FROM info_lomba';
    $params = [];
    if ($q !== '') {
        $query .= ' WHERE judul LIKE :q';
        $params[':q'] = '%' . $q . '%';
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $info_lomba = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $info_lomba = [];
    $error_message = "Gagal memuat data: " . $e->getMessage();
}
?>

<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/pages/admin.css">

<div class="page-head">
    <div>
        <h1>Data Kompetisi</h1>
        <p>Kelola data kompetisi yang tersedia di sistem.</p>
    </div>
    <div class="btn-create">
        <a class="btn btn-navy" href="../lomba/buat-lomba.php">
            <i class="fas fa-plus"></i>info lomba</a>
    </div>
</div>


<section class="admin-content">
    <div class="toolbar">
        <form action="" method="GET">
            <!-- Input Pencarian -->
            <label class="field-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
                <input name="q" type="search" placeholder="Cari nama lomba" value="<?= htmlspecialchars($q) ?>">
            </label>

            <!-- Tombol Cari -->
            <button class="btn btn-navy toolbar-btn" type="submit">Cari</button>
        </form>
    </div>
    <div class="table-card">
        <div class="table-responsive">
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Kompetisi</th>
                        <th>Kategori</th>
                        <th>Deskripsi</th>
                        <th>Poster</th>
                        <th>Link Pendaftaran</th>
                        <th>Penutupan Pendaftaran</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($info_lomba)): ?>
                        <?php if ($error_message !== ''): ?>
                            <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
                        <?php endif; ?>
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 20px;">Belum ada data info lomba yang tersedia.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($info_lomba as $data_lomba): ?>
                            <tr>
                                <td><?= htmlspecialchars($data_lomba['id']) ?></td>
                                <td><?= htmlspecialchars($data_lomba['judul']) ?></td>
                                <td><?= htmlspecialchars($data_lomba['kategori']) ?></td>
                                <td>
                                    <div class="desc-truncate" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; max-width: 280px; line-height: 1.5;">
                                        <small><?= htmlspecialchars($data_lomba['deskripsi']) ?></small>
                                    </div>
                                    <button type="button"
                                        onclick="alert(this.dataset.full)"
                                        data-full="<?= htmlspecialchars($data_lomba['deskripsi']) ?>"
                                        style="background: none; border: none; color: #2563eb; font-size: 12px; cursor: pointer; padding: 0; margin-top: 4px;">
                                        Baca selengkapnya
                                    </button>
                                </td>
                                <td>
                                    <?php if (!empty($data_lomba['poster'])): ?>
                                        <img
                                            src="../assets/uploads/poster/<?= rawurlencode(basename($data_lomba['poster'])) ?>"
                                            alt="Poster <?= htmlspecialchars($data_lomba['judul'], ENT_QUOTES, 'UTF-8') ?>"
                                            style="width: 100px; height: auto; object-fit: cover;">
                                    <?php else: ?>
                                        Tidak ada poster
                                    <?php endif; ?>
                                </td>
                                <td>
<a href="<?= htmlspecialchars($data_lomba['link_detail'], ENT_QUOTES, 'UTF-8') ?>"
       target="_blank"
       rel="noopener noreferrer">
        <?= htmlspecialchars($data_lomba['link_detail'], ENT_QUOTES, 'UTF-8') ?>
    </a>                                </td>
                                <td><?= htmlspecialchars($data_lomba['tanggal_penutupan']) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit-lomba.php?id=<?= urlencode($data_lomba['id']) ?>" class="btn-action btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="hapus-lomba.php?id=<?= urlencode($data_lomba['id']) ?>" class="btn-action btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data dosen ini?');">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>

</section>
<?php require_once '../includes/footer.php'; ?>