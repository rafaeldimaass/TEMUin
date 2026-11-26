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
$id = (int) ($_POST['id'] ?? 0); // users.id, dikirim dari edit-mahasiswa.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Ambil data lama dari database (jangan percaya data dari form)
	$stmt = $pdo->prepare("SELECT u.id, pm.id AS profil_id, pm.foto_profil FROM users u LEFT JOIN profil_mahasiswa pm ON pm.user_id = u.id WHERE u.id = ? AND u.role = 'mahasiswa'");
	$stmt->execute([$id]);
	$lama = $stmt->fetch(PDO::FETCH_ASSOC);

	$nama = trim($_POST['nama'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? ''; // opsional, kosong = tidak diganti
	$nim = trim($_POST['nim'] ?? '');
	$no_wa = trim($_POST['no_wa'] ?? '');
	$jurusan = trim($_POST['jurusan'] ?? '');
	$prodi = trim($_POST['prodi'] ?? '');
	$skill = trim($_POST['skill'] ?? '');
	$portofolio = trim($_POST['portofolio'] ?? '');
	$tentang_saya = trim($_POST['tentang_saya'] ?? '');
	$foto_profil = $lama['foto_profil'] ?? null; // default: tetap pakai foto lama

	// Email boleh sama dengan miliknya sendiri, tapi tidak boleh dipakai user lain
	$cek = $pdo->prepare('SELECT 1 FROM users WHERE email = ? AND id <> ?');
	$cek->execute([$email, $id]);
	$email_dipakai = (bool) $cek->fetchColumn();

	if (!$lama) {
		$error_message = 'Data mahasiswa tidak ditemukan.';
	} elseif ($nama === '' || $email === '' || $nim === '' || $no_wa === '' || $jurusan === '' || $prodi === '' || $skill === '' || $tentang_saya === '') {
		$error_message = 'Semua field wajib diisi (kecuali password dan portofolio).';
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error_message = 'Format email tidak valid.';
	} elseif ($email_dipakai) {
		$error_message = 'Email sudah dipakai akun lain.';
	} elseif ($password !== '' && strlen($password) < 8) {
		$error_message = 'Password baru minimal 8 karakter.';
	} elseif (!in_array($jurusan, $jurusan_options, true)) {
		$error_message = 'Jurusan yang dipilih tidak valid.';
	} elseif ($portofolio !== '' && !filter_var($portofolio, FILTER_VALIDATE_URL)) {
		$error_message = 'Link portofolio harus berupa URL yang valid.';
	} elseif (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] !== UPLOAD_ERR_NO_FILE && $_FILES['foto_profil']['error'] !== UPLOAD_ERR_OK) {
		$error_message = 'Foto gagal diunggah.';
	} else {
		$uploaded_path = null;
		$upload_dir = '../assets/uploads/profile';
		if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
			$file = $_FILES['foto_profil'];
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			$mime = $finfo->file($file['tmp_name']);
			$allowed_types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

			if ($file['size'] > 2 * 1024 * 1024 || !isset($allowed_types[$mime])) {
				$error_message = 'Foto harus JPG, PNG, atau WEBP dengan ukuran maksimal 2 MB.';
			} else {
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

				if ($password !== '') {
					$stmt = $pdo->prepare('UPDATE users SET nama = ?, email = ?, no_wa = ?, password = ? WHERE id = ?');
					$stmt->execute([$nama, $email, $no_wa, password_hash($password, PASSWORD_DEFAULT), $id]);
				} else {
					$stmt = $pdo->prepare('UPDATE users SET nama = ?, email = ?, no_wa = ? WHERE id = ?');
					$stmt->execute([$nama, $email, $no_wa, $id]);
				}

				if ($lama['profil_id']) {
					$stmt = $pdo->prepare('UPDATE profil_mahasiswa SET nim = ?, jurusan = ?, prodi = ?, skill = ?, portofolio = ?, tentang_saya = ?, foto_profil = ?, is_complete = true WHERE user_id = ?');
					$stmt->execute([$nim, $jurusan, $prodi, $skill, $portofolio, $tentang_saya, $foto_profil, $id]);
				} else {
					// Mahasiswa yang belum punya baris profil -> buatkan
					$stmt = $pdo->prepare('INSERT INTO profil_mahasiswa (user_id, nim, jurusan, prodi, skill, portofolio, tentang_saya, foto_profil, is_complete) VALUES (?, ?, ?, ?, ?, ?, ?, ?, true)');
					$stmt->execute([$id, $nim, $jurusan, $prodi, $skill, $portofolio, $tentang_saya, $foto_profil]);
				}

				$pdo->commit();

				// Kalau foto diganti, hapus file foto lama
				if ($uploaded_path !== null && !empty($lama['foto_profil'])) {
					$foto_lama_path = $upload_dir . '/' . basename($lama['foto_profil']);
					if (is_file($foto_lama_path)) {
						unlink($foto_lama_path);
					}
				}

				$_SESSION['success'] = 'Data mahasiswa berhasil diperbarui.';
				header('Location: data-mahasiswa.php');
				exit;
			} catch (PDOException $e) {
				if ($pdo->inTransaction()) {
					$pdo->rollBack();
				}
				if ($uploaded_path !== null && is_file($uploaded_path)) {
					unlink($uploaded_path);
				}
				$error_message = 'Data mahasiswa gagal diperbarui.';
			}
		}
	}
}

// Sampai di sini berarti ada error (atau bukan POST) -> kembali ke form edit
$_SESSION['error'] = $error_message !== '' ? $error_message : 'Permintaan tidak valid.';
header('Location: ' . ($id > 0 ? "edit-mahasiswa.php?id=$id" : 'data-mahasiswa.php'));
exit;