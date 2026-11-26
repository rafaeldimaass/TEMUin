<?php
session_start();

$role = $_SESSION['role'] ?? '';
if (!isset($_SESSION['user_id']) || !in_array($role, ['mahasiswa', 'admin'], true)) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/koneksi.php';

$isAdmin = ($role == 'admin');
$halamanKembali = $isAdmin ? 'data-tim.php' : 'kelola-tim.php';
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM tim_lomba WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$tim = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tim || (!$isAdmin && (int) $tim['ketua_id'] !== (int) $_SESSION['user_id'])) {
    $_SESSION['error'] = 'Tim tidak ditemukan atau kamu tidak berhak mengeditnya.';
    header("Location: $halamanKembali");
    exit;
}

$statusOptions = $isAdmin ? ['open', 'closed', 'banned'] : ['open', 'closed'];
$page_title    = 'Edit Tim | TEMUin';
require_once $isAdmin ? '../includes/sidebar.php' : '../includes/header.php';
require_once '../includes/flash.php';
?>
<link rel="stylesheet" href="../assets/css/pages/tim.css">
<link rel="stylesheet" href="../assets/css/components.css">

<div class="page-head">
    <div>
        <h1>Edit Info Tim</h1>
        <p>Perbarui informasi rekrutmen tim.</p>
    </div>
</div>

<div class="form-tim-wrapper">
    <form action="proses-edit-tim.php" method="POST" enctype="multipart/form-data" class="tim-form">
        <input type="hidden" name="id" value="<?= (int) $tim['id'] ?>">

        <div class="tim-header-img">
            <label for="tim-img-upload" class="img-upload-label">
                <input type="file" id="tim-img-upload" name="tim-img" accept="image/*" style="display: none;">
                <div id="img-preview" class="img-preview">
                    <?php if (!empty($tim['gambar'])): ?>
                        <img src="../<?= htmlspecialchars($tim['gambar']) ?>" alt="Gambar Tim" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">
                    <?php else: ?>
                        <i class="fas fa-cloud-upload-alt"></i><span>Upload Gambar</span>
                    <?php endif; ?>
                </div>
            </label>
        </div>

        <div class="tim-container">
            <div class="tim-form-grid">
                <div class="tim-form-column">
                    <div class="tim-input-group">
                        <label for="nama-lomba">Nama Lomba</label>
                        <input type="text" id="nama-lomba" name="nama-lomba" value="<?= htmlspecialchars($tim['nama_lomba'] ?? '') ?>" required>
                    </div>

                    <div class="tim-input-group">
                        <label for="kategori">Kategori Lomba</label>
                        <select id="kategori" name="kategori" required style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;">
                            <?php foreach (['Lomba IT', 'Lomba Non IT'] as $k): ?>
                                <option value="<?= $k ?>" <?= ($tim['kategori'] === $k) ? 'selected' : '' ?>><?= $k ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="tim-input-group">
                        <label for="deskripsi-tim">Deskripsi</label>
                        <input type="text" id="deskripsi-tim" name="deskripsi-tim" value="<?= htmlspecialchars($tim['deskripsi'] ?? '') ?>">
                    </div>

                    <div class="tim-input-group">
                        <label for="skillsInput">Skills yang dibutuhkan</label>
                        <div class="chip-input-container" id="chip-container">
                            <div id="chipList"></div>
                            <!-- value diisi skill lama; tim.js akan mengubahnya jadi chip -->
                            <input type="hidden" name="skills_dibutuhkan" id="skillsHidden" value="<?= htmlspecialchars($tim['skills_dibutuhkan'] ?? '') ?>" required>
                            <input type="text" id="skillsInput" placeholder="Ketik skill lalu Enter">
                        </div>
                    </div>
                </div>

                <div class="tim-form-column">
                    <div class="tim-input-group">
                        <label for="nama-tim">Nama Tim</label>
                        <input type="text" id="nama-tim" name="nama-tim" value="<?= htmlspecialchars($tim['nama_tim'] ?? '') ?>" required>
                    </div>

                    <div class="tim-input-group">
                        <label for="jumlah-anggota">Jumlah Anggota Dibutuhkan</label>
                        <input type="number" id="jumlah-anggota" name="jumlah-anggota" min="1" max="10" value="<?= (int) $tim['jumlah_anggota'] ?>" required>
                    </div>

                    <div class="tim-input-group">
                        <label for="tanggal-penutupan">Tanggal Penutupan</label>
                        <input type="date" id="tanggal-penutupan" name="tanggal-penutupan" value="<?= htmlspecialchars(substr($tim['tanggal_penutupan'] ?? '', 0, 10)) ?>" required>
                    </div>

                    <div class="tim-input-group">
                        <label for="status_tim">Status Rekrutmen</label>
                        <select id="status_tim" name="status_tim" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;">
                            <?php foreach ($statusOptions as $s): ?>
                                <option value="<?= $s ?>" <?= ($tim['status_tim'] === $s) ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="tim-form-actions">
                <a href="<?= $halamanKembali ?>" class="tim-btn tim-btn-cancel">Batal</a>
                <button type="submit" class="tim-btn tim-btn-submit">Simpan Perubahan</button>
            </div>
        </div>
    </form>
</div>

<script src="../assets/js/tim.js"></script>
<?php require_once '../includes/footer.php'; ?>