<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
	header('Location: ../auth/login.php');
	exit;
}

require_once '../includes/koneksi.php';

// Ambil ID dosen dari parameter URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
	header('Location: data-dosen.php');
	exit;
}

// Ambil data dosen dari database
$stmt = $pdo->prepare('SELECT u.*, pd.nip, pd.jurusan, pd.bidang_kepakaran, pd.pengalaman, pd.foto_profil
                       FROM users u
                       LEFT JOIN profil_dosen pd ON pd.user_id = u.id 
                       WHERE u.id = ? LIMIT 1');
$stmt->execute([$id]);
$dosen = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dosen) {
	header('Location: data-dosen.php');
	exit;
}

$page_title = 'Edit Dosen | TEMUin';
$error_message = '';
$prodi_options = [
	'Teknologi Informasi',
	'Teknik Elektro',
	'Teknik Mesin',
	'Teknik Sipil',
	'Teknik Kimia',
	'Akuntansi',
	'Administrasi Niaga',
];

// Gunakan data dari $_POST jika gagal submit, atau dari database saat pertama dibuka
$val_nama             = $_POST['nama'] ?? ($dosen['nama'] ?? '');
$val_nip              = $_POST['nip'] ?? ($dosen['nip'] ?? '');
$val_role             = $_POST['role'] ?? ($dosen['role'] ?? '');
$val_jurusan            = $_POST['jurusan'] ?? ($dosen['jurusan'] ?? '');
$val_no_wa            = $_POST['no_wa'] ?? ($dosen['no_wa'] ?? '');
$val_email            = $_POST['email'] ?? ($dosen['email'] ?? '');
$val_bidang_kepakaran = $_POST['bidang_kepakaran'] ?? ($dosen['bidang_kepakaran'] ?? '');
$val_pengalaman       = $_POST['pengalaman'] ?? ($dosen['pengalaman'] ?? '');
$foto_lama            = $dosen['foto_profil'] ?? '';

require_once '../includes/sidebar.php';
?>

<link rel="stylesheet" href="../assets/css/pages/tim.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/global.css">

<?php if ($error_message !== ''): ?>
	<div class="alert 
    alert-danger" role="alert" style="padding: 14px 20px; background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; justify-content: space-between; align-items: center;">
		<span><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></span>
		<button type="button" onclick="this.parentElement.style.display='none';" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #991b1b;">&times;</button>
	</div>
<?php endif; ?>

<div class="page-head">
	<div>
		<h1>Edit Dosen</h1>
		<p>Perbarui informasi akun dan profil dosen pembimbing.</p>
	</div>
</div>

<section class="block">
	<div class="form-tim-wrapper">
		<form action="proses-edit-dosen.php" method="POST" enctype="multipart/form-data" class="tim-form">
			<!-- Kirim ID dan nama file foto lama secara tersembunyi -->
			<input type="hidden" name="id" value="<?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?>">
			<input type="hidden" name="foto_lama" value="<?= htmlspecialchars($foto_lama, ENT_QUOTES, 'UTF-8') ?>">

			<div class="form-group">
				<div class="tim-header-img">
					<label for="foto_profil" class="img-upload-label">
						<input type="file" id="foto_profil" name="foto_profil" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" style="display: none;">
						<div id="img-preview" class="img-preview">
							<?php if (!empty($foto_lama)): ?>
								<img src="../assets/uploads/profile/<?= htmlspecialchars($foto_lama, ENT_QUOTES, 'UTF-8') ?>" alt="Foto Profil Dosen" style="max-height: 180px; border-radius: 8px; object-fit: cover;">
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
							<label for="nama">Nama Dosen</label>
							<input type="text" id="nama" name="nama" placeholder="Contoh: Dr. Anonim, S.T., M.T." value="<?= htmlspecialchars($val_nama, ENT_QUOTES, 'UTF-8') ?>" required>
							<small class="tim-hint">Masukkan nama lengkap beserta gelar akademik.</small>
						</div>

						<div class="tim-input-group">
							<label for="nip">NIP</label>
							<input type="text" id="nip" name="nip" placeholder="Contoh: 198501012010121001" value="<?= htmlspecialchars($val_nip, ENT_QUOTES, 'UTF-8') ?>" required>
							<small class="tim-hint">Nomor Induk Pegawai resmi dosen.</small>
						</div>

						<div class="tim-input-group">
							<label for="role">Role</label>
							<select id="role" name="role" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; font-family: inherit;">
								<option value="">Pilih role</option>
								<option value="dosen" <?= ($val_role === 'dosen') ? 'selected' : '' ?>>Dosen</option>
								<option value="admin" <?= ($val_role === 'admin') ? 'selected' : '' ?>>Admin</option>
							</select>
							<small class="tim-hint">Pilih role akun yang digunakan.</small>
						</div>

						<div class="tim-input-group">
							<label for="prodi">Program Studi</label>
							<select id="prodi" name="prodi" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; font-family: inherit;">
								<option value="">Pilih program studi</option>
								<?php foreach ($prodi_options as $option): ?>
									<option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>" <?= ($val_jurusan === $option) ? 'selected' : '' ?>>
										<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>
									</option>
								<?php endforeach; ?>
							</select>
							<small class="tim-hint">Pilih jurusan dosen pembimbing.</small>
						</div>
					</div>

					<div class="tim-form-column">
						<div class="tim-input-group">
							<label for="no_wa">No. WhatsApp</label>
							<input type="text" id="no_wa" name="no_wa" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($val_no_wa, ENT_QUOTES, 'UTF-8') ?>" required>
							<small class="tim-hint">Nomor WhatsApp aktif dosen.</small>
						</div>

						<div class="tim-input-group">
							<label for="email">Email</label>
							<input type="email" id="email" name="email" placeholder="Contoh: budi.santoso@polinema.ac.id" value="<?= htmlspecialchars($val_email, ENT_QUOTES, 'UTF-8') ?>" required>
							<small class="tim-hint">Gunakan email aktif untuk login ke sistem.</small>
						</div>

						<div class="tim-input-group">
							<label for="password">Password Baru <span style="font-weight: 400; color: #64748b;">(Opsional)</span></label>
							<input type="password" id="password" name="password" minlength="8" autocomplete="new-password" placeholder="Kosongkan jika tidak ingin mengubah password">
							<small class="tim-hint">Isi minimal 8 karakter hanya jika ingin mengganti password.</small>
						</div>

						<div class="tim-input-group">
							<label for="bidang_kepakaran">Bidang Kepakaran</label>
							<input type="text" id="bidang_kepakaran" name="bidang_kepakaran" placeholder="Contoh: Artificial Intelligence, UI/UX, IoT" value="<?= htmlspecialchars($val_bidang_kepakaran, ENT_QUOTES, 'UTF-8') ?>" required>
							<small class="tim-hint">Pisahkan dengan tanda koma (,) jika lebih dari satu bidang.</small>
						</div>
					</div>

					<div class="tim-input-group" style="grid-column: 1 / -1;">
						<label for="pengalaman">Pengalaman</label>
						<textarea id="pengalaman" name="pengalaman" rows="4" placeholder="Tuliskan riwayat bimbingan lomba, riset, atau pengalaman relevan lainnya..." required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; resize: vertical;"><?= htmlspecialchars($val_pengalaman, ENT_QUOTES, 'UTF-8') ?></textarea>
						<small class="tim-hint">Jelaskan rekam jejak atau pengalaman membimbing kompetisi mahasiswa.</small>
					</div>
				</div>
			</div>

			<div class="tim-form-actions">
				<a href="data-dosen.php" class="tim-btn tim-btn-cancel">Batal</a>
				<button type="submit" class="tim-btn tim-btn-submit">Perbarui Data Dosen</button>
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
					imgPreview.innerHTML = `
						<img src="${e.target.result}" alt="Preview Foto Profil" style="max-height: 180px; border-radius: 8px; object-fit: cover;">
						<small style="font-size: 12px; color: #64748b; margin-top: 8px; display: block;">Klik gambar untuk mengganti foto</small>
					`;
				};
				reader.readAsDataURL(file);
			}
		});
	}
</script>

<?php require_once '../includes/footer.php'; ?>