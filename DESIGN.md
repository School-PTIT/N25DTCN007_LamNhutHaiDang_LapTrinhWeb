# BẢNG THIẾT KẾ ĐỘC BẢN: QUIET LUXURY & DAYLIGHT STUDIO (DESIGN.MD)
> **Thương hiệu**: **LÂM NHỰT HẢI ĐĂNG // STUDIO & BOUTIQUE**  
> **Sinh viên**: **Lâm Nhựt Hải Đăng** — MSSV: **N25DTCN007**  
> **Học viện**: Học viện Công nghệ Bưu chính Viễn thông (PTIT)  
> **Ngôn ngữ thẩm mỹ**: **Quiet Luxury & Minimalist Daylight** (Lấy cảm hứng từ *Apple Store*, *Bang & Olufsen* và *Leica Studio*)

---

## 1. Triết Lý Thiết Kế "Đơn Giản & Sang Trọng"
- **Tối giản (Minimalism)**: Loại bỏ hoàn toàn các dải thông tin rườm rà, mã vạch trang trí, góc chữ thập hoặc viền phát sáng chói lọi. Không gian trắng (whitespace) hào phóng giúp sản phẩm trở thành tâm điểm của thị giác.
- **Sang trọng (Quiet Luxury)**: 
  - Nền ngọc trai ấm dịu mắt (`#fafafc`) kết hợp bề mặt thẻ trắng tinh khiết (`#ffffff`).
  - Chữ màu đen than quý phái (`#111827`) với độ tương phản cao, dễ đọc tuyệt đối.
  - Viền **Hairline 1px** siêu mảnh (`#e5e7eb`), bóng đổ mềm như ánh sáng tự nhiên (`0 4px 20px rgba(0,0,0,0.04)`).
  - Điểm nhấn bằng sắc đen tuyền tối thượng (`#111827`) và ánh vàng Champagne nhẹ nhàng (`#c5a059`).
- **Nhận diện Cá nhân hóa Tinh Tế**:
  - Logo chân dung **Lâm Nhựt Hải Đăng** được lồng trong khung tròn viền bạch kim thanh lịch, góc phải đính chấm xanh trực tuyến kín đáo.
  - Chữ thương hiệu `LÂM NHỰT HẢI ĐĂNG` sử dụng phông chữ hình học cao cấp, đi kèm phụ đề thanh nhã `STUDIO & ATELIER · EST. 2026`.
  - Chân trang ghi rõ định danh bắt buộc: `MSSV: N25DTCN007 — HỌ VÀ TÊN: LÂM NHỰT HẢI ĐĂNG`.

---

## 2. Bảng Màu Chuẩn (CSS Custom Properties trong `:root`)

```css
:root {
  /* Nền & Bề mặt (Luminous Daylight Surfaces) */
  --bg-body: #fafafc;              /* Nền ngọc trai dịu mát, không lóa */
  --bg-surface: #ffffff;           /* Bề mặt thẻ, bảng điều khiển trắng tinh */
  --bg-elevated: #f4f4f6;          /* Nền phụ, thẻ tag nhẹ nhàng */
  --bg-header: rgba(255, 255, 255, 0.88); /* Kính mờ Header cao cấp */
  --bg-hover: #f1f2f4;

  /* Viền & Đổ bóng (Hairline & Soft Natural Elevation) */
  --border-subtle: #e5e7eb;        /* Viền mảnh 1px thanh lịch */
  --border-hover: #d1d5db;         /* Khi rê chuột */
  --border-focus: #111827;         /* Khi chọn ô nhập */
  --shadow-subtle: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);
  --shadow-card: 0 4px 20px -2px rgba(0, 0, 0, 0.04), 0 2px 6px -1px rgba(0, 0, 0, 0.02);
  --shadow-hover: 0 12px 30px -4px rgba(0, 0, 0, 0.08);

  /* Màu chữ & Tương phản (High-Clarity Typography) */
  --text-primary: #111827;         /* Đen than sang trọng, độ tương phản 16:1 */
  --text-secondary: #4b5563;       /* Xám trung tính cho mô tả */
  --text-muted: #9ca3af;           /* Xám nhạt cho chú thích */

  /* Màu thương hiệu & Điểm nhấn (Timeless Accents) */
  --brand-black: #111827;          /* Nút chính đen tuyền thời thượng */
  --brand-black-hover: #1f2937;
  --accent-gold: #c5a059;          /* Vàng Champagne cao cấp */
  --status-live: #059669;          /* Xanh ngọc lục bảo: Còn hàng, thành công */
  --status-pending: #d97706;       /* Hổ phách: Chờ xử lý */
  --status-alert: #dc2626;         /* Đỏ rượu: Hủy đơn, báo lỗi */

  /* Phông chữ & Tỷ lệ */
  --font-main: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  --font-mono: 'JetBrains Mono', 'Consolas', monospace;

  /* Bo góc công nghệ cao */
  --radius-sm: 8px;
  --radius-md: 14px;
  --radius-lg: 20px;
  --radius-full: 9999px;
  --transition-smooth: 200ms cubic-bezier(0.16, 1, 0.3, 1);
}
```

---

## 3. Kiến Trúc Giao Diện Độc Bản

### A. Thẻ Sản Phẩm "Minimalist Luxury Card"
- Khung trắng tinh khôi bo tròn nhẹ nhàng 14px, viền mảnh tự nhiên, không đổ bóng đậm màu.
- Ảnh sản phẩm đặt trong khung nền sáng studio (`#f5f6f8`), tỷ lệ cân đối hoàn hảo.
- Tên sản phẩm rõ ràng, kèm 1 dòng tóm tắt thông số ngắn gọn, thanh lịch.
- Giá tiền in đậm sắc nét, nút `+ Thêm vào giỏ` hình viên thuốc đen tuyền nhỏ gọn, bấm nảy êm ái.

### B. Bảng Thông Số Kỹ Thuật (A2 Data Sheet)
- Bảng kẻ line ngang siêu mỏng màu xám nhạt (`#e5e7eb`), không kẻ viền dọc thô cứng.
- Cột bên trái là tiêu chí chữ xám mờ (`#6b7280`), cột bên phải là thông số kỹ thuật chữ đen đậm đà.

### C. Giỏ Hàng & Thanh Toán (Boutique Order Experience)
- Gọn gàng như một bảng kê khai của các nhà mốt xa xỉ. Các nút tăng giảm số lượng thiết kế thanh thoát, số tiền tự động cập nhật ngay lập tức.
