<?php
if (!isset($base)) {
    $__jobsheetRoot = dirname(__DIR__); 
    $__scriptDir = dirname($_SERVER['SCRIPT_FILENAME']);
    $__rel = ltrim(str_replace('\\', '/', substr($__scriptDir, strlen($__jobsheetRoot))), '/');
    $base = $__rel === '' ? '' : str_repeat('../', substr_count($__rel, '/') + 1);
}
?>

</main>
    <footer>
        <p>&copy; <?= date('Y') ?> TEMUin - KejarDetlen. All Rights Reserved.</p>
    </footer>
    <script src="<?php echo $base; ?>assets/js/script.js"></script>
</body>
</html>