<?php
/**
 * ĐĂNG XUẤT AN TOÀN (TIÊU CHÍ C2)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 */

require_once __DIR__ . '/includes/init.php';

// Xóa toàn bộ thông tin đăng nhập trong phiên
unset($_SESSION['user']);

// Hủy hoàn toàn session nếu muốn
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Khởi động lại session mới để gửi Flash message
session_start();
require_once __DIR__ . '/includes/functions.php';

set_flash('info', 'Bạn đã đăng xuất khỏi hệ thống an toàn.');
header('Location: ' . BASE_URL);
exit;
