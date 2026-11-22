<?php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola-lomba.php');
    exit;
}

$id = $_POST['id'] ?? null;
$posterLama = trim($_POST['poster_lama'] ?? '');
$judul = trim($_POST['judul'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');
$kategori = trim($_POST['kategori'] ?? '');
$tanggal_penutupan = trim($_POST['tanggal_penutupan'] ?? '');
$link_detail = trim($_POST['link_detail'] ?? '');

if (!$id || !is_numeric($id)) {
    $_SESSION['error'] = "ID lomba tidak valid.";
    header('Location: kelola-lomba.php');
    exit;
}

$id = (int) $id;

// Validasi field wajib diisi
if (empty($judul) || empty($deskripsi) || empty($kategori) || empty($tanggal_penutupan) || empty($link_detail)) {
    $_SESSION['error'] = "Semua form wajib diisi.";
    header("Location: edit-lomba.php?id={$id}");
    exit;
}

// Ambil data lomba saat ini dari database untuk memastikan record ada dan nama poster tersimpan
try {
    $cekStmt = $pdo->prepare("SELECT id, poster FROM info_lomba WHERE id = :id LIMIT 1");
    $cekStmt->execute(['id' => $id]);
    $lombaLama = $cekStmt->fetch(PDO::FETCH_ASSOC);

    if (!$lombaLama) {
        $_SESSION['error'] = "Data lomba tidak ditemukan.";
        header('Location: kelola-lomba.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal memeriksa data lomba: " . $e->getMessage();
    header("Location: edit-lomba.php?id={$id}");
    exit;
}

$posterFinal = !empty($lombaLama['poster']) ? $lombaLama['poster'] : basename($posterLama);
$posterBaruDiunggah = false;

// Handle upload poster baru jika user memilih file baru
if (isset($_FILES['poster']) && $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['poster']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "Terjadi kesalahan saat mengunggah file poster.";
        header("Location: edit-lomba.php?id={$id}");
        exit;
    }

    $fileTmpPath = $_FILES['poster']['tmp_name'];
    $fileName = $_FILES['poster']['name'];
    $fileSize = $_FILES['poster']['size'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $maxSize = 5 * 1024 * 1024; // 

    if (!in_array($fileExtension, $allowedExtensions)) {
        $_SESSION['error'] = "Format poster tidak didukung. Gunakan JPG, JPEG, PNG, atau WEBP.";
        header("Location: edit-lomba.php?id={$id}");
        exit;
    }

    if ($fileSize > $maxSize) {
        $_SESSION['error'] = "Ukuran file poster maksimal 5MB.";
        header("Location: edit-lomba.php?id={$id}");
        exit;
    }

    $uploadFileDir = '../assets/uploads/poster/';
    if (!is_dir($uploadFileDir)) {
        mkdir($uploadFileDir, 0755, true);
    }

    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
    $destPath = $uploadFileDir . $newFileName;

    if (!move_uploaded_file($fileTmpPath, $destPath)) {
        $_SESSION['error'] = "Gagal mengunggah file poster ke server.";
        header("Location: edit-lomba.php?id={$id}");
        exit;
    }

    $posterFinal = $newFileName;
    $posterBaruDiunggah = true;
}

// Pastikan tetap ada poster (baik dari upload baru maupun poster lama)
if (empty($posterFinal)) {
    $_SESSION['error'] = "Poster lomba wajib ada.";
    header("Location: edit-lomba.php?id={$id}");
    exit;
}

try {
    $sql = "UPDATE info_lomba
            SET judul = :judul,
                deskripsi = :deskripsi,
                kategori = :kategori,
                tanggal_penutupan = :tanggal_penutupan,
                poster = :poster,
                link_detail = :link_detail
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'judul' => $judul,
        'deskripsi' => $deskripsi,
        'kategori' => $kategori,
        'tanggal_penutupan' => $tanggal_penutupan,
        'poster' => $posterFinal,
        'link_detail' => $link_detail,
        'id' => $id
    ]);

    // Hapus file poster lama hanya setelah UPDATE database berhasil dan ada poster baru
    if ($posterBaruDiunggah && !empty($lombaLama['poster'])) {
        $oldFilePath = realpath(__DIR__ . '/../assets/uploads/poster/' . basename($lombaLama['poster']));
        $uploadDirReal = realpath(__DIR__ . '/../assets/uploads/poster/');

        if ($oldFilePath && $uploadDirReal && strpos($oldFilePath, $uploadDirReal) === 0 && file_exists($oldFilePath)) {
            @unlink($oldFilePath);
        }
    }

    $_SESSION['berhasil'] = "Info lomba berhasil diperbarui.";
    header('Location: kelola-lomba.php');
    exit;
} catch (PDOException $e) {
    // Jika update gagal setelah upload file baru, bersihkan file baru agar tidak menumpuk
    if ($posterBaruDiunggah && isset($destPath) && file_exists($destPath)) {
        @unlink($destPath);
    }

    $_SESSION['error'] = "Gagal memperbarui data lomba: " . $e->getMessage();
    header("Location: edit-lomba.php?id={$id}");
    exit;
}