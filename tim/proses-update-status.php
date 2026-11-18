<?php
session_start();
require_once '../includes/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lamaran_id = $_POST['lamaran_id'];
    $tim_id = $_POST['tim_id'];
    $user_id = $_POST['user_id'];
    $posisi = $_POST['posisi'];
    $status_baru = $_POST['status_baru'];

    try {
        $pdo->beginTransaction();

        // 1. Update status di tabel lamaran_anggota
        $stmtUpdate = $pdo->prepare("UPDATE lamaran_anggota SET status = ? WHERE id = ?");
        $stmtUpdate->execute([$status_baru, $lamaran_id]);

        // 2. Jika statusnya 'diterima', masukkan ke tabel anggota_tim jika belum ada
        if ($status_baru === 'diterima') {
            // Cek dulu apakah sudah tercatat sebagai anggota di tim ini
            $stmtCek = $pdo->prepare("SELECT id FROM anggota_tim WHERE tim_id = ? AND user_id = ?");
            $stmtCek->execute([$tim_id, $user_id]);


            if ($stmtCek->rowCount() === 0) {
                $stmtInsert = $pdo->prepare("INSERT INTO anggota_tim (tim_id, user_id, peran_di_tim) VALUES (?, ?, ?)");
                $stmtInsert->execute([$tim_id, $user_id, $posisi]);
            }
        } else {
            // Jika status diubah dari diterima menjadi ditolak/pending, hapus dari tabel anggota_tim jika perlu
            $stmtDelete = $pdo->prepare("DELETE FROM anggota_tim WHERE tim_id = ? AND user_id = ?");
            $stmtDelete->execute([$tim_id, $user_id]);
        }

        $pdo->commit();
        header('Location: kelola-tim.php?status=sukses');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Terjadi kesalahan: " . $e->getMessage());
    }
}
