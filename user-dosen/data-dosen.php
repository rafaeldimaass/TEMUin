<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/koneksi.php';
$page_title = "Data Dosen | TEMUin";
require_once '../includes/sidebar.php';

// Search dan filtering
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$jurusan_filter = isset($_GET['jurusan']) ? trim($_GET['jurusan']) : '';

$query = "SELECT pd.*, u.nama, u.email 
          FROM profil_dosen pd
          JOIN users u ON pd.user_id = u.id 
          WHERE 1=1";
$params = [];

// Searching berdasarkan nama atau nip
if (!empty($search)) {
    $query .= " AND (u.nama ILIKE ? OR pd.nip ILIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Filter prodi
if (!empty($jurusan_filter)) {
    $query .= " AND pd.jurusan = ?";
    $params[] = $jurusan_filter;
}

$query .= "ORDER BY pd.id DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $list_dosen = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $list_dosen = [];
    $error_message = "Gagal memuat data: " . $e->getMessage();
}
?>

<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/pages/admin.css">

<div class="page-head">
    <div>
        <h1>Data Dosen</h1>
        <p>Kelola data dosen pembimbing dan penasihat akademik di sistem.</p>
    </div>
    <a class="btn btn-navy" href="buat-dosen.php">
        <i class="fas fa-plus"></i> Tambah Dosen Baru
    </a>
</div>

<section class="admin-content-section">
    <!-- Toolbar Pencarian & Filter -->
    <div class="toolbar">
        <form action="" method="GET">
            <!-- Input Pencarian -->
            <label class="field-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
                <input name="search" type="search" placeholder="Cari nama atau NIP dosen..." value="<?= htmlspecialchars($search) ?>">
            </label>

            <!-- Dropdown Filter Program Studi -->
            <label class="sort">
                <select name="jurusan">
                    <option value="">Semua Jurusan</option>
                    <option value="Teknologi Informasi">Teknologi Informasi</option>
                    <option value="Teknik Elektro">Teknik Elektro</option>
                    <option value="Teknik Mesin">Teknik Mesin</option>
                    <option value="Teknik Sipil">Teknik Sipil</option>
                    <option value="Teknik Kimia">Teknik Kimia</option>
                    <option value="Akuntansi">Akuntansi</option>
                    <option value="Administrasi Niaga">Administrasi Niaga</option>
                </select>
            </label>

            <!-- Tombol Cari -->
            <button class="btn btn-navy toolbar-btn" type="submit">Terapkan</button>
        </form>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Dosen</th>
                        <th>NIP</th>
                        <th>Keahlian</th>
                        <th>Pengalaman</th>
                        <th>Program Studi</th>
                        <th>Email</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list_dosen)): ?>
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 20px;">Belum ada data dosen yang tersedia.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($list_dosen as $data_dosen): ?>
                            <tr>
                                <td><?= htmlspecialchars($data_dosen['id']) ?></td>

                                <td>
                                    <?php if (!empty($data_dosen['foto_profil'])): ?>
                                        <?php $foto = "../assets/images/uploads/profile" . $data_dosen['foto_profil']; ?>
                                        <img src="<?= htmlspecialchars($foto) ?>" alt="Foto" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="avatar avatar-blue"><?= htmlspecialchars(strtoupper(substr($data_dosen['nama'], 0, 2))) ?></div>
                                    <?php endif; ?>
                                </td>

                        <td><?= htmlspecialchars($data_dosen['nama']) ?></td>
                        <td><?= htmlspecialchars($data_dosen['nip']) ?></td>
                        <td>
                            <span class="badge badge-nasional"><?= htmlspecialchars($data_dosen['prodi']) ?></span>
                        </td>
                        <td><?= htmlspecialchars($data_dosen['bidang_kepakaran']) ?></td>
                        <td><?= htmlspecialchars($data_dosen['pengalaman']) ?></td>
                        <td><?= htmlspecialchars($data_dosen['email']) ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit-dosen.php?id=<?= urlencode($data_dosen['id']) ?>" class="btn-action btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="hapus-dosen.php?id=<?= urlencode($data_dosen['id']) ?>" class="btn-action btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data dosen ini?');">
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
</section>
<!-- Paginasi -->
<div class="pagination-container">
    <span>Menampilkan <?= count($list_dosen) ?> data dosen</span>
    <div class="pagination-nav">
        <button class="page-btn">Sebelumnya</button>
        <button class="page-btn active">1</button>
        <button class="page-btn">2</button>
        <button class="page-btn">Selanjutnya</button>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>