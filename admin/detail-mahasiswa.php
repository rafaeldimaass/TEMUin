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

$page_title = 'Detail Mahasiswa | TEMUin';

function e($v) { return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8'); }

// Pecah skill yang dipisahkan koma menjadi daftar
$skills = array_filter(array_map('trim', explode(',', (string) ($mhs['skill'] ?? ''))), 'strlen');

require_once '../includes/sidebar.php';
?>

<link rel="stylesheet" href="../assets/css/pages/tim.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/global.css">

<div class="page-head">
	<div>
		<h1>Detail Mahasiswa</h1>
		<p>Informasi lengkap profil mahasiswa.</p>
	</div>
	<a class="btn btn-navy" href="data-mahasiswa.php">
		<i class="fas fa-arrow-left"></i> Kembali
	</a>
</div>

<?php require_once '../includes/flash.php'; ?>

<section class="detail-mhs">
	<div class="detail-mhs-card detail-mhs-profile">
		<?php if (!empty($mhs['foto_profil'])): ?>
			<img src="../assets/uploads/profile/<?= e($mhs['foto_profil']) ?>" alt="Foto Profil" class="detail-mhs-avatar">
		<?php else: ?>
			<div class="detail-mhs-avatar detail-mhs-avatar-initial"><?= e(strtoupper(substr($mhs['nama'], 0, 2))) ?></div>
		<?php endif; ?>

		<div class="detail-mhs-identity">
			<h2><?= e($mhs['nama']) ?></h2>
			<p><?= e($mhs['nim'] ?: '-') ?></p>
		</div>

		<div class="detail-mhs-actions">
			<a href="edit-mahasiswa.php?id=<?= (int) $mhs['id'] ?>" class="btn-action btn-navy">
				<i class="fas fa-edit"></i> Edit
			</a>
			<a href="hapus-user.php?role=mahasiswa&id=<?= (int) $mhs['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Hapus mahasiswa ini? Tim dan lamaran miliknya juga akan ikut terhapus.');">
				<i class="fas fa-trash"></i> Hapus
			</a>
		</div>
	</div>

	<div class="detail-mhs-card">
		<h3>Informasi Akademik &amp; Kontak</h3>
		<dl class="detail-mhs-grid">
			<div>
				<dt>Jurusan</dt>
				<dd><?= e($mhs['jurusan'] ?: '-') ?></dd>
			</div>
			<div>
				<dt>Program Studi</dt>
				<dd><?= e($mhs['prodi'] ?: '-') ?></dd>
			</div>
			<div>
				<dt>Email</dt>
				<dd><a href="mailto:<?= e($mhs['email']) ?>"><?= e($mhs['email']) ?></a></dd>
			</div>
			<div>
				<dt>No. WhatsApp</dt>
				<dd><?= e($mhs['no_wa'] ?: 'Belum Memasukkan No WA') ?></dd>
			</div>
			<div class="detail-mhs-full">
				<dt>Portofolio</dt>
				<dd>
					<?php if (!empty($mhs['portofolio'])): ?>
						<a href="<?= e($mhs['portofolio']) ?>" target="_blank" rel="noopener">Link Portofolio</a>
					<?php else: ?>
						-
					<?php endif; ?>
				</dd>
			</div>
		</dl>
	</div>

	<div class="detail-mhs-card">
		<h3>Skill</h3>
		<?php if (!empty($skills)): ?>
			<div class="detail-mhs-skills">
				<?php foreach ($skills as $skill): ?>
					<span class="detail-mhs-skill"><?= e($skill) ?></span>
				<?php endforeach; ?>
			</div>
		<?php else: ?>
			<p class="detail-mhs-empty">Belum ada skill.</p>
		<?php endif; ?>
	</div>

	<div class="detail-mhs-card">
		<h3>Tentang Saya</h3>
		<?php if (!empty($mhs['tentang_saya'])): ?>
			<p class="detail-mhs-about"><?= nl2br(e($mhs['tentang_saya'])) ?></p>
		<?php else: ?>
			<p class="detail-mhs-empty">Belum ada deskripsi.</p>
		<?php endif; ?>
	</div>
</section>

<?php require_once '../includes/footer.php'; ?>
