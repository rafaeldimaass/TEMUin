<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once '../includes/koneksi.php';

$sudahLogin = isset($_SESSION['user_id']);

$current_url = $_SERVER['REQUEST_URI'];
$__jobsheetRoot = dirname(__DIR__);
$__scriptDir = dirname($_SERVER['SCRIPT_FILENAME']);
$__rel = ltrim(str_replace('\\', '/', substr($__scriptDir, strlen($__jobsheetRoot))), '/');
$base = $__rel === '' ? '' : str_repeat('../', substr_count($__rel, '/') + 1);

$role = $_SESSION['role'] ?? '';
$defaultNama = ($role === 'dosen') ? 'Dosen' : (($role === 'admin') ? 'Admin' : 'Mahasiswa');
$namaUser = $_SESSION['nama'] ?? $defaultNama;

$initials = '';
if (!empty($namaUser)) {
  $words = explode(' ', trim($namaUser));
  $initials = mb_strtoupper(mb_substr($words[0], 0, 1) . (isset($words[1]) ? mb_substr($words[1], 0, 1) : ''));
} else {
  $initials = mb_strtoupper(mb_substr($defaultNama, 0, 1));
}

$fotoProfil = '';
$idUser = $_SESSION['user_id'] ?? null;

if ($sudahLogin && $idUser !== null) {

  try {
    if ($role === 'dosen') {
      $stmt = $pdo->prepare("SELECT foto_profil FROM profil_dosen WHERE user_id = ? LIMIT 1");
    } elseif ($role === 'mahasiswa') {
      $stmt = $pdo->prepare("SELECT foto_profil FROM profil_mahasiswa WHERE user_id = ? LIMIT 1");
    }
    if (isset($stmt)) {
      if ($pdo instanceof PDO) {
        $stmt->execute([$idUser]);
        $fotoProfil = (string) ($stmt->fetchColumn() ?: '');
      }
    }
  } catch (Throwable $e) {
    $fotoProfil = '';
  }
}

if ($fotoProfil !== '') {
  $namaFileFoto = basename(str_replace('\\', '/', $fotoProfil));
  $fotoProfil = $base . 'assets/uploads/profile/' . rawurlencode($namaFileFoto);
}

// echo "<pre>";
// var_dump("Role: " . $role);
// var_dump("User ID: " . $idUser);
// var_dump("Hasil Foto dari DB: " . $fotoProfil);
// echo "</pre>";
// exit;
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo isset($page_title) ? $page_title . ' | ' : ''; ?>TEMUin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/global.css">
  <link rel="stylesheet" href="../assets/css/components.css">
  <link rel="stylesheet" href="../assets/css/pages/dashboard.css">
</head>

<body>

  <!-- ======================= NAVBAR ======================= -->
  <header class="topbar">
    <div class="wrap bar">
      <a class="logo" href="<?php echo $base; ?>user-mhs/dashboard.php" aria-label="TEMUin, ke beranda">
        <img src="<?php echo $base; ?>assets/img/LOGO.png" alt="Logo" style="height: 40px; width: auto; object-fit: contain;">
      </a>

      <nav class="links" aria-label="Menu utama">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
          <!-- MENU KHUSUS ADMIN -->
          <a href="<?php echo $base; ?>admin/dashboard.php" <?php echo strpos($current_url, 'dashboard.php') !== false ? 'aria-current="page"' : ''; ?>>Beranda</a>
          <a href="<?php echo $base; ?>lomba/info-lomba.php" <?php echo strpos($current_url, 'lomba.php') !== false ? 'aria-current="page"' : ''; ?>>Kelola Lomba</a>
          <a href="<?php echo $base; ?>admin/moderasi-admin.php" <?php echo strpos($current_url, 'moderasi') !== false ? 'aria-current="page"' : ''; ?>>Moderasi</a>
          <a href="<?php echo $base; ?>admin/kelola-user.php" <?php echo strpos($current_url, 'kelola-user') !== false ? 'aria-current="page"' : ''; ?>>Pengguna</a>

        <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'dosen'): ?>
          <!-- MENU KHUSUS DOSEN -->
          <a href="<?php echo $base; ?>user-dosen/dashboard.php" <?php echo strpos($current_url, 'dashboard.php') !== false ? 'aria-current="page"' : ''; ?>>Beranda</a>
          <a href="<?php echo $base; ?>tim/list-tim.php" <?php echo strpos($current_url, 'list-tim.php') !== false ? 'aria-current="page"' : ''; ?>>Tim</a>
          <a href="<?php echo $base; ?>lomba/info-lomba.php" <?php echo strpos($current_url, 'lomba.php') !== false ? 'aria-current="page"' : ''; ?>>Lomba</a>
          <a href="<?php echo $base; ?>user-dosen/list-bimbingan.php" <?php echo strpos($current_url, 'list-bimbingan.php') !== false ? 'aria-current="page"' : ''; ?>>Pengajuan Bimbingan</a>

        <?php else: ?>
          <a href="<?php echo $base; ?>user-mhs/dashboard.php" <?php echo strpos($current_url, 'dashboard.php') !== false ? 'aria-current="page"' : ''; ?>>Beranda</a>
          <a href="<?php echo $base; ?>tim/list-tim.php" <?php echo strpos($current_url, 'list-tim.php') !== false ? 'aria-current="page"' : ''; ?>>Tim</a>
          <a href="<?php echo $base; ?>lomba/info-lomba.php" <?php echo strpos($current_url, 'lomba.php') !== false ? 'aria-current="page"' : ''; ?>>Lomba</a>
          <a href="<?php echo $base; ?>user-dosen/list-dosen.php" <?php echo strpos($current_url, 'dosen.php') !== false ? 'aria-current="page"' : ''; ?>>Dosen Pembimbing</a>
          <a href="<?php echo $base; ?>informasi/riwayat-lamaran.php" <?php echo strpos($current_url, 'riwayat-lamaran.php') !== false ? 'aria-current="page"' : ''; ?>>Riwayat</a>
        <?php endif; ?>
      </nav>

      <div class="actions">
        <button class="avatar" type="button" aria-label="Akun saya" title="<?= htmlspecialchars($namaUser, ENT_QUOTES, 'UTF-8') ?>" onclick="window.location.href='<?= $base; ?>profile/profile.php'">
          <?php if (!empty($fotoProfil)): ?>
            <img src="<?= htmlspecialchars($fotoProfil, ENT_QUOTES, 'UTF-8') ?>" alt="Path: <?= htmlspecialchars($namaUser, ENT_QUOTES, 'UTF-8') ?>" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
          <?php else: ?>
            <?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?>
          <?php endif; ?>
        </button>
        <span><?php echo $_SESSION['nama']; ?></span>
      </div>

      <div class="auth-status">
        <?php if ($sudahLogin): ?>
          <a class="logout-link" href="<?php echo $base; ?>auth/logout.php" aria-label="Logout" title="Logout">
            <i class="bi bi-box-arrow-right logout-icon"></i>
          </a>
        <?php else: ?>
          <a href="<?php echo $base; ?>auth/login.php">Login</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <main class="wrap">