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
$id = (int) ($_POST['id'] ?? 0); // users.id, dikirim dari edit-dosen.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Ambil data lama dari database (jangan percaya data dari form)
	$stmt = $pdo->prepare("SELECT u.id, pd.id AS profil_id, pd.foto_profil FROM users u LEFT JOIN profil_dosen pd ON pd.user_id = u.id WHERE u.id = ? AND u.role = 'dosen'");
	$stmt->execute([$id]);
	$lama = $stmt->fetch(PDO::FETCH_ASSOC);

	$nama = trim($_POST['nama'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? ''; // opsional, kosong = tidak diganti
	$nip = trim($_POST['nip'] ?? '');
	$no_wa = trim($_POST['no_wa'] ?? '');
	$prodi = trim($_POST['prodi'] ?? '');
	$bidang_kepakaran = trim($_POST['bidang_kepakaran'] ?? '');
	$pengalaman = trim($_POST['pengalaman'] ?? '');
	$foto_profil = $lama['foto_profil'] ?? null; // default: tetap pakai foto lama

	// Email boleh sama dengan miliknya sendiri, tapi tidak boleh dipakai user lain
	$cek = $pdo->prepare('SELECT 1 FROM users WHERE email = ? AND id <> ?');
	$cek->execute([$email, $id]);
	$email_dipakai = (bool) $cek->fetchColumn();

	if (!$lama) {
		$error_message = 'Data dosen tidak ditemukan.';
	} elseif ($nama === '' || $email === '' || $nip === '' || $no_wa === '' || $prodi === '' || $bidang_kepakaran === '' || $pengalaman === '') {
		$error_message = 'Semua field wajib diisi (kecuali password).';
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error_message = 'Format email tidak valid.';
	} elseif ($email_dipakai) {
		$error_message = 'Email sudah dipakai akun lain.';
	} elseif ($password !== '' && strlen($password) < 8) {
		$error_message = 'Password baru minimal 8 karakter.';
	} elseif (!in_array($prodi, $prodi_options, true)) {
		$error_message = 'Program studi yang dipilih tidak valid.';
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
					$stmt = $pdo->prepare('UPDATE profil_dosen SET nip = ?, no_wa = ?, prodi = ?, jurusan = ?, bidang_kepakaran = ?, pengalaman = ?, foto_profil = ? WHERE user_id = ?');
					$stmt->execute([$nip, $no_wa, $prodi, $prodi, $bidang_kepakaran, $pengalaman, $foto_profil, $id]);
				} else {
					// Dosen yang belum punya baris profil -> buatkan
					$stmt = $pdo->prepare('INSERT INTO profil_dosen (user_id, nip, no_wa, prodi, jurusan, bidang_kepakaran, pengalaman, foto_profil) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
					$stmt->execute([$id, $nip, $no_wa, $prodi, $prodi, $bidang_kepakaran, $pengalaman, $foto_profil]);
				}

				$pdo->commit();

				// Kalau foto diganti, hapus file foto lama
				if ($uploaded_path !== null && !empty($lama['foto_profil'])) {
					$foto_lama_path = $upload_dir . '/' . basename($lama['foto_profil']);
					if (is_file($foto_lama_path)) {
						unlink($foto_lama_path);
					}
				}

				$_SESSION['success'] = 'Data dosen berhasil diperbarui.';
				header('Location: data-dosen.php');
				exit;
			} catch (PDOException $e) {
				if ($pdo->inTransaction()) {
					$pdo->rollBack();
				}
				if ($uploaded_path !== null && is_file($uploaded_path)) {
					unlink($uploaded_path);
				}
				$error_message = 'Data dosen gagal diperbarui.';
			}
		}
	}
}

// Sampai di sini berarti ada error (atau bukan POST) -> kembali ke form edit
$_SESSION['error'] = $error_message !== '' ? $error_message : 'Permintaan tidak valid.';
header('Location: ' . ($id > 0 ? "edit-dosen.php?id=$id" : 'data-dosen.php'));
exit;