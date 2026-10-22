
 <?php
$host = "localhost";
$port = "5432";
$db   = "temuin_db";
$user = "postgres"; // Sesuaikan username PostgreSQL Anda
$pass = ""; // Sesuaikan password PostgreSQL Anda

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass);
    // Set error mode PDO ke exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database PostgreSQL gagal: " . $e->getMessage());
}
?>