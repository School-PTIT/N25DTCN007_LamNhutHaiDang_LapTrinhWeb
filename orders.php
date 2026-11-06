<?php
/**
 * LỊCH SỬ ĐƠN HÀNG (TIÊU CHÍ C5 & C6)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Phong cách: Quiet Luxury & Minimalist Daylight
 */

require_once __DIR__ . '/includes/init.php';

require_login();
$user = current_user();

$page_title = 'Lịch Sử Đơn Hàng Của Bạn';

// TIÊU CHÍ C5: CHỈ LẤY ĐƠN HÀNG THUỘC VỀ USER ĐANG ĐĂNG NHẬP (CHỐNG LỖ HỔNG IDOR)
$stmt = $pdo->prepare("
    SELECT * FROM orders 
    WHERE user_id = ? 
    ORDER BY id DESC
");
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 2rem; padding-bottom: 5rem;">
    <span class="hero-tag">ĐƠN HÀNG CỦA TÔI</span>
    <h1 style="font-size: 1.85rem; font-weight: 800; margin-bottom: 2rem; color: var(--text-primary);">
        Lịch Sử Đơn Hàng
    </h1>

    <?php if (empty($orders)): ?>
        <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 4rem 2rem; text-align: center; box-shadow: var(--shadow-card);">
            <p style="color: var(--text-muted); font-size: 1rem; margin-bottom: 1.75rem;">Bạn chưa có đơn đặt hàng nào trong hệ thống.</p>
            <a href="<?= BASE_URL ?>" class="btn-action-primary" style="padding: 0.85rem 2rem;">Khám Phá Sản Phẩm</a>
        </div>
    <?php else: ?>
        <div class="manifest-card">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>MÃ ĐƠN HÀNG</th>
                        <th>NGÀY ĐẶT</th>
                        <th>NGƯỜI NHẬN</th>
                        <th>TỔNG TIỀN</th>
                        <th>TRẠNG THÁI</th>
                        <th style="text-align: right;">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $ord): ?>
                        <tr>
                            <td style="font-weight: 700; color: var(--text-primary);">
                                <?= e($ord['order_code']) ?>
                            </td>
                            <td style="font-size: 0.82rem; color: var(--text-muted);">
                                <?= date('d/m/Y H:i', strtotime($ord['created_at'])) ?>
                            </td>
                            <td>
                                <div style="font-weight: 600; font-size: 0.92rem;"><?= e($ord['recipient_name']) ?></div>
                                <div style="font-size: 0.78rem; color: var(--text-muted);"><?= e($ord['recipient_phone']) ?></div>
                            </td>
                            <td style="font-weight: 700; font-size: 0.95rem;">
                                <?= format_currency($ord['total_amount']) ?>
                            </td>
                            <td id="order-status-<?= $ord['id'] ?>">
                                <?php if ($ord['status'] === 'pending'): ?>
                                    <span class="badge-status badge-pending">
                                        <?= icon('clock', '', 12) ?> Chờ xử lý
                                    </span>
                                <?php elseif ($ord['status'] === 'shipping'): ?>
                                    <span class="badge-status badge-shipping">
                                        <?= icon('truck', '', 12) ?> Đang giao
                                    </span>
                                <?php elseif ($ord['status'] === 'completed'): ?>
                                    <span class="badge-status badge-completed">
                                        <?= icon('check-circle', '', 12) ?> Hoàn tất
                                    </span>
                                <?php elseif ($ord['status'] === 'cancelled'): ?>
                                    <span class="badge-status badge-cancelled">
                                        <?= icon('x-circle', '', 12) ?> Đã hủy
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                    <a href="<?= BASE_URL ?>order-detail.php?id=<?= $ord['id'] ?>" class="btn-icon" style="padding: 0.35rem 0.85rem;" title="Xem chi tiết đơn">
                                        <?= icon('eye', '', 14) ?> Chi tiết
                                    </a>

                                    <!-- Tiêu chí C6: Nút hủy đơn khi đơn còn 'pending' (Ajax Zero-Reload) -->
                                    <?php if ($ord['status'] === 'pending'): ?>
                                        <form action="<?= BASE_URL ?>order-cancel.php" method="POST" style="margin: 0; display: inline;" class="order-cancel-form" data-order-id="<?= $ord['id'] ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                            <button type="submit" class="btn-icon btn-cancel-order-ajax" data-confirm="Bạn có chắc chắn muốn hủy đơn hàng này không? Số lượng linh kiện sẽ được hoàn trả vào kho." style="color: var(--status-alert);">
                                                <?= icon('x', '', 14) ?> Hủy đơn
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
