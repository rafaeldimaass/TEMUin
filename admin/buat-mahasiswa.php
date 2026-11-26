<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
	header('Location: ../auth/login.php');
	exit;
}

require_once '../includes/koneksi.php';

$page_title = 'Tambah Mahasiswa | TEMUin';
$error_message = '';
$jurusan_options = [
	'Teknologi Informasi',
	'Teknik Elektro',
	'Teknik Mesin',
	'Teknik Sipil',
	'Teknik Kimia',
	'Akuntansi',
	'Administrasi Niaga',
];

require_once '../includes/sidebar.php';
?>

<link rel="stylesheet" href="../assets/css/pages/tim.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/global.css">

<?php require_once '../includes/flash.php'; ?>

<div class="page-head">
	<div>
		<h1>Tambah Mahasiswa</h1>
		<p>Lengkapi informasi akun dan profil mahasiswa.</p>
	</div>
</div>

<section class="block">
	<div class="form-tim-wrapper">
		<form action="proses-buat-mahasiswa.php" method="POST" enctype="multipart/form-data" class="tim-form">
			<div class="form-group">
				<div class="tim-header-img">
					<label for="foto_profil" class="img-upload-label">
						<input type="file" id="foto_profil" name="foto_profil" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" style="display: none;">
						<div id="img-preview" class="img-preview">
							<i class="fas fa-cloud-upload-alt"></i>
							<span>Upload Foto Profil</span>
							<small style="font-size: 12px; color: #64748b; margin-top: 4px;">JPG, PNG, atau WEBP (Maks. 2 MB)</small>
						</div>
					</label>
				</div>
			</div>

			<div class="tim-container">
				<div class="tim-form-grid">
					<div class="tim-form-column">
						<div class="tim-input-group">
							<label for="nama">Nama Mahasiswa</label>
							<input type="text" id="nama" name="nama" placeholder="Contoh: Aku Anak Indonesia" value="<?= htmlspecialchars($_POST['nama'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
							<small class="tim-hint">Masukkan nama lengkap mahasiswa.</small>
						</div>

						<div class="tim-input-group">
							<label for="nim">NIM</label>
							<input type="text" id="nim" name="nim" placeholder="Contoh: 2441720001" value="<?= htmlspecialchars($_POST['nim'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
							<small class="tim-hint">Nomor Induk Mahasiswa resmi.</small>
						</div>

						<div class="tim-input-group">
							<label for="jurusan">Jurusan</label>
							<select id="jurusan" name="jurusan" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; font-family: inherit;">
								<option value="">Pilih jurusan</option>
								<?php foreach ($jurusan_options as $option): ?>
									<option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>" <?= (($_POST['jurusan'] ?? '') === $option) ? 'selected' : '' ?>>
										<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>
									</option>
								<?php endforeach; ?>
							</select>
							<small class="tim-hint">Pilih jurusan mahasiswa.</small>
						</div>

						<div class="tim-input-group">
							<label for="prodi">Program Studi</label>
							<input type="text" id="prodi" name="prodi" placeholder="Contoh: D4 Teknik Informatika" value="<?= htmlspecialchars($_POST['prodi'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
							<small class="tim-hint">Masukkan program studi mahasiswa.</small>
						</div>
					</div>

					<div class="tim-form-column">
						<div class="tim-input-group">
							<label for="no_wa">No. WhatsApp</label>
							<input type="text" id="no_wa" name="no_wa" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($_POST['no_wa'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
							<small class="tim-hint">Nomor WhatsApp aktif mahasiswa.</small>
						</div>

						<div class="tim-input-group">
							<label for="email">Email</label>
							<input type="email" id="email" name="email" placeholder="Contoh: mahasiswa@student.polinema.ac.id" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
							<small class="tim-hint">Gunakan email aktif untuk login ke sistem.</small>
						</div>

						<div class="tim-input-group">
							<label for="password">Password Akun</label>
							<input type="password" id="password" name="password" minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter" required>
							<small class="tim-hint">Password awal untuk akun mahasiswa (minimal 8 karakter).</small>
						</div>

						<div class="tim-input-group">
							<label for="skill">Skills / Keahlian</label>
							<input type="text" id="skill" name="skill" placeholder="Contoh: UI/UX, Web Development, Public Speaking" value="<?= htmlspecialchars($_POST['skill'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
							<small class="tim-hint">Pisahkan dengan tanda koma (,) jika lebih dari satu keahlian.</small>
						</div>
					</div>

					<div class="tim-input-group" style="grid-column: 1 / -1;">
						<label for="portofolio">Link Portofolio / CV <span style="font-weight: 400; color: #64748b;">(Opsional)</span></label>
						<input type="url" id="portofolio" name="portofolio" placeholder="Contoh: https://github.com/username atau https://linkedin.com/in/username" value="<?= htmlspecialchars($_POST['portofolio'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
						<small class="tim-hint">Tautan portofolio, GitHub, LinkedIn, atau CV online mahasiswa.</small>
					</div>

					<div class="tim-input-group" style="grid-column: 1 / -1;">
						<label for="tentang_saya">Tentang Mahasiswa</label>
						<textarea id="tentang_saya" name="tentang_saya" rows="4" placeholder="Tuliskan deskripsi singkat, minat lomba, atau pengalaman organisasi..." required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; resize: vertical;"><?= htmlspecialchars($_POST['tentang_saya'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
						<small class="tim-hint">Jelaskan minat kompetisi atau ringkasan profil mahasiswa.</small>
					</div>
				</div>
			</div>

			<div class="tim-form-actions">
				<a href="data-mahasiswa.php" class="tim-btn tim-btn-cancel">Batal</a>
				<button type="submit" class="tim-btn tim-btn-submit">Simpan Data Mahasiswa</button>
			</div>
		</form>
	</div>
</section>

<script>
	const fotoInput = document.getElementById('foto_profil');
	const imgPreview = document.getElementById('img-preview');

	if (fotoInput && imgPreview) {
		fotoInput.addEventListener('change', function () {
			const file = this.files[0];
			if (file) {
				const reader = new FileReader();
				reader.onload = function (e) {
					imgPreview.innerHTML = `<img src="${e.target.result}" alt="Preview Foto Profil" style="max-height: 180px; border-radius: 8px; object-fit: cover;">`;
				};
				reader.readAsDataURL(file);
			}
		});
	}
</script>

<?php require_once '../includes/footer.php'; ?>