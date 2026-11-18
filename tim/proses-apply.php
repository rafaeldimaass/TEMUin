<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kandidat_id    = $_SESSION['user_id'];
    $tim_id         = intval($_POST['tim_id'] ?? 0);
    $posisi_dilamar = trim($_POST['posisi_dilamar'] ?? '');
    $pesan_lamaran  = trim($_POST['pesan_lamaran'] ?? '');

    // Tangkap portofolio dari input tersembunyi profil atau input manual
    $portofolio     = trim($_POST['portofolio'] ?? $_POST['portofolio_manual'] ?? '');

    if ($tim_id === 0 || empty($posisi_dilamar)) {
        header("Location: apply-tim.php?id=$tim_id&error=datakosong");
        exit;
    }
    try {
        // Cek apakah mahasiswa sudah pernah melamar ke tim ini sebelumnya
        $stmt_cek = $pdo->prepare("SELECT id FROM lamaran_anggota WHERE tim_id = ? AND kandidat_id = ?");
        $stmt_cek->execute([$tim_id, $kandidat_id]);
        
        if ($stmt_cek->rowCount() > 0) {
            header("Location: apply-tim.php?id=$tim_id&error=sudahmelamar");
            exit;
        }

        // Jika user mengisi portofolio secara manual dan kolom portofolio di tabel profil_mahasiswa masih kosong, simpan otomatis
        if (!empty($_POST['portofolio_manual'])) {
            $stmt_update_user = $pdo->prepare("UPDATE profil_mahasiswa SET portofolio = ? WHERE user_id = ? AND (portofolio IS NULL OR portofolio = '')");
            $stmt_update_user->execute([$portofolio, $kandidat_id]);
        }


        $sql = "INSERT INTO lamaran_anggota (tim_id, kandidat_id, posisi_dilamar, pesan_lamaran, status) 
                VALUES (?, ?, ?, ?, 'pending')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tim_id, $kandidat_id, $posisi_dilamar, $pesan_lamaran]);

        header("Location: list-tim.php?status=lamaran_terkirim");
        exit;
    } catch (PDOException $e) {
        echo "Gagal mengirim lamaran: " . $e->getMessage();
    }
} else {
    header("Location: list-tim.php");
    exit;
}
?>