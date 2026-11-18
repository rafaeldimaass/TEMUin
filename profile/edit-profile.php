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

try {
    $tabelProfil = $isDosen ? 'profil_dosen' : 'profil_mahasiswa';
    $stmtProfil = $pdo->prepare("SELECT p.*, u.nama FROM users u LEFT JOIN {$tabelProfil} p ON u.id = p.user_id WHERE u.id = ?");    
    $stmtProfil->execute([$user_id]);
    $profil = $stmtProfil->fetch(PDO::FETCH_ASSOC) ?: [];
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
        <h1>Edit Profil <?= $isDosen ? 'Dosen' : 'Mahasiswa'; ?></h1>
        <p>Kelola informasi akun, perbarui data pribadi, dan pantau aktivitas timmu di sini.</p>
    </div>
</div>

<div class="profile-card">
    <div class="profile-header-bg">
        <form action="../profile/proses-tambah-profile.php" method="POST" enctype="multipart/form-data">
            <div class="profile-avatar-wrapper">
                <?php if (!empty($profil['foto_profil']) && file_exists("../assets/uploads/profile/" . $profil['foto_profil'])): ?>
                    <img src="../assets/uploads/profile/<?= htmlspecialchars($profil['foto_profil']) ?>" alt="Foto Profil" class="profile-avatar">
                <?php else: ?>
                    <div class="profile-avatar-default" id="defaultAvatarContainer">
                        <span id="avatarInitial"><?= strtoupper(substr($namaMahasiswa, 0, 1)) ?></span>
                    </div>
                    <img src="" alt="preview foto" class="profile-avatar d-none" id="previewAvatar">
                <?php endif; ?>
                <button type="button" class="avatar-edit-btn" id="triggerUpload" title="Ubah Foto">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <input type="file" id="hiddenFotoInput" name="foto_profil" class="d-none" accept=".jpg, .jpeg, .png, .webp">
            </div>
    </div>

    <div class="profile-info">
        <div class="info-box">
            <div class="info-content">
                <span class="info-label">Nama</span>
                <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($profil['nama'] ?? ''); ?>" required>
            </div>
        </div>

        <div class="info-box">
            <div class="info-content">
                <span class="info-label"><?= $isDosen ? 'NIP' : 'NIM'; ?></span>
                <input type="text" class="form-control" id="identitas" name="identitas" value="<?= htmlspecialchars($isDosen ? ($profil['nip'] ?? '') : ($profil['nim'] ?? '')); ?>" required>
            </div>
        </div>

        <div class="info-box">
            <div class="info-content">
                <span class="info-label">JURUSAN</span>
                <select name="jurusan" class="form-control">
                    <option value="">Pilih Jurusan</option>
                    <option value="Teknologi Informasi" <?= (isset($profil['jurusan']) && $profil['jurusan'] === 'Teknologi Informasi') ? 'selected' : ''; ?>>Teknologi Informasi</option>
                    <option value="Teknik Elektro" <?= (isset($profil['jurusan']) && $profil['jurusan'] === 'Teknik Elektro') ? 'selected' : ''; ?>>Teknik Elektro</option>
                    <option value="Teknik Mesin" <?= (isset($profil['jurusan']) && $profil['jurusan'] === 'Teknik Mesin') ? 'selected' : ''; ?>>Teknik Mesin</option>
                    <option value="Teknik Sipil" <?= (isset($profil['jurusan']) && $profil['jurusan'] === 'Teknik Sipil') ? 'selected' : ''; ?>>Teknik Sipil</option>
                    <option value="Teknik Kimia" <?= (isset($profil['jurusan']) && $profil['jurusan'] === 'Teknik Kimia') ? 'selected' : ''; ?>>Teknik Kimia</option>
                    <option value="Akuntansi" <?= (isset($profil['jurusan']) && $profil['jurusan'] === 'Akuntansi') ? 'selected' : ''; ?>>Akuntansi</option>
                    <option value="Administrasi Niaga" <?= (isset($profil['jurusan']) && $profil['jurusan'] === 'Administrasi Niaga') ? 'selected' : ''; ?>>Administrasi Niaga</option>
                </select>
            </div>
        </div>
        <?php if (!$isDosen): ?>
        <div class="info-box">
            <div class="info-content">
                <span class="info-label">Program Studi</span>
                <input type="text" class="form-control" id="prodi" name="prodi" value="<?= htmlspecialchars($profil['prodi'] ?? ''); ?>" required>
            </div>
        </div>
        <?php endif; ?>

        <div class="info-box">
            <div class="info-content">
                <span class="info-label"><?= $isDosen ? 'Bidang Keahlian' : 'Skills'; ?></span>
                <input type="text" class="form-control" id="skill" name="skill" value="<?= htmlspecialchars($isDosen ? ($profil['bidang_kepakaran'] ?? '') : ($profil['skill'] ?? '')); ?>" required>
            </div>
        </div>
       
        <?php if (!$isDosen): ?>
        <div class="info-box">
            <div class="info-content">
                <span class="info-label">Link Portofolio/CV</span>
                <input type="text" class="form-control" id="portofolio" name="portofolio" value="<?= htmlspecialchars($profil['portofolio'] ?? ''); ?>" required>
            </div>
        </div>
         <?php endif; ?>

        <div class="about-section">
            <h3 class="about-title"><?= $isDosen ? 'Pengalaman' : 'Tentang Saya'; ?></h3>
            <p class="about-text">
            <textarea class="form-control" id="tentang_saya" name="tentang_saya" rows="4" required><?= htmlspecialchars($isDosen ? ($profil['pengalaman'] ?? '') : ($profil['tentang_saya'] ?? '')); ?></textarea>
        </p>
        </div>

        <div class="profile-actions" style="display: flex; justify-content: center; gap: 10px; margin-top: 20px; grid-column: 1 / -1;">
            <a href="../profile/profile.php" class="btn btn-ghost">Kembali</a>
            <button type="submit" class="btn btn-navy">Simpan Perubahan</button>
        </div>
    </div>
    </form>

    <script src="../assets/js/edit-profile.js"></script>
    <?php include '../includes/footer.php'; ?>