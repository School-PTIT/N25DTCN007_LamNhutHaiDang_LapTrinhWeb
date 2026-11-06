<?php
/**
 * TRANG BÁO LỖI 404 TÙY BIẾN (TIÊU CHÍ B5 & D5)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Phong cách: Quiet Luxury & Minimalist Daylight
 */

if (!headers_sent()) {
    http_response_code(404);
}

require_once __DIR__ . '/includes/init.php';

$page_title = '404 — Không Tìm Thấy Trang';

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 6rem 1rem; text-align: center;">
    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 4rem 2rem; max-width: 580px; margin: 0 auto; box-shadow: var(--shadow-card);">
        <div style="font-size: 4.5rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.05em; line-height: 1; margin-bottom: 0.5rem;">
            404
        </div>
        <div style="font-size: 0.95rem; font-weight: 600; color: var(--accent-gold); margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 0.05em;">
            Trang Không Tồn Tại
        </div>
        <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 2.25rem; line-height: 1.7;">
            Sản phẩm hoặc đường dẫn bạn đang tìm kiếm không tồn tại hoặc đã được chuyển sang vị trí mới.
        </p>
        <a href="<?= BASE_URL ?>" class="btn-action-primary" style="padding: 0.85rem 2rem; font-size: 0.92rem;">
            <?= icon('arrow-left', '', 15) ?> Quay Về Trang Chủ
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
