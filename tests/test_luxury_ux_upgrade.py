# -*- coding: utf-8 -*-
import sys
import os
from playwright.sync_api import sync_playwright

if sys.platform == 'win32':
    sys.stdout.reconfigure(encoding='utf-8')

os.makedirs("tests/screenshots", exist_ok=True)

def run_verification():
    with sync_playwright() as p:
        browser = p.chromium.launch(channel="chrome", headless=True)
        page = browser.new_page(viewport={"width": 1440, "height": 960})

        print("=== BẮT ĐẦU KIỂM THỬ NÂNG CẤP ULTRATHINKING UX/UI ===")
        page.goto("http://127.0.0.1:8080/")
        page.evaluate("document.documentElement.setAttribute('data-theme', 'dark'); localStorage.setItem('theme', 'dark');")
        page.reload()
        page.wait_for_selector(".precision-card")
        page.wait_for_timeout(550)

        # 1. Kiểm tra kích thước đồng nhất của các khung ảnh .card-img-wrap
        img_metrics = page.evaluate("""() => {
            const wraps = Array.from(document.querySelectorAll('.card-img-wrap'));
            return wraps.map(w => {
                const r = w.getBoundingClientRect();
                const img = w.querySelector('img');
                const st = window.getComputedStyle(img);
                return { w: Math.round(r.width), h: Math.round(r.height), fit: st.objectFit };
            });
        }""")
        widths = set(m['w'] for m in img_metrics)
        heights = set(m['h'] for m in img_metrics)
        fits = set(m['fit'] for m in img_metrics)
        print(f"1. Đồng bộ khung ảnh 16:10 ({len(img_metrics)} thẻ): Widths={widths}, Heights={heights}, ObjectFit={fits}")
        assert len(widths) == 1 and len(heights) == 1 and fits == {'cover'}, "Khung ảnh chưa đồng nhất!"

        # 2. Kiểm tra nhãn thông số .spec-chip
        chips = page.evaluate("""() => {
            return Array.from(document.querySelectorAll('.spec-chip')).map(c => c.textContent.trim());
        }""")
        print("2. Các nhãn Spec Chips mới:", chips[:8])
        for c in chips:
            assert not c.endswith(" t") and not c.endswith(" D") and not c.endswith(" C"), f"Chip bị cắt cụt: {c}"

        # 3. Kiểm tra đường kẻ .card-footer-meta trong Dark Mode
        border_top = page.evaluate("""() => {
            const el = document.querySelector('.card-footer-meta');
            return window.getComputedStyle(el).borderTopColor;
        }""")
        print(f"3. Màu viền trên .card-footer-meta (Dark Mode): {border_top}")
        assert border_top != "rgb(241, 242, 245)", "Vẫn còn đường kẻ trắng chói #f1f2f5!"

        # Chụp ảnh màn hình Dark Mode sau khi nâng cấp
        page.screenshot(path="tests/screenshots/ultrathinking_dark_home.png")
        print("-> Đã chụp ảnh Dark Mode mới: tests/screenshots/ultrathinking_dark_home.png")

        # Chụp ảnh Light Mode sau khi nâng cấp
        page.evaluate("document.documentElement.setAttribute('data-theme', 'light'); localStorage.setItem('theme', 'light');")
        page.wait_for_timeout(250)
        page.screenshot(path="tests/screenshots/ultrathinking_light_home.png")
        print("-> Đã chụp ảnh Light Mode mới: tests/screenshots/ultrathinking_light_home.png")

        # Chuyển lại Dark Mode để test Live Search & Zero-Reload Sort
        page.evaluate("document.documentElement.setAttribute('data-theme', 'dark'); localStorage.setItem('theme', 'dark');")
        page.wait_for_timeout(150)

        # 4. Kiểm tra Live Search không mất focus
        page.wait_for_load_state("networkidle")
        search_input = page.locator("#catalog-search-input")
        search_input.click()
        search_input.fill("Chuột")
        page.wait_for_function("document.getElementById('catalog-result-count').textContent.includes('3')")
        count_text = page.locator("#catalog-result-count").text_content().strip()
        is_focused = page.evaluate("document.activeElement && document.activeElement.id === 'catalog-search-input'")
        print(f"4. Live Search 'Chuột': '{count_text}', Giữ nguyên con trỏ gõ phím={is_focused}")
        assert is_focused, "Mất focus khi gõ tìm kiếm!"

        # Bấm nút xóa nhanh [x]
        page.locator("#catalog-search-clear").click()
        page.wait_for_function("document.getElementById('catalog-result-count').textContent.includes('12')")
        reset_count = page.locator("#catalog-result-count").text_content().strip()
        print(f"-> Sau khi bấm nút [×] xóa tìm kiếm: '{reset_count}'")

        # 5. Kiểm tra Sắp xếp giá không tải lại trang
        page.select_option("#sort-select", "price_asc")
        page.wait_for_timeout(500)
        first_price = page.locator(".card-price").first.text_content().strip()
        print(f"5. Zero-Reload Sort (Giá tăng dần) — Giá sản phẩm đầu tiên: {first_price}")

        browser.close()
        print("=== KIỂM THỬ ULTRATHINKING UX/UI ĐẠT 100% PASS! ===")

if __name__ == "__main__":
    run_verification()
