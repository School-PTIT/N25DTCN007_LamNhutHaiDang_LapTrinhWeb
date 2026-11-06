# ĐỒ ÁN CUỐI KỲ — MÔN LẬP TRÌNH WEB
### HỌC VIỆN CÔNG NGHỆ BƯU CHÍNH VIỄN THÔNG (PTIT)
> **Giảng viên hướng dẫn**: Thầy Lê Nhật Tùng (`@Le Nhat Tung`)  
> **Sinh viên thực hiện**: Lâm Nhựt Hải Đăng  
> **Mã số sinh viên (MSSV)**: `N25DTCN007`  
> **Thương hiệu cá nhân hóa**: **LÂM NHỰT HẢI ĐĂNG // STUDIO & BOUTIQUE**  
> **Công nghệ**: HTML5, CSS3 thuần (No Framework), JavaScript ES6+ thuần, PHP 8.2+ thuần (No Framework), Dual-Driver Database (MySQL PDO / SQLite PDO fallback).

---

## 📌 1. Giới thiệu tổng quan dự án

Website thương mại điện tử chuyên cung cấp thiết bị phần cứng, studio & đồ công nghệ cao cấp (**LÂM NHỰT HẢI ĐĂNG // STUDIO & BOUTIQUE**), được xây dựng theo triết lý thiết kế **Quiet Luxury & Minimalist Daylight** (lấy cảm hứng từ Apple Store, Bang & Olufsen, Leica). 

Toàn bộ hệ thống được viết hoàn toàn bằng **mã nguồn thuần (Vanilla)**, tuân thủ nghiêm ngặt tiêu chuẩn bảo mật hiện đại (OWASP), trải nghiệm Zero-Reload (mượt mà như Single Page App) và đáp ứng 100% yêu cầu đề thi của Học viện.

---

## 🎯 2. Đáp ứng 6 Điều kiện Cá nhân hóa bắt buộc (100%)

1. **Tên cửa hàng**: `LÂM NHỰT HẢI ĐĂNG // STUDIO & BOUTIQUE` hiển thị đồng bộ trên Header, Hero Banner và Footer của tất cả các trang.
2. **Logo cửa hàng**: Khung tròn viền bạch kim sang trọng với ảnh chân dung sinh viên Lâm Nhựt Hải Đăng có chấm xanh trạng thái Studio (`live-badge`) trên 100% trang khách hàng và trang quản trị.
3. **Chân trang (Footer)**: In rõ định danh pháp lý: `MSSV: N25DTCN007 — Họ và tên: Lâm Nhựt Hải Đăng — Lập trình Web PTIT` trên toàn bộ 15 trang web.
4. **Bảng màu riêng biệt**: Hệ thống Design Tokens CSS `:root` tại `css/style.css` với gam màu ngọc trai ấm `#fafafc`, đen than `#111827`, và vàng champagne `#c5a059`.
5. **Dữ liệu mẫu thực tế**:
   - 4 danh mục sản phẩm cao cấp.
   - 12 sản phẩm phần cứng với ảnh chụp studio độ phân giải cao và mô tả chi tiết.
   - 3 tài khoản người dùng (bao gồm quản trị viên `admin`).
   - 5 đơn hàng ở đầy đủ các trạng thái (`pending`, `shipping`, `completed`, `cancelled`).
6. **Mã nguồn tự phát triển**: Tự tay thiết kế và lập trình 100% mã nguồn, không sử dụng framework dựng sẵn (Bootstrap, Tailwind, Laravel, WordPress...).

---

## 🚀 3. Tính năng nổi bật & Trải nghiệm Zero-Reload

* **Bộ lọc và Danh mục tức thì (Zero-Reload Catalog)**: Lọc danh mục, tìm kiếm và sắp xếp giá trực tiếp bằng Fetch API + `DOMParser` + `history.pushState`, không chớp trắng màn hình hay tải lại trang.
* **Giỏ hàng mượt mà (Smooth Cart UX)**: Nút stepper số lượng `[-] [ 1 ] [+]` 44px chuẩn công thái học; xóa từng món hàng với hiệu ứng trượt mờ (`slide-out`) trong 250ms; cập nhật số lượng badge trên Header ngay lập tức.
* **Mua ngay 1 chạm (Express Checkout)**: Nút `Mua Ngay` đưa thẳng sản phẩm vào giỏ và chuyển đến trang thanh toán.
* **Mã giảm giá trực quan (Live Voucher Coupon)**: Hỗ trợ mã voucher (`PTIT2026` -10%, `HAIDANG` -15%) trừ tiền tức thì và cập nhật VietQR động trước mắt người dùng.
* **Thanh toán VietQR động**: Tự sinh mã QR ngân hàng Quân Đội (MB Bank) đúng số tiền sau khi áp dụng mã giảm giá và nội dung chuyển khoản chuẩn theo mã đơn hàng.
* **Theo dõi & In phiếu đơn hàng**: Thanh tiến trình đơn hàng trực quan 4 bước (`Chờ xử lý` ➔ `Đang giao` ➔ `Hoàn tất`); tích hợp nút in hóa đơn giao hàng chuẩn khổ giấy A4 (`@media print`).
* **Hệ thống Icon Vector Nội bộ**: Hơn 25 biểu tượng SVG nét mảnh 1.5px (Lucide Style) sinh trực tiếp qua hàm `icon()`, không phụ thuộc mạng CDN, tải 0ms.

---

## 🛡️ 4. Kiến trúc Bảo mật chuyên sâu (OWASP Hardening)

* **HTTP Security Headers**: Cấu hình toàn diện tại `includes/init.php` (`X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `X-XSS-Protection`, `Permissions-Policy`).
* **Phòng chống tấn công CSRF**: Cơ chế CSRF Token kiểm tra chặt chẽ trên toàn bộ các phương thức `POST` và Ajax request.
* **Bảo vệ CSDL & Chống SQL Injection**: Sử dụng 100% **PDO Prepared Statements** kết hợp tham số hóa; không bao giờ ghép chuỗi dữ liệu thô vào câu truy vấn.
* **Chống rò rỉ dữ liệu qua URL (IDOR)**: Kiểm tra nghiêm ngặt quyền sở hữu đơn hàng trước khi cho phép xem chi tiết hoặc hủy đơn.
* **Chống leo thang đặc quyền**: Hàm `require_admin()` kiểm tra phân quyền đầu trang quản trị; người dùng thường truy cập bị chặn với mã HTTP `403 Forbidden`.
* **Giới hạn số lần đăng nhập (Rate Limiting)**: Tạm khóa đăng nhập 5 phút nếu nhập sai mật khẩu liên tiếp 5 lần.
* **Phòng chống Open Redirect**: Whitelist và xác thực an toàn tham số `return_to` sau khi đăng nhập.
* **Bảo vệ tệp tin nhạy cảm**: Tệp `.htaccess` cấm hoàn toàn việc truy cập trực tiếp từ trình duyệt vào các thư mục `config/`, `includes/`, `database/` và các tệp CSDL SQLite.

---

## 📂 5. Cấu trúc thư mục dự án

```text
├── admin/                         # Phân hệ quản trị viên (Admin Panel)
│   ├── index.php                  # Dashboard thống kê doanh thu, đơn hàng, tồn kho
│   ├── products.php               # Quản lý sản phẩm (danh sách, phân trang, xóa)
│   ├── product-form.php           # Thêm/Sửa sản phẩm kèm tải ảnh an toàn
│   ├── categories.php             # Quản lý danh mục sản phẩm
│   ├── orders.php                 # Quản lý & cập nhật trạng thái đơn hàng (Ajax)
│   └── order-detail.php           # Xem chi tiết đơn hàng
├── config/                        # Cấu hình hệ thống
│   ├── config.php                 # Biến môi trường, múi giờ, hằng số bảo mật
│   └── db.php                     # Kết nối CSDL PDO (Hỗ trợ kép MySQL & SQLite)
├── css/                           # Bảng định kiểu giao diện
│   └── style.css                  # CSS thuần, biến màu :root, Responsive Flexbox & Grid
├── database/                      # Cơ sở dữ liệu & Kiểm thử
│   ├── final_web.sqlite           # CSDL SQLite chạy ngay lập tức không cần cài đặt
│   └── verify_security_and_assets.php # Bộ kiểm thử tự động 15 tiêu chí an toàn & dữ liệu
├── includes/                      # Các thành phần giao diện & hàm dùng chung
│   ├── init.php                   # Khởi tạo session an toàn, CSRF token, HTTP headers
│   ├── functions.php              # Hàm sinh vector icon, kiểm tra auth, format tiền tệ
│   ├── header.php                 # Header dùng chung, logo cá nhân hóa, thanh điều hướng
│   └── footer.php                 # Footer định danh MSSV và Họ tên sinh viên
├── js/                            # JavaScript xử lý nghiệp vụ & tương tác
│   ├── app.js                     # Trình nghe sự kiện, xác thực form, Ajax catalog & cart
│   └── auth.js                    # Kiểm tra độ khớp mật khẩu, ẩn/hiện mật khẩu
├── uploads/                       # Thư mục lưu trữ hình ảnh sản phẩm & avatar
├── 404.php                        # Trang báo lỗi 404 tùy biến
├── about.php                      # Trang giới thiệu thông tin sinh viên & cửa hàng
├── cart.php                       # Giỏ hàng mua sắm
├── checkout.php                   # Trang thanh toán & sinh VietQR
├── contact.php                    # Trang liên hệ lưu tin nhắn vào CSDL
├── index.php                      # Trang chủ trưng bày sản phẩm & bộ lọc danh mục
├── login.php                      # Trang đăng nhập
├── order-detail.php               # Chi tiết đơn hàng, thanh tiến trình & in hóa đơn
├── orders.php                     # Lịch sử đơn hàng của người dùng
├── product.php                    # Trang chi tiết sản phẩm & thông số kỹ thuật
├── profile.php                    # Cập nhật hồ sơ cá nhân
├── quick-login.php                # Đăng nhập nhanh 1-click phục vụ kiểm thử
├── register.php                   # Trang đăng ký tài khoản
├── N25DTCN007_LamNhutHaiDang_Database.sql # Tệp CSDL MySQL/MariaDB chuẩn nộp bài
└── README.md                      # Tài liệu hướng dẫn đồ án
```

---

## 💻 6. Hướng dẫn cài đặt & Khởi chạy

### Cách 1: Chạy ngay lập tức bằng PHP Built-in Server (Khuyên dùng)
Dự án được tích hợp sẵn CSDL SQLite độc lập, bạn có thể chạy website ngay mà không cần cài đặt XAMPP hay cấu hình MySQL:

1. Mở terminal hoặc PowerShell tại thư mục dự án:
   ```powershell
   php -S 127.0.0.1:8080
   ```
2. Mở trình duyệt và truy cập: **`http://127.0.0.1:8080/`**

### Cách 2: Chạy trên XAMPP / WampServer (MySQL)
1. Copy toàn bộ thư mục vào `C:\xampp\htdocs\final_web`.
2. Mở `phpMyAdmin` (`http://localhost/phpmyadmin`), tạo cơ sở dữ liệu mới tên là: `final_web`.
3. Import tệp **`N25DTCN007_LamNhutHaiDang_Database.sql`** vào cơ sở dữ liệu vừa tạo.
4. Mở file `config/db.php` để kiểm tra kết nối MySQL (mặc định user: `root`, pass: rỗng).
5. Mở trình duyệt và truy cập: **`http://localhost/final_web/`**

---

## 🔑 7. Tài khoản kiểm thử nghiệm thu

| Loại tài khoản | Email đăng nhập | Mật khẩu | Liên kết đăng nhập nhanh 1-click |
| :--- | :--- | :--- | :--- |
| **Quản trị viên (Admin)** | `admin@lab.ptit.edu.vn` | `admin123` | `http://127.0.0.1:8080/quick-login.php?as=admin` |
| **Khách hàng mẫu** | `khachhang1@gmail.com` | `customer123` | `http://127.0.0.1:8080/quick-login.php?as=customer` |

---

## 🏆 8. Kết quả kiểm thử tự động (QA Automated Verification)

Hệ thống tích hợp script kiểm thử tự động [`database/verify_security_and_assets.php`](database/verify_security_and_assets.php):
* **Tổng số bài kiểm tra**: 15/15 bài đạt chuẩn **PASS (0 FAILED)**.
* **Kiểm tra dữ liệu**: 100% đủ danh mục, sản phẩm và ảnh chụp sắc nét thực tế.
* **Kiểm tra bảo mật**: 100% bảo vệ CSRF, chặn truy cập trực tiếp file dữ liệu và khóa HTTP Headers.
