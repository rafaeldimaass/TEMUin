<?php
session_start();

// 1. Proteksi Akses: Hanya mahasiswa yang boleh masuk
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../includes/koneksi.php';
require_once '../includes/sidebar.php';
$q = trim($_GET['q'] ?? '');
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$jurusan_filter = isset($_GET['jurusan']) ? trim($_GET['jurusan']) : '';

// 1. Inisialisasi query dasar dengan JOIN tabel
$query = "SELECT pm.*, u.nama, u.email, u.no_wa
          FROM profil_mahasiswa pm
          JOIN users u ON pm.user_id = u.id 
          WHERE 1=1";
$params = [];

// 2. Logika pencarian berdasarkan nama atau nim
if (!empty($search)) {
    $query .= " AND (u.nama ILIKE ? OR pm.nim ILIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// 3. Logika filter berdasarkan jurusan
if (!empty($jurusan_filter)) {
    $query .= " AND pm.jurusan = ?";
    $params[] = $jurusan_filter;
}

// 4. Pengurutan data
$query .= " ORDER BY pm.id DESC";

// 5. Eksekusi query dengan Prepared Statement dan Error Handling
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $list_mahasiswa = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $list_mahasiswa = [];
    $error_message = "Gagal memuat data: " . $e->getMessage();
}
?>


<link rel="stylesheet" href="../assets/css/pages/tim.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/global.css">

<div class="page-head">
    <div>
        <h1>Monitoring Data Mahasiswa</h1>
        <p>Monitoring dan kelola data mahasiswa</p>
    </div>
    <a class="btn btn-navy" href="buat-mahasiswa.php">
        <i class="fas fa-plus"></i> Tambah Data Mahasiswa
    </a>
</div>
<?php require_once '../includes/flash.php'; ?>

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
                <input name="search" type="search" placeholder="Cari NAMA atau NIM Mahasiswa" value="<?= htmlspecialchars($search) ?>">
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
                        <th>Nama Mahasiswa</th>
                        <th>NIM</th>
                        <th>Jurusan</th>
                        <th>Program Studi</th>
                        <th>Portofolio</th>
                        <th>No WhatsApp</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list_mahasiswa)): ?>
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 20px;">Belum ada data mahasiswa yang tersedia.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($list_mahasiswa as $data_mahasiswa): ?>
                            <tr>
                                <td><?= htmlspecialchars($data_mahasiswa['id']) ?></td>

                                <td>
                                    <?php if (!empty($data_mahasiswa['foto_profil'])): ?>
                                        <?php $foto = "../assets/uploads/profile/" . $data_mahasiswa['foto_profil']; ?>
                                        <img src="<?= htmlspecialchars($foto) ?>" alt="Foto" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="avatar avatar-blue"><?= htmlspecialchars(strtoupper(substr($data_mahasiswa['nama'], 0, 2))) ?></div>
                                    <?php endif; ?>
                                    <span class="applicant-name"><?= htmlspecialchars($data_mahasiswa['nama']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($data_mahasiswa['nim']) ?></td>
                                <td>
                                    <span><?= htmlspecialchars($data_mahasiswa['jurusan']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($data_mahasiswa['prodi']) ?></td>
                                <td>
                                    <?php if (!empty($data_mahasiswa['portofolio'])): ?>
                                        <a href="<?= htmlspecialchars($data_mahasiswa['portofolio']) ?>" target="_blank" style="color: #253C6D; text-decoration: underline;">Link Portofolio</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($data_mahasiswa['no_wa'] ?? 'Belum Memasukkan No WA') ?></td>

                                <td>
                                    <div class="action-buttons">
                                        <a href="detail-mahasiswa.php?id=<?= urlencode($data_mahasiswa['user_id']) ?>" class="btn-action btn-detail">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        <a href="edit-mahasiswa.php?id=<?= urlencode($data_mahasiswa['user_id']) ?>" class="btn-action btn-navy">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="hapus-user.php?role=mahasiswa&id=<?= urlencode($data_mahasiswa['user_id']) ?>" class="btn-action btn-delete" onclick="return confirm('Hapus mahasiswa ini? Tim dan lamaran miliknya juga akan ikut terhapus.');">
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
    <span>Menampilkan total <?= count($list_mahasiswa) ?> data mahasiswa</span>
    <div class="pagination-nav">
        <button class="page-btn">Sebelumnya</button>
        <button class="page-btn active">1</button>
        <button class="page-btn">2</button>
        <button class="page-btn">Selanjutnya</button>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>