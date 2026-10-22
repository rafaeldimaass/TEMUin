<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa'){
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/koneksi.php';
$page_title = "Buat Tim | TEMUIN";
require_once __DIR__ . '/../includes/header.php';

$namaMahasiswa = $_SESSION['nama'] ?? 'Mahasiswa';
?>

<link rel="stylesheet" href="../assets/css/pages/tim.css">
<link rel="stylesheet" href="../assets/css/components.css">

 <div class="page-head">
    <div>
        <h1>Mulai TEMUin Timmu</h1>
        <p>Buat rekrutmennya, temukan tim dan bersiaplah untuk menuju kemenangan!</p>
    </div>
</div>
<div class="form-tim-wrapper">
     <div class="tim-header-img">
                    <label for="tim-img-upload" class="img-upload-label">
                        <input type="file" id="tim-img-upload" name="tim-img" accept="image/*" style="display: none;">
                        <div id="img-preview" class="img-preview">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Upload Gambar</span>
                        </div>
                    </label>
                </div>
<!-- section form buat tim -->
    <div class="tim-container">
            <!-- form buat tim -->
             <form action="proses-buat-tim.php" method="POST" class="tim-form">
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
                    <label for="deskripsi-tim">Deskripsi</label>
                    <input type="text" id="deskripsi-tim" name="deskripsi-tim" placeholder="Saya membutuhkan beberapa posisi untuk membentuk tim yang siap dan...">
                    <small class="tim-hint">Deskripsi singkat</small>
                </div>

                <div class="tim-input-group">
                    <label for="skills">Skills yang dibutuhkan</label>
                        <div class="chip-input-container" id="chip-container">
                            <div id="chipList"></div>
                            <input type="text" id="skillsInput" placeholder="Pilih multiple (Web Dev, UI/UX, Mobile)" required>
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
                        <label for="portofolio">Sertakan link portofolio Anda <span class="text-red-500">*</span></label>
                        <input type="url" id="portofolio" name="portofolio" placeholder="linkk yg penting bisa diakses yak ges" required>
                        <small class="tim-hint">Bisa berbentuk link Google Drive atau link personal web portofolio.</small>
                </div>

                <div class="tim-input-group">
                        <label for="tanggal-penutupan">Tanggal Penutupan Penerimaan Anggota</label>
                        <input type="date" id="tanggal-penutupan" name="tanggal-penutupan" required>
                        <small class="tim-hint">Tentukan tanggal penutupan maks h-1 sebelum pendaftaran lomba ditutup</small>
                </div>
            </div>
        </div>
        <div class="tim-form-actions">
                <a href="../user-mhs/tim.php" class="tim-btn tim-btn-cancel">Batal</a>
                <button type="submit" class="tim-btn tim-btn-submit">Simpan</button>
        </div>
        </div>
        </form>
    </div>

 <?php
  require_once '../includes/footer.php';
 ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== Image Upload =====
    const imageUpload = document.getElementById('tim-image-upload');
    const imagePreview = document.getElementById('image-preview');
    const imagePath = document.getElementById('tim-image-path');

    imageUpload.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validasi ukuran file (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran gambar maksimal 2MB');
                return;
            }

            // Validasi tipe file
            if (!file.type.startsWith('image/')) {
                alert('Silakan pilih file gambar');
                return;
            }

            // Preview gambar
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">`;
            };
            reader.readAsDataURL(file);

            // Simpan path untuk dikirim ke server
            imagePath.value = file.name;
        }
    });

    // ===== Skills Chip =====
    const chipInput = document.getElementById('skillsInput');
    const chipList = document.getElementById('chipList');
    
    chipInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && chipInput.value.trim() !== '') {
            e.preventDefault();
            addChip(chipInput.value.trim());
            chipInput.value = '';
        }
    });
    
    function addChip(skill) {
        const chip = document.createElement('span');
        chip.className = 'chip';
        chip.innerHTML = skill + ' <i class="fas fa-times"></i>';
        chipList.appendChild(chip);
        
        chip.querySelector('i').addEventListener('click', function() {
            chip.remove();
        });
    }

    // ===== Set minimal date to today for deadline =====
    const tanggalInput = document.getElementById('tanggal-penutupan');
    const today = new Date().toISOString().split('T')[0];
    tanggalInput.setAttribute('min', today);
});
</script>