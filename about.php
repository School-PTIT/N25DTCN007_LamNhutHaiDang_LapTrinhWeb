<?php
/**
 * TRANG GIỚI THIỆU PHÒNG LAB (TIÊU CHÍ A2: CÓ BẢNG THÔNG SỐ)
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Phong cách: Quiet Luxury & Minimalist Daylight
 */

require_once __DIR__ . '/includes/init.php';

$page_title = 'Giới Thiệu Studio & Nhà Sáng Lập';

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 2rem; padding-bottom: 5rem;">
    <!-- Giới thiệu chính -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 3rem; margin-bottom: 3rem; box-shadow: var(--shadow-card);">
        <span class="hero-tag">ABOUT OUR STUDIO</span>
        <h1 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 1.25rem; color: var(--text-primary);">
            Không Gian Công Nghệ Tuyển Chọn<br>
            Sáng Lập Bởi <span style="color: var(--accent-gold);"><?= e(APP_FOUNDER) ?></span>
        </h1>
        <p style="color: var(--text-secondary); font-size: 1.05rem; line-height: 1.8; margin-bottom: 1.5rem; max-width: 75ch;">
            Chào mừng bạn đến với Studio Công nghệ được xây dựng bởi sinh viên <strong><?= e(APP_FOUNDER) ?></strong> (Mã số sinh viên: <strong><?= e(APP_STUDENT_ID) ?></strong>) tại Học viện Công nghệ Bưu chính Viễn thông (PTIT). Định hướng của chúng tôi là mang đến những thiết bị phần cứng tối giản, tinh tế, sở hữu độ hoàn thiện cơ học cao nhất phục vụ cho các lập trình viên, nhà thiết kế và người yêu công nghệ.
        </p>
        <p style="color: var(--text-secondary); font-size: 0.98rem; line-height: 1.8; max-width: 75ch;">
            Mỗi thiết bị trong bộ sưu tập—từ bàn phím cơ custom vỏ nhôm nguyên khối, tai nghe màng từ phẳng Planar đến laptop máy trạm hiệu năng cao—đều trải qua quy trình kiểm thử và đánh giá thông số độc lập nghiêm ngặt.
        </p>
    </div>

    <!-- TIÊU CHÍ A2: BẢNG THÔNG SỐ STUDIO & HỒ SƠ SINH VIÊN -->
    <div class="spec-sheet-container">
        <div class="spec-sheet-header">
            <span>BẢNG THÔNG SỐ ĐỒ ÁN & THƯƠNG HIỆU (TIÊU CHÍ A2)</span>
            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">HỌC VIỆN CÔNG NGHỆ BƯU CHÍNH VIỄN THÔNG</span>
        </div>
        <table class="spec-table">
            <tbody>
                <tr>
                    <th>Nhà sáng lập / Sinh viên thực hiện</th>
                    <td><strong><?= e(APP_FOUNDER) ?></strong></td>
                </tr>
                <tr>
                    <th>Mã số sinh viên (MSSV)</th>
                    <td><strong style="color: var(--text-primary); font-family: var(--font-mono);"><?= e(APP_STUDENT_ID) ?></strong></td>
                </tr>
                <tr>
                    <th>Học viện đào tạo</th>
                    <td>Học viện Công nghệ Bưu chính Viễn thông (PTIT)</td>
                </tr>
                <tr>
                    <th>Môn học & Giảng viên phụ trách</th>
                    <td>Lập trình Web (HTML, CSS, JavaScript, PHP thuần, PDO, MySQL) — @Le Nhat Tung</td>
                </tr>
                <tr>
                    <th>Tiêu chuẩn kỹ thuật đồ án</th>
                    <td>Đáp ứng đầy đủ 38 tiêu chí A1–D11, bảo mật chống SQLi/XSS/CSRF, phân quyền 403</td>
                </tr>
                <tr>
                    <th>Chính sách bảo hành thiết bị</th>
                    <td>Bảo hành chính hãng 24 tháng theo số serial định danh</td>
                </tr>
                <tr>
                    <th>Địa chỉ trung tâm trải nghiệm</th>
                    <td><?= e(APP_ADDRESS) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
