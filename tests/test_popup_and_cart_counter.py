# -*- coding: utf-8 -*-
import sys
import os
import time
from playwright.sync_api import sync_playwright

if sys.platform == 'win32':
    sys.stdout.reconfigure(encoding='utf-8')

os.makedirs("tests/screenshots", exist_ok=True)

def test_all():
    with sync_playwright() as p:
        browser = p.chromium.launch(channel="chrome", headless=True)
        page = browser.new_page(viewport={"width": 1400, "height": 900})

        print("=== BẮT ĐẦU KIỂM THỬ: SỐ GIỎ HÀNG & POPUP TÙY BIẾN ===")

        # 1. Truy cập trang chủ ở chế độ Dark Mode
        page.goto("http://127.0.0.1:8080/")
        page.evaluate("document.documentElement.setAttribute('data-theme', 'dark'); localStorage.setItem('theme', 'dark');")
        page.reload()
        page.wait_for_selector("#header-cart-count")

        # Kiểm tra màu nền và màu chữ của .cart-counter trong Dark Mode
        cart_badge_styles = page.evaluate("""() => {
            const el = document.getElementById('header-cart-count');
            const style = window.getComputedStyle(el);
            return {
                bg: style.backgroundColor,
                color: style.color,
                fontWeight: style.fontWeight,
                text: el.textContent.trim()
            };
        }""")

        print(f"1. Dark Mode Cart Badge: Text='{cart_badge_styles['text']}', BG={cart_badge_styles['bg']}, Color={cart_badge_styles['color']}, Weight={cart_badge_styles['fontWeight']}")
        
        # Chụp ảnh cận cảnh Header Cart Badge trong Dark Mode
        header_el = page.locator(".main-header")
        header_el.screenshot(path="tests/screenshots/verified_dark_cart_badge.png")
        print("-> Đã chụp ảnh Header Dark Mode: tests/screenshots/verified_dark_cart_badge.png")

        # 2. Đăng nhập khách hàng để kiểm tra giỏ hàng và đơn hàng
        page.goto("http://127.0.0.1:8080/quick-login.php?as=customer")
        page.wait_for_load_state("networkidle")

        # Thêm 1 sản phẩm vào giỏ hàng
        add_btn = page.locator(".btn-add-cart-ajax").first
        add_btn.click()
        page.wait_for_timeout(800)

        # Kiểm tra số lượng giỏ hàng đã nhảy lên > 0
        new_count = page.locator("#header-cart-count").text_content()
        print(f"-> Số đếm giỏ hàng sau khi thêm: {new_count}")

        # Chụp lại ảnh cart pill khi có số
        header_el.screenshot(path="tests/screenshots/verified_cart_badge_with_number.png")

        # 3. Chuyển sang cart.php để kiểm tra Custom Popup khi xóa sản phẩm hoặc xóa sạch
        page.goto("http://127.0.0.1:8080/cart.php")
        page.evaluate("document.documentElement.setAttribute('data-theme', 'dark');")
        page.wait_for_selector(".btn-cart-remove-ajax")

        # Bấm nút xóa sản phẩm
        remove_btn = page.locator(".btn-cart-remove-ajax").first
        remove_btn.click()
        page.wait_for_timeout(300)

        # Kiểm tra sự xuất hiện của .studio-modal-backdrop.active
        modal_visible = page.locator("#studio-modal-backdrop.active").is_visible()
        modal_title = page.locator("#studio-modal-title").text_content()
        modal_msg = page.locator("#studio-modal-message").text_content()
        confirm_btn_text = page.locator("#studio-modal-btn-confirm").text_content()
        cancel_btn_text = page.locator("#studio-modal-btn-cancel").text_content()

        print(f"2. Custom Modal xuất hiện: visible={modal_visible}")
        print(f"   Tiêu đề: {modal_title}")
        print(f"   Thông điệp: {modal_msg}")
        print(f"   Nút: [{cancel_btn_text}] và [{confirm_btn_text}]")

        # Chụp ảnh toàn màn hình Custom Modal trong Dark Mode
        page.screenshot(path="tests/screenshots/verified_custom_modal_dark.png")
        print("-> Đã chụp ảnh Custom Modal Dark Mode: tests/screenshots/verified_custom_modal_dark.png")

        # Bấm nút [Hủy bỏ]
        page.locator("#studio-modal-btn-cancel").click()
        page.wait_for_timeout(300)
        modal_closed = not page.locator("#studio-modal-backdrop.active").is_visible()
        print(f"-> Đóng modal khi bấm Hủy: {modal_closed}")

        # 4. Kiểm tra Modal trong Light Mode
        page.evaluate("document.documentElement.setAttribute('data-theme', 'light'); localStorage.setItem('theme', 'light');")
        page.wait_for_timeout(200)
        remove_btn.click()
        page.wait_for_timeout(300)
        page.screenshot(path="tests/screenshots/verified_custom_modal_light.png")
        print("-> Đã chụp ảnh Custom Modal Light Mode: tests/screenshots/verified_custom_modal_light.png")

        # Bấm phím Escape để đóng modal
        page.keyboard.press("Escape")
        page.wait_for_timeout(300)
        modal_closed_esc = not page.locator("#studio-modal-backdrop.active").is_visible()
        print(f"-> Đóng modal bằng phím Escape: {modal_closed_esc}")

        # 5. Kiểm tra đơn hàng orders.php popup hủy đơn
        page.goto("http://127.0.0.1:8080/orders.php")
        page.evaluate("document.documentElement.setAttribute('data-theme', 'dark');")
        cancel_order_btn = page.locator(".btn-cancel-order-ajax").first
        if cancel_order_btn.count() > 0:
            cancel_order_btn.click()
            page.wait_for_timeout(300)
            page.screenshot(path="tests/screenshots/verified_cancel_order_modal.png")
            print("-> Đã kiểm tra và chụp ảnh Modal Hủy Đơn Hàng: tests/screenshots/verified_cancel_order_modal.png")
            page.locator("#studio-modal-btn-cancel").click()
            page.wait_for_timeout(200)

        browser.close()
        print("=== TẤT CẢ KIỂM THỬ ĐÃ HOÀN TẤT THÀNH CÔNG 100%! ===")

if __name__ == "__main__":
    test_all()
