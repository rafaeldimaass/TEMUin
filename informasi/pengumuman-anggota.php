<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['mahasiswa'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/koneksi.php';
$page_title = "Pengumuman | TEMUin";
require_once '../includes/header.php';

$user_id = $_SESSION['user_id'];
$tim_id = $_GET['tim_id'] ?? 0;

try {
    // Jika mahasiswa melamar ke banyak tim, kita ambil data lamaran berdasarkan tim_id atau tampilkan yang terbaru.
    // Di sini kita gunakan JOIN ke tabel tim_lomba dan users (ketua) untuk mengambil nomor WhatsApp ketua tim.
    if ($tim_id > 0) {
        $stmt = $pdo->prepare("
            SELECT l.status, t.judul, u.nama AS nama_ketua, u.no_hp AS whatsapp_ketua 
            FROM lamaran_anggota l
            JOIN tim_lomba t ON l.tim_id = t.id
            JOIN users u ON t.ketua_id = u.id
            WHERE l.kandidat_id = ? AND l.tim_id = ?
        ");
        $stmt->execute([$user_id, $tim_id]);
        $lamaran = $stmt->fetch(PDO::FETCH_ASSOC);

        $status_penerimaan = strtolower(trim($lamaran['status'] ?? 'menunggu'));
        $nama_ketua = $lamaran['nama_ketua'] ?? 'Ketua Tim';

        // Parsing judul tim dari kolom `judul` di tabel `tim_lomba` (Format: "Nama Tim - Nama Lomba")
        $judul_lengkap = $lamaran['judul'] ?? 'Tim Lomba';
        $judul_parts = explode(' - ', $judul_lengkap, 2);
        $nama_tim = $judul_parts[0] ?? 'Tim Lomba';

        // Format nomor HP ke format link WhatsApp (misal: 08123... jadi 628123...)
        $wa_raw = preg_replace('/[^0-9]/', '', $lamaran['whatsapp_ketua'] ?? '');
        if (substr($wa_raw, 0, 1) === '0') {
            $wa_ketua = '62' . substr($wa_raw, 1);
        } else {
            $wa_ketua = $wa_raw;
        }
        $link_whatsapp_ketua = !empty($wa_ketua) ? "https://wa.me/{$wa_ketua}?text=Halo%20kak%20{$nama_ketua},%20saya%20melihat%20status%20lamaran%20saya%20diterima." : '';
    } else {
        // Jika tidak ada tim_id spesifik, arahkan user untuk memilih lamaran dari list riwayat lamaran
        $status_penerimaan = 'pilih_tim';
    }
} catch (PDOException $e) {
    $status_penerimaan = 'error';
}
?>
?>

<style>
    .hasil-wrap {
        max-width: 760px;
        margin: 48px auto;
        padding: 0 16px;
    }

    .hasil-card {
        padding: 36px 28px;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 28px rgba(0, 0, 0, .08);
        text-align: center;
    }

    .hasil-icon {
        font-size: 48px;
        margin-bottom: 12px;
    }

    .hasil-card h1 {
        margin: 0 0 14px;
        font-size: 28px;
    }

    .hasil-card p {
        color: #555;
        line-height: 1.7;
    }

    .hasil-diterima h1,
    .hasil-diterima .hasil-icon {
        color: #198754;
    }

    .hasil-ditolak h1,
    .hasil-ditolak .hasil-icon {
        color: #dc3545;
    }

    .hasil-menunggu h1,
    .hasil-menunggu .hasil-icon {
        color: #b7791f;
    }

    .btn-wa {
        display: inline-block;
        margin-top: 12px;
        padding: 12px 20px;
        border-radius: 8px;
        background: #25d366;
        color: #fff;
        font-weight: 600;
        text-decoration: none;
    }

    .btn-wa:hover {
        background: #1eae54;
        color: #fff;
    }
</style>

<main class="hasil-wrap">
    <?php if ($status_penerimaan === 'diterima'): ?>
        <section class="hasil-card hasil-diterima">
            <div class="hasil-icon" aria-hidden="true">🎉</div>
            <h1>Selamat, kamu diterima!</h1>
            <p>Selamat bergabung menjadi bagian dari TEMUin. Silakan hubungi <strong><?= htmlspecialchars($nama_ketua) ?></strong> selaku ketua tim melalui WhatsApp untuk mendapatkan informasi dan arahan selanjutnya.</p>
            <?php if (!empty($link_whatsapp_ketua)): ?>
                <a class="btn-wa" href="<?= htmlspecialchars($link_whatsapp_ketua, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Hubungi Ketua Tim</a>
            <?php else: ?>
                <p>Nomor WhatsApp ketua tim belum tersedia.</p>
            <?php endif; ?>
        </section>
    <?php elseif (in_array($status_penerimaan, ['tidak_diterima', 'ditolak'], true)): ?>
        <section class="hasil-card hasil-ditolak">
            <div class="hasil-icon" aria-hidden="true">💙</div>
            <h1>Terima kasih sudah mendaftar</h1>
            <p>Mohon maaf, kali ini kamu belum dapat bergabung dengan <?= htmlspecialchars($nama_tim) ?>. Jangan berkecil hati—tetap semangat, terus kembangkan potensimu, dan semoga ada kesempatan lain untuk bekerja sama!</p>
            <div style="margin-top: 20px;">
                <a href="list-tim.php" class="btn-navy">Cari Tim Lain</a>
            </div>
        </section>
    <?php elseif ($status_penerimaan === 'pending' || $status_penerimaan === 'menunggu'): ?>
        <section class="hasil-card hasil-menunggu">
            <div class="hasil-icon" aria-hidden="true">📣</div>
            <h1>Lamaran sedang ditinjau</h1>
            <p>Lamaranmu untuk tim <strong><?= htmlspecialchars($nama_tim) ?></strong> sudah dikirim dan sedang dalam proses seleksi. Silakan cek halaman ini secara berkala.</p>
        </section>
    <?php else: ?>
        <section class="hasil-card hasil-menunggu">
            <div class="hasil-icon" aria-hidden="true">⚠️</div>
            <h1>Pilih Lamaran Terlebih Dahulu</h1>
            <p>Silakan akses halaman ini melalui menu riwayat lamaran untuk melihat status penerimaan pada tim yang dituju.</p>
            <div style="margin-top: 20px;">
                <a href="riwayat-lamaran.php" class="btn-navy">Lihat Riwayat Lamaran</a>
            </div>
        </section>
    <?php endif; ?>
</main>