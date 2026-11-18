<?php
session_start();

// 1. Proteksi Akses: Hanya admin yang boleh masuk
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../auth/login.php');
  exit;
}

// 2. Panggil koneksi database
require_once '../includes/koneksi.php';
$page_title = "Data Pengguna | TEMUin";
require_once '../includes/sidebar.php';

$search = trim($_GET['search'] ?? '');
$role = trim($_GET['role'] ?? '');
$allowedRoles = ['mahasiswa', 'dosen'];
$where = [];
$params = [];

if ($search !== '') {
  $where[] = '(u.nama LIKE :search_nama OR u.email LIKE :search_email OR u.no_wa LIKE :search_wa)';
  $searchTerm = '%' . $search . '%';
  $params['search_nama'] = $searchTerm;
  $params['search_email'] = $searchTerm;
  $params['search_wa'] = $searchTerm;
}

if (in_array($role, $allowedRoles, true)) {
  $where[] = 'u.role = :role';
  $params['role'] = $role;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$stmtUser = $pdo->prepare(
  "SELECT u.id, u.nama, u.email, u.role, u.no_wa, pd.foto_profil 
   FROM users AS u
   LEFT JOIN profil_dosen AS pd ON pd.user_id = u.id  
   $whereSql 
   ORDER BY u.nama"
);
$stmtUser->execute($params);
$User = $stmtUser->fetchAll(PDO::FETCH_ASSOC);
$totalUser = count($User);

?>
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/pages/dashboard.css">
<link rel="stylesheet" href="../assets/css/pages/tim.css">
<link rel="stylesheet" href="../assets/css/pages/admin.css">


<div class="page-head">
  <div>
    <h1>Data Pengguna</h1>
    <p>Kelola data pengguna TEMUin</p>
  </div>
  <a class="btn btn-navy" href="buat-user.php">
    <i class="fas fa-plus"></i> Tambah User Baru
  </a>
</div>

<section class="admin-content-section">
  <div class="toolbar">
    <form action="" method="GET">

      <!-- Input Pencarian -->
      <label class="field-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8" />
          <path d="m21 21-4.3-4.3" />
        </svg>
        <input name="search" type="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Cari nama atau email...">
      </label>

      <!-- Dropdown Filter Peran -->
      <label class="sort">
        <select name="role">
          <option value="">Semua Peran</option>
          <option value="mahasiswa" <?= $role === 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
          <option value="dosen" <?= $role === 'dosen' ? 'selected' : '' ?>>Dosen</option>
        </select>
      </label>

      <!-- Tombol Cari -->
      <button class="btn btn-navy toolbar-btn" type="submit">Cari</button>

    </form>
  </div>

  <!-- Tabel Daftar Pengguna -->
  <div class="table-card">
    <div class="table-card-header">
      <span class="table-card-count">Total: <?= $totalUser ?> Pengguna</span>
    </div>

    <div class="table-card">
      <div class="table-responsive">
        <table class="table-admin">
          <thead>
            <tr>
              <th>ID</th>
              <th>Profil Pengguna</th>
              <th>Email</th>
              <th>Peran</th>
              <th>No WhatsApp</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($User)): ?>
              <tr>
                <td colspan="6" class="text-center" style="padding: 20px;">Belum ada data pengguna.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($User as $user): ?>
                <tr>
                  <td><?= htmlspecialchars((string) ($user['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="role-cell">
                    <div class="profile-cell">
                      <?php if (!empty($user['foto_profil'])): ?>
                        <img class="avatar avatar-blue" src="<?= htmlspecialchars($user['foto_profil'], ENT_QUOTES, 'UTF-8') ?>" alt="Foto profil <?= htmlspecialchars($user['nama'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <?php else: ?>
                        <div class="avatar avatar-blue"><?= htmlspecialchars(strtoupper(substr($user['nama'] ?? '', 0, 2)), ENT_QUOTES, 'UTF-8') ?></div>
                      <?php endif; ?>
                      <span class="applicant-name">
                        <?= htmlspecialchars($user['nama'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                      </span>
                    </div>
                  </td>
                  <td><?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="badge badge-blue"><?= htmlspecialchars($user['role'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td><span><?= htmlspecialchars($user['no_wa'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td>
                    <div class="action-buttons">
                      <a href="edit-user.php?id=<?= urlencode($user['id']) ?>" class="btn-action btn-edit">
                        <i class="fas fa-edit"></i> Edit
                      </a>
                      <a href="hapus-user.php?id=<?= urlencode($user['id']) ?>" class="btn-action btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data dosen ini?');">
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