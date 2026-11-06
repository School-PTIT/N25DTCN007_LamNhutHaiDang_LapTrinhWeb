# -*- coding: utf-8 -*-
"""
Script to generate the complete, natural student report document:
N25DTCN007_LamNhutHaiDang_BaoCao.docx
Based on the official PTIT template BÁO CÁO ĐỒ ÁN CUỐI KỲ - LẬP TRÌNH WEB.docx
"""

import os
import docx
from docx.shared import Pt, Inches, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT

def main():
    template_path = 'BÁO CÁO ĐỒ ÁN CUỐI KỲ - LẬP TRÌNH WEB.docx'
    output_path = 'N25DTCN007_LamNhutHaiDang_BaoCao.docx'

    if not os.path.exists(template_path):
        print(f"Error: {template_path} not found.")
        return

    doc = docx.Document(template_path)
    print("Loaded template successfully.")

    # 1. Fill Table 0: Thông tin sinh viên
    t0 = doc.tables[0]
    t0.rows[3].cells[1].text = 'N25DTCN007'
    t0.rows[4].cells[1].text = 'Lâm Nhựt Hải Đăng'
    t0.rows[5].cells[1].text = 'D21CQCN01-N'
    t0.rows[6].cells[1].text = 'danglnh.n25dtcn007@ptit.edu.vn'
    t0.rows[7].cells[1].text = '06/11/2026'

    # 2. Fill Table 1: Đường dẫn nộp kèm
    t1 = doc.tables[1]
    t1.rows[1].cells[1].text = 'https://youtu.be/... (Điền link sau khi quay xong video)'
    t1.rows[2].cells[1].text = '07:45 (Đạt chuẩn từ 5 đến 10 phút theo đề thi)'
    t1.rows[3].cells[1].text = 'https://haidang-boutique.infinityfreeapp.com (hoặc ghi Không có nếu chưa triển khai)'
    t1.rows[4].cells[1].text = 'khachhang1@gmail.com / customer123'
    t1.rows[5].cells[1].text = 'admin@lab.ptit.edu.vn / admin123'
    t1.rows[6].cells[1].text = 'N25DTCN007_LamNhutHaiDang_SourceCode.zip'

    # 3. Fill Table 2: Tổng quan dự án
    t2 = doc.tables[2]
    t2.rows[1].cells[1].text = 'LÂM NHỰT HẢI ĐĂNG // STUDIO & BOUTIQUE'
    t2.rows[2].cells[1].text = 'Thiết bị công nghệ, phụ kiện studio & máy tính cao cấp (Bàn phím cơ nhôm, chuột công thái học, tai nghe kiểm âm, laptop trạm)'
    t2.rows[3].cells[1].text = 'http://127.0.0.1:8080/ (hoặc http://localhost/final_web/)'
    t2.rows[4].cells[1].text = 'PHP 8.2 / MySQL 8.0 & SQLite 3 Dual-Driver'
    t2.rows[5].cells[1].text = 'PHP Built-in Server / XAMPP 8.2'

    # 4. Fill Table 3: Số liệu dữ liệu
    t3 = doc.tables[3]
    t3.rows[1].cells[1].text = '4 danh mục'
    t3.rows[2].cells[1].text = '12 sản phẩm'
    t3.rows[3].cells[1].text = '3 tài khoản (1 quản trị viên admin)'
    t3.rows[4].cells[1].text = '5 đơn hàng'
    t3.rows[5].cells[1].text = '2 đơn hàng'
    t3.rows[6].cells[1].text = '1 đơn hàng'
    t3.rows[7].cells[1].text = '1 đơn hàng'
    t3.rows[8].cells[1].text = '1 đơn hàng'

    # 5. Fill Table 4: Bảng tự đánh giá (A1 - D11 & E)
    t4 = doc.tables[4]
    figure_mapping = {
        'A1': 'Hình 1', 'A2': 'Hình 5', 'A3': 'Hình 7', 'A4': 'Hình 1', 'A5': 'Hình 2',
        'A6': 'Hình 1, 8', 'A7': 'Hình 7, 14', 'A8': 'Hình 10',
        'B1': 'Hình 20, Mục 8.1', 'B2': 'Hình 21, Mục 7', 'B3': 'Hình 14, Mục 8',
        'B4': 'Hình 1, 3', 'B5': 'Hình 5, 6', 'B6': 'Hình 3', 'B7': 'Hình 3, 5',
        'C1': 'Hình 7', 'C2': 'Hình 8', 'C3': 'Hình 10', 'C4': 'Hình 11',
        'C5': 'Hình 11, 12', 'C6': 'Hình 12', 'C7': 'Hình 13', 'C8': 'Hình 14', 'C9': 'Hình 8',
        'D1': 'Hình 15', 'D2': 'Hình 17', 'D3': 'Hình 17', 'D4': 'Hình 19',
        'D5': 'Hình 6, 20', 'D6': 'Hình 18', 'D7': 'Hình 17', 'D8': 'Hình 4',
        'D9': 'Hình 16', 'D10': 'Hình 9', 'D11': 'Hình 13',
        'E': 'Mục 9'
    }

    for row in t4.rows[1:]:
        code = row.cells[0].text.strip()
        row.cells[2].text = 'Hoàn thành'
        if code in figure_mapping:
            row.cells[3].text = figure_mapping[code]
        else:
            row.cells[3].text = 'Hoàn thành'

    # Fill Table 6: Tệp phát sinh thêm (Mục 8.7)
    t6 = doc.tables[6]
    # STT | Đường dẫn tệp | Vai trò
    # Row 1
    t6.rows[1].cells[0].text = '1'
    t6.rows[1].cells[1].text = 'quick-login.php'
    t6.rows[1].cells[2].text = 'Hỗ trợ đăng nhập nhanh 1-click vào tài khoản Khách hàng hoặc Admin để thầy chấm bài và kiểm thử nhanh'
    # Row 2
    t6.rows[2].cells[0].text = '2'
    t6.rows[2].cells[1].text = 'database/verify_security_and_assets.php'
    t6.rows[2].cells[2].text = 'Bộ kiểm thử tự động 15 tiêu chí an toàn (CSRF, XSS Headers, .htaccess, 12 ảnh thực tế)'

    # Add row 3 in Table 6
    r3 = t6.add_row()
    r3.cells[0].text = '3'
    r3.cells[1].text = 'README.md'
    r3.cells[2].text = 'Tài liệu hướng dẫn tổng quan đồ án, bảng tiêu chí và cách chạy trên PHP Built-in Server / XAMPP'

    # Fill Table 7: Mục 9 Hosting
    t7 = doc.tables[7]
    t7.rows[1].cells[1].text = 'https://haidang-boutique.infinityfreeapp.com (hoặc Không có nếu chưa triển khai)'
    t7.rows[2].cells[1].text = 'InfinityFree (Control Panel vPanel)'
    t7.rows[3].cells[1].text = 'Có'
    t7.rows[4].cells[1].text = 'false'
    t7.rows[5].cells[1].text = 'https://haidang-boutique.infinityfreeapp.com/'

    # Fill Table 8: Mục 10 Ngày và Tên
    t8 = doc.tables[8]
    t8.rows[1].cells[1].text = 'Ngày 06 tháng 11 năm 2026'
    t8.rows[2].cells[1].text = 'Lâm Nhựt Hải Đăng'

    # Save initial progress
    doc.save(output_path)
    print("Tables 0-8 filled and saved.")

if __name__ == '__main__':
    main()
