<?php
require_once 'config/database.php';

try {
    // Buat tabel users jika belum ada
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        nama_lengkap VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Hapus data lama dan masukkan akun baru
    $pdo->exec("TRUNCATE TABLE users");
    
    // Password hash untuk 'admin123'
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap) VALUES (?, ?, ?)");
    $stmt->execute(['ustad.ali', $password_hash, 'Ustad Ali Tamam']);

    echo "<h3 style='color: green;'>Berhasil! Akun login telah dibuat ulang.</h3>";
    echo "<p>Silakan gunakan data berikut untuk login:</p>";
    echo "<ul>";
    echo "<li><b>Username:</b> ustad.ali</li>";
    echo "<li><b>Password:</b> admin123</li>";
    echo "</ul>";
    echo "<br><a href='index.php' style='padding: 10px 20px; background: #0f766e; color: white; text-decoration: none; border-radius: 5px;'>Kembali ke Halaman Login</a>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Gagal Membuat User:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>