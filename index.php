<?php
/**
 * TRANG CHỦ PHÒNG LAB CÔNG NGHỆ (TIÊU CHÍ B4, B6, B7, D8, A4, A5)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Phong cách: Quiet Luxury & Minimalist Daylight
 */

require_once __DIR__ . '/includes/init.php';

$page_title = 'Bộ Sưu Tập Thiết Bị Công Nghệ';

// 1. Thu thập bộ lọc & Từ khóa tìm kiếm (Tiêu chí B4)
$category_id = isset($_GET['cat']) ? (int)$_GET['cat'] : (isset($_GET['category']) ? (int)$_GET['category'] : 0);
$keyword = trim($_GET['q'] ?? '');

// 2. Sắp xếp an toàn theo Whitelist (Tiêu chí B6: Không ghép tham số vào SQL)
$sort = $_GET['sort'] ?? 'newest';
$sort_whitelist = [
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'newest'     => 'p.id DESC'
];
$order_by = $sort_whitelist[$sort] ?? 'p.id DESC';

// 3. Xây dựng điều kiện truy vấn động (Dùng 100% Prepared Statements)
$where_clauses = ['1=1'];
$params = [];

if ($category_id > 0) {
    $where_clauses[] = 'p.category_id = ?';
    $params[] = $category_id;
}

if (!empty($keyword)) {
    $where_clauses[] = '(p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)';
    $like = '%' . $keyword . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$where_sql = implode(' AND ', $where_clauses);

// 4. Đếm tổng số lượng kết quả (Tiêu chí B7 & D8)
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE $where_sql");
$count_stmt->execute($params);
$total_products = (int)$count_stmt->fetchColumn();

// 5. Tính toán phân trang 8 sản phẩm/trang (Tiêu chí D8)
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = PRODUCTS_PER_PAGE;
$total_pages = max(1, (int)ceil($total_products / $limit));
if ($page > $total_pages) {
    $page = $total_pages;
}
$offset = ($page - 1) * $limit;

// 6. Truy vấn danh sách sản phẩm trang hiện tại
$query = "
    SELECT p.*, c.name AS category_name, c.slug AS category_slug 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE $where_sql 
    ORDER BY $order_by 
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// 7. Lấy danh sách toàn bộ danh mục để hiển thị Bộ điều hướng
$categories = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- HERO SECTION: TINH TẾ & THOÁNG ĐÃNG -->
<section class="hero-section">
    <div class="container">
        <div class="hero-box">
            <span class="hero-tag">BỘ SƯU TẬP CAO CẤP 2026</span>
            <h1 class="hero-title">
                Thiết Bị Công Nghệ & Studio Tuyển Chọn
            </h1>
            <p class="hero-desc">
                Tuyển chọn những sản phẩm phần cứng tinh tế với trải nghiệm gõ phím cơ xúc giác, âm thanh trung thực và hiệu năng vượt bậc, mang dấu ấn cá nhân của nhà sáng lập Lâm Nhựt Hải Đăng.
            </p>
        </div>
    </div>
</section>

<!-- BỘ LỌC DANH MỤC, TÌM KIẾM & LƯỚI SẢN PHẨM (AJAX ZERO-RELOAD CONTAINER) -->
<div id="catalog-ajax-container" style="transition: opacity 150ms ease;">
<section class="container control-bar">
    <!-- Cụm nút Danh mục dạng Pills tròn sang trọng -->
    <div class="category-pills">
        <a href="<?= BASE_URL ?>" class="cat-pill <?= $category_id === 0 ? 'active' : '' ?>">
            Tất cả sản phẩm
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?= BASE_URL ?>?cat=<?= $cat['id'] ?><?= !empty($keyword) ? '&q=' . urlencode($keyword) : '' ?><?= $sort !== 'newest' ? '&sort=' . e($sort) : '' ?>" 
               class="cat-pill <?= $category_id == $cat['id'] ? 'active' : '' ?>">
                <?= e($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Thanh tìm kiếm & Sắp xếp -->
    <div class="filter-actions">
        <!-- Form tìm kiếm theo tên (Tiêu chí B4) -->
        <form action="<?= BASE_URL ?>" method="GET" class="search-form" id="catalog-search-form">
            <?php if ($category_id > 0): ?>
                <input type="hidden" name="cat" value="<?= $category_id ?>">
            <?php endif; ?>
            <?php if ($sort !== 'newest'): ?>
                <input type="hidden" name="sort" value="<?= e($sort) ?>">
            <?php endif; ?>
            <input type="text" name="q" id="catalog-search-input" value="<?= e($keyword) ?>" placeholder="Tìm kiếm nhanh thiết bị, mã SKU..." class="search-input" autocomplete="off">
            <button type="button" class="search-clear-btn" id="catalog-search-clear" title="Xóa tìm kiếm" style="<?= empty($keyword) ? 'display: none;' : '' ?>"><?= icon('x', 'no-margin', 14) ?></button>
            <button type="submit" class="search-btn" title="Tìm kiếm"><?= icon('search', 'no-margin', 16) ?></button>
        </form>

        <div style="display: flex; align-items: center; gap: 1.5rem;">
            <!-- Tiêu chí B7: Hiển thị thông báo Tìm thấy N sản phẩm -->
            <div class="result-count" id="catalog-result-count">
                Tìm thấy <strong><?= $total_products ?></strong> sản phẩm
            </div>

            <!-- Tiêu chí B6: Bộ chọn Sắp xếp theo giá tăng/giảm -->
            <form action="<?= BASE_URL ?>" method="GET" class="sort-group" id="sort-form">
                <?php if ($category_id > 0): ?>
                    <input type="hidden" name="cat" value="<?= $category_id ?>">
                <?php endif; ?>
                <?php if (!empty($keyword)): ?>
                    <input type="hidden" name="q" value="<?= e($keyword) ?>">
                <?php endif; ?>
                <label for="sort-select" style="font-weight: 500; display: inline-flex; align-items: center; gap: 0.3rem;">
                    <?= icon('sliders', '', 14) ?> Sắp xếp:
                </label>
                <select name="sort" id="sort-select" class="sort-select">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
                </select>
            </form>
        </div>
    </div>
</section>

<!-- LƯỚI SẢN PHẨM RESPONSIVE (TIÊU CHÍ A4 & A5) -->
<section class="container" id="catalog-grid-section" style="padding-top: 0.5rem;">
    <?php if (empty($products)): ?>
        <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 4rem 2rem; text-align: center; box-shadow: var(--shadow-card);">
            <div style="margin-bottom: 0.75rem; color: var(--text-muted);"><?= icon('box', '', 44) ?></div>
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem;">Không tìm thấy sản phẩm phù hợp</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">Vui lòng thử lại với từ khóa khác hoặc xóa bộ lọc danh mục.</p>
            <a href="<?= BASE_URL ?>" class="btn-action-primary" style="padding: 0.65rem 1.5rem;">Xem Tất Cả Sản Phẩm</a>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php $card_idx = 0; ?>
            <?php foreach ($products as $prod): ?>
                <article class="precision-card" style="--stagger: <?= $card_idx ?>;">
                    <!-- Top Metadata -->
                    <div class="card-top-meta">
                        <span class="sku-badge"><?= e($prod['sku']) ?></span>
                        <?php if ($prod['stock'] <= 3): ?>
                            <span class="stock-tag low" title="Sắp hết hàng">
                                <span class="status-indicator-dot dot-warning"></span> Còn <?= $prod['stock'] ?> chiếc
                            </span>
                        <?php else: ?>
                            <span class="stock-tag ready">
                                <span class="status-indicator-dot dot-ready"></span> Sẵn hàng
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Ảnh sản phẩm -->
                    <a href="<?= BASE_URL ?>product.php?id=<?= $prod['id'] ?>" class="card-img-wrap">
                        <img src="<?= BASE_URL ?>uploads/<?= e($prod['image']) ?>" alt="<?= e($prod['name']) ?>" loading="lazy">
                    </a>

                    <!-- Nội dung thẻ -->
                    <div class="card-body">
                        <h2 class="card-title">
                            <a href="<?= BASE_URL ?>product.php?id=<?= $prod['id'] ?>">
                                <?= e($prod['name']) ?>
                            </a>
                        </h2>

                        <!-- Micro-spec Chips -->
                        <?php 
                        $specs = json_decode($prod['specifications'] ?? '', true);
                        if (!empty($specs) && is_array($specs)):
                            $chip_count = 0;
                        ?>
                            <div class="spec-chips">
                                <?php foreach ($specs as $key => $val): ?>
                                    <?php if ($chip_count < 2): ?>
                                        <span class="spec-chip" title="<?= e($key . ': ' . $val) ?>"><?= e(format_spec_chip($val)) ?></span>
                                        <?php $chip_count++; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Footer Card: Giá & Nút hành động -->
                        <div class="card-footer-meta">
                            <div class="card-price">
                                <?= format_currency($prod['price']) ?>
                            </div>
                            <div class="card-actions">
                                <a href="<?= BASE_URL ?>product.php?id=<?= $prod['id'] ?>" class="btn-icon" title="Xem chi tiết">
                                    Chi tiết
                                </a>
                                <form action="<?= BASE_URL ?>cart-add.php" method="POST" style="margin: 0; width: 100%;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn-action-primary btn-add-cart-ajax" data-id="<?= $prod['id'] ?>" data-name="<?= e($prod['name']) ?>" title="Thêm vào giỏ">
                                        <?= icon('plus', '', 13) ?> Thêm
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            <?php $card_idx++; ?>
            <?php endforeach; ?>
        </div>

        <!-- PHÂN TRANG (TIÊU CHÍ D8: Giữ bộ lọc danh mục và từ khóa khi chuyển trang) -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php
                $query_params = [];
                if ($category_id > 0) $query_params['cat'] = $category_id;
                if (!empty($keyword)) $query_params['q'] = $keyword;
                if ($sort !== 'newest') $query_params['sort'] = $sort;

                for ($i = 1; $i <= $total_pages; $i++):
                    $query_params['page'] = $i;
                    $page_url = BASE_URL . '?' . http_build_query($query_params);
                ?>
                    <a href="<?= e($page_url) ?>" class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
