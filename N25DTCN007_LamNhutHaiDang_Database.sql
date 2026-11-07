-- ==============================================================================
-- CSDL ĐỒ ÁN CUỐI KỲ LẬP TRÌNH WEB (PTIT)
-- SINH VIÊN: LÂM NHỰT HẢI ĐĂNG - MSSV: N25DTCN007
-- CỬA HÀNG: LÂM NHỰT HẢI ĐĂNG // HARDWARE ARCHITECTURE & GEAR LAB
-- ==============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. BẢNG DANH MỤC SẢN PHẨM (categories)
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS contacts;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. BẢNG SẢN PHẨM THIẾT BỊ (products)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    sku VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    price DECIMAL(12, 0) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) NOT NULL,
    description TEXT,
    specifications TEXT, -- Bảng thông số kỹ thuật (A2)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. BẢNG TÀI KHOẢN NGƯỜI DÙNG (users)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    role ENUM('admin', 'customer') DEFAULT 'customer',
    failed_attempts INT DEFAULT 0,
    locked_until DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. BẢNG ĐƠN HÀNG (orders)
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_code VARCHAR(50) NOT NULL UNIQUE,
    recipient_name VARCHAR(100) NOT NULL,
    recipient_phone VARCHAR(20) NOT NULL,
    shipping_address VARCHAR(255) NOT NULL,
    total_amount DECIMAL(12, 0) NOT NULL,
    status ENUM('pending', 'shipping', 'completed', 'cancelled') DEFAULT 'pending',
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. BẢNG CHI TIẾT ĐƠN HÀNG (order_items)
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    price DECIMAL(12, 0) NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. BẢNG HỘP THƯ LIÊN HỆ (contacts - Tiêu chí C8)
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- DỮ LIỆU HẠT GIỐNG (SEED DATA)
-- ==============================================================================

-- 1. Seed Categories (4 danh mục công nghệ)
INSERT INTO categories (id, name, slug, description) VALUES
(1, 'Bàn Phím Cơ Custom', 'ban-phim-co-custom', 'Bàn phím cơ cao cấp, switch bôi trơn sẵn, mạch hotswap 8000Hz.'),
(2, 'Laptop Workstation & Gaming', 'laptop-workstation-gaming', 'Máy trạm di động và laptop gaming hiệu năng đỉnh cao cho kỹ sư & lập trình viên.'),
(3, 'Tai Nghe & Âm Thanh Studio', 'tai-nghe-am-thanh-studio', 'Tai nghe kiểm âm màng từ phẳng Planar và loa desktop chuẩn audiophile.'),
(4, 'Chuột & Thiết Bị Điều Khiển', 'chuột-thiet-bi-dieu-khien', 'Chuột siêu nhẹ hợp kim Magnesium, polling rate 8000Hz, cảm biến quang học đỉnh cao.');

-- 2. Seed Users (1 Admin, 2 Khách hàng)
-- Mật khẩu cho admin@lab.ptit.edu.vn: admin123
-- Mật khẩu cho khachhang1@gmail.com & khachhang2@gmail.com: customer123
INSERT INTO users (id, name, email, password, phone, address, role, failed_attempts, locked_until) VALUES
(1, 'Quản Trị Viên Lâm Nhựt Hải Đăng', 'admin@lab.ptit.edu.vn', '$2y$10$ZG8Ccx1OeGtQFMT6tPAlAuys8fW6qNUlTXs0y.eK5afD45D3A.IlO', '0912345678', 'Học viện Công nghệ Bưu chính Viễn thông PTIT, TP.HCM', 'admin', 0, NULL),
(2, 'Nguyễn Hoàng Long', 'khachhang1@gmail.com', '$2y$10$sQqj0zxbHCC059Gerx43UetQufmJGCz57.QqaId6bmzVIsa5YPq4S', '0987654321', 'Số 45 Lê Duẩn, Quận 1, TP. Hồ Chí Minh', 'customer', 0, NULL),
(3, 'Trần Minh Trang', 'khachhang2@gmail.com', '$2y$10$sQqj0zxbHCC059Gerx43UetQufmJGCz57.QqaId6bmzVIsa5YPq4S', '0903112233', 'Số 12 Chùa Bộc, Đống Đa, Hà Nội', 'customer', 0, NULL);

-- 3. Seed Products (12 thiết bị phần cứng cao cấp với thông số A2)
INSERT INTO products (id, category_id, sku, name, slug, price, stock, image, description, specifications) VALUES
(1, 1, 'KB-8700-PRO', 'Bàn Phím Cơ Custom Pro 87 // Edition 2026', 'ban-phim-co-custom-pro-87', 2490000, 3, 'keyboard-pro-87.jpg',
'Bàn phím cơ TKL 87 phím thiết kế mạch xuôi, switch Gateron Oil King được bôi trơn thủ công từ nhà máy.',
'{"Cấu trúc Switch": "Gateron Oil King (Linear 55g), Hotswap 5-Pin", "Tần số phản hồi": "8,000 Hz Polling Rate (Độ trễ 0.125ms)", "Chất liệu Keycap": "PBT Doubleshot Cherry Profile chống bóng", "Dung lượng Pin": "4,000 mAh Li-ion (Dùng 200 giờ)", "Kết nối": "Tri-Mode: 2.4GHz / Bluetooth 5.3 / Type-C bọc dù", "Trọng lượng": "1,150 gram (Khung nhôm anodized)"}'),

(2, 1, 'KB-7500-ALU', 'Bàn Phím Gasket-Mount Aluminum 75%', 'ban-phim-gasket-mount-aluminum-75', 3150000, 2, 'keyboard-alu-75.jpg',
'Vỏ nhôm CNC 6063 nguyên khối hoàn thiện anode mịn màng, kết cấu Gasket mount tiêu âm 5 lớp êm ái.',
'{"Cấu trúc": "Gasket Mount 5 lớp Poron Foam & IXPE pad", "Vỏ máy": "Nhôm Anodized CNC 6063 cao cấp", "Tần số phản hồi": "1,000 Hz Wired / 2.4GHz", "Plate": "FR4 flex-cut mạ vàng", "Kết nối": "Không dây 2.4G & Cáp Type-C", "Trọng lượng": "1,680 gram"}'),

(3, 1, 'KB-6800-SLIM', 'Bàn Phím Cơ Low-Profile Slim Wireless 68', 'ban-phim-co-low-profile-slim-68', 1850000, 8, 'keyboard-slim-68.jpg',
'Thiết kế siêu mỏng nhẹ chỉ 18mm, switch low-profile hành trình ngắn tối ưu cho tốc độ gõ code và di động.',
'{"Độ dày": "18mm điểm mỏng nhất", "Switch": "Kailh Choc V2 Red Low-Profile", "Thời lượng pin": "120 giờ không LED", "Trọng lượng": "520 gram siêu nhẹ", "Kết nối": "Bluetooth 5.3 (Lưu 3 thiết bị) & Type-C", "Chất liệu": "Mặt đáy nhựa ABS kỹ thuật, top nhôm"}'),

(4, 2, 'LP-1600-RZ', 'Laptop Razer Blade 16 Studio Edition', 'laptop-razer-blade-16-studio', 54900000, 2, 'laptop-razer-16.jpg',
'Cỗ máy trạm di động tối thượng trang bị màn hình OLED Dual-Mode và card đồ họa chuyên dụng đồ họa và AI.',
'{"Bộ vi xử lý": "Intel Core i9-14900HX (24 nhân, 32 luồng, Turbo 5.8GHz)", "Card đồ họa": "NVIDIA GeForce RTX 4080 12GB GDDR6 (175W TGP)", "Màn hình": "16 inch OLED 2.8K 240Hz / 4K 120Hz Dual-Mode 100% DCI-P3", "RAM": "32GB DDR5 5600MHz (Nâng cấp tối đa 96GB)", "Ổ cứng": "2TB NVMe PCIe Gen4 M.2 SSD", "Pin & Khối lượng": "95.2 Whr, 2.45 kg"}'),

(5, 2, 'LP-1600-MB', 'MacBook Pro 16 M3 Max Hardware Lab', 'macbook-pro-16-m3-max', 79900000, 3, 'macbook-pro-16.jpg',
'Sức mạnh xử lý biên dịch code và dựng đồ họa vô song với chip M3 Max 16-Core CPU và 40-Core GPU.',
'{"Chip": "Apple M3 Max (16 CPU Cores, 40 GPU Cores, 16 Neural Engine)", "Bộ nhớ thống nhất": "64GB Unified Memory (Băng thông 400GB/s)", "Màn hình": "16.2 inch Liquid Retina XDR (1600 nits đỉnh, ProMotion 120Hz)", "Lưu trữ": "1TB SSD siêu tốc 7.4GB/s", "Cổng kết nối": "3x Thunderbolt 4, HDMI, Khe SDXC, MagSafe 3", "Thời lượng pin": "Lên tới 22 giờ sử dụng"}'),

(6, 2, 'LP-1500-TP', 'ThinkPad P1 Gen 6 Mobile Workstation', 'thinkpad-p1-gen-6-workstation', 45500000, 4, 'laptop-thinkpad-p1.jpg',
'Độ bền quân đội chuẩn MIL-STD 810H, bảo mật sinh trắc học cấp doanh nghiệp, bàn phím gõ đỉnh cao.',
'{"Vi xử lý": "Intel Core i7-13800H vPro Enterprise", "Đồ họa": "NVIDIA RTX A2000 Ada Generation 8GB GDDR6", "Màn hình": "16 inch WQXGA (2560x1600) IPS chống chói 500 nits 165Hz", "RAM": "32GB DDR5 Non-ECC", "Bảo mật": "Chip dTPM 2.0, Đầu đọc vân tay, Camera IR mở khóa khuôn mặt", "Trọng lượng": "1.78 kg"}'),

(7, 3, 'HP-9000-PL', 'Tai Nghe Studio Planar Magnetic Pro', 'tai-nghe-studio-planar-magnetic-pro', 8900000, 2, 'headphone-planar-9000.jpg',
'Tai nghe màng từ phẳng Planar Magnetic độ méo tiếng siêu thấp <0.05%, tái tạo âm thanh chuẩn phòng thu.',
'{"Công nghệ củ loa": "Màng từ phẳng Planar Magnetic 100mm", "Dải tần đáp ứng": "8 Hz – 55,000 Hz", "Trở kháng": "32 Ohms (Dễ kéo)", "Độ nhạy": "102 dB / 1mW", "Dây cáp": "Đồng OFC mạ bạc 8 lõi, Jack cắm Balanced 4.4mm & 3.5mm", "Trọng lượng": "410 gram"}'),

(8, 3, 'HP-7000-WL', 'Tai Nghe Gaming Ultra-Low Latency Wireless', 'tai-nghe-gaming-wireless-latency', 3690000, 6, 'headphone-wireless-7000.jpg',
'Kết nối không dây công nghệ lossless 2.4GHz độ trễ siêu thấp dưới 15ms, màng loa Beryllium 50mm.',
'{"Độ trễ truyền âm": "< 15ms qua đầu thu USB 2.4G UltraSync", "Màng loa": "50mm Beryllium-coated Drivers", "Microphone": "Micro điện dung Broadcast tháo rời, lọc ồn AI", "Thời lượng pin": "60 giờ liên tục", "Âm thanh vòm": "Spatial Audio 7.1 tích hợp", "Trọng lượng": "285 gram"}'),

(9, 3, 'SP-5000-MN', 'Cặp Loa Kiểm Âm Để Bàn Desktop Monitor 100W', 'loa-kiem-am-desktop-monitor-100w', 4990000, 5, 'speaker-monitor-5000.jpg',
'Bộ loa kiểm âm active công suất 100W tích hợp DAC 24-bit/192kHz giải mã tín hiệu âm thanh độ phân giải cao.',
'{"Công suất": "100W RMS (50W mỗi kênh Class-D bi-amped)", "Củ loa Woofer": "4 inch sợi carbon tổng hợp", "Củ loa Tweeter": "1 inch màng lụa mềm vòm", "Ngõ vào": "USB Type-C Lossless, Cáp quang Optical, Cặp cổng RCA balanced", "Dải tần": "48 Hz – 22,000 Hz", "Kích thước": "140 x 180 x 220 mm"}'),

(10, 4, 'MS-3900-MG', 'Chuột Không Dây Magnesium Cực Nhẹ 39g', 'chuot-khong-day-magnesium-39g', 2850000, 2, 'mouse-magnesium-39g.jpg',
'Khung vỏ hợp kim magie đúc rỗng bằng công nghệ CNC, trọng lượng chỉ 39 gram, mắt đọc 8000Hz.',
'{"Trọng lượng": "39 gram (Vỏ Magie hợp kim hàng không)", "Cảm biến": "PixArt PAW3395 (26,000 DPI, 650 IPS, 50G gia tốc)", "Polling Rate": "8,000 Hz Wireless (Phản hồi 0.125ms)", "Switch bấm": "Quang học Omron Optical 100 triệu lần nhấn", "Pin": "Hoạt động 80 giờ ở 1000Hz, 30 giờ ở 8000Hz", "Chân chuột": "100% Virgin Grade PTFE bo tròn mép"}'),

(11, 4, 'MS-5000-MX', 'Chuột Công Thái Học Master Ergonomic Pro', 'chuot-cong-thai-hoc-master-pro', 2190000, 7, 'mouse-ergonomic-pro.jpg',
'Thiết kế nghiêng 57 độ tự nhiên giải tỏa áp lực cổ tay khi lập trình lâu, con lăn thép siêu tốc Magspeed.',
'{"Góc nghiêng công thái học": "57 độ chuẩn y khoa", "Con lăn": "Magspeed thép không gỉ trượt 1,000 dòng/giây", "Cảm biến": "Darkfield 8,000 DPI hoạt động trên mọi bề mặt kính", "Kết nối": "Bluetooth & Logi Bolt, kết nối cùng lúc 3 thiết bị", "Thời lượng pin": "70 ngày một lần sạc nhanh USB-C", "Trọng lượng": "141 gram"}'),

(12, 4, 'MP-1000-GL', 'Bàn Di Chuột Thủy Tinh Cường Lực Lab Mat Pro', 'ban-di-chuot-thuy-tinh-lab-mat', 1450000, 9, 'mousepad-glass-lab.jpg',
'Bề mặt kính silicate tôi luyện nhiệt độ cao tạo độ trơn trượt hoàn hảo không lực cản ma sát, đế cao su chống trượt.',
'{"Vật liệu bề mặt": "Kính cường lực Silicate mờ siêu mịn", "Kích thước": "500 mm x 400 mm x 3.8 mm (Cỡ XL)", "Đế lót": "Đế cao su thiên nhiên đúc vân bám chặt mặt bàn", "Độ bền": "Chống mài mòn vĩnh cửu, kháng nước và dễ lau chùi", "Trọng lượng": "1,800 gram bám bàn tuyệt đối", "Màu sắc": "Đen mờ phòng lab (Matte Lab Black)"}');

-- 4. Seed Orders (5 đơn hàng ở đủ 4 trạng thái theo yêu cầu đề bài)
INSERT INTO orders (id, user_id, order_code, recipient_name, recipient_phone, shipping_address, total_amount, status, note, created_at) VALUES
(1, 2, 'ORD-2026-001', 'Nguyễn Hoàng Long', '0987654321', 'Số 45 Lê Duẩn, Quận 1, TP. Hồ Chí Minh', 5640000, 'pending', 'Giao giờ hành chính, gọi trước khi đến.', '2026-11-03 09:15:00'),
(2, 2, 'ORD-2026-002', 'Nguyễn Hoàng Long', '0987654321', 'Số 45 Lê Duẩn, Quận 1, TP. Hồ Chí Minh', 54900000, 'shipping', 'Đơn hàng giá trị cao, yêu cầu đồng kiểm hàng.', '2026-11-02 14:30:00'),
(3, 3, 'ORD-2026-003', 'Trần Minh Trang', '0903112233', 'Số 12 Chùa Bộc, Đống Đa, Hà Nội', 4990000, 'completed', 'Đã nhận đủ hàng và test thử âm thanh loa rất tốt.', '2026-10-28 10:00:00'),
(4, 3, 'ORD-2026-004', 'Trần Minh Trang', '0903112233', 'Số 12 Chùa Bộc, Đống Đa, Hà Nội', 2490000, 'pending', 'Đang chờ xác nhận điều chuyển kho chi nhánh.', '2026-10-30 16:45:00'),
(5, 2, 'ORD-2026-005', 'Nguyễn Hoàng Long', '0987654321', 'Số 45 Lê Duẩn, Quận 1, TP. Hồ Chí Minh', 3150000, 'cancelled', 'Khách hàng đặt nhầm màu phím, đã hủy đơn.', '2026-11-01 11:20:00');

-- 5. Seed Order Items (Lưu đơn giá tại thời điểm mua)
INSERT INTO order_items (id, order_id, product_id, price, quantity) VALUES
(1, 1, 1, 2490000, 1),
(2, 1, 2, 3150000, 1),
(3, 2, 4, 54900000, 1),
(4, 3, 9, 4990000, 1),
(5, 4, 1, 2490000, 1),
(6, 5, 2, 3150000, 1);

-- 6. Seed Contacts (Tin nhắn mẫu - Tiêu chí C8)
INSERT INTO contacts (id, name, email, subject, message, created_at) VALUES
(1, 'Lê Quốc Bảo', 'baole@gmail.com', 'Tư vấn cấu hình máy trạm Razer Blade 16', 'Tôi cần tư vấn thêm về khả năng nâng cấp RAM của máy Razer Blade 16 khi chạy các mô hình AI cục bộ.', '2026-11-02 08:30:00'),
(2, 'Phạm Thu Hằng', 'hangpham@yahoo.com', 'Hỏi về thời gian bảo hành Bàn phím cơ Custom', 'Shop cho mình hỏi switch bàn phím Pro 87 có được bảo hành hotswap nếu tự thay tại nhà không ạ?', '2026-11-03 15:10:00');
