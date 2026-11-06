<?php
/**
 * ĐĂNG KÝ TÀI KHOẢN (TIÊU CHÍ A3, A7, C1, D5)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Phong cách: Quiet Luxury & Minimalist Daylight
 */

require_once __DIR__ . '/includes/init.php';

// Nếu đã đăng nhập thì chuyển hướng về trang chủ
if (is_logged_in()) {
    header('Location: ' . BASE_URL);
    exit;
}

$page_title = 'Đăng Ký Tài Khoản Khách Hàng';
$errors = [];

$name = '';
$email = '';
$phone = '';
$address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Xác thực CSRF Token (Tiêu chí D5)
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 2. Tiêu chí C1: Kiểm tra dữ liệu phía máy chủ (Server-side Validation)
    if (empty($name)) {
        $errors[] = 'Họ và tên không được để trống.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Địa chỉ email không đúng định dạng.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Mật khẩu phải có độ dài tối thiểu 6 ký tự.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Mật khẩu xác nhận không khớp.';
    }

    // Tiêu chí C1: Chặn email trùng lặp
    if (empty($errors)) {
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->execute([$email]);
        if ($check_stmt->fetch()) {
            $errors[] = 'Email này đã được đăng ký trong hệ thống. Vui lòng chọn email khác hoặc đăng nhập.';
        }
    }

    // 3. Tiêu chí C1: Lưu mật khẩu bằng password_hash
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, phone, address, role) 
            VALUES (?, ?, ?, ?, ?, 'customer')
        ");
        $stmt->execute([$name, $email, $hashed_password, $phone, $address]);
        $new_user_id = (int)$pdo->lastInsertId();

        // Tự động đăng nhập luôn (Auto-login)
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'      => $new_user_id,
            'name'    => $name,
            'email'   => $email,
            'phone'   => $phone,
            'address' => $address,
            'role'    => 'customer'
        ];

        set_flash('success', "Chào mừng " . e($name) . "! Tài khoản của bạn đã được khởi tạo và tự động đăng nhập.");
        header('Location: ' . BASE_URL);
        exit;
    }
    // Tiêu chí C1: Nếu có lỗi, dữ liệu đã nhập ($name, $email, $phone, $address) được giữ lại tự động trong form
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-bottom: 5rem;">
    <div class="form-card">
        <span class="hero-tag">ĐĂNG KÝ MỚI</span>
        <h1 style="font-size: 1.6rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--text-primary);">
            Tạo Tài Khoản Thành Viên
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

        <!-- Form Đăng ký (Tiêu chí A3 & A7: có label, thuộc tính HTML, id register-form) -->
        <form action="<?= BASE_URL ?>register.php" method="POST" id="register-form">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="reg-name">Họ và tên của bạn: *</label>
                <input type="text" id="reg-name" name="name" class="form-control" value="<?= e($name) ?>" placeholder="Nguyễn Văn A" required minlength="2">
            </div>

            <div class="form-group">
                <label class="form-label" for="reg-email">Địa chỉ Email: *</label>
                <input type="email" id="reg-email" name="email" class="form-control" value="<?= e($email) ?>" placeholder="ten@email.com" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="reg-phone">Số điện thoại liên hệ:</label>
                <input type="tel" id="reg-phone" name="phone" class="form-control" value="<?= e($phone) ?>" pattern="[0-9]{10,11}" placeholder="09xxxxxxxx">
            </div>

            <div class="form-group">
                <label class="form-label" for="reg-address">Địa chỉ nhận hàng mặc định:</label>
                <input type="text" id="reg-address" name="address" class="form-control" value="<?= e($address) ?>" placeholder="Số nhà, tên đường, phường/xã...">
            </div>

            <div class="form-group">
                <label class="form-label" for="reg-password">Mật khẩu bảo mật (Tối thiểu 6 ký tự): *</label>
                <div class="password-wrapper">
                    <input type="password" id="reg-password" name="password" class="form-control" placeholder="••••••••" required minlength="6">
                    <button type="button" class="password-toggle-btn" title="Hiện/Ẩn mật khẩu">
                        <svg class="svg-icon no-margin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="reg-confirm">Xác nhận lại mật khẩu: *</label>
                <div class="password-wrapper">
                    <input type="password" id="reg-confirm" name="confirm_password" class="form-control" placeholder="••••••••" required minlength="6">
                    <button type="button" class="password-toggle-btn" title="Hiện/Ẩn mật khẩu">
                        <svg class="svg-icon no-margin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-action-primary" style="width: 100%; justify-content: center; padding: 0.85rem; font-size: 0.95rem; margin-top: 0.5rem;">
                <?= icon('user', '', 16) ?> Tạo Tài Khoản
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.88rem; color: var(--text-secondary);">
            Đã có tài khoản? <a href="<?= BASE_URL ?>login.php" style="color: var(--text-primary); font-weight: 700; text-decoration: underline;">Đăng nhập ngay</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
