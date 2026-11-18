<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa'){
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/koneksi.php';
$page_title = "Detail Tim & Rekrutmen | TEMUIN";
require_once __DIR__ . '/../includes/header.php';

$tim_id = $_GET['id'] ?? 0;

try {
    // Ambil data tim beserta nama ketua dari tabel users
    $stmt = $pdo->prepare("SELECT t.*, u.nama AS nama_ketua, u.email AS email_ketua 
                           FROM tim_lomba t 
                           JOIN users u ON t.ketua_id = u.id 
                           WHERE t.id = ?");
                           $stmt->execute([$tim_id]);
    $tim = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tim) {
        echo "<p>Tim tidak ditemukan atau sudah dihapus.</p>";
        require_once '../includes/footer.php';
        exit;
    }

} catch (PDOException $e) {
    echo "Error database: " . $e->getMessage();
    exit;
}

// Parsing judul tim dan nama lomba dari kolom `judul` (Format: "Nama Tim - Nama Lomba")
// $judul_parts = explode(' - ', $tim['judul'], 2);
// $nama_tim = $judul_parts[0] ?? 'Tim Lomba';
// $nama_lomba = $judul_parts[1] ?? ($tim['kategori'] ?? 'Lomba');

// Ubah string skills yang dibutuhkan (misal: "Web Dev,UI/UX") menjadi array
$skills_array = !empty($tim['skills_dibutuhkan']) ? explode(',', $tim['skills_dibutuhkan']) : [];
?>

<link rel="stylesheet" href="../assets/css/pages/tim.css">
<link rel="stylesheet" href="../assets/css/components.css">

<div class="page-head">
    <div>
        <h1>Detail Tim Lomba</h1>
        <p>Pelajari kebutuhan tim ini dan kirimkan lamaran terbaikmu.</p>
    </div>
    <div class="btn-management-tim">
        <a class="btn btn-navy" href="list-tim.php">Kembali ke List Tim</a>
    </div>
</div>

<!-- Informasi Utama Tim -->
<div class="team-header-card">
    <div class="team-img">
        <?php if (!empty($tim['gambar'])): ?>
            <img src="../<?= htmlspecialchars($tim['gambar']) ?>" alt="Cover Tim" style="width: 100%; height: 100%; object-fit: cover;">
        <?php else: ?>
            <span style="font-weight: bold; color: #1f2d6b; font-size: 1.5rem;"><?= strtoupper(substr($tim['nama_lomba'], 0, 2)) ?></span>
        <?php endif; ?>
    </div>

    <div class="team-detail-info">
        <div class="badge">
            <span class="badge badge-green">Status: <?= htmlspecialchars(strtoupper($tim['status_tim'])) ?></span>
            <span class="badge badge-blue">Mencari: <?= htmlspecialchars($tim['jumlah_anggota']) ?> Anggota</span>
        </div>

    <h2 class="team-title-heading"><?= htmlspecialchars($tim['nama_tim']) ?></h2>
        <p class="team-desc-text"><?= htmlspecialchars($tim['nama_lomba']) ?></p>
        <p style="margin: 4px 0; font-size: 0.9rem; color: #475569;">
            Ketua Tim: <strong><?= htmlspecialchars($tim['nama_ketua']) ?></strong>
        </p>
        <p style="margin: 4px 0; font-size: 0.9rem; color: #475569;">
            Batas Pendaftaran: <strong><?= !empty($tim['tanggal_penutupan']) ? date('d F Y', strtotime($tim['tanggal_penutupan'])) : '-' ?></strong>
        </p>
        <div class="team-desc-box">
            <h4 style="margin: 0 0 5px 0; font-size: 0.95rem; color: #1e293b;">Deskripsi Tim:</h4>
            <p style="margin: 0; font-size: 0.9rem; color: #475569; line-height: 1.5;"><?= nl2br(htmlspecialchars($tim['deskripsi'])) ?></p>
        </div>
    </div>
</div>

<!-- Form Pengajuan Lamaran -->
<div class="form-card-container">
    <h3 class="form-card-title">Kirim Lamaran Bergabung</h3>
    <form action="proses-apply.php" method="POST" class="tim-form">
        <!-- Hidden input untuk ID Tim -->
        <input type="hidden" name="tim_id" value="<?= $tim['id'] ?>">
        <div class="tim-form-grid">
            <!-- Pilihan Posisi / Skill -->
            <div class="tim-input-group">
                <label for="posisi_dilamar">Pilih Skill yang Sesuai <span style="color: red;">*</span></label>
                <select id="posisi_dilamar" name="posisi_dilamar" required class="tim-form-control" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    <option value="" disabled selected>-- Pilih keahlian sesuai kebutuhan tim --</option>
                    <?php foreach ($skills_array as $skill): ?>
                        <?php $skill_option = trim($skill); ?>
                        <?php if (!empty($skill_option)): ?>
                            <option value="<?= htmlspecialchars($skill_option) ?>">
                                <?= htmlspecialchars($skill_option) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <small class="tim-hint" style="color: #64748b; font-size: 0.85rem; margin-top: 4px; display: block;">Pilih salah satu posisi yang dibuka oleh ketua tim di atas.</small>
            </div>
            <!-- Validasi Portofolio dari Profil -->
            <div class="tim-input-group">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #334155;">Portofolio Anda</label>
                <?php if (!empty($profil['portofolio'])): ?>
                    <input type="text" value="<?= htmlspecialchars($profil['portofolio']) ?>" disabled style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; background-color: #f1f5f9; color: #475569;">
                    <small class="tim-hint" style="color: #059669; font-size: 0.85rem; margin-top: 4px; display: block;">Portofolio otomatis dilampirkan dari data profil akunmu.</small>
                <?php else: ?>
                        <input type="url" name="portofolio_manual" placeholder="https://link-portofolio-anda.com" required class="tim-form-control">
                        <small class="tim-hint-danger">Profilmu belum memiliki portofolio. Silakan masukkan tautan portofolio eksternal di sini.</small>
                <?php endif; ?>
            </div>

            <!-- Pesan / Motivasi Bergabung -->
            <div class="tim-input-group">
                <label for="pesan_lamaran" style="display: block; font-weight: 600; margin-bottom: 8px; color: #334155;">Pesan / Alasan Bergabung (Opsional)</label>
                <textarea id="pesan_lamaran" name="pesan_lamaran" rows="4" placeholder="Halo ketua tim, saya tertarik bergabung karena memiliki pengalaman di bidang ini..." class="tim-form-control tim-textarea"></textarea>
            </div>
        </div>
        <div class="tim-form-actions" style="margin-top: 25px;">
            <button type="submit" class="btn btn-navy" style="width: 100%; padding: 12px; font-weight: 600; cursor: pointer;">Kirim Lamaran Sekarang</button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
