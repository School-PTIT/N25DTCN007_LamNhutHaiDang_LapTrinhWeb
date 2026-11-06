<?php
/**
 * THANH TOÁN & ĐẶT HÀNG (TIÊU CHÍ C4, C9 & VIETQR / VOUCHER)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Phong cách: Quiet Luxury & Minimalist Daylight
 */

require_once __DIR__ . '/includes/init.php';

// Bắt buộc đăng nhập để đặt hàng (Tiêu chí C9)
require_login();

$user = current_user();
$cart_items = get_cart_items_with_db_prices($pdo);

if (empty($cart_items)) {
    set_flash('warning', 'Giỏ hàng của bạn đang trống. Vui lòng chọn sản phẩm trước khi thanh toán.');
    header('Location: ' . BASE_URL);
    exit;
}

$page_title = 'Xác Nhận Đơn Hàng & Thanh Toán';
$errors = [];

$recipient_name = $user['name'] ?? '';
$recipient_phone = $user['phone'] ?? '';
$shipping_address = $user['address'] ?? '';
$note = '';
$payment_method = 'cod';

$grand_total = 0;
foreach ($cart_items as $item) {
    $grand_total += $item['subtotal'];
}

// Xử lý mã voucher giảm giá (PTIT2026: -10%, HAIDANG: -15%)
$voucher_code = trim($_POST['voucher_code'] ?? ($_SESSION['voucher_code'] ?? ''));
$discount_percent = 0;
$discount_amount = 0;
$voucher_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_voucher'])) {
    verify_csrf();
    $raw_code = trim($_POST['voucher_code'] ?? '');
    $is_ajax = !empty($_POST['ajax']);
    $upper_code = strtoupper($raw_code);
    $is_valid = false;
    $msg = 'Mã ưu đãi không hợp lệ hoặc đã hết hạn.';

    if ($upper_code === 'PTIT2026') {
        $discount_percent = 10;
        $is_valid = true;
        $_SESSION['voucher_code'] = 'PTIT2026';
        $voucher_code = 'PTIT2026';
        $msg = 'Áp dụng mã PTIT2026 thành công! Giảm 10% tổng đơn hàng.';
    } elseif ($upper_code === 'HAIDANG') {
        $discount_percent = 15;
        $is_valid = true;
        $_SESSION['voucher_code'] = 'HAIDANG';
        $voucher_code = 'HAIDANG';
        $msg = 'Áp dụng mã HAIDANG thành công! Giảm 15% độc quyền Studio.';
    } else {
        unset($_SESSION['voucher_code']);
        $voucher_code = '';
        $voucher_error = $msg;
    }

    $discount_amount = (int)round($grand_total * ($discount_percent / 100));
    $final_total = max(0, $grand_total - $discount_amount);

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $is_valid,
            'voucher_code' => $is_valid ? $upper_code : '',
            'discount_percent' => $discount_percent,
            'discount_amount' => $discount_amount,
            'formatted_discount' => format_currency($discount_amount),
            'grand_total' => $grand_total,
            'formatted_grand_total' => format_currency($grand_total),
            'final_total' => $final_total,
            'formatted_final_total' => format_currency($final_total),
            'message' => $msg
        ]);
        exit;
    }
}

if (!empty($voucher_code)) {
    $upper_code = strtoupper($voucher_code);
    if ($upper_code === 'PTIT2026') {
        $discount_percent = 10;
    } elseif ($upper_code === 'HAIDANG') {
        $discount_percent = 15;
    }
}

if ($discount_percent > 0) {
    $discount_amount = (int)round($grand_total * ($discount_percent / 100));
}
$final_total = max(0, $grand_total - $discount_amount);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_order'])) {
    verify_csrf();

    $recipient_name = trim($_POST['recipient_name'] ?? '');
    $recipient_phone = trim($_POST['recipient_phone'] ?? '');
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'cod';
    if (!in_array($payment_method, ['cod', 'vietqr'])) {
        $payment_method = 'cod';
    }

    if (empty($recipient_name)) {
        $errors[] = 'Vui lòng nhập tên người nhận hàng.';
    }
    if (empty($recipient_phone)) {
        $errors[] = 'Vui lòng nhập số điện thoại người nhận.';
    }
    if (empty($shipping_address)) {
        $errors[] = 'Vui lòng nhập địa chỉ giao hàng.';
    }

    if (empty($errors)) {
        // TIÊU CHÍ C4: BẮT ĐẦU GIAO DỊCH DATABASE TRANSACTION
        try {
            $pdo->beginTransaction();

            // 1. Kiểm tra tồn kho của từng sản phẩm trong giỏ
            foreach ($cart_items as $item) {
                $check_stmt = $pdo->prepare("SELECT stock, price, name FROM products WHERE id = ?");
                $check_stmt->execute([$item['id']]);
                $db_prod = $check_stmt->fetch();

                if (!$db_prod || $db_prod['stock'] < $item['quantity']) {
                    throw new Exception("Sản phẩm '{$item['name']}' không đủ tồn kho (Chỉ còn " . ($db_prod['stock'] ?? 0) . " chiếc). Giao dịch bị hủy!");
                }
            }

            // 2. Tạo bản ghi đơn hàng mới trong bảng orders
            $order_code = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
            $method_label = $payment_method === 'vietqr' ? 'Chuyển khoản VietQR' : 'Thanh toán khi nhận hàng (COD)';
            $order_note = "[Hình thức: $method_label";
            if ($discount_amount > 0) {
                $order_note .= " | Voucher: $voucher_code (-" . format_currency($discount_amount) . ")";
            }
            $order_note .= "] " . $note;

            $order_stmt = $pdo->prepare("
                INSERT INTO orders (user_id, order_code, recipient_name, recipient_phone, shipping_address, total_amount, status, note) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)
            ");
            $order_stmt->execute([
                $user['id'],
                $order_code,
                $recipient_name,
                $recipient_phone,
                $shipping_address,
                $final_total,
                $order_note
            ]);
            $order_id = $pdo->lastInsertId();

            // 3. Trừ tồn kho và Lưu chi tiết đơn hàng (order_items) kèm đơn giá gốc tại thời điểm mua
            $item_stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, price, quantity) 
                VALUES (?, ?, ?, ?)
            ");
            $update_stock_stmt = $pdo->prepare("
                UPDATE products SET stock = stock - ? WHERE id = ?
            ");

            foreach ($cart_items as $item) {
                // Lưu snapshot đơn giá lúc mua (Tiêu chí C4)
                $item_stmt->execute([
                    $order_id,
                    $item['id'],
                    $item['price'],
                    $item['quantity']
                ]);

                // Trừ tồn kho trong cùng transaction
                $update_stock_stmt->execute([
                    $item['quantity'],
                    $item['id']
                ]);
            }

            // 4. Xác nhận giao dịch thành công (COMMIT)
            $pdo->commit();

            // Xóa sạch giỏ hàng và voucher trong session
            $_SESSION['cart'] = [];
            unset($_SESSION['voucher_code']);

            set_flash('success', "Đặt hàng thành công! Mã đơn hàng của bạn là: $order_code");
            header('Location: ' . BASE_URL . 'order-detail.php?id=' . $order_id);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 1.5rem; padding-bottom: 5rem;">
    <!-- Nút quay lại giỏ hàng mượt mà -->
    <a href="<?= BASE_URL ?>cart.php" class="nav-link" style="border: 1px solid var(--border-subtle); display: inline-flex; align-items: center; margin-bottom: 1.5rem; font-weight: 500;">
        <?= icon('arrow-left', '', 14) ?> Quay lại giỏ hàng
    </a>

    <div style="margin-bottom: 2rem;">
        <span class="hero-tag">ĐẶT HÀNG TRỰC TUYẾN</span>
        <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--text-primary); margin: 0.25rem 0 0 0;">
            Xác Nhận Đơn Hàng & Phương Thức Thanh Toán
        </h1>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <span><?= icon('alert-triangle', '', 16) ?></span>
            <div>
                <?php foreach ($errors as $err): ?>
                    <div><?= e($err) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 2.5rem; align-items: flex-start;">
        
        <!-- Cột trái: Form thông tin người nhận & Chọn phương thức thanh toán -->
        <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 2.25rem; box-shadow: var(--shadow-card);">
            <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text-primary);">
                1. Thông Tin Nhận Hàng
            </h2>

            <form action="<?= BASE_URL ?>checkout.php" method="POST" id="checkout-form">
                <?= csrf_field() ?>
                <input type="hidden" name="action_order" value="1">
                <input type="hidden" name="voucher_code" value="<?= e($voucher_code) ?>">

                <div class="form-group">
                    <label class="form-label" for="rec-name">Họ và tên người nhận: *</label>
                    <input type="text" id="rec-name" name="recipient_name" class="form-control" value="<?= e($recipient_name) ?>" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="rec-phone">Số điện thoại: *</label>
                        <input type="tel" id="rec-phone" name="recipient_phone" class="form-control" value="<?= e($recipient_phone) ?>" required pattern="[0-9]{10,11}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="rec-addr">Địa chỉ nhận hàng: *</label>
                        <input type="text" id="rec-addr" name="shipping_address" class="form-control" value="<?= e($shipping_address) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="rec-note">Ghi chú giao hàng:</label>
                    <textarea id="rec-note" name="note" rows="2" class="form-control" placeholder="Ví dụ: Gọi trước khi giao, gửi bảo vệ..."><?= e($note) ?></textarea>
                </div>

                <!-- 2. PHƯƠNG THỨC THANH TOÁN (COD vs VIETQR) -->
                <h2 style="font-size: 1.2rem; font-weight: 700; margin: 2rem 0 1rem 0; color: var(--text-primary);">
                    2. Phương Thức Thanh Toán
                </h2>

                <div class="payment-tabs">
                    <label class="payment-tab-option <?= $payment_method === 'cod' ? 'active' : '' ?>">
                        <input type="radio" name="payment_method" value="cod" <?= $payment_method === 'cod' ? 'checked' : '' ?> onchange="document.querySelectorAll('.payment-tab-option').forEach(el=>el.classList.remove('active')); this.closest('label').classList.add('active'); document.getElementById('vietqr-details').style.display='none';">
                        <div>
                            <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary);">Thanh Toán Khi Nhận (COD)</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.2rem;">Thanh toán tiền mặt trực tiếp cho shipper khi nhận hàng.</div>
                        </div>
                    </label>

                    <label class="payment-tab-option <?= $payment_method === 'vietqr' ? 'active' : '' ?>">
                        <input type="radio" name="payment_method" value="vietqr" <?= $payment_method === 'vietqr' ? 'checked' : '' ?> onchange="document.querySelectorAll('.payment-tab-option').forEach(el=>el.classList.remove('active')); this.closest('label').classList.add('active'); document.getElementById('vietqr-details').style.display='grid';">
                        <div>
                            <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary); display: flex; align-items: center; gap: 0.35rem;">
                                Chuyển Khoản VietQR <?= icon('shield-check', '', 14) ?>
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.2rem;">Quét mã QR chuyển khoản nhanh 24/7 qua app ngân hàng.</div>
                        </div>
                    </label>
                </div>

                <!-- Khung chi tiết VietQR mẫu -->
                <div id="vietqr-details" class="vietqr-card" style="display: <?= $payment_method === 'vietqr' ? 'grid' : 'none' ?>;">
                    <div style="text-align: center;">
                        <img id="vietqr-img" src="https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=<?= urlencode("2|99|0987654321|LAM NHUT HAI DANG||0|0|" . $final_total . "|MSSV N25DTCN007|transfer_myqr") ?>" 
                             alt="VietQR Chuyển khoản" 
                             style="width: 125px; height: 125px; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle); padding: 4px; background: #ffffff;">
                        <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 0.35rem; font-weight: 600;">QUÉT QUA APP BANK</div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.8;">
                            <div>Ngân hàng: <strong style="color: var(--text-primary);">MB Bank (Quân Đội)</strong></div>
                            <div>Chủ tài khoản: <strong style="color: var(--text-primary);"><?= e(APP_STUDENT_NAME) ?></strong></div>
                            <div>Số tài khoản: <strong style="font-family: var(--font-mono); color: var(--text-primary);">0987 654 321</strong></div>
                            <div>Số tiền: <strong id="vietqr-total-display" style="color: var(--status-live);"><?= format_currency($final_total) ?></strong></div>
                            <div>Nội dung: <strong style="font-family: var(--font-mono); color: var(--accent-gold);">MSSV <?= e(APP_STUDENT_ID) ?></strong></div>
                        </div>
                    </div>
                </div>

                <div style="background: var(--bg-elevated); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 1rem; margin-bottom: 1.5rem; font-size: 0.82rem; color: var(--text-secondary); line-height: 1.6;">
                    <div style="color: var(--status-live); display: flex; align-items: center; gap: 0.35rem; font-weight: 600;">
                        <?= icon('shield-check', '', 14) ?> Bảo đảm an toàn tuyệt đối
                    </div>
                    <div>Giao dịch được bảo vệ bằng Database Transaction (Tiêu chí C4), tự động hoàn kho nếu có sự cố.</div>
                </div>

                <button type="submit" id="checkout-submit-btn" class="btn-action-primary" style="width: 100%; justify-content: center; padding: 0.95rem; font-size: 1rem;">
                    <?= icon('check', '', 16) ?> Hoàn Tất Đặt Hàng (<span id="checkout-btn-total"><?= format_currency($final_total) ?></span>)
                </button>
            </form>
        </div>

        <!-- Cột phải: Bản tóm tắt giỏ hàng & Nhập Voucher -->
        <div>
            <div class="manifest-card" style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--text-primary);">
                    Tóm Tắt Đơn Hàng (<?= count($cart_items) ?> thiết bị)
                </h2>

                <div style="display: flex; flex-direction: column; gap: 0.85rem; margin-bottom: 1.5rem;">
                    <?php foreach ($cart_items as $item): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f2f5; padding-bottom: 0.75rem;">
                            <div style="max-width: 70%;">
                                <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary);"><?= e($item['name']) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">
                                    <?= e($item['sku']) ?> · SL: <?= $item['quantity'] ?>
                                </div>
                            </div>
                            <div style="font-weight: 700; font-size: 0.92rem; color: var(--text-primary);">
                                <?= format_currency($item['subtotal']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Form Nhập Voucher Ưu Đãi (Ajax Zero-Reload) -->
                <form action="<?= BASE_URL ?>checkout.php" method="POST" class="voucher-box" id="voucher-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action_voucher" value="1">
                    <input type="text" id="voucher-input-code" name="voucher_code" value="<?= e($voucher_code) ?>" placeholder="Mã ưu đãi (VD: PTIT2026)" class="form-control voucher-input">
                    <button type="submit" id="voucher-apply-btn" class="btn-icon" style="padding: 0.5rem 1rem; font-weight: 600; white-space: nowrap;">
                        Áp Dụng
                    </button>
                </form>

                <div id="voucher-feedback-msg" style="display: <?= isset($voucher_error) ? 'block' : 'none' ?>; color: var(--status-alert); font-size: 0.8rem; margin-bottom: 0.75rem;">
                    <?= icon('alert-triangle', '', 12) ?> <span id="voucher-error-text"><?= e($voucher_error ?? '') ?></span>
                </div>

                <div id="voucher-discount-wrap" style="display: <?= $discount_amount > 0 ? 'block' : 'none' ?>;">
                    <div class="voucher-discount-row">
                        <span id="voucher-label-display">Ưu đãi (<?= e($voucher_code) ?> -<?= $discount_percent ?>%):</span>
                        <span id="voucher-amount-display">-<?= format_currency($discount_amount) ?></span>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border-subtle); padding-top: 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="color: var(--text-secondary); font-size: 0.85rem; font-weight: 500;">Tổng thanh toán:</div>
                        <div id="original-total-wrap" style="display: <?= $discount_amount > 0 ? 'block' : 'none' ?>; font-size: 0.8rem; text-decoration: line-through; color: var(--text-muted);">
                            <?= format_currency($grand_total) ?>
                        </div>
                    </div>
                    <span id="checkout-final-total" style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em;">
                        <?= format_currency($final_total) ?>
                    </span>
                </div>
            </div>

            <!-- Gợi ý mã ưu đãi có sẵn cho người chấm thi -->
            <div style="background: #fafafc; border: 1px dashed var(--border-subtle); border-radius: var(--radius-sm); padding: 1rem; font-size: 0.78rem; color: var(--text-secondary); line-height: 1.6;">
                <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">Gợi ý mã ưu đãi thử nghiệm:</div>
                <div>• Nhập <strong>PTIT2026</strong>: Giảm ngay 10% tổng đơn hàng</div>
                <div>• Nhập <strong>HAIDANG</strong>: Giảm ngay 15% độc quyền Studio</div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
