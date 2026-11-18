<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/koneksi.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     var_dump($_POST);
// exit;
    $admin_id = $_SESSION['user_id'];
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $kategori = trim($_POST['kategori']);
    $tanggal_penutupan = $_POST['tanggal_penutupan'];
    $link_detail = trim($_POST['link_detail']);
    $poster = null;

    // Handle Upload Poster
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['poster']['tmp_name'];
        $fileName = $_FILES['poster']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = '../assets/uploads/poster/';

            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $dest_path = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $poster = $newFileName;
            } else {
                $_SESSION['error'] = "Gagal mengunggah file poster ke server.";
                header('Location: buat-lomba.php');
                exit;
            }
        } else {
            $_SESSION['error'] = "Format poster tidak didukung. Gunakan JPG, JPEG, PNG, atau WEBP.";
            header('Location: buat-lomba.php');
            exit;
        }
    }

    if (!empty($judul) && !empty($deskripsi) && !empty($kategori) && !empty($tanggal_penutupan) && !empty($poster) && !empty($link_detail)) {
        try {
            $sql = "INSERT INTO info_lomba (admin_id, judul, deskripsi, kategori, tanggal_penutupan, poster, link_detail) 
                    VALUES (:admin_id, :judul, :deskripsi, :kategori, :tanggal_penutupan, :poster, :link_detail)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'admin_id' => $admin_id,
                'judul' => $judul,
                'deskripsi' => $deskripsi,
                'kategori' => $kategori,
                'tanggal_penutupan' => $tanggal_penutupan,
                'poster' => $poster,
                'link_detail' => $link_detail
            ]);

            $_SESSION['success'] = "Info lomba berhasil ditambahkan!";
            header('Location: info-lomba.php?status=sukses_tambah');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Gagal menyimpan data: " . $e->getMessage();
            header('Location: buat-lomba.php');
            exit;
        }
    } else {
        $_SESSION['error'] = "Semua form wajib diisi";
        header('Location: buat-lomba.php');
        exit;
    }
} else {
    header('Location: buat-lomba.php');
}
