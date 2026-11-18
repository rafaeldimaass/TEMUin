<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin'])) {
  header('Location: ../auth/login.php');
  exit;
}

require_once '../includes/koneksi.php';
$page_title = "Tambah Info Lomba";
require_once '../includes/sidebar.php';
?>

<link rel="stylesheet" href="../assets/css/pages/tim.css">
<link rel="stylesheet" href="../assets/css/components.css">

<?php if (isset($_SESSION['berhasil'])): ?>
  <div class="alert alert-success" style="padding: 14px 20px; background: #d1fae5; color: #065f46; border: 1px solid #10b981; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; justify-content: space-between; align-items: center;">
    <span><?= htmlspecialchars($_SESSION['berhasil']) ?></span>
    <button type="button" onclick="this.parentElement.style.display='none';" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #065f46;">&times;</button>
  </div>
  <?php unset($_SESSION['berhasil']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error']) || !empty($error)): ?>
  <?php $errMsg = $_SESSION['error'] ?? $error; ?>
  <div class="alert alert-danger" style="padding: 14px 20px; background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; justify-content: space-between; align-items: center;">
    <span><?= htmlspecialchars($errMsg) ?></span>
    <button type="button" onclick="this.parentElement.style.display='none';" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #991b1b;">&times;</button>
  </div>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<section class="block">
  <div class="page-head">
    <div>
      <h1>Tambah Info Lomba Baru</h1>
      <p>Publikasikan informasi kompetisi terbaru, menuju indonesia emas!</p>
    </div>
  </div>

  <div class="form-tim-wrapper">
    <form action="proses-buat-lomba.php" method="POST" enctype="multipart/form-data" class="tim-form">
      <div class="form-group">
        <div class="tim-header-img">
          <label for="poster-upload" class="img-upload-label">
            <input type="file" id="poster-upload" name="poster" accept="image/*" style="display: none;">
            <div id="img-preview" class="img-preview">
              <i class="fas fa-cloud-upload-alt"></i>
              <span>Upload Poster Lomba</span>
            </div>
          </label>
        </div>
      </div>

      <div class="tim-container">
        <div class="tim-form-grid">
          <div class="tim-form-column">
            <div class="tim-input-group">
              <label for="judul">Nama Lomba</label>
              <input type="text" id="judul" name="judul" placeholder="Contoh: JTI FEST 2026" required>
              <small class="tim-hint">Masukkan nama resmi event lomba.</small>
            </div>

            <div class="tim-input-group">
              <label for="kategori">Kategori Lomba</label>
              <input type="text" id="kategori" name="kategori" placeholder="Contoh: Hackathon, UI/UX, Business Plan">
              <small class="tim-hint">Pisahkan dengan tanda koma (,) jika memiliki lebih dari satu kategori.</small>
            </div>
          </div>

          <div class="tim-form-column">
            <div class="tim-input-group">
              <label for="tanggal_penutupan">Tanggal Penutupan Pendaftaran</label>
              <input type="date" id="tanggal_penutupan" name="tanggal_penutupan" required>
              <small class="tim-hint">Tentukan batas akhir pendaftaran lomba.</small>
            </div>

            <div class="tim-input-group">
              <label for="link_detail">Link Pendaftaran</label>
              <input type="url" id="link_detail" name="link_detail" placeholder="https://contoh-link-pendaftaran.com">
              <small class="tim-hint">Sertakan tautan Instagram atau form pendaftaran resmi.</small>
            </div>
          </div>
          <div class="tim-input-group" style="grid-column: 1 / -1;">
            <label for="deskripsi">Deskripsi Lomba</label>
            <textarea id="deskripsi" name="deskripsi" rows="4" placeholder="Tuliskan detail singkat, persyaratan peserta, atau informasi penting lainnya..." required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; resize: vertical;"></textarea>
            <small class="tim-hint">Jelaskan informasi ringkas mengenai lomba ini.</small>
          </div>
        </div>
      </div>
      <div class="tim-form-actions">
        <a href="info-lomba.php" class="tim-btn tim-btn-cancel">Batal</a>
        <button type="submit" class="tim-btn tim-btn-submit">Posting Info Lomba</button>
      </div>
  </div>
  </form>
</section>
<script src="../assets/js/lomba.js"></script>
<?php require_once '../includes/footer.php'; ?>