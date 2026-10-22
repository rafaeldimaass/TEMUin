<?php
session_start();

// Cek status login pengguna
$isLoggedIn = isset($_SESSION['user_id']);
$userRole   = $_SESSION['role'] ?? '';
$userName   = $_SESSION['nama'] ?? '';

// Tentukan arah tombol Dashboard sesuai role
$dashboardUrl = 'auth/login.php';
if ($isLoggedIn) {
    if ($userRole === 'mahasiswa') {
        $dashboardUrl = 'user-mhs/index.php';
    } elseif ($userRole === 'dosen') {
        $dashboardUrl = 'user-dosen/index.php';
    } elseif ($userRole === 'admin') {
        $dashboardUrl = 'admin/index.php';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TEMUin - Platform Matchmaking Kompetisi Polinema</title>
    <!-- Import Font Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f8fafc;
            color: #334155;
        }

        /* --- NAVBAR --- */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 8%;
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar .brand {
            font-size: 24px;
            font-weight: 700;
            color: #2F497C;
            text-decoration: none;
        }

        .navbar .brand span {
            color: #e67e22;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* --- HERO SECTION --- */
        .hero-section {
            background: linear-gradient(135deg, #1e293b 0%, #2F497C 100%);
            color: #ffffff;
            padding: 80px 8%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
            min-height: 75vh;
        }

        .hero-content {
            max-width: 550px;
        }

        .hero-title {
            font-size: 42px;
            font-weight: 700;
            line-height: 1.25;
            margin-bottom: 20px;
        }

        .hero-title span {
            color: #e67e22;
        }

        .hero-subtitle {
            font-size: 16px;
            color: #cbd5e1;
            line-height: 1.6;
            margin-bottom: 35px;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
        }

        /* --- BUTTON STYLES --- */
        .btn {
            padding: 12px 28px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            cursor: pointer;
        }

        .btn-primary {
            background-color: #e67e22;
            color: #ffffff;
            border: 2px solid #e67e22;
        }

        .btn-primary:hover {
            background-color: #d35400;
            border-color: #d35400;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(230, 126, 34, 0.3);
        }

        .btn-outline {
            background-color: transparent;
            color: #ffffff;
            border: 2px solid #ffffff;
        }

        .btn-outline:hover {
            background-color: #ffffff;
            color: #2F497C;
            transform: translateY(-2px);
        }

        /* --- HERO IMAGE / PLACEHOLDER --- */
        .hero-image {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .hero-image-placeholder {
            width: 100%;
            max-width: 450px;
            height: 300px;
            background: rgba(255, 255, 255, 0.08);
            border: 2px dashed rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #94a3b8;
            backdrop-filter: blur(5px);
        }

        /* --- FEATURES SECTION --- */
        .features-section {
            padding: 80px 8%;
            text-align: center;
        }

        .section-header h2 {
            font-size: 32px;
            color: #1e293b;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .section-header p {
            color: #64748b;
            font-size: 16px;
            margin-bottom: 50px;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .card {
            background: #ffffff;
            border-radius: 12px;
            padding: 30px;
            text-align: left;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .card-icon {
            width: 50px;
            height: 50px;
            background: #f1f5f9;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .card h3 {
            font-size: 20px;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .card .subtitle {
            font-size: 13px;
            color: #e67e22;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 12px;
            display: block;
        }

        .card p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .card-tags {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .tag {
            background: #f1f5f9;
            color: #475569;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 500;
        }

        /* --- FOOTER --- */
        footer {
            background-color: #0f172a;
            color: #94a3b8;
            text-align: center;
            padding: 25px 8%;
            font-size: 14px;
            border-top: 1px solid #1e293b;
        }

        /* --- RESPONSIVE DESIGN --- */
        @media (max-width: 900px) {
            .hero-section {
                flex-direction: column;
                text-align: center;
                padding: 60px 5%;
            }

            .hero-content {
                max-width: 100%;
            }

            .hero-buttons {
                justify-content: center;
            }

            .hero-image {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <a href="index.php" class="brand">TEMU<span>in</span></a>
        <div class="nav-links">
            <?php if ($isLoggedIn): ?>
                <span style="font-size: 14px; color: #64748b;">Halo, <strong><?= htmlspecialchars($userName) ?></strong></span>
                <a href="<?= $dashboardUrl ?>" class="btn btn-primary">Dashboard</a>
                <a href="auth/logout.php" class="btn btn-outline" style="color: #2F497C; border-color: #2F497C;">Logout</a>
            <?php else: ?>
                <a href="auth/register.php" class="btn btn-outline" style="color: #2F497C; border-color: #2F497C;">Register</a>
                <a href="auth/login.php" class="btn btn-primary">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">TEMUkan timmu, menang<span>IN</span> lomba</h1>
            <p class="hero-subtitle">
                Platform match-making kompetisi untuk mahasiswa JTI Polinema—temukan tim sesuai skill dan buru kesempatan lomba bersama.
            </p>
            <div class="hero-buttons">
                <?php if ($isLoggedIn): ?>
                    <a href="<?= $dashboardUrl ?>" class="btn btn-primary">Masuk ke Dashboard</a>
                <?php else: ?>
                    <a href="auth/register.php" class="btn btn-primary">Mulai Sekarang</a>
                    <a href="auth/login.php" class="btn btn-outline">Login</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="hero-image">
            <!-- Ilustrasi / Placeholder Gambar -->
            <div class="hero-image-placeholder">
        <svg width="72" height="72" viewBox="0 0 640 640" fill="#e67e22" xmlns="http://www.w3.org/2000/svg">
            <path d="M208.3 64L432.3 64C458.8 64 480.4 85.8 479.4 112.2C479.2 117.5 479 122.8 478.7 128L528.3 128C554.4 128 577.4 149.6 575.4 177.8C567.9 281.5 514.9 338.5 457.4 368.3C441.6 376.5 425.5 382.6 410.2 387.1C390 415.7 369 430.8 352.3 438.9L352.3 512L416.3 512C434 512 448.3 526.3 448.3 544C448.3 561.7 434 576 416.3 576L224.3 576C206.6 576 192.3 561.7 192.3 544C192.3 526.3 206.6 512 224.3 512L288.3 512L288.3 438.9C272.3 431.2 252.4 416.9 233 390.6C214.6 385.8 194.6 378.5 175.1 367.5C121 337.2 72.2 280.1 65.2 177.6C63.3 149.5 86.2 127.9 112.3 127.9L161.9 127.9C161.6 122.7 161.4 117.5 161.2 112.1C160.2 85.6 181.8 63.9 208.3 63.9zM165.5 176L113.1 176C119.3 260.7 158.2 303.1 198.3 325.6C183.9 288.3 172 239.6 165.5 176zM444 320.8C484.5 297 521.1 254.7 527.3 176L475 176C468.8 236.9 457.6 284.2 444 320.8z"/>
        </svg>
        <span style="margin-top: 12px; font-weight: 600; color: #cbd5e1; font-size: 20px;">MenangIN Lomba</span>
    </div>
        </div>
    </section>

    <!-- FEATURES SECTION -->
    <section class="features-section">
        <div class="section-header">
            <h2>Halo Calon Juara</h2>
            <p>Gunakan akun kampus untuk bergabung dan mencari tim yang paling cocok.</p>
        </div>

        <div class="cards-grid">
            <!-- Card 1 -->
            <div class="card">
                <div class="card-icon">🚀</div>
                <span class="subtitle">Sistem Pencocokan</span>
                <h3>Jalur Cepat Menuju Tim Impian</h3>
                <p>Temukan anggota yang tepat berdasarkan skill dan kategori lomba. Lihat kecocokan tim secara cepat.</p>
                <div class="card-tags">
                    <span class="tag">🧠 Skill Match</span>
                    <span class="tag">🏆 Info Lomba</span>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="card">
                <div class="card-icon">💡</div>
                <span class="subtitle">Kenapa TEMUin?</span>
                <h3>Kolaborasi Tanpa Batas</h3>
                <p>Cari tim berdasarkan skill yang kamu butuhkan, temukan lomba terbaru, dan diskusikan ide proyek bersama dosen pembimbing.</p>
                <div class="card-tags">
                    <span class="tag">💬 Diskusi Tim</span>
                    <span class="tag">👨‍🏫 Bimbingan Dosen</span>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <?php include 'includes/footer.php'; ?>

</body>
</html>