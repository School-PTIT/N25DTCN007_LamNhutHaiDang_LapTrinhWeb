<?php
/**
 * TRANG HỒ SƠ TÀI KHOẢN (TIÊU CHÍ C7 & D5)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Phong cách: Quiet Luxury & Minimalist Daylight
 */

require_once __DIR__ . '/includes/init.php';

// Bắt buộc đăng nhập
require_login();

$page_title = 'Hồ Sơ Tài Khoản Cá Nhân';
$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Xác thực CSRF Token
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name)) {
        $errors[] = 'Họ và tên không được để trống.';
    }

    // Tiêu chí C7: Cập nhật thông tin vào CSDL
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET name = ?, phone = ?, address = ? 
            WHERE id = ?
        ");
        $stmt->execute([$name, $phone, $address, $user['id']]);

        // Cập nhật lại session
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['phone'] = $phone;
        $_SESSION['user']['address'] = $address;

        set_flash('success', 'Thông tin tài khoản của bạn đã được cập nhật thành công!');
        header('Location: ' . BASE_URL . 'profile.php');
        exit;
    }
}

// Lấy thông tin mới nhất từ CSDL
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$user_data = $stmt->fetch();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 3rem; padding-bottom: 5rem;">
    <div style="max-width: 600px; margin: 0 auto; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 2.5rem; box-shadow: var(--shadow-card);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
                <span class="hero-tag" style="margin-bottom: 0.4rem;">TÀI KHOẢN CỦA BẠN</span>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin: 0;">
                    Thông Tin Cá Nhân
                </h1>
            </div>
            <span class="badge-status <?= $user_data['role'] === 'admin' ? 'badge-completed' : 'badge-shipping' ?>">
                <?= $user_data['role'] === 'admin' ? icon('shield-check', '', 14) . ' Quản Trị Viên' : icon('user', '', 14) . ' Khách Hàng' ?>
            </span>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <span><?= icon('alert-triangle', '', 16) ?></span>
                <div>
                    <?php foreach ($errors as $err): ?>
                        <div><?= e($err) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- TIÊU CHÍ C7: SỬA HỌ TÊN, SỐ ĐIỆN THOẠI, ĐỊA CHỈ -->
        <form action="<?= BASE_URL ?>profile.php" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label">Địa chỉ Email (Định danh không đổi):</label>
                <input type="text" class="form-control" value="<?= e($user_data['email']) ?>" disabled style="opacity: 0.7; cursor: not-allowed; background: var(--bg-elevated);">
            </div>

            <div class="form-group">
                <label class="form-label" for="prof-name">Họ và tên của bạn: *</label>
                <input type="text" id="prof-name" name="name" class="form-control" value="<?= e($user_data['name']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="prof-phone">Số điện thoại liên lạc:</label>
                <input type="tel" id="prof-phone" name="phone" class="form-control" value="<?= e($user_data['phone']) ?>" pattern="[0-9]{10,11}">
            </div>

            <div class="form-group">
                <label class="form-label" for="prof-address">Địa chỉ giao nhận hàng mặc định:</label>
                <input type="text" id="prof-address" name="address" class="form-control" value="<?= e($user_data['address']) ?>">
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem; flex-wrap: wrap;">
                <button type="submit" class="btn-action-primary" style="flex: 1; justify-content: center; padding: 0.85rem; font-size: 0.92rem;">
                    <?= icon('save', '', 16) ?> Lưu Thay Đổi
                </button>
                <a href="<?= BASE_URL ?>change-password.php" class="btn-icon" style="padding: 0.85rem 1.25rem; display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600;">
                    <?= icon('lock', '', 15) ?> Đổi Mật Khẩu
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
