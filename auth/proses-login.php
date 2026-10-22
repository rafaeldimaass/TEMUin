<?php
session_start();

require_once '../includes/koneksi.php';


// 1. Menerima data dari form POST dengan null coalescing operator (??) dan trim
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
// 2. Validasi Server-Side
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
// Verifikasi apakah user dan password valid?
if ($user && password_verify($password, $user['password'])) {
    // 3. Jika valid: Simpan data user di Session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['role'] = $user['role'];

// 4. Logika pengalihan (redirect) berdasarkan role
    if ($user['role'] === 'mahasiswa') {
        header('Location: ../user-mhs/dashboard.php');
        exit;
    } elseif ($user['role'] === 'dosen') {
        header('Location: ../user-dosen/dashboard.php'); 
        exit;
    } elseif ($user['role'] === 'admin') {
        header('Location: ../admin/dashboard.php'); 
        exit;
    } else {
        // Fallback jika role tidak dikenali
        header('Location: ../index.php');
        exit;
    }
}

// 4. Jika tidak valid: Simpan pesan error di Session Flash & Redirect kembali ke form
$_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Email atau password salah.'];
header('Location: login.php');
exit;