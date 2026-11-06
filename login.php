<?php
/**
 * ĐĂNG NHẬP HỆ THỐNG & RATE-LIMITING (TIÊU CHÍ A3, C2, C9, D10, D5)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Phong cách: Quiet Luxury & Minimalist Daylight
 */

require_once __DIR__ . '/includes/init.php';

// Nếu đã đăng nhập thì chuyển hướng
if (is_logged_in()) {
    header('Location: ' . BASE_URL);
    exit;
}

$page_title = 'Đăng Nhập Tài Khoản';
$error = '';
$email = '';
$is_locked = false;
$lock_remaining = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ Email và Mật khẩu.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // TIÊU CHÍ D10: KIỂM TRA KHÓA FORM NẾU SAI MẬT KHẨU 5 LẦN
            if (!empty($user['locked_until'])) {
                $locked_time = strtotime($user['locked_until']);
                $now = time();

                if ($locked_time > $now) {
                    $is_locked = true;
                    $lock_remaining = ceil(($locked_time - $now) / 60);
                    $error = "Tài khoản tạm thời bị khóa do nhập sai mật khẩu 5 lần liên tiếp. Vui lòng thử lại sau $lock_remaining phút.";
                } else {
                    $reset_stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?");
                    $reset_stmt->execute([$user['id']]);
                    $user['failed_attempts'] = 0;
                    $user['locked_until'] = null;
                }
            }

            if (!$is_locked) {
                if (password_verify($password, $user['password'])) {
                    $reset_stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?");
                    $reset_stmt->execute([$user['id']]);

                    session_regenerate_id(true);

                    $_SESSION['user'] = [
                        'id'      => $user['id'],
                        'name'    => $user['name'],
                        'email'   => $user['email'],
                        'phone'   => $user['phone'],
                        'address' => $user['address'],
                        'role'    => $user['role']
                    ];

                    set_flash('success', 'Chào mừng ' . $user['name'] . ' đã đăng nhập thành công!');

                    $redirect_to = BASE_URL;
                    if (!empty($_SESSION['return_to'])) {
                        $target = $_SESSION['return_to'];
                        unset($_SESSION['return_to']);
                        // Phòng chống tấn công Open Redirect
                        $target_host = parse_url($target, PHP_URL_HOST);
                        if (empty($target_host) || $target_host === ($_SERVER['HTTP_HOST'] ?? '')) {
                            if (strpos($target, '//') !== 0) {
                                $redirect_to = $target;
                            }
                        }
                    } elseif ($user['role'] === 'admin') {
                        $redirect_to = BASE_URL . 'admin/index.php';
                    }

                    header('Location: ' . $redirect_to);
                    exit;
                } else {
                    $attempts = (int)$user['failed_attempts'] + 1;
                    if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                        $locked_until = date('Y-m-d H:i:s', time() + LOCKOUT_DURATION);
                        $lock_stmt = $pdo->prepare("UPDATE users SET failed_attempts = ?, locked_until = ? WHERE id = ?");
                        $lock_stmt->execute([$attempts, $locked_until, $user['id']]);
                        $error = 'Bạn đã nhập sai mật khẩu 5 lần liên tiếp. Form đăng nhập bị khóa trong vòng 5 phút!';
                        $is_locked = true;
                    } else {
                        $fail_stmt = $pdo->prepare("UPDATE users SET failed_attempts = ? WHERE id = ?");
                        $fail_stmt->execute([$attempts, $user['id']]);
                        $remaining = MAX_LOGIN_ATTEMPTS - $attempts;
                        $error = "Mật khẩu không chính xác. Bạn còn $remaining lần thử trước khi bị khóa 5 phút.";
                    }
                }
            }
        } else {
            $error = 'Địa chỉ email này chưa tồn tại trong hệ thống.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-bottom: 5rem;">
    <div class="form-card">
        <span class="hero-tag">ĐĂNG NHẬP</span>
        <h1 style="font-size: 1.6rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--text-primary);">
            Chào Mừng Trở Lại
        </h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <span><?= icon('alert-triangle', '', 16) ?></span>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Form Đăng nhập (Tiêu chí A3: có label, required, minlength) -->
        <form action="<?= BASE_URL ?>login.php" method="POST" id="login-form">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="login-email">Địa chỉ Email: *</label>
                <input type="email" id="login-email" name="email" class="form-control" value="<?= e($email) ?>" placeholder="ten@email.com" required <?= $is_locked ? 'disabled' : '' ?>>
            </div>

            <div class="form-group">
                <label class="form-label" for="login-password">Mật khẩu: *</label>
                <div class="password-wrapper">
                    <input type="password" id="login-password" name="password" class="form-control" placeholder="••••••••" required minlength="6" <?= $is_locked ? 'disabled' : '' ?>>
                    <button type="button" class="password-toggle-btn" title="Hiện/Ẩn mật khẩu">
                        <svg class="svg-icon no-margin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-action-primary" style="width: 100%; justify-content: center; padding: 0.85rem; font-size: 0.95rem;" <?= $is_locked ? 'disabled' : '' ?>>
                <?= $is_locked ? 'Tạm Khóa Form (5 phút)' : icon('lock', '', 16) . ' Đăng Nhập' ?>
            </button>
        </form>

        <!-- Tài khoản mẫu để chấm điểm -->
        <div style="background: var(--bg-elevated); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 1rem; margin-top: 2rem; font-size: 0.8rem; color: var(--text-secondary); line-height: 1.8;">
            <div style="color: var(--text-primary); font-weight: 700; margin-bottom: 0.2rem;">TÀI KHOẢN CHẤM THỬ (MỤC 2 BÁO CÁO):</div>
            <div>• Quản trị viên: <code>admin@lab.ptit.edu.vn</code> / <code>admin123</code></div>
            <div>• Khách hàng: <code>khachhang1@gmail.com</code> / <code>customer123</code></div>
        </div>

        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.88rem; color: var(--text-muted);">
            Chưa có tài khoản? <a href="<?= BASE_URL ?>register.php" style="color: var(--text-primary); font-weight: 600;">Đăng ký thành viên</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
