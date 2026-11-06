<?php
/**
 * CẬP NHẬT & XÓA GIỎ HÀNG (TIÊU CHÍ C3 & A8)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 */

require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? 'update';
    $is_ajax = !empty($_POST['ajax']);

    // 1. Xóa toàn bộ giỏ hàng
    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        set_flash('info', 'Đã xóa toàn bộ thiết bị trong giỏ hàng.');

        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'cart_count' => 0,
                'grand_total' => 0,
                'formatted_grand_total' => format_currency(0),
                'is_empty' => true,
                'message' => 'Đã xóa toàn bộ giỏ hàng thành công.'
            ]);
            exit;
        }

        header('Location: ' . BASE_URL . 'cart.php');
        exit;
    }

    // 2. Xóa 1 dòng sản phẩm cụ thể
    if ($action === 'remove') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            set_flash('info', 'Đã loại bỏ thiết bị khỏi giỏ hàng.');
        }

        if ($is_ajax) {
            $cart_items = get_cart_items_with_db_prices($pdo);
            $grand_total = 0;
            foreach ($cart_items as $item) {
                $grand_total += $item['subtotal'];
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'product_id' => $product_id,
                'cart_count' => cart_count(),
                'grand_total' => $grand_total,
                'formatted_grand_total' => format_currency($grand_total),
                'is_empty' => empty($cart_items),
                'message' => 'Đã xóa sản phẩm khỏi giỏ hàng.'
            ]);
            exit;
        }

        header('Location: ' . BASE_URL . 'cart.php');
        exit;
    }

    // 3. Cập nhật số lượng (hỗ trợ cả Ajax real-time)
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);

    if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
        // Kiểm tra tồn kho từ CSDL
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $stock = (int)$stmt->fetchColumn();

        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            if ($quantity > $stock) {
                $quantity = $stock;
            }
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        }

        if ($is_ajax) {
            $cart_items = get_cart_items_with_db_prices($pdo);
            $grand_total = 0;
            foreach ($cart_items as $item) {
                $grand_total += $item['subtotal'];
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'product_id' => $product_id,
                'cart_count' => cart_count(),
                'grand_total' => $grand_total,
                'formatted_grand_total' => format_currency($grand_total)
            ]);
            exit;
        }
    }
}

header('Location: ' . BASE_URL . 'cart.php');
exit;
