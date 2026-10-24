<?php
/**
 * Cấu hình kết nối Cơ sở dữ liệu (MySQL PDO)
 * Tự động đọc biến môi trường từ Docker Compose (.env)
 */

$db_host = getenv('DB_HOST') ?: 'gym-db';
$db_port = getenv('DB_PORT') ?: '3306';
$db_name = getenv('DB_NAME') ?: 'gym_db';
$db_user = getenv('DB_USER') ?: 'gym_app_user';
$db_pass = getenv('DB_PASSWORD') ?: 'GymApp@Pass2026!Strong';
$aes_key = getenv('AES_SECRET_KEY') ?: 'GymSuperSecretKey2026!';

try {
    $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    die("<div style='font-family:sans-serif;padding:30px;background:#ffebee;color:#c62828;border-radius:8px;margin:30px;'>
        <h2>⚠️ Lỗi kết nối Cơ sở dữ liệu</h2>
        <p>Không thể kết nối đến máy chủ MySQL tại <strong>{$db_host}:{$db_port}</strong> với user <strong>{$db_user}</strong>.</p>
        <p>Chi tiết: " . htmlspecialchars($e->getMessage()) . "</p>
    </div>");
}
?>
