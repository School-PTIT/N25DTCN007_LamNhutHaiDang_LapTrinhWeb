<?php
/**
 * TRANG CHI TIẾT SẢN PHẨM & BẢNG THÔNG SỐ (TIÊU CHÍ B5, A2, B7)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Phong cách: Quiet Luxury & Minimalist Daylight
 */

require_once __DIR__ . '/includes/init.php';

// 1. Kiểm tra tham số id sản phẩm
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    exit;
}

// 2. Truy vấn chi tiết sản phẩm bằng Prepared Statement (Tiêu chí B3 & B5)
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name, c.slug AS category_slug 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$id]);
$product = $stmt->fetch();

// Tiêu chí B5: Trả về mã 404 khi không tìm thấy sản phẩm trong CSDL
if (!$product) {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    exit;
}

$page_title = $product['name'] . ' (' . $product['sku'] . ')';

// 3. Tiêu chí B7: Lấy tối đa 3 sản phẩm liên quan cùng danh mục
$rel_stmt = $pdo->prepare("
    SELECT * FROM products 
    WHERE category_id = ? AND id != ? 
    ORDER BY id DESC 
    LIMIT 3
");
$rel_stmt->execute([$product['category_id'], $id]);
$related_products = $rel_stmt->fetchAll();

// 4. Giải mã bảng thông số kỹ thuật (Tiêu chí A2)
$specs = json_decode($product['specifications'] ?? '', true);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 1.5rem; padding-bottom: 4rem;">
    <!-- Breadcrumbs thanh lịch -->
    <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 1.75rem;">
        <a href="<?= BASE_URL ?>" style="color: var(--text-secondary);">Trang chủ</a> 
        <span style="margin: 0 0.4rem; opacity: 0.4;">/</span> 
        <a href="<?= BASE_URL ?>?cat=<?= $product['category_id'] ?>" style="color: var(--text-secondary);"><?= e($product['category_name']) ?></a>
        <span style="margin: 0 0.4rem; opacity: 0.4;">/</span> 
        <span style="color: var(--text-primary); font-weight: 500;"><?= e($product['name']) ?></span>
    </div>

    <!-- KHỐI CHI TIẾT SẢN PHẨM -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-bottom: 3.5rem; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 2.5rem; box-shadow: var(--shadow-card);">
        <!-- Cột trái: Ảnh sản phẩm khung Studio -->
        <div style="background: #f8f9fa; border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 2.5rem; display: flex; align-items: center; justify-content: center;">
            <img src="<?= BASE_URL ?>uploads/<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" style="max-height: 340px; width: auto; object-fit: contain;">
        </div>

        <!-- Cột phải: Thông tin, Giá & Mua hàng -->
        <div style="display: flex; flex-direction: column;">
            <div style="font-size: 0.78rem; font-weight: 600; color: var(--accent-gold); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                <?= e($product['category_name']) ?> · Mã: <?= e($product['sku']) ?>
            </div>

            <h1 style="font-size: 1.85rem; font-weight: 800; line-height: 1.3; margin-bottom: 0.75rem; color: var(--text-primary);">
                <?= e($product['name']) ?>
            </h1>

            <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin-bottom: 1.25rem;">
                <?= format_currency($product['price']) ?>
            </div>

            <!-- Tình trạng tồn kho -->
            <div style="margin-bottom: 1.5rem; font-size: 0.88rem; font-weight: 500;">
                Tình trạng kho: 
                <?php if ($product['stock'] > 0): ?>
                    <span style="color: var(--status-live); font-weight: 600; display: inline-flex; align-items: center; gap: 0.45rem;">
                        <span class="status-indicator-dot dot-ready"></span> Sẵn hàng (Còn <?= $product['stock'] ?> chiếc)
                    </span>
                <?php else: ?>
                    <span style="color: var(--status-alert); font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem;">
                        <?= icon('x-circle', '', 14) ?> Tạm hết hàng
                    </span>
                <?php endif; ?>
            </div>

            <!-- Mô tả ngắn -->
            <div style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.7; margin-bottom: 2rem; border-left: 2px solid var(--brand-black); padding-left: 1.25rem;">
                <?= nl2br(e($product['description'])) ?>
            </div>

            <!-- Form Thêm Giỏ Hàng & Mua Ngay -->
            <?php if ($product['stock'] > 0): ?>
                <form action="<?= BASE_URL ?>cart-add.php" method="POST" style="margin-top: auto; display: flex; flex-wrap: wrap; align-items: center; gap: 1rem;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div style="display: flex; align-items: center; gap: 0.65rem; font-size: 0.88rem; font-weight: 600;">
                        <span>Số lượng:</span>
                        <div class="qty-stepper">
                            <button type="button" class="qty-btn qty-btn-minus" title="Giảm số lượng">-</button>
                            <input type="number" id="qty-input" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" class="cart-qty-input" data-stock="<?= $product['stock'] ?>">
                            <button type="button" class="qty-btn qty-btn-plus" title="Tăng số lượng">+</button>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                        <button type="submit" class="btn-action-primary" style="padding: 0.85rem 1.75rem; font-size: 0.92rem;">
                            <?= icon('bag', '', 18) ?> Thêm Vào Giỏ
                        </button>
                        <button type="submit" name="express" value="1" class="btn-express-buy" title="Thêm vào giỏ và tiến hành thanh toán ngay lập tức">
                            <?= icon('check', '', 16) ?> Mua Ngay
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- TIÊU CHÍ A2: BẢNG THÔNG SỐ KỸ THUẬT SANG TRỌNG -->
    <div class="spec-sheet-container">
        <div class="spec-sheet-header">
            <span>BẢNG THÔNG SỐ KỸ THUẬT (TIÊU CHÍ A2)</span>
            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">KIỂM ĐỊNH CHÍNH HÃNG</span>
        </div>
        <table class="spec-table">
            <tbody>
                <tr>
                    <th>Mã sản phẩm (SKU)</th>
                    <td><?= e($product['sku']) ?></td>
                </tr>
                <tr>
                    <th>Danh mục thiết bị</th>
                    <td><?= e($product['category_name']) ?></td>
                </tr>
                <tr>
                    <th>Số lượng tồn kho</th>
                    <td><?= $product['stock'] ?> chiếc sẵn sàng xuất kho</td>
                </tr>
                <?php if (!empty($specs) && is_array($specs)): ?>
                    <?php foreach ($specs as $param => $val): ?>
                        <tr>
                            <th><?= e($param) ?></th>
                            <td><?= e($val) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- TIÊU CHÍ B7: CUỐI TRANG CHI TIẾT HIỂN THỊ TỐI ĐA 3 SẢN PHẨM CÙNG DANH MỤC -->
    <?php if (!empty($related_products)): ?>
        <div style="margin-top: 4rem;">
            <div style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1.5rem;">
                Sản phẩm cùng danh mục (Tối đa 3 sản phẩm)
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
                <?php foreach ($related_products as $rel): ?>
                    <article class="precision-card">
                        <div class="card-top-meta">
                            <span class="sku-badge"><?= e($rel['sku']) ?></span>
                            <span class="stock-tag ready"><span class="status-indicator-dot dot-ready"></span> Còn <?= $rel['stock'] ?></span>
                        </div>
                        <a href="<?= BASE_URL ?>product.php?id=<?= $rel['id'] ?>" class="card-img-wrap">
                            <img src="<?= BASE_URL ?>uploads/<?= e($rel['image']) ?>" alt="<?= e($rel['name']) ?>" style="max-height: 130px;">
                        </a>
                        <div class="card-body">
                            <h3 class="card-title" style="font-size: 0.92rem; height: 2.5rem;">
                                <a href="<?= BASE_URL ?>product.php?id=<?= $rel['id'] ?>"><?= e($rel['name']) ?></a>
                            </h3>
                            <div class="card-footer-meta">
                                <div class="card-price" style="font-size: 1rem;"><?= format_currency($rel['price']) ?></div>
                                <a href="<?= BASE_URL ?>product.php?id=<?= $rel['id'] ?>" class="btn-action-primary" style="padding: 0.35rem 0.85rem; font-size: 0.8rem;">
                                    Chi tiết
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
