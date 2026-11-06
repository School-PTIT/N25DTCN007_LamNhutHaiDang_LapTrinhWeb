<?php
/**
 * ĐỔI MẬT KHẨU TÀI KHOẢN (TIÊU CHÍ D11 & D5)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Phong cách: Quiet Luxury & Minimalist Daylight
 */

require_once __DIR__ . '/includes/init.php';

require_login();

$page_title = 'Đổi Mật Khẩu Bảo Mật';
$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Xác thực CSRF Token
    verify_csrf();

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 2. Lấy hash mật khẩu hiện tại trong CSDL
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $db_user = $stmt->fetch();

    // TIÊU CHÍ D11: BẮT BUỘC NHẬP ĐÚNG MẬT KHẨU HIỆN TẠI
    if (!password_verify($current_password, $db_user['password'])) {
        $errors[] = 'Mật khẩu hiện tại không chính xác.';
    }

    if (strlen($new_password) < 6) {
        $errors[] = 'Mật khẩu mới phải có độ dài tối thiểu 6 ký tự.';
    }

    if ($new_password !== $confirm_password) {
        $errors[] = 'Xác nhận mật khẩu mới không khớp.';
    }

    // 3. Cập nhật mật khẩu mới
    if (empty($errors)) {
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_stmt->execute([$new_hash, $user['id']]);

        set_flash('success', 'Đổi mật khẩu thành công! Mật khẩu mới đã có hiệu lực.');
        header('Location: ' . BASE_URL . 'profile.php');
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 3rem; padding-bottom: 5rem;">
    <div class="form-card">
        <span class="hero-tag">BẢO MẬT TÀI KHOẢN</span>
        <h1 style="font-size: 1.6rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--text-primary);">
            Thiết Lập Mật Khẩu Mới
        </h1>

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

        <!-- Form Đổi Mật Khẩu (Tiêu chí D11) -->
        <form action="<?= BASE_URL ?>change-password.php" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="cur-pass">Mật khẩu hiện tại của bạn: *</label>
                <div class="password-wrapper">
                    <input type="password" id="cur-pass" name="current_password" class="form-control" placeholder="••••••••" required>
                    <button type="button" class="password-toggle-btn" title="Hiện/Ẩn mật khẩu">
                        <svg class="svg-icon no-margin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="new-pass">Mật khẩu mới (Tối thiểu 6 ký tự): *</label>
                <div class="password-wrapper">
                    <input type="password" id="new-pass" name="new_password" class="form-control" placeholder="••••••••" required minlength="6">
                    <button type="button" class="password-toggle-btn" title="Hiện/Ẩn mật khẩu">
                        <svg class="svg-icon no-margin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="conf-pass">Xác nhận mật khẩu mới: *</label>
                <div class="password-wrapper">
                    <input type="password" id="conf-pass" name="confirm_password" class="form-control" placeholder="••••••••" required minlength="6">
                    <button type="button" class="password-toggle-btn" title="Hiện/Ẩn mật khẩu">
                        <svg class="svg-icon no-margin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-action-primary" style="width: 100%; justify-content: center; padding: 0.85rem; font-size: 0.95rem; margin-top: 0.5rem;">
                <?= icon('lock', '', 16) ?> Cập Nhật Mật Khẩu
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="<?= BASE_URL ?>profile.php" style="color: var(--text-secondary); font-size: 0.88rem; text-decoration: underline; display: inline-flex; align-items: center; gap: 0.3rem;">
                <?= icon('arrow-left', '', 14) ?> Quay lại trang Hồ sơ
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
