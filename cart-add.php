<?php
/**
 * THÊM SẢN PHẨM VÀO GIỎ HÀNG (TIÊU CHÍ C3 & EXPRESS CHECKOUT)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 */

require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
    $is_ajax = !empty($_POST['ajax']);
    $is_express = !empty($_POST['express']);

    // Kiểm tra sản phẩm có tồn tại và còn hàng không
    $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    $success = false;
    $message = '';
    $msg_type = 'danger';

    if ($product) {
        if ($product['stock'] <= 0) {
            $message = 'Thiết bị này hiện đã tạm thời hết hàng trong kho!';
            if (!$is_ajax) {
                set_flash('danger', $message);
            }
        } else {
            if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            $current_qty = (int)($_SESSION['cart'][$product_id]['quantity'] ?? 0);
            $new_qty = $current_qty + $quantity;

            if ($new_qty > $product['stock']) {
                $new_qty = $product['stock'];
                $message = "Kho chỉ còn {$product['stock']} thiết bị. Đã tự động điều chỉnh số lượng tối đa.";
                $msg_type = 'warning';
                if (!$is_ajax && !$is_express) {
                    set_flash('warning', $message);
                }
            } else {
                $message = "Đã thêm '{$product['name']}' vào giỏ hàng!";
                $msg_type = 'success';
                if (!$is_ajax && !$is_express) {
                    set_flash('success', $message);
                }
            }

            // Tiêu chí C3: Lưu số lượng vào session (Giá luôn lấy từ CSDL khi hiển thị)
            $_SESSION['cart'][$product_id] = [
                'quantity' => $new_qty
            ];
            $success = true;
        }
    } else {
        $message = 'Không tìm thấy thiết bị cần thêm vào giỏ hàng.';
        if (!$is_ajax) {
            set_flash('danger', $message);
        }
    }

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success'      => $success,
            'message'      => $message,
            'type'         => $msg_type,
            'cart_count'   => cart_count(),
            'product_name' => $product['name'] ?? ''
        ]);
        exit;
    }

    if ($is_express && $success) {
        header('Location: ' . BASE_URL . 'checkout.php');
        exit;
    }
}

header('Location: ' . BASE_URL . 'cart.php');
exit;
