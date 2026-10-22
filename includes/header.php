<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$sudahLogin = isset($_SESSION['user_id']);

$current_url = $_SERVER['REQUEST_URI'];
$__jobsheetRoot = dirname(__DIR__); // Sesuaikan dengan struktur project anda
$__scriptDir = dirname($_SERVER['SCRIPT_FILENAME']);
$__rel = ltrim(str_replace('\\', '/', substr($__scriptDir, strlen($__jobsheetRoot))), '/');
$base = $__rel === '' ? '' : str_repeat('../', substr_count($__rel, '/') + 1);
// Pastikan session sudah aktif sebelum memanggil file ini
$namaUser = $_SESSION['nama'] ?? 'Mahasiswa';
$initials = '';
if (!empty($_SESSION['nama'])) {
    $words = explode(' ', $_SESSION['nama']);
    $initials = mb_strtoupper(mb_substr($words[0], 0, 1) . (isset($words[1]) ? mb_substr($words[1], 0, 1) : ''));
} else {
    $initials = 'M';
}
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
        <a href="<?php echo $base; ?>user-mhs/dashboard.php" <?php echo strpos($current_url, 'dashboard.php') !== false ? 'aria-current="page"' : ''; ?>>Beranda</a>
        <a href="<?php echo $base; ?>tim/list-tim.php" <?php echo strpos($current_url, 'list-tim.php') !== false ? 'aria-current="page"' : ''; ?>>Tim</a>
        <a href="<?php echo $base; ?>lomba/info-lomba.php" <?php echo strpos($current_url, 'lomba.php') !== false ? 'aria-current="page"' : ''; ?>>Lomba</a>
        <a href="<?php echo $base; ?>user-dosen/list-dosen.php" <?php echo strpos($current_url, 'dosen.php') !== false ? 'aria-current="page"' : ''; ?>>Dosen Pembimbing</a>
      </nav>
 
      <div class="actions">
        <a class="btn btn-navy" href="<?php echo $base; ?>tim/buat-tim.php">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
          Buat Tim
        </a>
        <button class="avatar" type="button" aria-label="Akun saya" title="<?= htmlspecialchars($namaUser) ?>"><?= htmlspecialchars($initials) ?></button>
        <span><?php echo $_SESSION['nama']; ?></span>
      </div>

      <div class="auth-status">
            <?php if ($sudahLogin): ?>
                
                <a href="<?php echo $base; ?>auth/logout.php">Logout</a>
            <?php else: ?>
                <a href="<?php echo $base; ?>auth/login.php">Login</a>
            <?php endif; ?>
        </div>
    </div>
  </header>

  <main class="wrap">