<?php
session_start();
require_once '../includes/koneksi.php';

$role = $_SESSION['role'] ?? '';
if (!isset($_SESSION['user_id']) || !in_array($role, ['mahasiswa', 'admin'], true)) {
    header('Location: ../auth/login.php');
    exit;
}

$isAdmin        = ($role === 'admin');
$halamanKembali = $isAdmin ? 'data-tim.php' : 'kelola-tim.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $halamanKembali");
    exit;
}

$id          = (int) ($_POST['id'] ?? 0);
$halamanEdit = "edit-tim.php?id=$id";

// 1. Cek ulang kepemilikan dari DATABASE (jangan percaya data form)
$stmt = $pdo->prepare('SELECT id, ketua_id, gambar FROM tim_lomba WHERE id = ?');
$stmt->execute([$id]);
$timLama = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$timLama || (!$isAdmin && (int) $timLama['ketua_id'] !== (int) $_SESSION['user_id'])) {
    $_SESSION['error'] = 'Tim tidak ditemukan atau kamu tidak berhak mengeditnya.';
    header("Location: $halamanKembali");
    exit;
}

// 2. Ambil & validasi input
$nama_lomba        = trim($_POST['nama-lomba'] ?? '');
$nama_tim          = trim($_POST['nama-tim'] ?? '');
$kategori          = trim($_POST['kategori'] ?? '');
$deskripsi         = trim($_POST['deskripsi-tim'] ?? '');
$skills_dibutuhkan = trim($_POST['skills_dibutuhkan'] ?? '');
$jumlah_anggota    = (int) ($_POST['jumlah-anggota'] ?? 0);
$tanggal_penutupan = trim($_POST['tanggal-penutupan'] ?? '');
$status_tim        = $_POST['status_tim'] ?? 'open';
$statusValid       = $isAdmin ? ['open', 'closed', 'banned'] : ['open', 'closed'];

if ($nama_lomba === '' || $nama_tim === '' || $jumlah_anggota < 1 || $tanggal_penutupan === '') {
    $_SESSION['error'] = 'Semua field wajib harus diisi!';
    header("Location: $halamanEdit");
    exit;
}

if (!in_array($kategori, ['Lomba IT', 'Lomba Non IT'], true) || !in_array($status_tim, $statusValid, true)) {
    $_SESSION['error'] = 'Kategori atau status tidak valid.';
    header("Location: $halamanEdit");
    exit;
}

// 3. Upload gambar baru (opsional). Kalau tidak upload, pakai gambar lama
$gambar     = $timLama['gambar'];
$gambarBaru = null;

if (isset($_FILES['tim-img']) && $_FILES['tim-img']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['tim-img']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        $_SESSION['error'] = 'Format gambar harus JPG, PNG, atau WEBP.';
        header("Location: $halamanEdit");
        exit;
    }

    $folder   = __DIR__ . '/../assets/uploads/tim/';
    $namaFile = md5(time() . $_FILES['tim-img']['name']) . '.' . $ext;
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }

    if (move_uploaded_file($_FILES['tim-img']['tmp_name'], $folder . $namaFile)) {
        $gambarBaru = 'assets/uploads/tim/' . $namaFile;
        $gambar     = $gambarBaru;
    }
}

// 4. UPDATE
try {
    $stmt = $pdo->prepare(
        'UPDATE tim_lomba
         SET nama_lomba = ?, nama_tim = ?, kategori = ?, deskripsi = ?, skills_dibutuhkan = ?,
             jumlah_anggota = ?, tanggal_penutupan = ?, status_tim = ?, gambar = ?
         WHERE id = ?'
    );
    $stmt->execute([$nama_lomba, $nama_tim, $kategori, $deskripsi, $skills_dibutuhkan,
                    $jumlah_anggota, $tanggal_penutupan, $status_tim, $gambar, $id]);

    // Hapus gambar lama hanya kalau ada gambar baru & update berhasil
    if ($gambarBaru && !empty($timLama['gambar'])) {
        $pathLama = __DIR__ . '/../assets/uploads/tim/' . basename($timLama['gambar']);
        if (is_file($pathLama)) {
            unlink($pathLama);
        }
    }

    $_SESSION['success'] = 'Info tim berhasil diperbarui.';
    header("Location: $halamanKembali");
    exit;
} catch (PDOException $e) {
    if ($gambarBaru) {
        @unlink(__DIR__ . '/../' . $gambarBaru);
    }
    $_SESSION['error'] = 'Gagal memperbarui tim: ' . $e->getMessage();
    header("Location: $halamanEdit");
    exit;
}
