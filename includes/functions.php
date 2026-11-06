<?php
/**
 * THƯ VIỆN HÀM TIỆN ÍCH & BẢO MẬT HỆ THỐNG
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 */

/**
 * Tiêu chí B3: Mọi dữ liệu in ra HTML đều bọc qua e() chống tấn công XSS
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Tiêu chí D5: Tạo CSRF Token an toàn trong Session
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Sinh thẻ input ẩn chứa CSRF Token cho form POST
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Tiêu chí D5: Xác thực CSRF Token khi nhận yêu cầu POST
 */
function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            if (!empty($_POST['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'CSRF Token không hợp lệ hoặc phiên làm việc đã hết hạn.']);
                exit;
            }
            die('LỖI BẢO MẬT: CSRF Token không hợp lệ hoặc phiên làm việc đã hết hạn. Vui lòng tải lại trang.');
        }
    }
}

/**
 * Quản lý Flash Message (Thông báo một lần - Tiêu chí C2 PRG Pattern)
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type'    => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Kiểm tra trạng thái đăng nhập
 */
function is_logged_in() {
    return !empty($_SESSION['user']);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function is_admin() {
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

/**
 * Yêu cầu người dùng đăng nhập trước khi tiếp tục (Tiêu chí C9)
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
        set_flash('warning', 'Vui lòng đăng nhập để tiếp tục thao tác.');
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}

/**
 * Tiêu chí D1: Phân quyền quản trị
 * Đặt require_admin() ở đầu mọi trang admin/. Tài khoản thường bị từ chối với mã HTTP 403.
 */
function require_admin() {
    if (!is_logged_in()) {
        $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
        set_flash('danger', 'Vui lòng đăng nhập tài khoản quản trị viên.');
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }

    if (!is_admin()) {
        http_response_code(403);
        include ROOT_PATH . '404.php'; // Hoặc trang thông báo 403 chuyên dụng
        exit;
    }
}

/**
 * Định dạng tiền tệ Việt Nam (VNĐ)
 */
function format_currency($amount) {
    return number_format($amount, 0, ',', '.') . ' ₫';
}

/**
 * Chuyển đổi chuỗi tiếng Việt thành Slug URL thân thiện
 */
function slugify($text) {
    $text = preg_replace('/[áàảãạăắằẳẵặâấầẩẫậ]/u', 'a', $text);
    $text = preg_replace('/[éèẻẽẹêếềểễệ]/u', 'e', $text);
    $text = preg_replace('/[íìỉĩị]/u', 'i', $text);
    $text = preg_replace('/[óòỏõọôốồổỗộơớờởỡợ]/u', 'o', $text);
    $text = preg_replace('/[úùủũụưứừửữự]/u', 'u', $text);
    $text = preg_replace('/[ýỳỷỹỵ]/u', 'y', $text);
    $text = preg_replace('/[đ]/u', 'd', $text);
    $text = preg_replace('/[^a-z0-9\s-]/i', '', $text);
    $text = preg_replace('/[\s-]+/', '-', trim($text));
    return strtolower($text);
}

/**
 * Đếm tổng số lượng món hàng trong giỏ (Session Cart)
 */
function cart_count() {
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return 0;
    }
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += (int)($item['quantity'] ?? 0);
    }
    return $total;
}

/**
 * Tính tổng tiền giỏ hàng (Luôn đối soát giá từ CSDL - Tiêu chí C3)
 */
function get_cart_items_with_db_prices($pdo) {
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return [];
    }

    $product_ids = array_keys($_SESSION['cart']);
    if (empty($product_ids)) {
        return [];
    }

    // Truy vấn giá mới nhất từ CSDL
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt = $pdo->prepare("SELECT id, name, sku, slug, price, stock, image FROM products WHERE id IN ($placeholders)");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll();

    $cart_items = [];
    foreach ($products as $prod) {
        $qty = (int)($_SESSION['cart'][$prod['id']]['quantity'] ?? 1);
        if ($qty > $prod['stock']) {
            $qty = $prod['stock']; // Không vượt quá tồn kho
            $_SESSION['cart'][$prod['id']]['quantity'] = $qty;
        }

        $cart_items[] = [
            'id'       => $prod['id'],
            'name'     => $prod['name'],
            'sku'      => $prod['sku'],
            'slug'     => $prod['slug'],
            'price'    => (float)$prod['price'],
            'stock'    => (int)$prod['stock'],
            'image'    => $prod['image'],
            'quantity' => $qty,
            'subtotal' => (float)$prod['price'] * $qty
        ];
    }

    return $cart_items;
}

/**
 * THƯ VIỆN SVG VECTOR ICON CHUẨN THƯƠNG MẠI ĐIỆN TỬ (QUIET LUXURY)
 * Thay thế toàn bộ OS emoji bằng vector sắc nét, kế thừa màu chữ (currentColor)
 */
function icon($name, $class = '', $size = 18) {
    $icons = [
        'bag' => '<path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>',
        'cart' => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>',
        'user' => '<path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'search' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        'sliders' => '<line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/>',
        'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
        'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'arrow-up' => '<line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/>',
        'chevron-down' => '<polyline points="6 9 12 15 18 9"/>',
        'chevron-right' => '<polyline points="9 18 15 12 9 6"/>',
        'lock' => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>',
        'shield-check' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>',
        'truck' => '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'check-circle' => '<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
        'x-circle' => '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
        'x' => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
        'alert-triangle' => '<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'trash' => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/>',
        'edit' => '<path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>',
        'plus' => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'save' => '<path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>',
        'external-link' => '<path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>',
        'eye' => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'dashboard' => '<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>',
        'box' => '<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
        'folder' => '<path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>',
        'receipt' => '<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1z"/><line x1="8" y1="7" x2="16" y2="7"/><line x1="8" y1="11" x2="16" y2="11"/><line x1="8" y1="15" x2="12" y2="15"/>',
        'keyboard' => '<rect x="2" y="4" width="20" height="16" rx="2"/><line x1="6" y1="8" x2="6.01" y2="8"/><line x1="10" y1="8" x2="10.01" y2="8"/><line x1="14" y1="8" x2="14.01" y2="8"/><line x1="18" y1="8" x2="18.01" y2="8"/><line x1="6" y1="12" x2="6.01" y2="12"/><line x1="10" y1="12" x2="10.01" y2="12"/><line x1="14" y1="12" x2="14.01" y2="12"/><line x1="18" y1="12" x2="18.01" y2="12"/><line x1="7" y1="16" x2="17" y2="16"/>',
        'laptop' => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M2 20h20"/>',
        'headphones' => '<path d="M3 18v-6a9 9 0 0118 0v6"/><path d="M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3zM3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3z"/>',
        'mouse' => '<rect x="6" y="3" width="12" height="18" rx="6"/><line x1="12" y1="7" x2="12" y2="11"/>',
        'logout' => '<path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>'
    ];

    $path = $icons[$name] ?? '<circle cx="12" cy="12" r="10"/>';
    $class_attr = !empty($class) ? ' ' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') : '';
    
    return sprintf(
        '<svg class="svg-icon%s" width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%s</svg>',
        $class_attr,
        (int)$size,
        (int)$size,
        $path
    );
}
