<?php
/**
 * KHỞI ĐỘNG PHIÊN & NẠP HỆ THỐNG
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 */

// Cấu hình cookie session bảo mật trước khi session_start()
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    
    // Nếu có HTTPS thì bật cookie secure
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }
    
    session_start();
}

// Gửi HTTP Security Headers chuẩn OWASP chống Clickjacking, MIME-sniffing & XSS
if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-XSS-Protection: 1; mode=block');
    header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

// Khởi tạo kết nối PDO dùng chung
$pdo = get_db_connection();
