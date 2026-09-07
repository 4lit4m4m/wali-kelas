<?php
require_once 'config/database.php';

try {
    $stmt = $pdo->query("SELECT VERSION()");
    $version = $stmt->fetchColumn();
    echo "<h3 style='color: green;'>Koneksi ke Database Berhasil!</h3>";
    echo "<p>Versi MySQL/MariaDB: <b>" . $version . "</b></p>";

    // Cek jumlah siswa
    $jml = $pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn();
    echo "<p>Jumlah data siswa di database: <b>" . $jml . " siswa</b></p>";
} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Koneksi Gagal!</h3>";
    echo "<p>Pesan Error: " . $e->getMessage() . "</p>";
}
?>