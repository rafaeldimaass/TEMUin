<?php
session_start();


if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['mahasiswa', 'dosen'], true)) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/koneksi.php';
$role = $_SESSION['role'];
$isDosen = $role === 'dosen';
$page_title = $isDosen ? "Profile Dosen | TEMUin" : "Profile Mahasiswa | TEMUin";
require_once '../includes/header.php';

$namaMahasiswa = $_SESSION['nama'] ?? 'Mahasiswa';
$user_id = $_SESSION['user_id'];

// Ambil data profil mahasiswa dari database
try {
    $tabelProfil = $isDosen ? 'profil_dosen' : 'profil_mahasiswa';
    $stmtProfil = $pdo->prepare("SELECT u.nama, u.email, u.no_wa, p.* FROM users u LEFT JOIN {$tabelProfil} p ON u.id = p.user_id WHERE u.id = ?");
    // $stmtProfil = $pdo->prepare("SELECT * FROM {$tabelProfil} WHERE user_id = ?");
    $stmtProfil->execute([$user_id]);
    $profil = $stmtProfil->fetch(PDO::FETCH_ASSOC) ?: [];

    //DEBUG: Cek isi data di sini
    // echo "<pre>";
    // print_r($profil);
    // echo "</pre>";
    // exit;   

} catch (PDOException $e) {
    echo "Error database: " . $e->getMessage();
    exit;
}

?>
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/pages/profile.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="page-head">
    <div>
        <h1>Profil <?= $isDosen ? 'Dosen' : 'Mahasiswa'; ?></h1>
        <p>Kelola informasi akun, perbarui data pribadi, dan pantau aktivitas timmu di sini.</p>
    </div>
</div>

<div class="profile-card">
    <!-- Bagian Header Background Biru dan Foto Profil -->
    <div class="profile-header-bg">
        <div class="profile-avatar-wrapper">
            <?= !empty($profil['foto_profil']) && file_exists("../assets/uploads/profile/" . $profil['foto_profil']) ? '<img src="../assets/uploads/profile/' . htmlspecialchars($profil['foto_profil']) . '" alt="Foto Profil" class="profile-avatar">' : '<div class="profile-avatar-default"><span>' . strtoupper(substr($namaMahasiswa, 0, 1)) . '</span></div>'; ?>
        </div>
    </div>

    </form>
    <!-- Informasi Teks Nama & NIM -->
    <div class="profile-user-info">
        <h2 class="profile-name"><?= htmlspecialchars($namaMahasiswa); ?></h2>
        <span class="profile-nim"><?= htmlspecialchars($isDosen ? ($profil['nip'] ?? $profil['nip'] ?? 'NIP belum diisi') : ($profil['nim'] ?? 'NIM belum diisi')); ?></span>
    </div>

    <div class="profile-info">
        <div class="info-box">
            <div class="info-icon">
                <i class="bi bi-building"></i>
            </div>
            <div class="info-content">
                <span class="info-label">JURUSAN</span>
                <span class="info-value"><?= htmlspecialchars($isDosen ? ($profil['jurusan'] ?? $profil['jurusan'] ?? 'NIP belum diisi') : ($profil['jurusan'] ?? 'Jurusan belum diisi')); ?></span>
            </div>
        </div>

        <?php if (!$isDosen): ?>
        <div class="info-box">
            <div class="info-icon">
                <i class="bi bi-mortarboard"></i>
            </div>
            <div class="info-content">
                <span class="info-label">Program Studi</span>
                <span class="info-value"><?= htmlspecialchars($profil['prodi'] ?? 'Program Studi belum diisi'); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <div class="info-box">
            <div class="info-icon">
                <i class="bi bi-code-slash"></i>
            </div>
            <div class="info-content">
                <span class="info-label">Skills</span>
                <span class="info-value"><?= htmlspecialchars($isDosen ? ($profil['bidang_kepakaran'] ?? $profil['skill'] ?? 'Bidang keahlian belum diisi') : ($profil['skill'] ?? 'Skills belum diisi')); ?></span>
            </div>
        </div>

        <?php if (!$isDosen): ?>
        <div class="info-box">
            <div class="info-icon">
                <i class="bi bi-link-45deg"></i>
            </div>
            <div class="info-content">
                <span class="info-label">Link Portofolioc/CV</span>
                <span class="info-value"><?= htmlspecialchars($profil['portofolio'] ?? 'Link belum diisi'); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <div class="about-section">
            <h3 class="about-title"><?= $isDosen ? 'Pengalaman' : 'Tentang Saya'; ?></h3>
            <p class="about-text">
<?= htmlspecialchars($isDosen ? ($profil['pengalaman'] ?? 'Pengalaman belum diisi') : ($profil['tentang_saya'] ?? 'Informasi tentang saya belum diisi')); ?>            </p>
        </div>

    </div>

    <div class="profile-contact-security-card" style="background: #f8f9fa; border-radius: 12px; padding: 20px; margin-top: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); grid-column: 1 / -1;">
        <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 15px; color: #253C6D;">Informasi Kontak & Akun</h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 20px;">
            <div class="info-box">
                <i class="bi bi-envelope" style="font-size: 1.2rem; color: #F2842F;"></i>
                <div>
                    <span style="font-size: 0.85rem; color: #6c757d; display: block;">Email</span>
                    <span style="font-weight: 500; color: #333;"><?= htmlspecialchars($profil['email'] ?? 'Email belum diisi'); ?></span>
                </div>
            </div>

            <div class="info-box">
                <i class="bi bi-whatsapp" style="font-size: 1.2rem; color: #25D366;"></i>
                <div>
                    <span style="font-size: 0.85rem; color: #6c757d; display: block;">No. WhatsApp</span>
                    <span style="font-weight: 500; color: #333;"><?= htmlspecialchars($profil['no_wa'] ?? 'No. WhatsApp belum diisi'); ?></span>
                </div>
            </div>
        </div>

        <div style="border-top: 1px solid #dee2e6; padding-top: 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <span style="font-size: 0.9rem; color: #6c757d;">Keamanan Akun (Password)</span>
            <a href="../auth/ubah-password.php" class="btn btn-navy">
                <i class="bi bi-key"></i> Ubah / Lupa Password
            </a>
        </div>
    </div>

    <div class="profile-actions" style="display: flex; justify-content: center; gap: 10px; margin-top: 20px; margin-bottom: 20px; grid-column: 1 / -1;">
        <a href="edit-profile.php" class="btn btn-navy">Edit Profil</a>
        <a href="<?= ($_SESSION['role'] === 'dosen') ? '../user-dosen/dashboard.php' : '../user-mhs/dashboard.php'; ?>" class="btn btn-ghost">Keluar</a>
    </div>

</div>


<?php include '../includes/footer.php'; ?>