<?php
/**
 * XÓA SẢN PHẨM BẰNG PHƯƠNG THỨC POST (TIÊU CHÍ D1 & D2)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 */

require_once __DIR__ . '/../includes/init.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $id = (int)($_POST['id'] ?? 0);

    // 1. Kiểm tra sản phẩm tồn tại
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        set_flash('danger', 'Không tìm thấy thiết bị cần xóa.');
        header('Location: ' . BASE_URL . 'admin/products.php');
        exit;
    }

    // TIÊU CHÍ D2: KHÔNG XÓA ĐƯỢC SẢN PHẨM ĐÃ CÓ TRONG ĐƠN HÀNG
    $check_orders = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
    $check_orders->execute([$id]);
    $in_orders_count = (int)$check_orders->fetchColumn();

    if ($in_orders_count > 0) {
        set_flash('danger', "BỊ CHẶN: Không thể xóa thiết bị '{$product['name']}' vì đã phát sinh trong $in_orders_count đơn hàng của khách! (Ràng buộc dữ liệu Tiêu chí D2)");
        header('Location: ' . BASE_URL . 'admin/products.php');
        exit;
    }

    // 2. Xóa ảnh thiết bị trong uploads/ nếu có
    if (!empty($product['image'])) {
        $file_path = UPLOAD_DIR . $product['image'];
        if (file_exists($file_path) && is_file($file_path)) {
            unlink($file_path);
        }
    }

    // 3. Xóa sản phẩm khỏi CSDL
    $del_stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $del_stmt->execute([$id]);

    set_flash('success', "Đã xóa thiết bị '{$product['name']}' thành công khỏi hệ thống kho!");
}

header('Location: ' . BASE_URL . 'admin/products.php');
exit;
