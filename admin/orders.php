<?php
/**
 * QUẢN LÝ ĐƠN HÀNG TOÀN HỆ THỐNG (TIÊU CHÍ D1 & D4)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Phong cách: Quiet Luxury & Minimalist Daylight
 */

require_once __DIR__ . '/../includes/init.php';

require_admin();

$page_title = 'Quản Lý Vận Đơn Khách Hàng';

// TIÊU CHÍ D4: ĐỔI TRẠNG THÁI ĐƠN HÀNG (HỖ TRỢ AJAX ZERO-RELOAD)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $order_id = (int)($_POST['order_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';
    $allowed_statuses = ['pending', 'shipping', 'completed', 'cancelled'];
    $is_ajax = !empty($_POST['ajax']);

    if ($order_id > 0 && in_array($new_status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);

        $status_labels = [
            'pending' => 'Chờ xử lý',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn tất',
            'cancelled' => 'Đã hủy'
        ];
        $label = $status_labels[$new_status] ?? $new_status;

        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'order_id' => $order_id,
                'status' => $new_status,
                'label' => $label,
                'message' => "Đã chuyển trạng thái đơn #{$order_id} sang '{$label}' thành công!"
            ]);
            exit;
        }

        set_flash('success', "Đã cập nhật trạng thái đơn hàng sang '$label' thành công!");
        header('Location: ' . BASE_URL . 'admin/orders.php');
        exit;
    }
}

// TIÊU CHÍ D4: LỌC THEO TRẠNG THÁI & TÌM KIẾM ĐƠN HÀNG
$filter_status = $_GET['status'] ?? 'all';
$search_q = trim($_GET['q'] ?? '');
$where_clauses = ['1=1'];
$params = [];

if (in_array($filter_status, ['pending', 'shipping', 'completed', 'cancelled'])) {
    $where_clauses[] = 'o.status = ?';
    $params[] = $filter_status;
}

if (!empty($search_q)) {
    $where_clauses[] = '(o.order_code LIKE ? OR o.recipient_name LIKE ? OR o.recipient_phone LIKE ?)';
    $like = '%' . $search_q . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$where = implode(' AND ', $where_clauses);

$stmt = $pdo->prepare("
    SELECT o.*, u.email AS user_email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE $where 
    ORDER BY o.id DESC
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Thanh điều hướng phụ cho quản trị viên -->
<?php require_once __DIR__ . '/../includes/admin-nav.php'; ?>

<div class="container" style="padding-bottom: 5rem;">
    <!-- Tiêu đề trang & Bộ lọc trạng thái (Tiêu chí D4) -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <span class="hero-tag" style="margin-bottom: 0.4rem;">QUẢN LÝ VẬN ĐƠN</span>
            <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--text-primary); margin: 0;">
                Danh Sách Đơn Đặt Hàng
            </h1>
            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">
                Hiện có <strong><?= count($orders) ?></strong> đơn trong danh mục hiển thị
            </div>
        </div>

        <!-- TIÊU CHÍ D4: BỘ LỌC THEO TRẠNG THÁI ĐƠN & TÌM KIẾM -->
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <form action="<?= BASE_URL ?>admin/orders.php" method="GET" class="search-form" style="margin: 0;">
                <?php if ($filter_status !== 'all'): ?>
                    <input type="hidden" name="status" value="<?= e($filter_status) ?>">
                <?php endif; ?>
                <input type="text" name="q" value="<?= e($search_q) ?>" placeholder="Mã đơn, tên, SĐT..." class="search-input" style="padding: 0.45rem 0.85rem; font-size: 0.82rem; min-width: 180px;">
                <?php if (!empty($search_q)): ?>
                    <button type="button" class="search-clear-btn" title="Xóa tìm kiếm"><?= icon('x', 'no-margin', 14) ?></button>
                <?php endif; ?>
                <button type="submit" class="search-btn" title="Tìm đơn"><?= icon('search', 'no-margin', 14) ?></button>
            </form>

            <div class="category-pills" style="margin: 0; padding-bottom: 0;">
                <a href="<?= BASE_URL ?>admin/orders.php<?= !empty($search_q) ? '?q=' . urlencode($search_q) : '' ?>" class="cat-pill <?= $filter_status === 'all' ? 'active' : '' ?>">
                    Tất Cả
                </a>
                <a href="<?= BASE_URL ?>admin/orders.php?status=pending<?= !empty($search_q) ? '&q=' . urlencode($search_q) : '' ?>" class="cat-pill <?= $filter_status === 'pending' ? 'active' : '' ?>">
                    <?= icon('clock', '', 13) ?> Chờ Xử Lý
                </a>
                <a href="<?= BASE_URL ?>admin/orders.php?status=shipping<?= !empty($search_q) ? '&q=' . urlencode($search_q) : '' ?>" class="cat-pill <?= $filter_status === 'shipping' ? 'active' : '' ?>">
                    <?= icon('truck', '', 13) ?> Đang Giao
                </a>
                <a href="<?= BASE_URL ?>admin/orders.php?status=completed<?= !empty($search_q) ? '&q=' . urlencode($search_q) : '' ?>" class="cat-pill <?= $filter_status === 'completed' ? 'active' : '' ?>">
                    <?= icon('check-circle', '', 13) ?> Hoàn Tất
                </a>
                <a href="<?= BASE_URL ?>admin/orders.php?status=cancelled<?= !empty($search_q) ? '&q=' . urlencode($search_q) : '' ?>" class="cat-pill <?= $filter_status === 'cancelled' ? 'active' : '' ?>">
                    <?= icon('x-circle', '', 13) ?> Đã Hủy
                </a>
            </div>
        </div>
    </div>

    <!-- Danh sách đơn hàng -->
    <div class="manifest-card" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="cart-table" style="margin-bottom: 0;">
            <thead style="background: var(--bg-subtle);">
                <tr>
                    <th style="padding-left: 1.5rem;">MÃ ĐƠN</th>
                    <th>NGÀY TẠO</th>
                    <th>NGƯỜI NHẬN / LIÊN LẠC</th>
                    <th>ĐỊA CHỈ GIAO HÀNG</th>
                    <th>TỔNG TIỀN</th>
                    <th>CẬP NHẬT TRẠNG THÁI (D4)</th>
                    <th style="text-align: right; padding-right: 1.5rem;">CHI TIẾT</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            Chưa có đơn hàng nào trong trạng thái này.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $ord): ?>
                        <tr>
                            <td style="padding-left: 1.5rem; font-family: var(--font-mono); font-size: 0.85rem; font-weight: 700; color: var(--text-primary);">
                                <?= e($ord['order_code']) ?>
                            </td>
                            <td style="font-size: 0.82rem; color: var(--text-secondary);">
                                <?= date('d/m/Y H:i', strtotime($ord['created_at'])) ?>
                            </td>
                            <td>
                                <div style="font-weight: 600; font-size: 0.92rem; color: var(--text-primary);"><?= e($ord['recipient_name']) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-mono);">
                                    <?= e($ord['recipient_phone']) ?> · <?= e($ord['user_email'] ?? '') ?>
                                </div>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--text-secondary); max-width: 220px; line-height: 1.4;">
                                <?= e($ord['shipping_address']) ?>
                            </td>
                            <td style="font-weight: 700; color: var(--text-primary);">
                                <?= format_currency($ord['total_amount']) ?>
                            </td>
                            <td>
                                <!-- TIÊU CHÍ D4: FORM ĐỔI TRẠNG THÁI TRỰC TIẾP -->
                                <form action="<?= BASE_URL ?>admin/orders.php" method="POST" style="margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                    <select name="status" class="sort-select" style="padding: 0.35rem 0.75rem; font-size: 0.82rem; font-weight: 600;" onchange="this.form.submit();">
                                        <option value="pending" <?= $ord['status'] === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                                        <option value="shipping" <?= $ord['status'] === 'shipping' ? 'selected' : '' ?>>Đang giao</option>
                                        <option value="completed" <?= $ord['status'] === 'completed' ? 'selected' : '' ?>>Hoàn tất</option>
                                        <option value="cancelled" <?= $ord['status'] === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                                    </select>
                                </form>
                            </td>
                            <td style="text-align: right; padding-right: 1.5rem;">
                                <a href="<?= BASE_URL ?>order-detail.php?id=<?= $ord['id'] ?>" class="btn-icon" target="_blank" title="Xem chi tiết vận đơn" style="font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <?= icon('eye', '', 13) ?> Chi Tiết
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
