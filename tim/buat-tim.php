<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/koneksi.php';
$page_title = "Buat Tim | TEMUIN";
require_once '../includes/header.php';

$namaMahasiswa = $_SESSION['nama'] ?? 'Mahasiswa';
?>

<link rel="stylesheet" href="../assets/css/pages/tim.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/global.css">


<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success" style="padding: 14px 20px; background: #d1fae5; color: #065f46; border: 1px solid #10b981; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; justify-content: space-between; align-items: center;">
        <span><?= htmlspecialchars($_SESSION['success']) ?></span>
        <button type="button" onclick="this.parentElement.style.display='none';" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #065f46;">&times;</button>
    </div>
    <?php unset($_SESSION['success']); // Hapus session setelah ditampilkan 
    ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger" style="padding: 14px 20px; background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; justify-content: space-between; align-items: center;">
        <span><?= htmlspecialchars($_SESSION['error']) ?></span>
        <button type="button" onclick="this.parentElement.style.display='none';" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #991b1b;">&times;</button>
    </div>
    <?php unset($_SESSION['error']); // Hapus session setelah ditampilkan 
    ?>
<?php endif; ?>

<div class="page-head">
    <div>
        <h1>Mulai TEMUin Timmu</h1>
        <p>Buat rekrutmennya, temukan tim dan bersiaplah untuk menuju kemenangan!</p>
    </div>
</div>
<div class="form-tim-wrapper">
    <form action="proses-buat-tim.php" method="POST" enctype="multipart/form-data" class="tim-form">
        <div class="tim-header-img">
            <label for="tim-img-upload" class="img-upload-label">
                <input type="file" id="tim-img-upload" name="tim-img" accept="image/*" style="display: none;">
                <div id="img-preview" class="img-preview">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Upload Gambar</span>
                </div>
            </label>
        </div>

        <div class="tim-container">
            <!-- form buat tim -->

            <!-- hidden input path gambarr -->
            <input type="hidden" id="tim-img-path" name="tim-img-path" value="">

            <div class="tim-form-grid">
                <div class="tim-form-column">
                    <div class="tim-input-group">
                        <label for="nama-lomba">Masukkan Nama Lomba</label>
                        <input type="text" id="nama-lomba" name="nama-lomba" placeholder="JTI FEST - UI/UX DESIGN">
                        <small class="tim-hint">Masukkan nama lomba dan nama pilihan lomba yang akan diikuti. Contoh: JTI FEST - UI/UX DESIGN</small>
                    </div>

                    <div class="tim-input-group">
                        <label for="kategori">Kategori Lomba</label>
                        <select id="kategori" name="kategori" class="tim-form-control" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                            <option value="" disabled selected>-- Pilih Kategori --</option>
                            <option value="Lomba IT">Lomba IT</option>
                            <option value="Lomba Non IT">Lomba Non IT</option>
                        </select>
                        <small class="tim-hint">Pilih jenis kategori lomba.</small>
                    </div>

                    <div class="tim-input-group">
                        <label for="deskripsi-tim">Deskripsi</label>
                        <input type="text" id="deskripsi-tim" name="deskripsi-tim" placeholder="Saya membutuhkan beberapa posisi untuk membentuk tim yang siap dan...">
                        <small class="tim-hint">Deskripsi singkat</small>
                    </div>

                    <div class="tim-input-group">
                        <label for="skills">Skills yang dibutuhkan</label>
                        <div class="chip-input-container" id="chip-container">
                            <div id="chipList"></div>
                            <input type="hidden" name="skills_dibutuhkan" id="skillsHidden" required>
                            <input type="text" id="skillsInput" placeholder="Pilih multiple (Web Dev, UI/UX, Mobile)">
                        </div>
                        <small class="tim-hint">Masukkan skills yang dibutuhkan</small>
                    </div>
                </div>

                <div class="tim-form-column">
                    <div class="tim-input-group">
                        <label for="nama-tim">Nama tim</label>
                        <input type="text" id="nama-tim" name="nama-tim" placeholder="Tim SOLIDD" required>
                        <small class="tim-hint">Tentukan nama tim terlebih dahulu</small>
                    </div>

                    <div class="tim-input-group">
                        <label for="jumlah-anggota">Jumlah Anggota Dibutuhkan</label>
                        <input type="number" id="jumlah-anggota" name="jumlah-anggota" placeholder="Contoh: 3" min="1" max="10" required>
                        <small class="tim-hint">Isi sesuai kebutuhan tim</small>
                    </div>

                    <div class="tim-input-group">
                        <label for="tanggal-penutupan">Tanggal Penutupan Penerimaan Anggota</label>
                        <input type="date" id="tanggal-penutupan" name="tanggal-penutupan" required>
                        <small class="tim-hint">Tentukan tanggal penutupan maks h-1 sebelum pendaftaran lomba ditutup</small>
                    </div>
                </div>
            </div>
            <div class="tim-form-actions">
                <a href="../tim/buat-tim.php" class="tim-btn tim-btn-cancel">Batal</a>
                <button type="submit" class="tim-btn tim-btn-submit">Buat Tim</button>
            </div>
        </div>
    </form>
</div>

<script src="../assets/js/tim.js"></script>
<?php
require_once '../includes/footer.php';
?>