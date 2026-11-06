<?php
/**
 * QUẢN LÝ SẢN PHẨM & PHÂN TRANG (TIÊU CHÍ D1, D2)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Phong cách: Quiet Luxury & Minimalist Daylight
 */

require_once __DIR__ . '/../includes/init.php';

require_admin();

$page_title = 'Quản Lý Sản Phẩm Thiết Bị';

// 1. Phân trang sản phẩm trong Admin
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$total_stmt = $pdo->query("SELECT COUNT(*) FROM products");
$total_products = (int)$total_stmt->fetchColumn();
$total_pages = max(1, (int)ceil($total_products / $limit));

// 2. Truy vấn danh sách sản phẩm
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    ORDER BY p.id DESC 
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Thanh điều hướng phụ cho quản trị viên -->
<?php require_once __DIR__ . '/../includes/admin-nav.php'; ?>

<div class="container" style="padding-bottom: 5rem;">
    <!-- Tiêu đề trang & Nút thêm mới -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <span class="hero-tag" style="margin-bottom: 0.4rem;">KHO SẢN PHẨM THIẾT BỊ</span>
            <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--text-primary); margin: 0;">
                Danh Mục Thiết Bị Studio
            </h1>
            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">
                Hiện có <strong><?= $total_products ?></strong> sản phẩm đang được quản lý
            </div>
        </div>

        <a href="<?= BASE_URL ?>admin/product-form.php" class="btn-action-primary" style="padding: 0.7rem 1.35rem; font-size: 0.88rem;">
            <?= icon('plus', '', 14) ?> Thêm Sản Phẩm Mới
        </a>
    </div>

    <!-- TIÊU CHÍ D2: DANH SÁCH CÓ PHÂN TRANG, THÊM, SỬA, XÓA -->
    <div class="manifest-card" style="padding: 0; overflow: hidden;">
        <table class="cart-table" style="margin-bottom: 0;">
            <thead style="background: var(--bg-subtle);">
                <tr>
                    <th style="width: 80px; padding-left: 1.5rem;">ẢNH</th>
                    <th>MÃ SKU / TÊN THIẾT BỊ</th>
                    <th>DANH MỤC</th>
                    <th>GIÁ NIÊM YẾT</th>
                    <th>TỒN KHO</th>
                    <th style="text-align: right; width: 180px; padding-right: 1.5rem;">THAO TÁC</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td style="padding-left: 1.5rem;">
                            <img src="<?= BASE_URL ?>uploads/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" class="admin-thumb" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\' fill=\'%23f3f4f6\'><rect width=\'100\' height=\'100\'/></svg>'">
                        </td>
                        <td>
                            <div style="font-weight: 600; font-size: 0.94rem; color: var(--text-primary);"><?= e($p['name']) ?></div>
                            <div style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">
                                SKU: <?= e($p['sku']) ?> · /<?= e($p['slug']) ?>
                            </div>
                        </td>
                        <td style="color: var(--text-secondary); font-size: 0.88rem;">
                            <?= e($p['category_name']) ?>
                        </td>
                        <td style="font-weight: 600; color: var(--text-primary);">
                            <?= format_currency($p['price']) ?>
                        </td>
                        <td>
                            <span class="badge-status <?= $p['stock'] <= 3 ? 'badge-cancelled' : 'badge-completed' ?>">
                                <?= $p['stock'] ?> chiếc
                            </span>
                        </td>
                        <td style="text-align: right; padding-right: 1.5rem;">
                            <div style="display: inline-flex; gap: 0.4rem; align-items: center;">
                                <a href="<?= BASE_URL ?>admin/product-form.php?id=<?= $p['id'] ?>" class="btn-icon" title="Chỉnh sửa sản phẩm" style="font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <?= icon('edit', '', 13) ?> Sửa
                                </a>

                                <!-- TIÊU CHÍ D2: XÓA BẰNG PHƯƠNG THỨC POST VÀ CHỐNG CSRF -->
                                <form action="<?= BASE_URL ?>admin/product-delete.php" method="POST" style="margin: 0; display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn-icon" data-confirm="Bạn có chắc chắn muốn xóa sản phẩm này không? (Nếu sản phẩm đã có trong đơn hàng sẽ bị từ chối xóa)" style="color: var(--status-alert); border-color: rgba(239, 68, 68, 0.3); font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.25rem;" title="Xóa sản phẩm">
                                        <?= icon('trash', '', 13) ?> Xóa
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Phân trang (Tiêu chí D8 & D2) -->
        <?php if ($total_pages > 1): ?>
            <div style="padding: 1.5rem; border-top: 1px solid var(--border-subtle); display: flex; justify-content: center;">
                <div class="pagination" style="margin: 0;">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="<?= BASE_URL ?>admin/products.php?page=<?= $i ?>" class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
