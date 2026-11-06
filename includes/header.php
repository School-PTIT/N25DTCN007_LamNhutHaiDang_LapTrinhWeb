<?php
/**
 * PHẦN ĐẦU TRANG DÙNG CHUNG (TIÊU CHÍ A1 & A6)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Phong cách: Quiet Luxury & Minimalist Daylight
 */

$current_page = basename($_SERVER['SCRIPT_NAME'] ?? '');
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? e($page_title) . ' — ' : '' ?><?= e(APP_NAME) ?> // <?= e(APP_SUB_TITLE) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css?v=3.0">
    <script>window.BASE_URL = '<?= BASE_URL ?>'; window.CSRF_TOKEN = '<?= csrf_token() ?>';</script>
</head>
<body>

<!-- 1. THANH TIỆN ÍCH ĐỈNH TRANG (ĐƠN GIẢN & SANG TRỌNG) -->
<div class="top-strip">
    <div class="container">
        <div class="top-strip-brand">
            <span class="top-live-dot"></span>
            <span>Studio & Cửa hàng Chính thức của <strong><?= e(APP_FOUNDER) ?></strong></span>
        </div>
        <div>
            <span>MSSV: <strong><?= e(APP_STUDENT_ID) ?></strong></span>
            <span style="margin: 0 0.5rem; opacity: 0.4;">·</span>
            <span>Giao hàng bảo đảm & Đồng kiểm</span>
        </div>
    </div>
</div>

<!-- 2. HEADER CHÍNH (GLASSMORPHIC & TỐI GIẢN) -->
<header class="main-header">
    <div class="container">
        <div class="header-inner">
            <!-- Logo Chân dung & Tên sinh viên (Mục 2 đề thi cá nhân hóa) -->
            <a href="<?= BASE_URL ?>" class="founder-brand">
                <div class="founder-avatar-frame">
                    <img src="<?= BASE_URL ?>uploads/avatar.jpg" alt="Chân dung Lâm Nhựt Hải Đăng - N25DTCN007" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\' fill=\'%23111827\'><circle cx=\'50\' cy=\'50\' r=\'48\' fill=\'%23f3f4f6\'/><circle cx=\'50\' cy=\'40\' r=\'20\' fill=\'%23334155\'/><path d=\'M22,85 C22,65 35,60 50,60 C65,60 78,65 78,85 Z\' fill=\'%23334155\'/></svg>'">
                    <span class="founder-live-badge" title="Studio Founder Trực Tuyến"></span>
                </div>
                <div class="founder-text">
                    <div class="founder-name"><?= e(APP_NAME) ?></div>
                    <div class="founder-sub">STUDIO & BOUTIQUE · EST. 2026</div>
                </div>
            </a>

            <!-- Menu Điều Hướng (Tiêu chí A6: Đánh dấu trang hiện tại .active) -->
            <nav class="main-nav">
                <a href="<?= BASE_URL ?>" class="nav-link <?= in_array($current_page, ['index.php', '']) ? 'active' : '' ?>">
                    Trang Chủ
                </a>
                <a href="<?= BASE_URL ?>about.php" class="nav-link <?= $current_page === 'about.php' ? 'active' : '' ?>">
                    Giới Thiệu
                </a>
                <a href="<?= BASE_URL ?>contact.php" class="nav-link <?= $current_page === 'contact.php' ? 'active' : '' ?>">
                    Liên Hệ
                </a>
                <?php if (is_admin()): ?>
                    <a href="<?= BASE_URL ?>admin/index.php" class="nav-link" style="color: var(--accent-gold); font-weight: 600;">
                        <?= icon('dashboard', '', 15) ?> Quản Trị Studio
                    </a>
                <?php endif; ?>
            </nav>

            <!-- Tiện Ích: Giỏ Hàng & Tài Khoản -->
            <div class="header-actions">
                <a href="<?= BASE_URL ?>cart.php" class="cart-pill" title="Mở giỏ hàng">
                    <?= icon('bag', '', 16) ?>
                    <span>Giỏ hàng</span>
                    <span class="cart-counter" id="header-cart-count"><?= cart_count() ?></span>
                </a>

                <?php if (is_logged_in()): ?>
                    <div style="display: flex; align-items: center; gap: 0.35rem; flex-shrink: 0; white-space: nowrap;">
                        <a href="<?= BASE_URL ?>orders.php" class="nav-link <?= $current_page === 'orders.php' ? 'active' : '' ?>">
                            <?= icon('receipt', '', 15) ?> Đơn hàng
                        </a>
                        <a href="<?= BASE_URL ?>profile.php" class="nav-link <?= $current_page === 'profile.php' ? 'active' : '' ?>" style="font-weight: 600;">
                            <?= icon('user', '', 15) ?> <?= e(mb_substr(current_user()['name'], 0, 10)) ?>...
                        </a>
                        <a href="<?= BASE_URL ?>logout.php" class="btn-icon" title="Đăng xuất" style="padding: 0.4rem 0.65rem; color: var(--status-alert); flex-shrink: 0;">
                            <?= icon('logout', 'no-margin', 14) ?>
                        </a>
                    </div>
                <?php else: ?>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <a href="<?= BASE_URL ?>login.php" class="nav-link"><?= icon('user', '', 15) ?> Đăng Nhập</a>
                        <a href="<?= BASE_URL ?>register.php" class="btn-action-primary" style="padding: 0.45rem 1rem;">Đăng Ký</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- THÔNG BÁO FLASH MESSAGE (TIÊU CHÍ C2) -->
<?php if ($flash): ?>
<div class="container" style="margin-top: 1.25rem;">
    <div class="alert alert-<?= e($flash['type']) ?>" style="display: flex; align-items: center; gap: 0.65rem;">
        <span><?= $flash['type'] === 'success' ? icon('check-circle', '', 18) : icon('alert-triangle', '', 18) ?></span>
        <span><?= e($flash['message']) ?></span>
    </div>
</div>
<?php endif; ?>

<main class="main-content">
