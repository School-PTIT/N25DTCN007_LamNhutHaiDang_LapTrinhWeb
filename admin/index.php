<?php
/**
 * TRUNG TÂM CHỈ HUY QUẢN TRỊ (TIÊU CHÍ D1 & D9)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Phong cách: Quiet Luxury & Minimalist Daylight
 */

require_once __DIR__ . '/../includes/init.php';

// TIÊU CHÍ D1: PHÂN QUYỀN QUẢN TRỊ (TRẢ VỀ 403 KHI TRUY CẬP TRÁI PHÉP)
require_admin();

$page_title = 'Bảng Điều Khiển Quản Trị Studio';

// TIÊU CHÍ D9: THỐNG KÊ SỐ ĐƠN THEO TỪNG TRẠNG THÁI
$status_counts = [
    'pending'   => 0,
    'shipping'  => 0,
    'completed' => 0,
    'cancelled' => 0
];

$stmt = $pdo->query("SELECT status, COUNT(*) AS count FROM orders GROUP BY status");
while ($row = $stmt->fetch()) {
    $status_counts[$row['status']] = (int)$row['count'];
}

// TIÊU CHÍ D9: DOANH THU ĐƠN HOÀN TẤT
$rev_stmt = $pdo->query("SELECT SUM(total_amount) AS total_revenue FROM orders WHERE status = 'completed'");
$total_revenue = (float)$rev_stmt->fetchColumn();

// Tổng số thiết bị trong kho & tài khoản
$prod_count = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$user_count = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// TIÊU CHÍ D9: 5 SẢN PHẨM SẮP HẾT HÀNG (TỒN KHO THẤP NHẤT)
$low_stock_stmt = $pdo->query("
    SELECT p.*, c.name AS category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    ORDER BY p.stock ASC 
    LIMIT 5
");
$low_stock_products = $low_stock_stmt->fetchAll();

// 5 Đơn hàng mới nhất
$recent_orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Thanh điều hướng phụ cho quản trị viên -->
<?php require_once __DIR__ . '/../includes/admin-nav.php'; ?>

<div class="container" style="padding-bottom: 5rem;">
    <!-- Tiêu đề trang -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <span class="hero-tag" style="margin-bottom: 0.4rem;">TỔNG QUAN HOẠT ĐỘNG</span>
            <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--text-primary); margin: 0;">
                Bảng Điều Khiển Studio
            </h1>
        </div>
        <div style="font-size: 0.85rem; color: var(--text-secondary);">
            Hệ thống: <strong><?= $prod_count ?></strong> thiết bị · <strong><?= $user_count ?></strong> người dùng
        </div>
    </div>

    <!-- TIÊU CHÍ D9: 4 THẺ KPI CHÍNH VÀ DOANH THU -->
    <div class="admin-kpi-grid">
        <!-- Doanh thu hoàn tất -->
        <div class="admin-kpi-card" style="border-top: 3px solid var(--accent-gold);">
            <div class="admin-kpi-label" style="display: flex; align-items: center; justify-content: space-between;">
                <span>Doanh Thu Hoàn Tất</span>
                <?= icon('bag', '', 16) ?>
            </div>
            <div class="admin-kpi-value" style="color: var(--accent-gold);">
                <?= format_currency($total_revenue) ?>
            </div>
            <div class="admin-kpi-sub">
                Từ <strong><?= $status_counts['completed'] ?></strong> đơn giao hàng thành công
            </div>
        </div>

        <!-- Chờ xử lý -->
        <div class="admin-kpi-card" style="border-top: 3px solid #d97706;">
            <div class="admin-kpi-label" style="display: flex; align-items: center; justify-content: space-between;">
                <span>Đơn Hàng Chờ Xử Lý</span>
                <?= icon('clock', '', 16) ?>
            </div>
            <div class="admin-kpi-value" style="color: #d97706;">
                <?= $status_counts['pending'] ?> <span style="font-size: 1rem; font-weight: 500;">đơn</span>
            </div>
            <div class="admin-kpi-sub">
                Cần xuất kho và đóng gói kiểm định
            </div>
        </div>

        <!-- Đang vận chuyển -->
        <div class="admin-kpi-card" style="border-top: 3px solid #2563eb;">
            <div class="admin-kpi-label" style="display: flex; align-items: center; justify-content: space-between;">
                <span>Đang Vận Chuyển</span>
                <?= icon('truck', '', 16) ?>
            </div>
            <div class="admin-kpi-value" style="color: #2563eb;">
                <?= $status_counts['shipping'] ?> <span style="font-size: 1rem; font-weight: 500;">đơn</span>
            </div>
            <div class="admin-kpi-sub">
                Hàng đang trên đường giao tới khách
            </div>
        </div>

        <!-- Đã hủy -->
        <div class="admin-kpi-card" style="border-top: 3px solid var(--status-alert);">
            <div class="admin-kpi-label" style="display: flex; align-items: center; justify-content: space-between;">
                <span>Vận Đơn Đã Hủy</span>
                <?= icon('x-circle', '', 16) ?>
            </div>
            <div class="admin-kpi-value" style="color: var(--status-alert);">
                <?= $status_counts['cancelled'] ?> <span style="font-size: 1rem; font-weight: 500;">đơn</span>
            </div>
            <div class="admin-kpi-sub">
                Đã tự động hoàn trả số lượng vào kho
            </div>
        </div>
    </div>

    <!-- TIÊU CHÍ D9: TOP 5 SẢN PHẨM SẮP HẾT HÀNG (CẢNH BÁO TỒN KHO) -->
    <div style="margin-bottom: 3rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                <span style="color: #d97706;"><?= icon('alert-triangle', '', 18) ?></span> Top 5 Sản Phẩm Tồn Kho Thấp Nhất (Tiêu chí D9)
            </h2>
            <a href="<?= BASE_URL ?>admin/products.php" class="btn-icon" style="font-size: 0.8rem; display: inline-flex; align-items: center;">
                Xem tất cả kho thiết bị <?= icon('arrow-right', '', 14) ?>
            </a>
        </div>

        <div class="manifest-card" style="padding: 0; overflow: hidden;">
            <table class="cart-table" style="margin-bottom: 0;">
                <thead style="background: #fafafc;">
                    <tr>
                        <th style="padding-left: 1.5rem;">MÃ SKU</th>
                        <th>TÊN THIẾT BỊ</th>
                        <th>DANH MỤC</th>
                        <th>GIÁ BÁN</th>
                        <th>TỒN KHO</th>
                        <th style="text-align: right; padding-right: 1.5rem;">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($low_stock_products as $lp): ?>
                        <tr>
                            <td style="padding-left: 1.5rem; font-family: var(--font-mono); font-size: 0.82rem; font-weight: 600; color: var(--text-secondary);">
                                <?= e($lp['sku']) ?>
                            </td>
                            <td>
                                <div style="font-weight: 600; font-size: 0.92rem; color: var(--text-primary);">
                                    <?= e($lp['name']) ?>
                                </div>
                            </td>
                            <td style="color: var(--text-secondary); font-size: 0.88rem;">
                                <?= e($lp['category_name']) ?>
                            </td>
                            <td style="font-weight: 600; color: var(--text-primary);">
                                <?= format_currency($lp['price']) ?>
                            </td>
                            <td>
                                <span class="badge-status <?= $lp['stock'] <= 3 ? 'badge-cancelled' : 'badge-pending' ?>" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span class="status-indicator-dot <?= $lp['stock'] <= 3 ? 'dot-warning' : 'dot-ready' ?>"></span> Còn <?= $lp['stock'] ?> chiếc
                                </span>
                            </td>
                            <td style="text-align: right; padding-right: 1.5rem;">
                                <a href="<?= BASE_URL ?>admin/product-form.php?id=<?= $lp['id'] ?>" class="btn-icon" style="font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <?= icon('edit', '', 13) ?> Nhập kho / Sửa
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5 ĐƠN HÀNG GẦN NHẤT -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                5 Đơn Hàng Vừa Tiếp Nhận
            </h2>
            <a href="<?= BASE_URL ?>admin/orders.php" class="btn-icon" style="font-size: 0.8rem; display: inline-flex; align-items: center;">
                Quản lý toàn bộ đơn hàng <?= icon('arrow-right', '', 14) ?>
            </a>
        </div>

        <div class="manifest-card" style="padding: 0; overflow: hidden;">
            <table class="cart-table" style="margin-bottom: 0;">
                <thead style="background: #fafafc;">
                    <tr>
                        <th style="padding-left: 1.5rem;">MÃ VẬN ĐƠN</th>
                        <th>NGÀY ĐẶT</th>
                        <th>NGƯỜI NHẬN</th>
                        <th>TỔNG GIÁ TRỊ</th>
                        <th>TRẠNG THÁI</th>
                        <th style="text-align: right; padding-right: 1.5rem;">QUẢN LÝ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $ro): ?>
                        <tr>
                            <td style="padding-left: 1.5rem; font-family: var(--font-mono); font-size: 0.82rem; font-weight: 700; color: var(--text-primary);">
                                <?= e($ro['order_code']) ?>
                            </td>
                            <td style="font-size: 0.82rem; color: var(--text-secondary);">
                                <?= date('d/m/Y H:i', strtotime($ro['created_at'])) ?>
                            </td>
                            <td style="font-weight: 500;">
                                <?= e($ro['recipient_name']) ?>
                            </td>
                            <td style="font-weight: 700; color: var(--text-primary);">
                                <?= format_currency($ro['total_amount']) ?>
                            </td>
                            <td>
                                <?php
                                $badge_class = 'badge-pending';
                                $badge_icon = 'clock';
                                $badge_label = 'Chờ xử lý';
                                if ($ro['status'] === 'shipping') { $badge_class = 'badge-shipping'; $badge_icon = 'truck'; $badge_label = 'Đang giao'; }
                                elseif ($ro['status'] === 'completed') { $badge_class = 'badge-completed'; $badge_icon = 'check-circle'; $badge_label = 'Hoàn tất'; }
                                elseif ($ro['status'] === 'cancelled') { $badge_class = 'badge-cancelled'; $badge_icon = 'x-circle'; $badge_label = 'Đã hủy'; }
                                ?>
                                <span class="badge-status <?= $badge_class ?>">
                                    <?= icon($badge_icon, '', 12) ?> <?= $badge_label ?>
                                </span>
                            </td>
                            <td style="text-align: right; padding-right: 1.5rem;">
                                <a href="<?= BASE_URL ?>order-detail.php?id=<?= $ro['id'] ?>" class="btn-icon" target="_blank" style="font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <?= icon('eye', '', 13) ?> Chi Tiết
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
