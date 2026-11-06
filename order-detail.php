<?php
/**
 * CHI TIẾT ĐƠN HÀNG, PHÒNG CHỐNG IDOR & IN HÓA ĐƠN (TIÊU CHÍ C5 & C6)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Phong cách: Quiet Luxury & Minimalist Daylight
 */

require_once __DIR__ . '/includes/init.php';

require_login();
$user = current_user();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    exit;
}

// 1. Truy vấn thông tin đơn hàng
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    exit;
}

// 2. TIÊU CHÍ C5: BẢO MẬT CHỐNG LỖ HỔNG IDOR
if ($order['user_id'] != $user['id'] && !is_admin()) {
    http_response_code(403);
    die('<div style="background:#fafafc; color:#dc2626; font-family:sans-serif; padding:4rem; text-align:center;">
        <h2>[403 FORBIDDEN - LỖI PHÂN QUYỀN IDOR]</h2>
        <p>Bạn không có quyền truy cập hoặc xem đơn hàng của người dùng khác!</p>
        <a href="' . BASE_URL . 'orders.php" style="color:#111827; font-weight:600;">← Quay lại danh sách đơn hàng</a>
    </div>');
}

$page_title = 'Chi Tiết Đơn Hàng ' . $order['order_code'];

// 3. Truy vấn các mục linh kiện trong đơn (order_items) kèm đơn giá lúc mua
$item_stmt = $pdo->prepare("
    SELECT oi.*, p.name AS product_name, p.sku AS product_sku, p.image AS product_image 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$item_stmt->execute([$order_id]);
$items = $item_stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- TIÊU ĐỀ IN HÓA ĐƠN RIÊNG BIỆT (CHỈ HIỂN THỊ KHI IN RA GIẤY) -->
<div class="print-invoice-header" style="display: none;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <h1 style="font-size: 1.6rem; font-weight: 800; margin: 0;"><?= e(APP_NAME) ?></h1>
            <div style="font-size: 0.85rem; color: #555555; margin-top: 0.25rem;">STUDIO & BOUTIQUE · EST. 2026</div>
            <div style="font-size: 0.85rem; font-weight: 600; margin-top: 0.5rem;">MSSV: <?= e(APP_STUDENT_ID) ?> — <?= e(APP_STUDENT_NAME) ?></div>
        </div>
        <div style="text-align: right;">
            <h2 style="font-size: 1.25rem; font-weight: 700; margin: 0;">PHIẾU GIAO HÀNG & BẢO HÀNH</h2>
            <div style="font-size: 0.85rem; margin-top: 0.25rem;">Mã vận đơn: <strong><?= e($order['order_code']) ?></strong></div>
            <div style="font-size: 0.85rem;">Ngày lập: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
        </div>
    </div>
</div>

<div class="container" style="padding-top: 2rem; padding-bottom: 5rem;">
    <!-- Nút quay lại -->
    <a href="<?= BASE_URL ?>orders.php" class="nav-link" style="border: 1px solid var(--border-subtle); display: inline-flex; align-items: center; margin-bottom: 1.5rem; font-weight: 500;">
        <?= icon('arrow-left', '', 14) ?> Quay lại danh sách đơn hàng
    </a>

    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <span class="hero-tag">CHI TIẾT VẬN ĐƠN</span>
            <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--text-primary); margin: 0.25rem 0 0 0;">
                Đơn Hàng: <?= e($order['order_code']) ?>
            </h1>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <!-- Nút In Hóa Đơn -->
            <button type="button" onclick="window.print()" class="btn-icon" style="padding: 0.5rem 1rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem;" title="In phiếu giao hàng khổ A4">
                <?= icon('receipt', '', 15) ?> In Phiếu Giao Hàng
            </button>

            <!-- Nút Hủy Đơn khi còn Pending (Ajax Zero-Reload) -->
            <?php if ($order['status'] === 'pending'): ?>
                <form action="<?= BASE_URL ?>order-cancel.php" method="POST" style="margin: 0; display: inline;" class="order-detail-cancel-form" data-order-id="<?= $order['id'] ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <button type="submit" class="btn-icon btn-cancel-detail-ajax" data-confirm="Bạn có chắc chắn muốn hủy đơn hàng này không? Số lượng linh kiện sẽ được hoàn trả kho." style="color: var(--status-alert); padding: 0.5rem 1rem;">
                        <?= icon('x-circle', '', 15) ?> Hủy đơn hàng này
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- THANH TIẾN TRÌNH TRỰC QUAN (ORDER STEPPER) -->
    <?php if ($order['status'] !== 'cancelled'): ?>
        <div class="manifest-card" style="margin-bottom: 2rem; padding: 1.5rem 2rem;">
            <div class="order-stepper">
                <div class="stepper-item completed">
                    <div class="stepper-icon"><?= icon('check', 'no-margin', 14) ?></div>
                    <div class="stepper-label">Đã đặt hàng</div>
                </div>
                <div class="stepper-item <?= in_array($order['status'], ['pending', 'shipping', 'completed']) ? ($order['status'] === 'pending' ? 'active' : 'completed') : '' ?>">
                    <div class="stepper-icon"><?= $order['status'] === 'pending' ? '●' : icon('check', 'no-margin', 14) ?></div>
                    <div class="stepper-label">Chờ duyệt</div>
                </div>
                <div class="stepper-item <?= in_array($order['status'], ['shipping', 'completed']) ? ($order['status'] === 'shipping' ? 'active' : 'completed') : '' ?>">
                    <div class="stepper-icon"><?= $order['status'] === 'shipping' ? '●' : ($order['status'] === 'completed' ? icon('check', 'no-margin', 14) : '3') ?></div>
                    <div class="stepper-label">Đang giao hàng</div>
                </div>
                <div class="stepper-item <?= $order['status'] === 'completed' ? 'completed' : '' ?>">
                    <div class="stepper-icon"><?= $order['status'] === 'completed' ? icon('check', 'no-margin', 14) : '4' ?></div>
                    <div class="stepper-label">Hoàn tất</div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger" style="margin-bottom: 2rem;">
            <span><?= icon('x-circle', '', 18) ?></span>
            <div><strong>Đơn hàng này đã bị hủy.</strong> Các linh kiện phần cứng đã được hệ thống tự động hoàn trả tồn kho.</div>
        </div>
    <?php endif; ?>

    <!-- Thông tin người nhận và vận chuyển -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2.5rem;">
        <div class="manifest-card">
            <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--text-primary);">
                Địa Chỉ & Người Nhận Hàng
            </h3>
            <div style="font-size: 0.92rem; line-height: 1.9; color: var(--text-secondary);">
                <div>Người nhận: <strong style="color: var(--text-primary);"><?= e($order['recipient_name']) ?></strong></div>
                <div>Số điện thoại: <strong><?= e($order['recipient_phone']) ?></strong></div>
                <div>Địa chỉ giao: <?= e($order['shipping_address']) ?></div>
                <?php if (!empty($order['note'])): ?>
                    <div style="color: var(--accent-gold); margin-top: 0.25rem;">Ghi chú: <?= e($order['note']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="manifest-card">
            <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--text-primary);">
                Trạng Thái & Thời Gian
            </h3>
            <div style="font-size: 0.9rem; line-height: 1.9; color: var(--text-secondary);">
                <div>Thời gian đặt: <?= date('d/m/Y H:i:s', strtotime($order['created_at'])) ?></div>
                <div>Trạng thái: 
                    <?php if ($order['status'] === 'pending'): ?>
                        <span class="badge-status badge-pending"><?= icon('clock', '', 12) ?> Chờ xử lý</span>
                    <?php elseif ($order['status'] === 'shipping'): ?>
                        <span class="badge-status badge-shipping"><?= icon('truck', '', 12) ?> Đang giao hàng</span>
                    <?php elseif ($order['status'] === 'completed'): ?>
                        <span class="badge-status badge-completed"><?= icon('check-circle', '', 12) ?> Hoàn tất</span>
                    <?php elseif ($order['status'] === 'cancelled'): ?>
                        <span class="badge-status badge-cancelled"><?= icon('x-circle', '', 12) ?> Đã hủy</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng danh sách sản phẩm trong đơn -->
    <div class="manifest-card">
        <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text-primary);">
            Danh Sách Thiết Bị Trong Đơn
        </h3>
        <div class="table-responsive">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>SẢN PHẨM</th>
                        <th>ĐƠN GIÁ LÚC MUA</th>
                        <th style="text-align: center;">SỐ LƯỢNG</th>
                        <th style="text-align: right;">THÀNH TIỀN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <img src="<?= BASE_URL ?>uploads/<?= e($it['product_image']) ?>" alt="<?= e($it['product_name']) ?>" style="width: 48px; height: 48px; object-fit: contain; background: #f8f9fa; padding: 4px; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle);">
                                    <div>
                                        <div style="font-weight: 600; font-size: 0.95rem; color: var(--text-primary);"><?= e($it['product_name']) ?></div>
                                        <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.2rem;">Mã SKU: <?= e($it['product_sku']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size: 0.92rem; color: var(--text-secondary);"><?= format_currency($it['price']) ?></td>
                            <td style="text-align: center; font-weight: 600;"><?= $it['quantity'] ?></td>
                            <td style="font-weight: 700; text-align: right; color: var(--text-primary);">
                                <?= format_currency($it['price'] * $it['quantity']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="display: flex; justify-content: flex-end; align-items: center; border-top: 1px solid var(--border-subtle); padding-top: 1.75rem;">
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em;">
                Tổng tiền thanh toán: <?= format_currency($order['total_amount']) ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
