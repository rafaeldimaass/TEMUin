<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if (!isset($base)) {
    $__jobsheetRoot = dirname(__DIR__); 
    $__scriptDir = dirname($_SERVER['SCRIPT_FILENAME']);
    $__rel = ltrim(str_replace('\\', '/', substr($__scriptDir, strlen($__jobsheetRoot))), '/');
    $base = $__rel === '' ? '' : str_repeat('../', substr_count($__rel, '/') + 1);
}

$page_title = "Login - TEMUin";
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TEMUin</title>
    <!-- Base & Resets -->
    <link rel="stylesheet" href="../assets/css/global.css">

    <!-- Components -->
    <link rel="stylesheet" href="../assets/css/components.css">

    <!-- Page Specific -->
    <link rel="stylesheet" href="../assets/css/pages/login.css">
    <!-- FontAwesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <!-- Import Font Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <div class="container-login">
        <!-- sisi kiri -->
        <div class="left-section">
            <div class="logo-container">
                <img src="../assets/img/LOGO.png" alt="Logo TEMUin" class="logo">
            </div>

            <h1>TEMUkan tim mu, menangIN lombanya</h1>
            <p class="subtitle">
                Selamat datang para juara di sistem matchmaking tim lomba JTI Polinema! Temukan tim, diskusikan inovasi, dan beri aksi yang nyata untuk Polinema!
            </p>

            <div class="illustration-container">
                <div class="network-node member-1">
                    <div class="avatar"><i class="fa-solid fa-user"></i></div>
                    <span>Anggota 1</span>
                </div>
                <div class="network-node member-2">
                    <div class="avatar"><i class="fa-solid fa-user"></i></div>
                    <span>Anggota 2</span>
                </div>
                <div class="network-node member-3">
                    <div class="avatar"><i class="fa-solid fa-user"></i></div>
                    <span>Anggota 3</span>
                </div>
                <div class="network-node advisor">
                    <div class="avatar"><i class="fa-solid fa-user"></i></div>
                    <span>Dosen Pembimbing</span>
                </div>
                <div class="center-icon">
                    <i class="fa-solid fa-info"></i>
                </div>
            </div>

            <div class="features-list">
                <div class="feature-item">
                    <div class="feature-icon"><i class="fa-solid fa-user-group"></i></div>
                    <span>Cari rekan tim sesuai keahlian</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fa-solid fa-trophy"></i></div>
                    <span>Temukan info lomba terbaru</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fa-solid fa-comments"></i></div>
                    <span>Diskusikan ide inovasi bersama</span>
                </div>
            </div>
        </div>

        <div class="right-section">
            <div class="login-card">
                <h2>Selamat Datang Kembali!</h2>
                <p>Mulai raih prestasimu sekarang!</p>
                <?php
                if (isset($_SESSION['flash'])) {
                    echo '<div style="color: red; margin-bottom: 15px;">' . htmlspecialchars($_SESSION['flash']['pesan']) . '</div>';
                    unset($_SESSION['flash']);
                }
                ?>
                <form action="proses-login.php" method="POST">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-at"></i>
                            <input type="email" id="email" name="email" placeholder="Masukkan email" required>
                        </div>
                        <div class="input-group">
                            <label for="password">Password</label>
                            <div class="input-wrapper">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                                <button type="button" id="togglePassword">
                                <i class="fa-solid fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                            <div class="forgot-password">
                                <a href="#">Forgot password?</a>
                            </div>
                        </div>
                        <button type="submit" class="btn-login">Login</button>
                    </div>
                </form>
                <script src="<?php echo $base; ?>assets/js/script.js"></script>
            </div>
        </div>
    </div>