<?php
/**
 * THANH ĐIỀU HƯỚNG QUẢN TRỊ VIÊN (QUIET LUXURY ADMIN SUBBAR)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 */

$admin_page = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<div class="admin-subbar">
    <div class="container admin-subbar-inner">
        <div class="admin-subbar-badge">
            <span class="admin-subbar-dot"></span>
            <span>HỆ THỐNG QUẢN TRỊ STUDIO // <?= e(APP_STUDENT_ID) ?></span>
        </div>

        <nav class="admin-nav-tabs">
            <a href="<?= BASE_URL ?>admin/index.php" class="admin-tab <?= $admin_page === 'index.php' ? 'active' : '' ?>">
                <?= icon('dashboard', '', 15) ?> Tổng Quan
            </a>
            <a href="<?= BASE_URL ?>admin/products.php" class="admin-tab <?= in_array($admin_page, ['products.php', 'product-form.php']) ? 'active' : '' ?>">
                <?= icon('box', '', 15) ?> Sản Phẩm
            </a>
            <a href="<?= BASE_URL ?>admin/categories.php" class="admin-tab <?= $admin_page === 'categories.php' ? 'active' : '' ?>">
                <?= icon('folder', '', 15) ?> Danh Mục
            </a>
            <a href="<?= BASE_URL ?>admin/orders.php" class="admin-tab <?= $admin_page === 'orders.php' ? 'active' : '' ?>">
                <?= icon('receipt', '', 15) ?> Đơn Hàng
            </a>
        </nav>

        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="<?= BASE_URL ?>" class="btn-icon" target="_blank" title="Mở trang khách hàng trong tab mới" style="font-size: 0.8rem;">
                <?= icon('external-link', '', 14) ?> Xem Store Khách
            </a>
            <a href="<?= BASE_URL ?>logout.php" class="btn-icon" style="color: var(--status-alert); border-color: rgba(239, 68, 68, 0.3); font-size: 0.8rem;">
                <?= icon('logout', '', 14) ?> Đăng Xuất
            </a>
        </div>
    </div>
</div>
