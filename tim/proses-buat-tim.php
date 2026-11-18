<?php
session_start();

// 1. Cek apakah user sudah login dan perannya adalah mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ketua_id = $_SESSION['user_id'];

    // Ambil dan sanitasi input dari form
    $nama_lomba        = trim($_POST['nama-lomba'] ?? '');
    $kategori          = trim($_POST['kategori'] ?? '');
    $deskripsi     = trim($_POST['deskripsi-tim'] ?? '');
    $skills_dibutuhkan          = trim($_POST['skills'] ?? ''); 
    $nama_tim          = trim($_POST['nama-tim'] ?? '');
    $jumlah_anggota    = intval($_POST['jumlah-anggota'] ?? 0);
    // $portofolio        = trim($_POST['portofolio'] ?? '');
    $tanggal_penutupan = trim($_POST['tanggal-penutupan'] ?? '');

    if (empty($nama_lomba) || empty($nama_tim) || empty($jumlah_anggota)  || empty($tanggal_penutupan)) {
        $_SESSION['error'] = "Semua field yang wajib harus diisi!";
        header('Location: buat-tim.php');
        exit;
    }

    $gambar = null;
    if (isset($_FILES['tim-img']) && $_FILES['tim-img']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $_FILES['tim-img']['tmp_name'];
        $fileName      = $_FILES['tim-img']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName   = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/../assets/uploads/tim/';
            
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $gambar = 'assets/uploads/tim/' . $newFileName;
            }
        }
    }
    $judul_lengkap = $nama_tim . ' - ' . $nama_lomba; 
    $status_tim    = 'open';

    try {
        $sql = "INSERT INTO tim_lomba (
                    ketua_id, 
                    nama_lomba, 
                    nama_tim,
                    kategori,
                    deskripsi, 
                    status_tim, 
                    gambar, 
                    skills_dibutuhkan, 
                    jumlah_anggota,  
                    tanggal_penutupan, 
                    created_at
                ) VALUES (
                    :ketua_id, 
                    :nama_lomba,
                    :nama_tim,
                    :kategori,
                    :deskripsi, 
                    :status_tim, 
                    :gambar, 
                    :skills_dibutuhkan, 
                    :jumlah_anggota, 
                    :tanggal_penutupan, 
                    NOW()
                )";

                $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ketua_id'          => $ketua_id,
            ':nama_lomba'        => $nama_lomba,
            ':nama_tim'          => $nama_tim,
            ':kategori'          => $kategori, 
            ':deskripsi'         => $deskripsi,
            ':status_tim'        => $status_tim,
            ':gambar'            => $gambar,
            ':skills_dibutuhkan' => $skills_dibutuhkan,
            ':jumlah_anggota'    => $jumlah_anggota,
            ':tanggal_penutupan' => $tanggal_penutupan
        ]);

        $_SESSION['success'] = "Tim berhasil dibuat!";
        header('Location: ../tim/kelola-tim.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Gagal menyimpan data ke database: " . $e->getMessage();
        header('Location: buat-tim.php');
        exit;
    }
} else {
    header('Location: buat-tim.php');
    exit;
}
?>