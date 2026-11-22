<?php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/koneksi.php';

// Terima ID dari POST atau GET jika dipanggil lewat link
$id = $_POST['id'] ?? $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    $_SESSION['error'] = "ID lomba tidak valid.";
    header('Location: kelola-lomba.php');
    exit;
}

$id = (int) $id;

try {
    // Ambil data lomba untuk cek keberadaan & nama file poster
    $stmt = $pdo->prepare("SELECT id, poster FROM info_lomba WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $lomba = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lomba) {
        $_SESSION['error'] = "Data lomba tidak ditemukan.";
        header('Location: kelola-lomba.php');
        exit;
    }

    // Hapus record dari tabel info_lomba
    $deleteStmt = $pdo->prepare("DELETE FROM info_lomba WHERE id = :id");
    $deleteStmt->execute(['id' => $id]);

    // Hapus file poster di server jika ada
    if (!empty($lomba['poster'])) {
        $posterPath = realpath(__DIR__ . '/../assets/uploads/poster/' . basename($lomba['poster']));
        $uploadDir  = realpath(__DIR__ . '/../assets/uploads/poster/');

        if ($posterPath && $uploadDir && strpos($posterPath, $uploadDir) === 0 && file_exists($posterPath)) {
            @unlink($posterPath);
        }
    }

    $_SESSION['berhasil'] = "Info lomba berhasil dihapus.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal menghapus info lomba: " . $e->getMessage();
}

header('Location: kelola-lomba.php');
exit;