<?php
/**
 * QUẢN LÝ DANH MỤC THIẾT BỊ (TIÊU CHÍ D1 & D6)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Phong cách: Quiet Luxury & Minimalist Daylight
 */

require_once __DIR__ . '/../includes/init.php';

require_admin();

$page_title = 'Quản Lý Danh Mục Thiết Bị';
$errors = [];

// Xử lý các thao tác POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    // 1. TIÊU CHÍ D6: THÊM DANH MỤC MỚI
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $slug = slugify($name);

        if (empty($name)) {
            $errors[] = 'Tên danh mục không được để trống.';
        } else {
            $check = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
            $check->execute([$slug]);
            if ($check->fetch()) {
                $errors[] = 'Danh mục này đã tồn tại trong hệ thống.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
                $stmt->execute([$name, $slug, $description]);
                set_flash('success', "Thêm danh mục '{$name}' thành công!");
                header('Location: ' . BASE_URL . 'admin/categories.php');
                exit;
            }
        }
    }

    // 2. TIÊU CHÍ D6: ĐỔI TÊN DANH MỤC
    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $slug = slugify($name);

        if (empty($name)) {
            $errors[] = 'Tên danh mục không được để trống.';
        } else {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $description, $id]);
            set_flash('success', "Đổi tên danh mục thành '{$name}' thành công!");
            header('Location: ' . BASE_URL . 'admin/categories.php');
            exit;
        }
    }

    // 3. TIÊU CHÍ D6: XÓA DANH MỤC (KHÔNG XÓA ĐƯỢC DANH MỤC ĐANG CÓ SẢN PHẨM)
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);

        // Kiểm tra số lượng sản phẩm đang thuộc danh mục này
        $check_prod = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $check_prod->execute([$id]);
        $prod_count = (int)$check_prod->fetchColumn();

        if ($prod_count > 0) {
            set_flash('danger', "BỊ CHẶN: Không thể xóa danh mục này vì đang chứa {$prod_count} sản phẩm! Hãy chuyển hoặc xóa sản phẩm trước. (Ràng buộc toàn vẹn dữ liệu Tiêu chí D6)");
        } else {
            $del_stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $del_stmt->execute([$id]);
            set_flash('success', 'Đã xóa danh mục thành công!');
        }
        header('Location: ' . BASE_URL . 'admin/categories.php');
        exit;
    }
}

// Lấy danh sách danh mục kèm số lượng sản phẩm
$query = "
    SELECT c.*, COUNT(p.id) AS product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.id ASC
";
$categories = $pdo->query($query)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Thanh điều hướng phụ cho quản trị viên -->
<?php require_once __DIR__ . '/../includes/admin-nav.php'; ?>

<div class="container" style="padding-bottom: 5rem;">
    <!-- Tiêu đề trang -->
    <div style="margin-bottom: 2rem;">
        <span class="hero-tag" style="margin-bottom: 0.4rem;">CẤU TRÚC PHÂN LOẠI</span>
        <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--text-primary); margin: 0;">
            Quản Lý Danh Mục Thiết Bị
        </h1>
        <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">
            Tiêu chí D6: Thêm, đổi tên trực tiếp, và chặn xóa danh mục đang chứa sản phẩm
        </div>
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

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: flex-start;">
        
        <!-- Form Thêm Danh Mục Mới (Tiêu chí D6) -->
        <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 2rem; box-shadow: var(--shadow-card);">
            <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.4rem;">
                <?= icon('plus', '', 16) ?> Thêm Danh Mục Mới
            </h2>

            <form action="<?= BASE_URL ?>admin/categories.php" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label class="form-label" for="cat-name">Tên danh mục mới: *</label>
                    <input type="text" id="cat-name" name="name" class="form-control" placeholder="VD: Phụ Kiện Custom" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="cat-desc">Mô tả tóm tắt:</label>
                    <textarea id="cat-desc" name="description" rows="3" class="form-control" placeholder="Mô tả tiêu chuẩn của phân hệ..."></textarea>
                </div>

                <button type="submit" class="btn-action-primary" style="width: 100%; justify-content: center; padding: 0.8rem; font-size: 0.92rem; margin-top: 0.5rem;">
                    <?= icon('plus', '', 15) ?> Tạo Danh Mục
                </button>
            </form>
        </div>

        <!-- Danh sách danh mục & Đổi tên / Xóa (Tiêu chí D6) -->
        <div class="manifest-card" style="padding: 0; overflow: hidden;">
            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-subtle); background: #fafafc;">
                <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                    Các Phân Hệ Đang Hoạt Động (<?= count($categories) ?> danh mục)
                </h2>
            </div>

            <table class="cart-table" style="margin-bottom: 0;">
                <thead style="background: #fafafc;">
                    <tr>
                        <th style="padding-left: 1.5rem;">TÊN DANH MỤC / SLUG</th>
                        <th style="text-align: center;">SẢN PHẨM</th>
                        <th style="text-align: right; padding-right: 1.5rem;">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td style="padding-left: 1.5rem;">
                                <!-- Form inline để đổi tên danh mục (Tiêu chí D6) -->
                                <form action="<?= BASE_URL ?>admin/categories.php" method="POST" style="display: flex; gap: 0.5rem; align-items: center; margin: 0;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                    <input type="text" name="name" value="<?= e($cat['name']) ?>" class="form-control" style="padding: 0.4rem 0.75rem; font-size: 0.9rem; font-weight: 600; width: auto; min-width: 180px;">
                                    <button type="submit" class="btn-icon" title="Lưu thay đổi tên" style="font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('save', '', 13) ?> Lưu
                                    </button>
                                </form>
                                <div style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                                    Đường dẫn slug: /<?= e($cat['slug']) ?>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge-status badge-shipping">
                                    <?= $cat['product_count'] ?> sản phẩm
                                </span>
                            </td>
                            <td style="text-align: right; padding-right: 1.5rem;">
                                <!-- TIÊU CHÍ D6: XÓA DANH MỤC (CHẶN NẾU ĐANG CÓ SẢN PHẨM) -->
                                <form action="<?= BASE_URL ?>admin/categories.php" method="POST" style="margin: 0; display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                    <button type="submit" 
                                            class="btn-icon" 
                                            data-confirm="Bạn có chắc chắn muốn xóa danh mục '<?= e($cat['name']) ?>' không?" 
                                            style="color: var(--status-alert); border-color: rgba(239, 68, 68, 0.3); font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.25rem;"
                                            <?= $cat['product_count'] > 0 ? 'title="Danh mục đang có sản phẩm (sẽ bị hệ thống chặn xóa)"' : 'title="Xóa danh mục"' ?>>
                                        <?= icon('trash', '', 13) ?> Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
