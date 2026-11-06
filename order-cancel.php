<?php
/**
 * HỦY ĐƠN HÀNG & HOÀN TỒN KHO TRONG TRANSACTION (TIÊU CHÍ C6 & AJAX ZERO-RELOAD)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 */

require_once __DIR__ . '/includes/init.php';

require_login();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $order_id = (int)($_POST['order_id'] ?? 0);
    $is_ajax = !empty($_POST['ajax']);

    // 1. Kiểm tra đơn hàng tồn tại
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng cần hủy.']);
            exit;
        }
        set_flash('danger', 'Không tìm thấy đơn hàng cần hủy.');
        header('Location: ' . BASE_URL . 'orders.php');
        exit;
    }

    // Kiểm tra quyền sở hữu (Chống IDOR)
    if ($order['user_id'] != $user['id'] && !is_admin()) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Lỗi bảo mật: Bạn không có quyền hủy đơn hàng của người khác!']);
            exit;
        }
        http_response_code(403);
        die('Lỗi bảo mật: Bạn không có quyền hủy đơn hàng của người khác!');
    }

    // TIÊU CHÍ C6: CHỈ ĐƯỢC HỦY KHI ĐƠN CÒN Ở TRẠNG THÁI "CHỜ XỬ LÝ" (pending)
    if ($order['status'] !== 'pending') {
        $msg = 'Chỉ có thể hủy đơn hàng khi đang ở trạng thái Chờ xử lý. Đơn hàng hiện tại không thể hủy.';
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $msg]);
            exit;
        }
        set_flash('danger', $msg);
        header('Location: ' . BASE_URL . 'order-detail.php?id=' . $order_id);
        exit;
    }

    // TIÊU CHÍ C6: CỘNG TRẢ TỒN KHO TRONG MỘT GIAO DỊCH DATABASE TRANSACTION
    try {
        $pdo->beginTransaction();

        // Cập nhật trạng thái đơn sang 'cancelled'
        $update_order = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        $update_order->execute([$order_id]);

        // Lấy danh sách sản phẩm và số lượng trong đơn để hoàn trả kho
        $items_stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $items_stmt->execute([$order_id]);
        $items = $items_stmt->fetchAll();

        $restore_stock = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        foreach ($items as $it) {
            $restore_stock->execute([$it['quantity'], $it['product_id']]);
        }

        // Commit transaction
        $pdo->commit();

        $msg = "Đơn hàng {$order['order_code']} đã được hủy thành công. Số lượng thiết bị đã được hoàn trả lại vào kho.";
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'order_id' => $order_id,
                'order_code' => $order['order_code'],
                'status' => 'cancelled',
                'message' => $msg
            ]);
            exit;
        }
        set_flash('success', $msg);

    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = 'Có lỗi xảy ra trong quá trình hủy đơn: ' . $e->getMessage();
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $msg]);
            exit;
        }
        set_flash('danger', $msg);
    }
}

header('Location: ' . BASE_URL . 'orders.php');
exit;
