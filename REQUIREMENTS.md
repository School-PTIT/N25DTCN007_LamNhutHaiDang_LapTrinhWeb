# TỔNG HỢP TOÀN BỘ YÊU CẦU ĐỒ ÁN CUỐI KỲ - LẬP TRÌNH WEB
> **Học viện Công nghệ Bưu chính Viễn thông (PTIT)**  
> **Giảng viên**: @Le Nhat Tung  
> **Môn học**: Lập trình Web (HTML, CSS, JavaScript, PHP thuần + MySQL/MariaDB)  
> **Hạn nộp**: Trước 23:00 ngày 07/11/2026 (Nộp cả LMS & Google Form)  
> **Thang điểm**: Tối đa 10.0 điểm (9.0 điểm dự án + 1.0 điểm cộng hosting)

---

## I. 6 QUY TẮC CÁ NHÂN HÓA BẮT BUỘC (Trừ tối đa 1 điểm nếu vi phạm)
1. **Tên cửa hàng**: Phải là họ tên của chính sinh viên (VD: "Cửa hàng Nguyễn Văn An"), tuyệt đối không dùng tên "MiniShop". Nhóm sản phẩm tự chọn.
2. **Logo cửa hàng**: Là ảnh chân dung của chính sinh viên, hiển thị ở khu vực logo trên đầu trang của **mọi trang** (kể cả các trang quản trị admin).
3. **Ảnh chụp trong báo cáo**: Mọi ảnh chụp màn hình minh chứng tính năng phải thấy rõ **tên cửa hàng** và **ảnh chân dung ở logo**. Ảnh thiếu 1 trong 2 không được tính điểm.
4. **Dữ liệu tối thiểu trong CSDL**:
   - Tối thiểu 3 danh mục (`categories`).
   - Tối thiểu 12 sản phẩm (`products`) có ảnh thật, mô tả và giá riêng.
   - Tối thiểu 3 tài khoản (`users`), trong đó có ít nhất 1 quản trị viên (`role = admin`).
   - Tối thiểu 5 đơn hàng (`orders`) ở ít nhất 3 trạng thái khác nhau (`pending`, `shipping`, `completed`, `cancelled`).
5. **Chân trang (Footer)**: Tất cả mọi trang đều phải in rõ **Mã số sinh viên (MSSV)** và **Họ và tên**.
6. **Giao diện riêng**: Thay đổi bảng màu bằng biến CSS trong `:root` (không dùng nguyên giao diện mẫu MiniShop).

---

## II. CHI TIẾT 38 TIÊU CHÍ CHẤM ĐIỂM (A1 - D11 & E)

### PHẦN A — Giao diện HTML, CSS, JavaScript (1.5 điểm)
- **A1**: HTML đúng cấu trúc, sử dụng thẻ ngữ nghĩa (`header`, `nav`, `main`, `footer`); toàn bộ thẻ `<img>` có thuộc tính `alt`; đường dẫn tương đối hoặc qua `url()`.
- **A2**: Trang Giới thiệu (`about.php`), Liên hệ (`contact.php`), Chi tiết sản phẩm (`product.php`) có bảng thông số kỹ thuật.
- **A3**: Form đăng ký, đăng nhập có thẻ `<label>`, kiểm tra hợp lệ bằng thuộc tính HTML5 (`required`, `pattern`, `minlength`).
- **A4**: CSS tệp riêng (`css/style.css`), khai báo biến màu `:root`, Flexbox cho phần đầu trang, CSS Grid cho lưới sản phẩm.
- **A5**: Giao diện đáp ứng (Responsive Design): lưới 4 cột / 3 cột (màn hình 901–1100px) / 2 cột / 1 cột; không xuất hiện thanh cuộn ngang.
- **A6**: Đánh dấu trang hiện tại trên thanh menu điều hướng (gán class `active`).
- **A7**: JavaScript kiểm tra hai mật khẩu nhập lại phải khớp nhau; form liên hệ chặn nội dung gửi dưới 10 ký tự.
- **A8**: JavaScript trong trang giỏ hàng tự động tính lại thành tiền ngay khi đổi số lượng; nút xóa giỏ có popup hộp thoại xác nhận (`confirm`).

---

### PHẦN B — Trang động và Cơ sở dữ liệu (1.5 điểm)
- **B1**: Bố cục dùng chung: `includes/init.php`, `includes/functions.php`, `includes/header.php`, `includes/footer.php`; cấu hình tập trung trong `config/config.php` và `config/db.php`.
- **B2**: Cơ sở dữ liệu chuẩn gồm 5 bảng chính: `categories`, `products`, `users`, `orders`, `order_items` có khóa chính, khóa ngoại, bảng mã `utf8mb4`.
- **B3**: Kết nối CSDL qua **PDO**; 100% truy vấn có dữ liệu từ người dùng đều dùng câu lệnh chuẩn bị sẵn (**Prepared Statements**); 100% dữ liệu in ra HTML đều bọc qua hàm `e()` (`htmlspecialchars`) chống XSS.
- **B4**: Trang chủ lấy sản phẩm động từ CSDL; lọc theo danh mục; tìm kiếm theo tên sản phẩm.
- **B5**: Trang chi tiết `product.php?id=` trả về mã trạng thái HTTP 404 khi không tìm thấy sản phẩm.
- **B6**: Sắp xếp theo giá tăng/giảm (chọn chuỗi `ORDER BY` viết sẵn theo whitelist, không ghép tham số trực tiếp vào SQL chống SQL Injection).
- **B7**: Hiển thị thông báo “Tìm thấy N sản phẩm”; cuối trang chi tiết hiển thị tối đa 3 sản phẩm liên quan cùng danh mục.

---

### PHẦN C — Tài khoản, Giỏ hàng, Đặt hàng (2.0 điểm)
- **C1**: Đăng ký tài khoản: kiểm tra dữ liệu phía máy chủ (PHP validation), giữ lại dữ liệu đã nhập khi phát hiện lỗi, chặn email trùng lặp, mã hóa mật khẩu an toàn bằng `password_hash()`.
- **C2**: Đăng nhập, đăng xuất bằng PHP session; gọi `session_regenerate_id()` chống tấn công Session Fixation; thanh điều hướng thay đổi hiển thị theo trạng thái đăng nhập; áp dụng mô hình Post/Redirect/Get (PRG) kèm thông báo Flash message (hiển thị 1 lần).
- **C3**: Giỏ hàng lưu trong session: thêm, cập nhật số lượng, xóa từng dòng, xóa toàn bộ giỏ; đơn giá luôn lấy trực tiếp từ CSDL (không lấy từ form client gửi lên).
- **C4**: Đặt hàng trong một giao dịch cơ sở dữ liệu (**Transaction**: `beginTransaction`, `commit`, `rollBack`); kiểm tra số lượng tồn kho; trừ tồn kho khi đặt thành công; lưu giá sản phẩm tại thời điểm mua vào chi tiết đơn.
- **C5**: Lịch sử đơn hàng (`orders.php`) và chi tiết đơn (`order-detail.php`); bảo mật chống lỗ hổng IDOR (người dùng này không thể xem đơn của người khác bằng cách đổi `id` trên URL).
- **C6**: Cho phép hủy đơn khi đơn hàng còn ở trạng thái “Chờ xử lý” (`pending`), tự động hoàn trả số lượng vào tồn kho trong một giao dịch Transaction.
- **C7**: Trang hồ sơ cá nhân `profile.php`: cho phép cập nhật họ tên, số điện thoại, địa chỉ nhận hàng.
- **C8**: Form liên hệ gửi thông tin được lưu trữ vào bảng `contacts` trong CSDL.
- **C9**: Sau khi người dùng đăng nhập thành công, tự động chuyển hướng quay lại đúng trang họ định truy cập trước đó (Return URL).

---

### PHẦN D — Quản trị và Bảo mật (2.0 điểm)
- **D1**: Phân quyền quản trị: hàm kiểm tra `require_admin()` đặt ở đầu tất cả các trang trong thư mục `admin/`; tài khoản người dùng thường truy cập bị từ chối với mã HTTP 403 Forbidden.
- **D2**: Quản lý sản phẩm: danh sách có phân trang, chức năng thêm, sửa, xóa (thực hiện xóa bằng phương thức POST; chặn không cho xóa sản phẩm đã từng phát sinh trong đơn hàng).
- **D3**: Upload ảnh an toàn: giới hạn dung lượng tệp (tối đa 2MB), kiểm tra nội dung và định dạng ảnh hợp lệ, đổi tên tệp ngẫu nhiên (UUID/uniqid), tệp `.htaccess` cấm thực thi mã PHP trong thư mục `uploads/`.
- **D4**: Quản lý đơn hàng: xem toàn bộ đơn hàng của khách, lọc theo trạng thái đơn, cập nhật trạng thái đơn hàng.
- **D5**: Bảo vệ CSRF Token trong mọi form POST; file `.htaccess` chặn triệt để quyền truy cập trực tiếp từ trình duyệt vào các thư mục `config/`, `includes/`, `database/`; trang `404.php` tùy biến riêng.
- **D6**: Quản lý danh mục: thêm, đổi tên, xóa danh mục; chặn xóa danh mục đang có sản phẩm.
- **D7**: Khi sửa sản phẩm và tải ảnh mới lên, hệ thống tự động xóa tệp ảnh cũ trong thư mục `uploads/`.
- **D8**: Phân trang danh sách sản phẩm ở trang chủ (8 sản phẩm/trang), giữ nguyên bộ lọc danh mục và từ khóa tìm kiếm khi chuyển trang.
- **D9**: Trang dashboard tổng quan quản trị (`admin/index.php`): thống kê số đơn theo từng trạng thái, tổng doanh thu các đơn hoàn tất, hiển thị top 5 sản phẩm sắp hết hàng (tồn kho thấp nhất).
- **D10**: Giới hạn đăng nhập sai: nếu nhập sai mật khẩu 5 lần liên tiếp thì tạm khóa form đăng nhập trong vòng 5 phút (Rate limiting).
- **D11**: Đổi mật khẩu (`change-password.php`): bắt buộc nhập đúng mật khẩu hiện tại trước khi thiết lập mật khẩu mới.

---

### PHẦN E — Triển khai Hosting thật (Cộng 1.0 điểm)
- Website chạy trên hosting có panel quản trị (cPanel, aaPanel, DirectAdmin...), truy cập được từ Internet bằng tên miền hoặc tên miền phụ (subdomain).
- Cơ sở dữ liệu chạy online trên hosting, có đầy đủ dữ liệu tối thiểu.
- Biến cấu hình `APP_DEBUG = false`. Mở trực tiếp `/config/config.php` và `/database/` trên trình duyệt phải trả về mã lỗi 403.
- Trang web có chứng chỉ bảo mật HTTPS (biểu tượng ổ khóa màu xanh).
- Giữ website hoạt động liên tục cho đến khi giảng viên chấm xong điểm.

---

## III. YÊU CẦU BÁO CÁO VÀ VIDEO THUYẾT MINH

### 1. Báo cáo nghiệm thu (1.0 điểm)
- Điền đầy đủ thông tin vào mẫu Word: `MSSV_HoTen_BaoCao.docx`.
- Chụp ảnh màn hình minh chứng đầy đủ cho từng yêu cầu (A1 – D11).
- Chụp ảnh kết quả chạy câu lệnh SQL thống kê số lượng dữ liệu (danh mục, sản phẩm, tài khoản, đơn hàng).
- Dán trọn vẹn mã nguồn của tất cả các tệp trong dự án vào mục 8 của báo cáo.

### 2. Video trình bày (1.0 điểm)
- Thời lượng bắt buộc: **từ 5 đến 10 phút** (ngắn hơn 5 phút hoặc dài hơn 10 phút bị hạ tối đa còn 0.5 điểm).
- Quay màn hình kèm lời thuyết minh của sinh viên:
  - `~0.5 phút`: Giới thiệu MSSV, họ tên, tên shop, nhóm sản phẩm.
  - `~2.5 phút`: Luồng khách hàng (xem sp, lọc, tìm kiếm, đăng ký, đăng nhập, giỏ hàng, đặt hàng, xem & hủy đơn).
  - `~2.0 phút`: Luồng admin (dashboard, quản lý sản phẩm có upload ảnh, danh mục, đơn hàng).
  - `~2.0 phút`: Trình bày mã nguồn & CSDL (cấu trúc thư mục, bảng trong phpMyAdmin, giải thích đoạn code giao dịch đặt hàng).
  - `~1.0 phút`: Kiểm tra bảo mật (thử dùng tài khoản thường vào trang admin bị 403, thử nhập thẻ HTML vào form để chứng minh chống XSS).
  - `~1.0 phút`: Trình chiếu website chạy trên hosting thật (nếu làm phần E).
- Đăng tải video lên YouTube (chế độ Không công khai) hoặc Google Drive (Bất kỳ ai có liên kết đều xem được).

---

## IV. QUY ĐỊNH NỘP BÀI VÀ ĐẶT TÊN TỆP
1. **Nộp cả 2 nơi**: 
   - LMS: `lms.pttc1.edu.vn` (tải lên file Báo cáo và file Nén mã nguồn).
   - Google Form do giảng viên gửi (tích bảng tự đánh giá, dán link Drive báo cáo, mã nguồn, file .sql, ảnh chân dung và link video).
2. **Quy tắc đặt tên file**:
   - Tệp Báo cáo: `MSSV_HoTen_BaoCao.docx` (hoặc `.pdf`)
   - Tệp Mã nguồn: `MSSV_HoTen_SourceCode.zip` (chứa toàn bộ code, file CSDL `.sql` và thư mục ảnh).
   - Tệp CSDL: `MSSV_HoTen_Database.sql`.
