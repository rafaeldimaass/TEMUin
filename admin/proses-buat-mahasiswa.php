<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/koneksi.php';

$jurusan_options = [
	'Teknologi Informasi',
	'Teknik Elektro',
	'Teknik Mesin',
	'Teknik Sipil',
	'Teknik Kimia',
	'Akuntansi',
	'Administrasi Niaga',
];

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$nama = trim($_POST['nama'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';
	$role = 'mahasiswa';
	$nim = trim($_POST['nim'] ?? '');
	$no_wa = trim($_POST['no_wa'] ?? '');
	$jurusan = trim($_POST['jurusan'] ?? '');
	$prodi = trim($_POST['prodi'] ?? '');
	$skill = trim($_POST['skill'] ?? '');
	$portofolio = trim($_POST['portofolio'] ?? '');
	$tentang_saya = trim($_POST['tentang_saya'] ?? '');
	$foto_profil = null;

	if ($nama === '' || $email === '' || $password === '' || $nim === '' || $no_wa === '' || $jurusan === '' || $prodi === '' || $skill === '' || $tentang_saya === '') {
		$error_message = 'Semua field wajib diisi (kecuali portofolio).';
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error_message = 'Format email tidak valid.';
	} elseif (strlen($password) < 8) {
		$error_message = 'Password harus terdiri dari minimal 8 karakter.';
	} elseif (!in_array($jurusan, $jurusan_options, true)) {
		$error_message = 'Jurusan yang dipilih tidak valid.';
	} elseif ($portofolio !== '' && !filter_var($portofolio, FILTER_VALIDATE_URL)) {
		$error_message = 'Link portofolio harus berupa URL yang valid.';
	} elseif (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] !== UPLOAD_ERR_NO_FILE && $_FILES['foto_profil']['error'] !== UPLOAD_ERR_OK) {
		$error_message = 'Foto gagal diunggah.';
	} else {
		$uploaded_path = null;
		if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
			$file = $_FILES['foto_profil'];
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			$mime = $finfo->file($file['tmp_name']);
			$allowed_types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

			if ($file['size'] > 2 * 1024 * 1024 || !isset($allowed_types[$mime])) {
				$error_message = 'Foto harus JPG, PNG, atau WEBP dengan ukuran maksimal 2 MB.';
			} else {
				$upload_dir = '../assets/uploads/profile';
				if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
					$error_message = 'Folder penyimpanan foto tidak dapat dibuat.';
				} else {
					$filename = bin2hex(random_bytes(16)) . '.' . $allowed_types[$mime];
					$uploaded_path = $upload_dir . '/' . $filename;
					if (move_uploaded_file($file['tmp_name'], $uploaded_path)) {
						$foto_profil = $filename;
					} else {
						$error_message = 'Foto tidak dapat disimpan.';
					}
				}
			}
		}

		if ($error_message === '') {
			try {
				$pdo->beginTransaction();

				$stmt = $pdo->prepare('INSERT INTO users (nama, email, password, role, no_wa) VALUES (?, ?, ?, ?, ?) RETURNING id');
				$stmt->execute([$nama, $email, password_hash($password, PASSWORD_DEFAULT), $role, $no_wa]);
				$user_id = $stmt->fetchColumn();

				$stmt = $pdo->prepare('INSERT INTO profil_mahasiswa (user_id, nim, jurusan, prodi, skill, portofolio, tentang_saya, foto_profil, is_complete) VALUES (?, ?, ?, ?, ?, ?, ?, ?, true)');
				$stmt->execute([$user_id, $nim, $jurusan, $prodi, $skill, $portofolio, $tentang_saya, $foto_profil]);

				$pdo->commit();
				$_SESSION['success'] = 'Mahasiswa berhasil ditambahkan.';
				header('Location: data-mahasiswa.php');
				exit;
			} catch (PDOException $e) {
				if ($pdo->inTransaction()) {
					$pdo->rollBack();
				}
				if ($uploaded_path !== null && is_file($uploaded_path)) {
					unlink($uploaded_path);
				}
				$error_message = 'Data mahasiswa gagal disimpan. Pastikan email belum digunakan.';
			}
		}
	}
}

// Sampai di sini berarti ada error (atau bukan POST) -> kembali ke form
$_SESSION['error'] = $error_message !== '' ? $error_message : 'Permintaan tidak valid.';
header('Location: buat-mahasiswa.php');
exit;