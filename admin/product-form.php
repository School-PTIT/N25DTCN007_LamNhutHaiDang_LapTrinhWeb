<?php
/**
 * THÊM / SỬA SẢN PHẨM & UPLOAD AN TOÀN (TIÊU CHÍ D1, D2, D3, D7)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Phong cách: Quiet Luxury & Minimalist Daylight
 */

require_once __DIR__ . '/../includes/init.php';

require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = ($id > 0);

$product = [
    'id'             => 0,
    'category_id'    => 1,
    'sku'            => '',
    'name'           => '',
    'slug'           => '',
    'price'          => 0,
    'stock'          => 10,
    'image'          => '',
    'description'    => '',
    'specifications' => ''
];

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) {
        set_flash('danger', 'Không tìm thấy thiết bị cần chỉnh sửa.');
        header('Location: ' . BASE_URL . 'admin/products.php');
        exit;
    }
}

$page_title = $is_edit ? 'Cập Nhật Thiết Bị: ' . $product['name'] : 'Thêm Thiết Bị Mới Vào Kho';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $category_id = (int)($_POST['category_id'] ?? 1);
    $sku = trim($_POST['sku'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $specifications_raw = trim($_POST['specifications'] ?? '');

    // Tạo slug
    $slug = slugify($name);

    if (empty($sku)) $errors[] = 'Mã SKU không được để trống.';
    if (empty($name)) $errors[] = 'Tên thiết bị không được để trống.';
    if ($price <= 0) $errors[] = 'Giá bán phải lớn hơn 0 VNĐ.';
    if ($stock < 0) $errors[] = 'Số lượng tồn kho không được âm.';

    // Kiểm tra trùng SKU
    $check_sku = $pdo->prepare("SELECT id FROM products WHERE sku = ? AND id != ?");
    $check_sku->execute([$sku, $id]);
    if ($check_sku->fetch()) {
        $errors[] = "Mã SKU '$sku' đã tồn tại trong hệ thống kho.";
    }

    // Xử lý specifications: Chuyển đổi định dạng Key: Value thành JSON nếu cần
    $specs_json = $specifications_raw;
    if (!empty($specifications_raw) && json_decode($specifications_raw) === null) {
        $lines = explode("\n", $specifications_raw);
        $specs_arr = [];
        foreach ($lines as $line) {
            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $specs_arr[trim($parts[0])] = trim($parts[1]);
            }
        }
        $specs_json = json_encode($specs_arr, JSON_UNESCAPED_UNICODE);
    }

    // TIÊU CHÍ D3: TẢI ẢNH AN TOÀN (DUNG LƯỢNG, NỘI DUNG ẢNH, ĐẶT TÊN NGẪU NHIÊN)
    $image_name = $product['image'];
    $uploaded_new_image = false;

    if (!empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];
        $max_size = 2 * 1024 * 1024; // 2MB

        // 1. Giới hạn dung lượng
        if ($file['size'] > $max_size) {
            $errors[] = 'Dung lượng ảnh vượt quá giới hạn 2MB cho phép.';
        }

        // 2. Kiểm tra nội dung tệp thực tế (MIME Type an toàn)
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_mimes)) {
            $errors[] = 'Tệp tải lên không phải là ảnh hợp lệ (Chỉ chấp nhận JPG, PNG, WEBP, SVG).';
        }

        // Kiểm tra an toàn đối với tệp SVG (Chống Stored XSS)
        if ($mime_type === 'image/svg+xml') {
            $svg_content = file_get_contents($file['tmp_name']);
            if (preg_match('/<script|onload|onerror|onclick|javascript:/i', $svg_content)) {
                $errors[] = 'Tệp SVG chứa mã script không an toàn và bị hệ thống từ chối tải lên.';
            }
        }

        // 3. Đặt tên ngẫu nhiên (UUID/uniqid)
        if (empty($errors)) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (empty($ext) || $ext === 'svg') $ext = 'svg';
            $image_name = uniqid('prod_', true) . '.' . strtolower($ext);
            $destination = UPLOAD_DIR . $image_name;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $uploaded_new_image = true;
            } else {
                $errors[] = 'Không thể lưu tệp ảnh vào máy chủ.';
            }
        }
    }

    if (!$is_edit && empty($image_name)) {
        $errors[] = 'Vui lòng chọn ảnh cho sản phẩm mới.';
    }

    // Lưu dữ liệu vào CSDL
    if (empty($errors)) {
        if ($is_edit) {
            // TIÊU CHÍ D7: KHI SỬA VÀ TẢI ẢNH MỚI -> XÓA TỆP ẢNH CŨ TRONG UPLOADS/
            if ($uploaded_new_image && !empty($product['image']) && $product['image'] !== $image_name) {
                $old_file = UPLOAD_DIR . $product['image'];
                if (file_exists($old_file) && is_file($old_file)) {
                    unlink($old_file); // Xóa file ảnh cũ tránh rác ổ cứng
                }
            }

            $stmt = $pdo->prepare("
                UPDATE products 
                SET category_id = ?, sku = ?, name = ?, slug = ?, price = ?, stock = ?, image = ?, description = ?, specifications = ? 
                WHERE id = ?
            ");
            $stmt->execute([$category_id, $sku, $name, $slug, $price, $stock, $image_name, $description, $specs_json, $id]);
            set_flash('success', "Cập nhật thiết bị '{$name}' thành công!");
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO products (category_id, sku, name, slug, price, stock, image, description, specifications) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$category_id, $sku, $name, $slug, $price, $stock, $image_name, $description, $specs_json]);
            set_flash('success', "Thêm sản phẩm mới '{$name}' vào kho thành công!");
        }

        header('Location: ' . BASE_URL . 'admin/products.php');
        exit;
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Thanh điều hướng phụ cho quản trị viên -->
<?php require_once __DIR__ . '/../includes/admin-nav.php'; ?>

<div class="container" style="padding-bottom: 5rem;">
    <div style="max-width: 800px; margin: 0 auto; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 2.5rem; box-shadow: var(--shadow-card);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
                <span class="hero-tag" style="margin-bottom: 0.4rem;"><?= $is_edit ? 'CHỈNH SỬA SẢN PHẨM' : 'THÊM MỚI SẢN PHẨM' ?></span>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin: 0;">
                    <?= $is_edit ? 'Cập Nhật Thiết Bị: ' . e($product['name']) : 'Thêm Thiết Bị Vào Kho Studio' ?>
                </h1>
            </div>
            <a href="<?= BASE_URL ?>admin/products.php" class="btn-icon" style="font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                <?= icon('arrow-left', '', 14) ?> Quay Lại Kho
            </a>
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

        <!-- Form Thêm / Sửa Sản Phẩm (Tiêu chí D2, D3, D7) -->
        <form action="<?= BASE_URL ?>admin/product-form.php<?= $is_edit ? '?id=' . $product['id'] : '' ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="prod-sku">Mã linh kiện (SKU): *</label>
                    <input type="text" id="prod-sku" name="sku" class="form-control" value="<?= e($product['sku']) ?>" placeholder="VD: KB-8700-PRO" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="prod-cat">Phân hệ danh mục: *</label>
                    <select id="prod-cat" name="category_id" class="form-control">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="prod-name">Tên sản phẩm đầy đủ: *</label>
                <input type="text" id="prod-name" name="name" class="form-control" value="<?= e($product['name']) ?>" placeholder="Tên sản phẩm thiết bị..." required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="prod-price">Giá niêm yết (VNĐ): *</label>
                    <input type="number" id="prod-price" name="price" class="form-control" value="<?= e($product['price']) ?>" required min="1000">
                </div>

                <div class="form-group">
                    <label class="form-label" for="prod-stock">Số lượng tồn kho: *</label>
                    <input type="number" id="prod-stock" name="stock" class="form-control" value="<?= e($product['stock']) ?>" required min="0">
                </div>
            </div>

            <!-- TIÊU CHÍ D3 & D7: TẢI ẢNH AN TOÀN -->
            <div class="form-group" style="background: #fafafc; border: 1px dashed var(--border-subtle); border-radius: var(--radius-sm); padding: 1.5rem;">
                <label class="form-label" for="prod-img">
                    Hình ảnh sản phẩm (Tối đa 2MB, định dạng JPG, PNG, WEBP, SVG): <?= $is_edit ? '' : '*' ?>
                </label>
                <input type="file" id="prod-img" name="image" class="form-control" accept="image/*" <?= $is_edit ? '' : 'required' ?> style="background: #ffffff;">
                
                <!-- Khung xem trước ảnh mới tải lên (Live Preview) -->
                <div id="preview-wrapper" style="margin-top: 1rem; display: flex; align-items: center; gap: 1rem;">
                    <img id="prod-img-preview" src="#" alt="Xem trước ảnh" class="admin-thumb" style="display: none; width: 70px; height: 70px; object-fit: contain; border: 2px solid var(--status-live); padding: 4px;">
                </div>
                
                <?php if ($is_edit && !empty($product['image'])): ?>
                    <div style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem;">
                        <img src="<?= BASE_URL ?>uploads/<?= e($product['image']) ?>" alt="Ảnh hiện tại" class="admin-thumb" style="width: 60px; height: 60px;">
                        <div>
                            <div style="font-size: 0.82rem; font-weight: 600; color: var(--text-primary);"><?= e($product['image']) ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Ảnh cũ sẽ tự động được xóa khỏi máy chủ nếu bạn chọn ảnh mới.</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="prod-desc">Mô tả tổng quan sản phẩm:</label>
                <textarea id="prod-desc" name="description" rows="3" class="form-control" placeholder="Mô tả các đặc điểm nổi bật..."><?= e($product['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="prod-specs">
                    Bảng thông số kỹ thuật A2 (Mỗi dòng một cặp: <code>Tên thông số: Giá trị</code>):
                </label>
                <textarea id="prod-specs" name="specifications" rows="5" class="form-control" style="font-family: var(--font-mono); font-size: 0.85rem; line-height: 1.6;" placeholder="Trọng lượng: 1.2 kg&#10;Kết nối: USB-C, Bluetooth 5.3&#10;Bảo hành: 24 tháng"><?php
                    if (!empty($product['specifications'])) {
                        $decoded = json_decode($product['specifications'], true);
                        if (is_array($decoded)) {
                            foreach ($decoded as $k => $v) {
                                echo e($k . ': ' . $v) . "\n";
                            }
                        } else {
                            echo e($product['specifications']);
                        }
                    }
                ?></textarea>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn-action-primary" style="flex: 1; justify-content: center; padding: 0.85rem; font-size: 0.95rem;">
                    <?= icon('save', '', 16) ?> <?= $is_edit ? 'Lưu Thay Đổi Sản Phẩm' : 'Thêm Vào Kho Sản Phẩm' ?>
                </button>
                <a href="<?= BASE_URL ?>admin/products.php" class="btn-icon" style="padding: 0.85rem 1.5rem; display: inline-flex; align-items: center;">
                    Hủy Bỏ
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
