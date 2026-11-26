<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
	header('Location: ../auth/login.php');
	exit;
}

require_once '../includes/koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
	header('Location: data-pengguna.php');
	exit;
}

$page_title = 'Edit Pengguna | TEMUin';
$error_message = '';
$role_options = [
	'mahasiswa' => 'Mahasiswa',
	'dosen'     => 'Dosen',
	'admin'     => 'Admin',
];

try {
	$stmt = $pdo->prepare("SELECT id, nama, email, role, no_wa FROM users WHERE id = ? LIMIT 1");
	$stmt->execute([$id]);
	$user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
	$error_message = 'Gagal memuat data pengguna: ' . $e->getMessage();
	$user = false;
}

if (!$user) {
	header('Location: data-pengguna.php');
	exit;
}

$val_nama  = $_POST['nama'] ?? ($user['nama'] ?? '');
$val_email = $_POST['email'] ?? ($user['email'] ?? '');
$val_no_wa = $_POST['no_wa'] ?? ($user['no_wa'] ?? '');
$val_role  = $_POST['role'] ?? ($user['role'] ?? '');

require_once '../includes/sidebar.php';
?>

<link rel="stylesheet" href="../assets/css/pages/tim.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/global.css">

<?php if ($error_message !== ''): ?>
	<div class="alert alert-danger" role="alert" style="padding: 14px 20px; background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; justify-content: space-between; align-items: center;">
		<span><?= htmlspecialchars((string)$error_message, ENT_QUOTES, 'UTF-8') ?></span>
		<button type="button" onclick="this.parentElement.style.display='none';" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #991b1b;">&times;</button>
	</div>
<?php endif; ?>

<div class="page-head">
	<div>
		<h1>Edit Pengguna</h1>
		<p>Perbarui informasi akun dan hak akses pengguna pada sistem.</p>
	</div>
</div>

<section class="block">
	<div class="form-tim-wrapper">
		<form action="proses-edit-user.php" method="POST" class="tim-form">
			<input type="hidden" name="id" value="<?= htmlspecialchars((string)$user['id'], ENT_QUOTES, 'UTF-8') ?>">

			<div class="tim-container">
				<div class="tim-form-grid">
					<div class="tim-form-column">
						<div class="tim-input-group">
							<label for="nama">Nama Pengguna</label>
							<input type="text" id="nama" name="nama" placeholder="Contoh: AtPolinema" value="<?= htmlspecialchars($val_nama, ENT_QUOTES, 'UTF-8') ?>" required>
							<small class="tim-hint">Masukkan nama lengkap atau nama akun pengguna.</small>
						</div>

						<div class="tim-input-group">
							<label for="email">Email</label>
							<input type="email" id="email" name="email" placeholder="Contoh: mahasiswa@polinema.ac.id" value="<?= htmlspecialchars($val_email, ENT_QUOTES, 'UTF-8') ?>" required>
							<small class="tim-hint">Gunakan email aktif untuk login ke sistem.</small>
						</div>

						<div class="tim-input-group">
							<label for="no_wa">No. WhatsApp</label>
							<input type="text" id="no_wa" name="no_wa" placeholder="Contoh: 085334808508" value="<?= htmlspecialchars($val_no_wa, ENT_QUOTES, 'UTF-8') ?>" required>
							<small class="tim-hint">Nomor WhatsApp aktif pengguna.</small>
						</div>
					</div>

					<div class="tim-form-column">
						<div class="tim-input-group">
							<label for="role">Role</label>
							<select id="role" name="role" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; font-family: inherit;">
								<option value="">Pilih role</option>
								<?php foreach ($role_options as $role_val => $role_label): ?>
									<option value="<?= htmlspecialchars($role_val, ENT_QUOTES, 'UTF-8') ?>" <?= ($val_role === $role_val) ? 'selected' : '' ?>>
										<?= htmlspecialchars($role_label, ENT_QUOTES, 'UTF-8') ?>
									</option>
								<?php endforeach; ?>
							</select>
							<small class="tim-hint">Pilih hak akses akun pengguna.</small>
						</div>

						<div class="tim-input-group">
							<label for="password">Password Baru <span style="font-weight: 400; color: #64748b;">(Opsional)</span></label>
							<input type="password" id="password" name="password" minlength="6" autocomplete="new-password" placeholder="Kosongkan jika tidak ingin mengubah password">
							<small class="tim-hint">Isi hanya jika ingin mengganti password akun.</small>
						</div>
					</div>
				</div>
			</div>

			<div class="tim-form-actions">
				<a href="kelola-user.php" class="tim-btn tim-btn-cancel">Batal</a>
				<button type="submit" class="tim-btn tim-btn-submit">Simpan Perubahan</button>
			</div>
		</form>
	</div>
</section>

<?php require_once '../includes/footer.php'; ?>