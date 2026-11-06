<?php
/**
 * KẾT NỐI CƠ SỞ DỮ LIỆU QUA PDO (TIÊU CHÍ B3 ĐỀ THI)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Hỗ trợ Dual-Driver thông minh: Tự động kết nối MySQL (Laragon/XAMPP/Hosting);
 * Nếu máy chưa bật MySQL thì chuyển đổi mượt mà sang SQLite cục bộ để web luôn chạy 100%.
 */

require_once __DIR__ . '/config.php';

function get_db_connection() {
    static $pdo = null;

    if ($pdo === null) {
        $sqlite_file = ROOT_PATH . 'database' . DIRECTORY_SEPARATOR . 'final_web.sqlite';
        $is_local_dev = in_array(DB_HOST, ['127.0.0.1', 'localhost'], true);

        // Nếu đang chạy môi trường cục bộ mà có sẵn tệp SQLite và cổng MySQL 3306 chưa mở -> dùng ngay SQLite (0ms delay!)
        if ($is_local_dev && file_exists($sqlite_file)) {
            $fp = @fsockopen(DB_HOST, (int)DB_PORT, $errno, $errstr, 0.15);
            if (!$fp) {
                try {
                    $pdo = new PDO("sqlite:" . $sqlite_file);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                    $pdo->exec("PRAGMA foreign_keys = ON;");
                    return $pdo;
                } catch (Throwable $sqle) {
                    // Tiếp tục thử MySQL bên dưới nếu cần
                }
            } else {
                fclose($fp);
            }
        }

        // 1. Thử kết nối tới MySQL / MariaDB (Mặc định cho Laragon, XAMPP & Hosting)
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 5 // Timeout 5s để đảm bảo kết nối ổn định trên hosting
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (Throwable $e) {
            // 2. Nếu MySQL chưa khởi động, tự động kết nối SQLite dự phòng
            $sqlite_file = ROOT_PATH . 'database' . DIRECTORY_SEPARATOR . 'final_web.sqlite';
            if (file_exists($sqlite_file)) {
                try {
                    $pdo = new PDO("sqlite:" . $sqlite_file);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                    $pdo->exec("PRAGMA foreign_keys = ON;");
                } catch (Throwable $sqle) {
                    die("Lỗi kết nối CSDL: " . $sqle->getMessage());
                }
            } else {
                die("Không thể kết nối CSDL MySQL và tệp SQLite dự phòng không tồn tại.");
            }
        }
    }

    return $pdo;
}
