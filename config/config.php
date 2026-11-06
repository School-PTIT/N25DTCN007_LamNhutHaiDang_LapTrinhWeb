<?php
/**
 * CẤU HÌNH HỆ THỐNG - CỬA HÀNG CÔNG NGHỆ LÂM NHỰT HẢI ĐĂNG
 * Sinh viên: Lâm Nhựt Hải Đăng | MSSV: N25DTCN007 | Lớp: D25CQCN01-N
 * Môn học: Lập trình Web - Học viện Công nghệ Bưu chính Viễn thông (PTIT)
 */

// Chế độ gỡ lỗi (Tiêu chí 3 Mục 7 đề thi: APP_DEBUG phải là false)
define('APP_DEBUG', false);

if (APP_DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Múi giờ chuẩn Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Thông tin định danh sinh viên & thương hiệu (Mục 2 cá nhân hóa bắt buộc)
define('APP_NAME', 'LÂM NHỰT HẢI ĐĂNG');
define('APP_SUB_TITLE', 'HARDWARE ARCHITECTURE & GEAR LAB');
define('APP_FOUNDER', 'Lâm Nhựt Hải Đăng');
define('APP_STUDENT_ID', 'N25DTCN007');
define('APP_HOTLINE', '1900 6868');
define('APP_EMAIL', 'dang.ptit2026@gmail.com');
define('APP_ADDRESS', 'Học viện Công nghệ Bưu chính Viễn thông, TP. Hồ Chí Minh');

// Cấu hình Cơ sở dữ liệu MySQL (Tự động thích ứng giữa Cục bộ và Hosting InfinityFree)
$is_hosting = (
    (isset($_SERVER['HTTP_HOST']) && (
        strpos($_SERVER['HTTP_HOST'], 'rf.gd') !== false ||
        strpos($_SERVER['HTTP_HOST'], 'infinityfree') !== false
    ))
);

if ($is_hosting) {
    define('DB_HOST', 'sql107.infinityfree.com');
    define('DB_PORT', '3306');
    define('DB_NAME', 'if0_43372666_shop');
    define('DB_USER', 'if0_43372666');
    define('DB_PASS', 'xununam449977');
    define('DB_CHARSET', 'utf8mb4');
} else {
    define('DB_HOST', '127.0.0.1');
    define('DB_PORT', '3306');
    define('DB_NAME', 'final_web');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
}

// Tự động nhận diện BASE_URL linh hoạt (chạy tốt trên cả Laragon, XAMPP, PHP Built-in Server và Hosting thật)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_name = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$sub_dir = rtrim($script_name, '/');

// Loại bỏ '/admin' nếu đang truy cập từ thư mục admin
if (substr($sub_dir, -6) === '/admin') {
    $sub_dir = substr($sub_dir, 0, -6);
}

define('BASE_URL', rtrim($protocol . $host . $sub_dir, '/') . '/');

// Đường dẫn thư mục máy chủ
define('ROOT_PATH', realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);
define('UPLOAD_DIR', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR);
define('UPLOAD_URL', BASE_URL . 'uploads/');

// Quy định phân trang & Giới hạn
define('PRODUCTS_PER_PAGE', 8); // Tiêu chí D8: Phân trang 8 sản phẩm/trang
define('MAX_LOGIN_ATTEMPTS', 5); // Tiêu chí D10: 5 lần sai
define('LOCKOUT_DURATION', 300); // 5 phút = 300 giây
