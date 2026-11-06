<?php
/**
 * ĐĂNG NHẬP NHANH PHỤC VỤ TRÌNH DIỄN & CHẤM THI
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 */

require_once __DIR__ . '/includes/init.php';

$as = $_GET['as'] ?? 'customer';

if ($as === 'admin') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'      => $user['id'],
            'name'    => $user['name'],
            'email'   => $user['email'],
            'phone'   => $user['phone'],
            'address' => $user['address'],
            'role'    => $user['role']
        ];
        set_flash('success', 'Đã tự động đăng nhập với tư cách Quản trị viên: ' . $user['name']);
        header('Location: ' . BASE_URL . 'admin/index.php');
        exit;
    }
} else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'customer' LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'      => $user['id'],
            'name'    => $user['name'],
            'email'   => $user['email'],
            'phone'   => $user['phone'],
            'address' => $user['address'],
            'role'    => $user['role']
        ];
        set_flash('success', 'Đã tự động đăng nhập với tư cách Khách hàng: ' . $user['name']);
        header('Location: ' . BASE_URL);
        exit;
    }
}

header('Location: ' . BASE_URL);
exit;
