</main>

<!-- CHÂN TRANG BẮT BUỘC (QUY ĐỊNH MỤC 2 & 5 ĐỀ THI) -->
<footer class="main-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <div class="footer-brand-title"><?= e(APP_NAME) ?></div>
                <p style="margin-bottom: 0.8rem; line-height: 1.7; max-width: 48ch;">
                    Không gian công nghệ & thiết bị ngoại vi cao cấp được tuyển chọn tỉ mỉ. Tối ưu cho trải nghiệm lập trình, sáng tạo và giải trí đỉnh cao.
                </p>
                <!-- DÒNG ĐỊNH DANH BẮT BUỘC THEO ĐỀ THI -->
                <div class="footer-mandatory-id">
                    MSSV: <?= e(APP_STUDENT_ID) ?> — HỌ VÀ TÊN: <?= e(APP_FOUNDER) ?>
                </div>
            </div>

            <div>
                <h4 style="color: var(--text-primary); margin-bottom: 0.85rem; font-size: 0.9rem; font-weight: 700;">HỆ THỐNG</h4>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.5rem;">
                    <li><a href="<?= BASE_URL ?>" class="nav-link" style="padding: 0;">Trang Chủ Thiết Bị</a></li>
                    <li><a href="<?= BASE_URL ?>about.php" class="nav-link" style="padding: 0;">Giới Thiệu Studio</a></li>
                    <li><a href="<?= BASE_URL ?>contact.php" class="nav-link" style="padding: 0;">Liên Hệ & Tư Vấn</a></li>
                    <li><a href="<?= BASE_URL ?>cart.php" class="nav-link" style="padding: 0;">Giỏ Hàng Của Bạn</a></li>
                </ul>
            </div>

            <div>
                <h4 style="color: var(--text-primary); margin-bottom: 0.85rem; font-size: 0.9rem; font-weight: 700;">THÔNG TIN ĐỒ ÁN</h4>
                <div style="font-size: 0.85rem; line-height: 1.8; color: var(--text-secondary);">
                    <div>Học viện CN Bưu chính Viễn thông</div>
                    <div>Học phần: Lập trình Web PTIT</div>
                    <div>Giảng viên phụ trách: @Le Nhat Tung</div>
                    <div>Hotline: <?= e(APP_HOTLINE) ?></div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div>© 2026 <?= e(APP_NAME) ?>. Tất cả quyền được bảo lưu.</div>
            <div>Thiết kế theo tiêu chuẩn đồ án cá nhân PTIT</div>
        </div>
    </div>
</footer>

<!-- Nút cuộn nhanh lên đầu trang (Back to top) -->
<button type="button" id="back-to-top" title="Cuộn lên đầu trang">
    <?= icon('arrow-up', 'no-margin', 18) ?>
</button>

<script src="<?= BASE_URL ?>js/main.js?v=4.9"></script>
</body>
</html>
