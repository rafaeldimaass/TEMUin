<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/koneksi.php';

// Dipakai untuk hapus dosen dan mahasiswa:
//   hapus-user.php?role=dosen&id=<users.id>
//   hapus-user.php?role=mahasiswa&id=<users.id>
$id   = (int) ($_GET['id'] ?? 0);
$role = $_GET['role'] ?? '';

if (!in_array($role, ['dosen', 'mahasiswa'], true)) {
    header('Location: dashboard.php');
    exit;
}

$back        = $role === 'dosen' ? 'data-dosen.php' : 'data-mahasiswa.php';
$tabelProfil = $role === 'dosen' ? 'profil_dosen' : 'profil_mahasiswa';

// Pastikan user ada dan role-nya sesuai (supaya akun admin tidak bisa terhapus dari sini)
$stmt = $pdo->prepare(
    "SELECT u.id, p.foto_profil
     FROM users u
     LEFT JOIN {$tabelProfil} p ON p.user_id = u.id
     WHERE u.id = ? AND u.role = ?"
);
$stmt->execute([$id, $role]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = 'Data tidak ditemukan.';
    header("Location: $back");
    exit;
}

try {
    // profil, tim, lamaran, dan bimbingan milik user ini ikut terhapus (ON DELETE CASCADE)
    $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);

    // Hapus juga file fotonya dari folder uploads
    if (!empty($user['foto_profil'])) {
        $path_foto = '../assets/uploads/profile/' . basename($user['foto_profil']);
        if (is_file($path_foto)) {
            unlink($path_foto);
        }
    }

    $_SESSION['success'] = 'Data berhasil dihapus.';
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = 'Data gagal dihapus.';
}

header("Location: $back");
exit;