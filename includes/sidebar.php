<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mendapatkan URL saat ini untuk menentukan menu yang sedang aktif
$current_url = $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' | ' : ''; ?>TEMUin Admin</title>
    
    <!-- Import Font Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Import FontAwesome untuk ikon menu -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Import CSS -->
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/pages/dashboard.css">
    <link rel="stylesheet" href="../assets/css/pages/tim.css">
    <link rel="stylesheet" href="../assets/css/pages/admin.css">
</head>
<body>

<aside class="sidebar">
    <div class="logo">
        <img src="../assets/img/logo-putih.png" alt="Logo TEMUin Admin">
    </div>
    <ul class="nav-menu">
        <!-- Deteksi URL: jika terdapat kata 'dashboard.php', beri class 'active' -->
        <li class="<?= strpos($current_url, 'dashboard.php') !== false ? 'active' : '' ?>">
            <a href="../admin/dashboard.php" style="color: inherit; text-decoration: none; display: flex; align-items: center; gap: 15px; width: 100%;">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>
        <li class="<?= strpos($current_url, 'data-mahasiswa.php') !== false ? 'active' : '' ?>">
            <a href="../user-mhs/data-mahasiswa.php" style="color: inherit; text-decoration: none; display: flex; align-items: center; gap: 15px; width: 100%;">
                <i class="fas fa-users"></i> Data Mahasiswa
            </a>
        </li>
        <li class="<?= strpos($current_url, 'data-dosen.php') !== false ? 'active' : '' ?>">
            <a href="../user-dosen/data-dosen.php" style="color: inherit; text-decoration: none; display: flex; align-items: center; gap: 15px; width: 100%;">
                <i class="fas fa-chalkboard-teacher"></i> Data Dosen
            </a>
        </li>
        <li class="<?= strpos($current_url, 'kelola-lomba.php') !== false ? 'active' : '' ?>">
            <a href="../lomba/kelola-lomba.php" style="color: inherit; text-decoration: none; display: flex; align-items: center; gap: 15px; width: 100%;">
                <i class="fas fa-trophy"></i> Kelola Info Kompetisi
            </a>
        </li>
        <li class="<?= strpos($current_url, 'data-tim.php') !== false ? 'active' : '' ?>">
            <a href="../tim/data-tim.php" style="color: inherit; text-decoration: none; display: flex; align-items: center; gap: 15px; width: 100%;">
                <i class="fas fa-user-friends"></i> Data Tim
            </a>
        </li>
        <li class="<?= strpos($current_url, 'kelola-user.php') !== false ? 'active' : '' ?>">
            <a href="../admin/kelola-user.php" style="color: inherit; text-decoration: none; display: flex; align-items: center; gap: 15px; width: 100%;">
                <i class="fas fa-user-friends"></i> Data Pengguna
            </a>
        </li>
    </ul>
    
    <div class="logout">
        <!-- Pastikan mengarah ke proses logout yang benar -->
        <a href="../auth/logout.php" style="color: inherit; text-decoration: none; display: flex; align-items: center; gap: 15px; width: 100%;">
            <i class="fas fa-sign-out-alt"></i> Keluar
        </a>
    </div>
</aside>

<main class="main-content">