<?php
session_start();

// Cek apakah user sudah login dan rolenya mahasiswa
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['mahasiswa', 'dosen'], true)) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id        = $_SESSION['user_id'];
    $role           = $_SESSION['role'];
    $isDosen        = ($role === 'dosen');

    $nama           = $_POST['nama'] ?? '';
    $identitas      = $_POST['identitas'] ?? '';
    $jurusan        = $_POST['jurusan'] ?? '';
    $prodi          = $_POST['prodi'] ?? '';
    $skill_keahlian = $_POST['skill'] ?? '';
    $portofolio     = $_POST['portofolio'] ?? '';
    $tentang_saya   = $_POST['tentang_saya'] ?? '';

    // Tabel berdasarkan role
    $tabelProfil = $isDosen ? 'profil_dosen' : 'profil_mahasiswa';
    $kolomIden   = $isDosen ? 'nip' : 'nim';
    $kolomSkill  = $isDosen ? 'bidang_keahlian' : 'skill';
    $kolomAboutMe = $isDosen ? 'pengalaman' : 'tentang_saya';

    $foto_profil = null;
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $_FILES['foto_profil']['tmp_name'];
        $fileName      = $_FILES['foto_profil']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Ekstensi yang diizinkan
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = 'profile_' . $user_id . '_' . time() . '.' . $fileExtension;
            $uploadFileDir = '../assets/uploads/profile/';

            // Buat folder jika belum ada
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $dest_path = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $foto_profil = $newFileName;
            }
        }
    }

    try {
        // Transaction agar jika salah satu gagal, dibatalkan semua
        $pdo->beginTransaction();

        $tabelProfil = $isDosen ? 'profil_dosen' : 'profil_mahasiswa';
        $stmtProfil = $pdo->prepare("SELECT u.nama, u.email, u.no_wa, p.* FROM users u LEFT JOIN {$tabelProfil} p ON u.id = p.user_id WHERE u.id = ?");
        $stmtProfil->execute([$user_id]);
        $profil = $stmtProfil->fetch(PDO::FETCH_ASSOC) ?: [];

        // 1. Update nama di tabel USERS
        $stmtUser = $pdo->prepare("UPDATE users SET nama = ? WHERE id = ?");
        $stmtUser->execute([$nama, $user_id]);

        // 2. Cek apakah baris profil untuk user ini sudah ada di tabel spesifik
        $stmtCek = $pdo->prepare("SELECT foto_profil FROM {$tabelProfil} WHERE user_id = ?");
        $stmtCek->execute([$user_id]);
        $existing = $stmtCek->fetch(PDO::FETCH_ASSOC);

        if (!$foto_profil && $existing) {
            $foto_profil = $existing['foto_profil'] ?? null;
        }

        if ($existing) {
            //JIKA DATA SUDAH ADA LAKUKAN UPDATE
            if ($isDosen) {
            // UPDATE DOSEN
            $stmtUpdate = $pdo->prepare("UPDATE profil_dosen SET nip = ?, jurusan = ?, bidang_kepakaran = ?, pengalaman = ?, foto_profil = ?, is_complete = true WHERE user_id = ?");            
            $stmtUpdate->execute([$identitas, $jurusan, $skill_keahlian, $tentang_saya, $foto_profil, $user_id]);
        } else {
            // UPDATE MAHASISWA
            $stmtUpdate = $pdo->prepare("UPDATE profil_mahasiswa SET nim = ?, jurusan = ?, prodi = ?, skill = ?, portofolio = ?, tentang_saya = ?, foto_profil = ?, is_complete = true WHERE user_id = ?");
            $stmtUpdate->execute([$identitas, $jurusan, $prodi, $skill_keahlian, $portofolio, $tentang_saya, $foto_profil, $user_id]);        }
    } else {
        // JIKA DATA BELUM ADA LAKUKAN INSERT
        if ($isDosen) {
            // INSERT DOSEN
            $stmtInsert = $pdo->prepare("INSERT INTO profil_dosen (user_id, nip, jurusan, bidang_kepakaran, pengalaman, foto_profil, is_complete) VALUES (?, ?, ?, ?, ?, ?, true)");
            $stmtInsert->execute([$user_id, $identitas, $jurusan, $skill_keahlian, $tentang_saya, $foto_profil]);
        } else {
            // INSERT MAHASISWA
            $stmtInsert = $pdo->prepare("INSERT INTO profil_mahasiswa (user_id, nim, jurusan, prodi, skill, portofolio, tentang_saya, foto_profil, is_complete) VALUES (?, ?, ?, ?, ?, ?, ?, ?, true)");
            $stmtInsert->execute([$user_id, $identitas, $jurusan, $prodi, $skill_keahlian, $portofolio, $tentang_saya, $foto_profil]);
        }
    }
        

        $pdo->commit();

        // Redirect kembali ke halaman profil dengan status sukses
        header('Location: profile.php?status=success');
        exit;
        
    } catch (PDOException $e) {
        // Batalkan transaksi jika terjadi kesalahan pada database
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "Gagal menyimpan data: " . $e->getMessage();
        exit;
    }
} else {
    header('Location: profile.php');
    exit;
}