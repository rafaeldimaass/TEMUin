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

$page_title = 'Register | TEMUin';
?>

<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Register - TEMUin</title>
	<link rel="stylesheet" href="../assets/css/global.css">
	<link rel="stylesheet" href="../assets/css/components.css">
	<link rel="stylesheet" href="../assets/css/pages/login.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
	<div class="container-login">
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
				<div class="center-icon"><i class="fa-solid fa-info"></i></div>
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
				<h2>Buat Akun TEMUin</h2>
				<p>Daftar dan mulai temukan tim terbaikmu!</p>
				<?php
				if (isset($_SESSION['flash'])) {
					echo '<div style="color: red; margin-bottom: 15px;">' . htmlspecialchars($_SESSION['flash']['pesan'] ?? '', ENT_QUOTES, 'UTF-8') . '</div>';
					unset($_SESSION['flash']);
				}
				?>
				<form action="proses-register.php" method="POST">
					<div class="form-group">
						<label for="nama">Nama</label>
						<div class="input-wrapper">
							<i class="fa-solid fa-user"></i>
							<input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap" autocomplete="name" required>
						</div>

						<label for="email">Email</label>
						<div class="input-wrapper">
							<i class="fa-solid fa-at"></i>
							<input type="email" id="email" name="email" placeholder="Masukkan email" autocomplete="email" required>
						</div>

						<div class="input-group">
							<label for="password">Password</label>
							<div class="input-wrapper">
								<i class="fa-solid fa-lock"></i>
								<input type="password" id="password" name="password" placeholder="Buat password" autocomplete="new-password" required>
								<button type="button" id="togglePassword" aria-label="Tampilkan atau sembunyikan password">
									<i class="fa-solid fa-eye" id="eyeIcon"></i>
								</button>
							</div>
						</div>

						<button type="submit" class="btn-login">Daftar</button>
					</div>
				</form>
				<p>Sudah punya akun? <a href="login.php">Login</a></p>
				<script src="<?php echo htmlspecialchars($base, ENT_QUOTES, 'UTF-8'); ?>assets/js/script.js"></script>
			</div>
		</div>
	</div>
</body>

</html>
