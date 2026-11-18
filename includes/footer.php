<?php
if (!isset($base)) {
    $__jobsheetRoot = dirname(__DIR__); 
    $__scriptDir = dirname($_SERVER['SCRIPT_FILENAME']);
    $__rel = ltrim(str_replace('\\', '/', substr($__scriptDir, strlen($__jobsheetRoot))), '/');
    $base = $__rel === '' ? '' : str_repeat('../', substr_count($__rel, '/') + 1);
}
?>

<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <!-- KHUSUS ADMIN: Footer di dalam <main> agar tidak menyamping -->
    <footer>
        <p>&copy; <?= date('Y') ?> TEMUin - KejarDetlen. All Rights Reserved.</p>
    </footer>
</main> <!-- Penutup diletakkan SETELAH footer -->
<?php else: ?>
    <!-- KHUSUS MAHASISWA/DOSEN: Footer di luar <main> -->
</main>
    <footer>
        <p>&copy; <?= date('Y') ?> TEMUin - KejarDetlen. All Rights Reserved.</p>
    </footer>
    <?php endif; ?>
    <script src="<?php echo $base; ?>assets/js/script.js"></script>
</body>
</html>