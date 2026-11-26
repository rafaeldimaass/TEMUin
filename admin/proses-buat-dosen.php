<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/koneksi.php';

$prodi_options = [
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
	$role = 'dosen';
	$nip = trim($_POST['nip'] ?? '');
	$no_wa = trim($_POST['no_wa'] ?? '');
	$prodi = trim($_POST['prodi'] ?? '');
	$bidang_kepakaran = trim($_POST['bidang_kepakaran'] ?? '');
	$pengalaman = trim($_POST['pengalaman'] ?? '');
	$foto_profil = null;

	if ($nama === '' || $email === '' || $password === '' || $role === '' || $nip === '' || $no_wa === '' || $prodi === '' || $bidang_kepakaran === '' || $pengalaman === '') {
		$error_message = 'Semua field wajib diisi.';
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error_message = 'Format email tidak valid.';
	} elseif (strlen($password) < 8) {
		$error_message = 'Password harus terdiri dari minimal 8 karakter.';
	} elseif (!in_array($role, ['dosen', 'admin', 'mahasiswa'], true)) {
		$error_message = 'Role yang dipilih tidak valid.';
	} elseif (!in_array($prodi, $prodi_options, true)) {
		$error_message = 'Program studi yang dipilih tidak valid.';
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
						// Format ini sesuai dengan path foto pada halaman data dosen.
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

				$stmt = $pdo->prepare('INSERT INTO profil_dosen (user_id, nip, no_wa, prodi, jurusan, bidang_kepakaran, pengalaman, foto_profil) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
				$stmt->execute([$user_id, $nip, $no_wa, $prodi, $prodi, $bidang_kepakaran, $pengalaman, $foto_profil]);

				$pdo->commit();
				$_SESSION['success'] = 'Dosen berhasil ditambahkan.';
				header('Location: data-dosen.php');
				exit;
			} catch (PDOException $e) {
				if ($pdo->inTransaction()) {
					$pdo->rollBack();
				}
				if ($uploaded_path !== null && is_file($uploaded_path)) {
					unlink($uploaded_path);
				}
				$error_message = 'Data dosen gagal disimpan. Pastikan email dan NIP belum digunakan.';
			}
			// Sampai di sini berarti ada error (atau bukan POST) -> kembali ke form
			$_SESSION['error'] = $error_message !== '' ? $error_message : 'Permintaan tidak valid.';
			header('Location: buat-dosen.php');
			exit;
		}
	}
}