<?php
session_start();

require_once '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$nama = trim($_POST['nama'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? $password;
$role = strtolower(trim($_POST['role'] ?? 'mahasiswa'));

$allowedRoles = ['mahasiswa', 'dosen', 'admin'];

if ($nama === '' || $email === '' || $password === '') {
    $_SESSION['register_error'] = 'Semua field wajib diisi.';
    header('Location: register.php');
    exit;
}

if (mb_strlen($nama) < 2) {
    $_SESSION['register_error'] = 'Nama minimal 2 karakter.';
    header('Location: register.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_error'] = 'Format email tidak valid.';
    header('Location: register.php');
    exit;
}

if (strlen($password) < 8) {
    $_SESSION['register_error'] = 'Password minimal 8 karakter.';
    header('Location: register.php');
    exit;
}

if ($password !== $confirmPassword) {
    $_SESSION['register_error'] = 'Konfirmasi password tidak sesuai.';
    header('Location: register.php');
    exit;
}

if (!in_array($role, $allowedRoles, true)) {
    $role = 'mahasiswa';
}

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$dbName = $_ENV['DB_NAME'] ?? 'temuin';
$dbUser = $_ENV['DB_USER'] ?? 'postgres';
$dbPass = $_ENV['DB_PASS'] ?? 'postgres';
$dbPort = $_ENV['DB_PORT'] ?? '5432';

$dsn = "pgsql:host={$host};port={$dbPort};dbname={$dbName};user={$dbUser};password={$dbPass}";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $cek = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $cek->execute([':email' => $email]);

    if ($cek->fetch()) {
        $_SESSION['register_error'] = 'Email sudah terdaftar.';
        header('Location: register.php');
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :password, :role)');
    $stmt->execute([
        ':nama' => $nama,
        ':email' => $email,
        ':password' => $hashedPassword,
        ':role' => $role,
    ]);

    $_SESSION['register_success'] = 'Registrasi berhasil. Silakan login.';
    header('Location: login.php');
    exit;
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['register_error'] = 'Registrasi gagal. Silakan coba lagi.';
    header('Location: register.php');
    exit;
}
