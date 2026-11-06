<?php
/**
 * TRANG GIỎ HÀNG (TIÊU CHÍ C3 & A8)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Phong cách: Quiet Luxury & Minimalist Daylight
 */

require_once __DIR__ . '/includes/init.php';

$page_title = 'Giỏ Hàng Tuyển Chọn';

// Tiêu chí C3: Giá sản phẩm luôn được lấy trực tiếp từ CSDL
$cart_items = get_cart_items_with_db_prices($pdo);
$grand_total = 0;
foreach ($cart_items as $item) {
    $grand_total += $item['subtotal'];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 2rem; padding-bottom: 5rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <span class="hero-tag">GIỎ HÀNG TRỰC TUYẾN</span>
            <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--text-primary);">
                Sản Phẩm Bạn Đã Chọn
            </h1>
        </div>

        <?php if (!empty($cart_items)): ?>
            <!-- Tiêu chí A8: Nút xóa giỏ hàng có hộp xác nhận (Hỗ trợ Ajax) -->
            <form action="<?= BASE_URL ?>cart-update.php" method="POST" novalidate style="margin: 0;" class="cart-clear-form">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="clear">
                <button type="submit" class="btn-icon btn-cart-clear-ajax" data-confirm="Bạn có chắc chắn muốn xóa tất cả sản phẩm khỏi giỏ hàng?" style="color: var(--status-alert);">
                    <?= icon('trash', '', 15) ?> Xóa sạch giỏ hàng
                </button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (empty($cart_items)): ?>
        <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 5rem 2rem; text-align: center; box-shadow: var(--shadow-card);">
            <div style="margin-bottom: 1rem; color: var(--text-muted);"><?= icon('bag', '', 48) ?></div>
            <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);">Giỏ hàng của bạn đang trống</h2>
            <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 2rem;">Hãy khám phá các thiết bị công nghệ cao cấp trong bộ sưu tập của chúng tôi.</p>
            <a href="<?= BASE_URL ?>" class="btn-action-primary" style="padding: 0.85rem 2rem; font-size: 0.92rem;">
                Khám Phá Sản Phẩm
            </a>
        </div>
    <?php else: ?>
        <div class="manifest-card">
            <!-- Bảng danh sách thiết bị giỏ hàng (Tiêu chí A8: Tự tính lại thành tiền bằng JS) -->
            <div class="table-responsive">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th style="width: 45%;">SẢN PHẨM</th>
                            <th style="width: 18%;">ĐƠN GIÁ</th>
                            <th style="width: 15%; text-align: center;">SỐ LƯỢNG</th>
                            <th style="width: 17%; text-align: right;">THÀNH TIỀN</th>
                            <th style="width: 5%; text-align: center;">XÓA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr id="cart-row-<?= $item['id'] ?>" style="transition: all 250ms ease;">
                                <td>
                                    <div style="display: flex; align-items: center; gap: 1.25rem;">
                                        <img src="<?= BASE_URL ?>uploads/<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" style="width: 55px; height: 55px; object-fit: contain; background: #f8f9fa; border-radius: var(--radius-sm); padding: 6px; border: 1px solid var(--border-subtle);">
                                        <div>
                                            <div style="font-weight: 600; font-size: 0.95rem;">
                                                <a href="<?= BASE_URL ?>product.php?id=<?= $item['id'] ?>"><?= e($item['name']) ?></a>
                                            </div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem;">
                                                Mã: <?= e($item['sku']) ?> · Tồn kho: <?= $item['stock'] ?> chiếc
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size: 0.95rem; color: var(--text-secondary); font-weight: 500;">
                                    <?= format_currency($item['price']) ?>
                                </td>
                                <td style="text-align: center;">
                                    <!-- Cụm Stepper tăng giảm tiện lợi kèm thuộc tính data-price, data-stock -->
                                    <div class="qty-stepper">
                                        <button type="button" class="qty-btn qty-btn-minus" title="Giảm">-</button>
                                        <input type="number" 
                                               class="cart-qty-input" 
                                               value="<?= $item['quantity'] ?>" 
                                               min="1" 
                                               max="<?= $item['stock'] ?>"
                                               data-id="<?= $item['id'] ?>"
                                               data-price="<?= $item['price'] ?>"
                                               data-stock="<?= $item['stock'] ?>">
                                        <button type="button" class="qty-btn qty-btn-plus" title="Tăng">+</button>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <span class="cart-subtotal subtotal-<?= $item['id'] ?>" style="font-size: 1rem;">
                                        <?= format_currency($item['subtotal']) ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <form action="<?= BASE_URL ?>cart-update.php" method="POST" novalidate style="margin: 0;" class="cart-remove-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn-icon btn-cart-remove-ajax" data-id="<?= $item['id'] ?>" data-confirm="Xóa sản phẩm này khỏi giỏ?" style="color: var(--status-alert); padding: 0.35rem 0.55rem;" title="Xóa">
                                            <?= icon('trash', 'no-margin', 15) ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tổng tiền & Nút thanh toán -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-subtle); padding-top: 2rem; flex-wrap: wrap; gap: 1.5rem;">
                <a href="<?= BASE_URL ?>" class="nav-link" style="border: 1px solid var(--border-subtle); font-weight: 500; display: inline-flex; align-items: center;">
                    <?= icon('arrow-left', '', 15) ?> Tiếp tục mua sắm
                </a>

                <div style="display: flex; align-items: center; gap: 2.5rem;">
                    <div style="text-align: right;">
                        <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">TỔNG TIỀN THANH TOÁN:</span>
                        <div id="cart-grand-total" style="font-size: 1.85rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em;">
                            <?= format_currency($grand_total) ?>
                        </div>
                    </div>

                    <a href="<?= BASE_URL ?>checkout.php" class="btn-action-primary" style="padding: 0.95rem 2.25rem; font-size: 0.95rem;">
                        Tiến Hành Đặt Hàng <?= icon('arrow-right', '', 16) ?>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
