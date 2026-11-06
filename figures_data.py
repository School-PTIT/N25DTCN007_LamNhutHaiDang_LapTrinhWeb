# -*- coding: utf-8 -*-
"""
Full metadata and natural student descriptions for 21 demonstration figures
"""

FIGURES_DATA = [
    {
        "id": 1,
        "title": "Trang chủ hiển thị danh sách sản phẩm trên máy tính (lưới 4 cột)",
        "url": "http://127.0.0.1:8080/index.php",
        "req": "A1, A4, B4",
        "desc": "Khi em mở trang chủ trên máy tính, giao diện được chia thành lưới 4 cột bằng CSS Grid. Phần đầu trang có logo là ảnh chân dung của em kèm tên shop LÂM NHỰT HẢI ĐĂNG đồng nhất. Phía dưới hiển thị đầy đủ danh mục và các sản phẩm lấy động từ cơ sở dữ liệu kèm giá bán, nút xem chi tiết và thêm vào giỏ hàng.",
        "file": "index.php, includes/header.php, css/style.css"
    },
    {
        "id": 2,
        "title": "Trang chủ ở chế độ điện thoại di động (DevTools, chiều rộng ~390px)",
        "url": "http://127.0.0.1:8080/index.php",
        "req": "A5",
        "desc": "Em mở F12 DevTools chuyển sang kích thước màn hình điện thoại (khoảng 390px - 400px). Nhờ sử dụng @media query trong file CSS, lưới sản phẩm tự động co lại thành 1 cột thẳng đứng, menu và các nút bấm được tối ưu để chạm ngón tay dễ dàng mà không bị tràn màn hình hay xuất hiện thanh cuộn ngang.",
        "file": "css/style.css, index.php"
    },
    {
        "id": 3,
        "title": "Lọc sản phẩm theo danh mục, tìm kiếm và sắp xếp theo giá",
        "url": "http://127.0.0.1:8080/index.php?category=1&q=ban+phim&sort=price_asc",
        "req": "B4, B6, B7",
        "desc": "Em thử chọn danh mục 'Bàn phím cơ', nhập từ khóa tìm kiếm và chọn sắp xếp giá từ thấp đến cao. Hệ thống hiển thị dòng thông báo 'Tìm thấy 3 sản phẩm' và danh sách bên dưới được xếp đúng thứ tự giá tăng dần. Em dùng fetch JavaScript ngầm nên trang web cập nhật danh sách ngay mà không cần reload.",
        "file": "index.php, js/main.js"
    },
    {
        "id": 4,
        "title": "Phân trang danh sách sản phẩm ở trang chủ (chuyển sang trang 2)",
        "url": "http://127.0.0.1:8080/index.php?page=2",
        "req": "D8",
        "desc": "Vì trang chủ quy định mỗi trang hiển thị tối đa 8 sản phẩm, nên khi có 12 sản phẩm hệ thống sẽ tự sinh ra thanh phân trang ở cuối trang. Khi em bấm sang Trang 2, hệ thống hiển thị tiếp 4 sản phẩm còn lại và vẫn giữ nguyên các bộ lọc danh mục đã chọn trước đó.",
        "file": "index.php"
    },
    {
        "id": 5,
        "title": "Trang chi tiết sản phẩm và danh sách sản phẩm liên quan cùng danh mục",
        "url": "http://127.0.0.1:8080/product.php?id=1",
        "req": "A2, B5, B7",
        "desc": "Khi bấm vào một sản phẩm bất kỳ, trang chi tiết mở ra hiển thị ảnh lớn, giá tiền, mô tả và bảng thông số kỹ thuật chi tiết của thiết bị. Phía dưới cùng, hệ thống tự động truy vấn và hiển thị 3 sản phẩm liên quan cùng danh mục để người mua tiện tham khảo thêm.",
        "file": "product.php"
    },
    {
        "id": 6,
        "title": "Trang báo lỗi 404 tùy biến khi mở ID sản phẩm không tồn tại",
        "url": "http://127.0.0.1:8080/product.php?id=99999",
        "req": "B5, D5",
        "desc": "Em thử nhập một đường dẫn có ID sản phẩm không tồn tại trong database (id=99999). Mã PHP kiểm tra nếu truy vấn trả về rỗng thì lập tức gọi hàm http_response_code(404) và hiển thị trang 404 thân thiện kèm nút quay về trang chủ, giúp người dùng không bị bối rối.",
        "file": "product.php, 404.php"
    },
    {
        "id": 7,
        "title": "Form đăng ký tài khoản và thông báo lỗi hợp lệ từ máy chủ",
        "url": "http://127.0.0.1:8080/register.php",
        "req": "A3, A7, C1",
        "desc": "Ở form đăng ký, em làm tính năng JavaScript kiểm tra hai ô mật khẩu phải trùng nhau trước khi gửi. Phía máy chủ PHP cũng kiểm tra lại một lần nữa: nếu email đã tồn tại trong database thì giữ lại các thông tin họ tên, email đã nhập và in thông báo lỗi màu đỏ rõ ràng để người dùng biết đường sửa.",
        "file": "register.php, js/main.js"
    },
    {
        "id": 8,
        "title": "Đăng nhập thành công và thanh điều hướng đổi sang trạng thái người dùng",
        "url": "http://127.0.0.1:8080/login.php",
        "req": "C2, C9",
        "desc": "Sau khi em nhập đúng tài khoản và mật khẩu, hệ thống sinh lại session id chống Session Fixation và hiển thị thông báo flash message 'Đăng nhập thành công'. Trên thanh điều hướng (header), nút 'Đăng nhập' được tự động thay thế bằng tên của em và menu tài khoản cá nhân.",
        "file": "login.php, includes/header.php"
    },
    {
        "id": 9,
        "title": "Form đăng nhập bị tạm khóa sau 5 lần nhập sai liên tiếp",
        "url": "http://127.0.0.1:8080/login.php",
        "req": "D10",
        "desc": "Để phòng chống tấn công dò mật khẩu (Brute-force), em cài đặt cơ chế đếm số lần sai trong session. Khi em thử gõ sai mật khẩu liên tiếp 5 lần, form đăng nhập lập tức bị vô hiệu hóa kèm thông báo yêu cầu chờ 5 phút mới được thử lại, mọi yêu cầu gửi lên lúc này đều bị server từ chối.",
        "file": "login.php"
    },
    {
        "id": 10,
        "title": "Trang giỏ hàng, tự động tính lại thành tiền và hộp thoại xác nhận khi xóa",
        "url": "http://127.0.0.1:8080/cart.php",
        "req": "A8, C3",
        "desc": "Giỏ hàng hiển thị danh sách các món em đã chọn mua, có đơn giá, số lượng và thành tiền từng món. Khi em bấm nút tăng giảm số lượng (+ hoặc -), JavaScript tự động tính lại thành tiền ngay lập tức. Khi bấm nút xóa toàn bộ giỏ, trình duyệt sẽ bật popup hỏi 'Bạn có chắc chắn muốn xóa giỏ hàng?' để tránh bấm nhầm.",
        "file": "cart.php, cart-update.php, js/main.js"
    },
    {
        "id": 11,
        "title": "Trang đặt hàng an toàn trong Transaction và trang chi tiết đơn hàng",
        "url": "http://127.0.0.1:8080/checkout.php",
        "req": "C4, C5",
        "desc": "Em điền địa chỉ nhận hàng, chọn phương thức thanh toán rồi bấm Xác nhận đặt hàng. Toàn bộ quá trình lưu đơn vào bảng orders, lưu từng món vào order_items và trừ số lượng tồn kho trong bảng products được em đặt trong một Transaction của CSDL để đảm bảo an toàn tuyệt đối, sau đó chuyển hướng về trang xem chi tiết đơn vừa tạo.",
        "file": "checkout.php, order-detail.php"
    },
    {
        "id": 12,
        "title": "Lịch sử đơn hàng và chức năng hủy đơn hàng đang chờ xử lý",
        "url": "http://127.0.0.1:8080/orders.php",
        "req": "C5, C6",
        "desc": "Trang này liệt kê tất cả các đơn hàng mà tài khoản của em đã từng mua. Với những đơn hàng đang ở trạng thái 'Chờ xử lý' (pending), hệ thống hiển thị nút 'Hủy đơn hàng'. Khi em bấm hủy, đơn chuyển sang trạng thái 'Đã hủy' và số lượng sản phẩm được tự động hoàn trả lại kho trong database.",
        "file": "orders.php, order-cancel.php"
    },
    {
        "id": 13,
        "title": "Trang thông tin tài khoản cá nhân và chức năng đổi mật khẩu",
        "url": "http://127.0.0.1:8080/profile.php và change-password.php",
        "req": "C7, D11",
        "desc": "Tại trang cá nhân, em có thể cập nhật lại số điện thoại và địa chỉ giao hàng của mình. Trong trang đổi mật khẩu, hệ thống bắt buộc em phải nhập đúng mật khẩu hiện tại trước khi thiết lập mật khẩu mới và dùng hàm password_hash để mã hóa mật khẩu mới vào cơ sở dữ liệu.",
        "file": "profile.php, change-password.php"
    },
    {
        "id": 14,
        "title": "Gửi form liên hệ và minh chứng dữ liệu được lưu vào bảng contacts",
        "url": "http://127.0.0.1:8080/contact.php",
        "req": "A7, C8",
        "desc": "Em thử điền form liên hệ gửi tin nhắn đóng góp ý kiến. JavaScript chặn không cho gửi nếu nội dung ít hơn 10 ký tự. Khi gửi hợp lệ, PHP dùng Prepared Statement để chèn dữ liệu vào bảng contacts trong database, đồng thời em chụp màn hình phpMyAdmin để chứng minh tin nhắn đã được lưu vào bảng.",
        "file": "contact.php"
    },
    {
        "id": 15,
        "title": "Tài khoản khách hàng thông thường bị chặn khi truy cập trang quản trị (HTTP 403)",
        "url": "http://127.0.0.1:8080/admin/index.php",
        "req": "D1",
        "desc": "Em đăng nhập bằng tài khoản khách hàng thông thường rồi cố tình gõ đường dẫn /admin/index.php trên trình duyệt. Ở đầu trang admin có hàm require_admin() kiểm tra quyền, nếu không phải admin thì lập tức ngắt kết nối và trả về mã lỗi HTTP 403 Forbidden kèm thông báo từ chối truy cập.",
        "file": "admin/index.php, includes/functions.php"
    },
    {
        "id": 16,
        "title": "Trang tổng quan Dashboard của Quản trị viên",
        "url": "http://127.0.0.1:8080/admin/index.php",
        "req": "D9",
        "desc": "Khi đăng nhập bằng tài khoản admin, trang tổng quan mở ra thống kê nhanh: tổng số đơn hàng theo từng trạng thái, tổng doanh thu từ các đơn đã hoàn tất, và bảng danh sách 5 sản phẩm có tồn kho thấp nhất (dưới 5 món) để admin kịp thời nhập thêm hàng.",
        "file": "admin/index.php"
    },
    {
        "id": 17,
        "title": "Quản lý danh sách sản phẩm và form thêm/sửa sản phẩm có tải ảnh an toàn",
        "url": "http://127.0.0.1:8080/admin/products.php và admin/product-form.php",
        "req": "D2, D3, D7",
        "desc": "Admin có thể xem danh sách toàn bộ sản phẩm có phân trang, sửa thông tin và tải ảnh mới lên. File PHP kiểm tra đuôi ảnh hợp lệ, đổi tên file ngẫu nhiên và tự động xóa file ảnh cũ trong thư mục uploads/ khi có ảnh mới được thay thế để tránh làm đầy dung lượng ổ đĩa.",
        "file": "admin/products.php, admin/product-form.php"
    },
    {
        "id": 18,
        "title": "Quản lý danh mục và thông báo cảnh báo khi xóa danh mục đang có sản phẩm",
        "url": "http://127.0.0.1:8080/admin/categories.php",
        "req": "D6",
        "desc": "Trang cho phép admin thêm danh mục mới hoặc đổi tên danh mục cũ. Khi em thử bấm xóa một danh mục đang có chứa sản phẩm, hệ thống sẽ kiểm tra trong bảng products và đưa ra thông báo cảnh báo màu đỏ không cho xóa để tránh làm hỏng liên kết dữ liệu của sản phẩm.",
        "file": "admin/categories.php"
    },
    {
        "id": 19,
        "title": "Quản lý đơn hàng, lọc theo trạng thái và cập nhật trạng thái đơn",
        "url": "http://127.0.0.1:8080/admin/orders.php",
        "req": "D4",
        "desc": "Admin xem được toàn bộ đơn hàng của khách, có thể lọc đơn theo trạng thái (Đang chờ, Đang giao, Đã hoàn tất, Đã hủy). Admin có thể đổi trạng thái đơn hàng trực tiếp bằng dropdown, hệ thống sẽ lưu ngầm và hiển thị thông báo đã cập nhật thành công.",
        "file": "admin/orders.php"
    },
    {
        "id": 20,
        "title": "Chặn truy cập trực tiếp file cấu hình từ trình duyệt (.htaccess HTTP 403)",
        "url": "http://127.0.0.1:8080/config/config.php",
        "req": "D5",
        "desc": "Em thử gõ trực tiếp đường dẫn file cấu hình /config/config.php hoặc thư mục /database/ trên trình duyệt. Nhờ tệp cấu hình .htaccess đặt trong các thư mục này với dòng lệnh 'Deny from all', máy chủ Apache lập tức chặn quyền truy cập và báo lỗi 403 Forbidden, giúp bảo vệ an toàn cho thông tin kết nối CSDL.",
        "file": "config/.htaccess, database/.htaccess"
    },
    {
        "id": 21,
        "title": "Sơ đồ cơ sở dữ liệu quan hệ và cấu trúc các bảng (phpMyAdmin Designer)",
        "url": "http://localhost/phpmyadmin (Thẻ Designer)",
        "req": "B2",
        "desc": "Sơ đồ cơ sở dữ liệu của dự án gồm 6 bảng có quan hệ chặt chẽ: categories, products, users, orders, order_items và contacts. Tất cả các bảng đều có khóa chính tự tăng (id), các khóa ngoại liên kết (ví dụ category_id, user_id, order_id) và sử dụng bảng mã utf8mb4 hiển thị tiếng Việt hoàn toàn không bị lỗi font.",
        "file": "N25DTCN007_LamNhutHaiDang_Database.sql"
    }
]
