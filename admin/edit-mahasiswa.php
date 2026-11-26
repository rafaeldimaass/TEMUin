<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
	header('Location: ../auth/login.php');
	exit;
}

require_once '../includes/koneksi.php';

// Ambil ID mahasiswa (users.id) dari URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare(
	"SELECT u.id, u.nama, u.email, u.no_wa,
	        pm.nim, pm.jurusan, pm.prodi, pm.skill, pm.portofolio, pm.tentang_saya, pm.foto_profil
	 FROM users u
	 LEFT JOIN profil_mahasiswa pm ON pm.user_id = u.id
	 WHERE u.id = ? AND u.role = 'mahasiswa'
	 LIMIT 1"
);
$stmt->execute([$id]);
$mhs = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect harus dilakukan SEBELUM sidebar di-include (sebelum ada HTML yang keluar)
if (!$mhs) {
	$_SESSION['error'] = 'Data mahasiswa tidak ditemukan.';
	header('Location: data-mahasiswa.php');
	exit;
}

$page_title = 'Edit Mahasiswa | TEMUin';
$jurusan_options = [
	'Teknologi Informasi',
	'Teknik Elektro',
	'Teknik Mesin',
	'Teknik Sipil',
	'Teknik Kimia',
	'Akuntansi',
	'Administrasi Niaga',
];

function e($v) { return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8'); }

require_once '../includes/sidebar.php';
?>

<link rel="stylesheet" href="../assets/css/pages/tim.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/global.css">

<div class="page-head">
	<div>
		<h1>Edit Mahasiswa</h1>
		<p>Perbarui informasi akun dan profil mahasiswa.</p>
	</div>
</div>

<?php require_once '../includes/flash.php'; ?>

<section class="block">
	<div class="form-tim-wrapper">
		<form action="proses-edit-mahasiswa.php" method="POST" enctype="multipart/form-data" class="tim-form">
			<input type="hidden" name="id" value="<?= (int) $mhs['id'] ?>">

			<div class="form-group">
				<div class="tim-header-img">
					<label for="foto_profil" class="img-upload-label">
						<input type="file" id="foto_profil" name="foto_profil" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" style="display: none;">
						<div id="img-preview" class="img-preview">
							<?php if (!empty($mhs['foto_profil'])): ?>
								<img src="../assets/uploads/profile/<?= e($mhs['foto_profil']) ?>" alt="Foto Profil Mahasiswa" style="max-height: 180px; border-radius: 8px; object-fit: cover;">
								<small style="font-size: 12px; color: #64748b; margin-top: 8px; display: block;">Klik gambar untuk mengganti foto (JPG, PNG, WEBP Maks. 2 MB)</small>
							<?php else: ?>
								<i class="fas fa-cloud-upload-alt"></i>
								<span>Upload Foto Profil</span>
								<small style="font-size: 12px; color: #64748b; margin-top: 4px;">JPG, PNG, atau WEBP (Maks. 2 MB)</small>
							<?php endif; ?>
						</div>
					</label>
				</div>
			</div>

			<div class="tim-container">
				<div class="tim-form-grid">
					<div class="tim-form-column">
						<div class="tim-input-group">
							<label for="nama">Nama Mahasiswa</label>
							<input type="text" id="nama" name="nama" value="<?= e($mhs['nama']) ?>" required>
						</div>

						<div class="tim-input-group">
							<label for="nim">NIM</label>
							<input type="text" id="nim" name="nim" value="<?= e($mhs['nim']) ?>" required>
						</div>

						<div class="tim-input-group">
							<label for="jurusan">Jurusan</label>
							<select id="jurusan" name="jurusan" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; font-family: inherit;">
								<option value="">Pilih jurusan</option>
								<?php foreach ($jurusan_options as $option): ?>
									<option value="<?= e($option) ?>" <?= ($mhs['jurusan'] === $option) ? 'selected' : '' ?>><?= e($option) ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="tim-input-group">
							<label for="prodi">Program Studi</label>
							<input type="text" id="prodi" name="prodi" value="<?= e($mhs['prodi']) ?>" required>
						</div>
					</div>

					<div class="tim-form-column">
						<div class="tim-input-group">
							<label for="no_wa">No. WhatsApp</label>
							<input type="text" id="no_wa" name="no_wa" value="<?= e($mhs['no_wa']) ?>" required>
						</div>

						<div class="tim-input-group">
							<label for="email">Email</label>
							<input type="email" id="email" name="email" value="<?= e($mhs['email']) ?>" required>
						</div>

						<div class="tim-input-group">
							<label for="password">Password Baru <span style="font-weight: 400; color: #64748b;">(Opsional)</span></label>
							<input type="password" id="password" name="password" minlength="8" autocomplete="new-password" placeholder="Kosongkan jika tidak ingin mengubah password">
							<small class="tim-hint">Isi minimal 8 karakter hanya jika ingin mengganti password.</small>
						</div>

						<div class="tim-input-group">
							<label for="skill">Skills / Keahlian</label>
							<input type="text" id="skill" name="skill" value="<?= e($mhs['skill']) ?>" required>
							<small class="tim-hint">Pisahkan dengan tanda koma (,) jika lebih dari satu keahlian.</small>
						</div>
					</div>

					<div class="tim-input-group" style="grid-column: 1 / -1;">
						<label for="portofolio">Link Portofolio / CV <span style="font-weight: 400; color: #64748b;">(Opsional)</span></label>
						<input type="url" id="portofolio" name="portofolio" value="<?= e($mhs['portofolio']) ?>">
					</div>

					<div class="tim-input-group" style="grid-column: 1 / -1;">
						<label for="tentang_saya">Tentang Mahasiswa</label>
						<textarea id="tentang_saya" name="tentang_saya" rows="4" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; resize: vertical;"><?= e($mhs['tentang_saya']) ?></textarea>
					</div>
				</div>
			</div>

			<div class="tim-form-actions">
				<a href="data-mahasiswa.php" class="tim-btn tim-btn-cancel">Batal</a>
				<button type="submit" class="tim-btn tim-btn-submit">Perbarui Data Mahasiswa</button>
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