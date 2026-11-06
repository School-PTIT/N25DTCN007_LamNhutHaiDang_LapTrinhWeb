# -*- coding: utf-8 -*-
import sys
import os
from playwright.sync_api import sync_playwright

if sys.platform == 'win32':
    sys.stdout.reconfigure(encoding='utf-8')

BASE_URL = "http://127.0.0.1:8080"
ARTIFACT_DIR = r"C:\Users\kyuu4\.gemini\antigravity\brain\df2a50c8-9034-4f43-812c-449493381f35"

def run_tests():
    with sync_playwright() as p:
        browser = p.chromium.launch(channel="chrome", headless=True)
        context = browser.new_context(viewport={"width": 1440, "height": 900})
        page = context.new_page()

        console_errors = []
        page.on("console", lambda msg: print(f"CONSOLE [{msg.type}]: {msg.text}"))
        page.on("pageerror", lambda err: print(f"PAGE ERROR: {err}"))
        page.on("response", lambda res: print(f"HTTP {res.status}: {res.url}") if res.status >= 400 or "cart" in res.url else None)

        print("1. Opening Home Page and adding 2 items to cart...")
        page.goto(f"{BASE_URL}/index.php", wait_until="networkidle")
        
        add_btns = page.locator(".btn-add-cart-ajax")
        assert add_btns.count() >= 2, "Need at least 2 products on index.php"
        
        modal_backdrop = page.locator("#studio-modal-backdrop")
        cancel_btn = page.locator("#studio-modal-btn-cancel")
        confirm_btn = page.locator("#studio-modal-btn-confirm")

        add_btns.nth(0).click()
        page.wait_for_timeout(750)
        assert "active" in (modal_backdrop.get_attribute("class") or ""), "Add to cart modal did not open on 1st item!"
        page.screenshot(path=os.path.join(ARTIFACT_DIR, "verified_add_to_cart_popup_home.png"))
        cancel_btn.click() # Click 'Tiếp tục mua sắm'
        page.wait_for_timeout(250)

        add_btns.nth(1).click()
        page.wait_for_timeout(750)
        assert "active" in (modal_backdrop.get_attribute("class") or ""), "Add to cart modal did not open on 2nd item!"
        cancel_btn.click() # Click 'Tiếp tục mua sắm'
        page.wait_for_timeout(250)

        cart_badge = page.locator("#header-cart-count").inner_text().strip()
        print(f"   -> Header cart count after adding 2 products via Popup: {cart_badge}")
        assert int(cart_badge) >= 2, f"Expected cart count >= 2, got {cart_badge}"

        print("1.5 Testing Add to Cart Popup on Product Detail Page (product.php)...")
        page.goto(f"{BASE_URL}/product.php?id=1", wait_until="networkidle")
        detail_add_btn = page.locator(".btn-add-cart-ajax").first
        detail_add_btn.click()
        page.wait_for_timeout(750)
        assert "active" in (modal_backdrop.get_attribute("class") or ""), "Add to cart modal did not open on product.php!"
        page.screenshot(path=os.path.join(ARTIFACT_DIR, "verified_add_to_cart_popup_detail.png"))
        confirm_btn.click() # Click 'Xem giỏ hàng ngay' -> should navigate to cart.php!
        page.wait_for_load_state("networkidle")
        assert "cart.php" in page.url, f"Expected redirect to cart.php when clicking Xem giỏ hàng ngay, got {page.url}"
        print("   -> Product Detail Add-to-Cart Popup & 'Xem giỏ hàng ngay' navigation: PASS")

        print("2. Testing Single Item Remove on cart.php...")
        rows_before = page.locator("tr[id^='cart-row-']").count()
        print(f"   -> Cart rows before remove: {rows_before}")
        assert rows_before >= 2, "Expected at least 2 rows in cart"

        first_remove_btn = page.locator(".btn-cart-remove-ajax").first
        first_id = first_remove_btn.get_attribute("data-id")
        first_remove_btn.click()
        page.wait_for_timeout(300)

        # Verify Custom Modal Appears
        assert "active" in (modal_backdrop.get_attribute("class") or ""), "Custom modal did not open on remove click!"
        page.screenshot(path=os.path.join(ARTIFACT_DIR, "verified_delete_item_modal.png"))

        # Click Confirm Delete inside custom modal
        confirm_btn.click()
        page.wait_for_timeout(500)

        # Verify row is removed from DOM
        removed_row_count = page.locator(f"#cart-row-{first_id}").count()
        rows_after = page.locator("tr[id^='cart-row-']").count()
        print(f"   -> Row #{first_id} count after confirm: {removed_row_count}, total rows left: {rows_after}")
        assert removed_row_count == 0, f"Row #cart-row-{first_id} was not removed!"
        assert rows_after == rows_before - 1, "Row count did not decrease by 1!"
        page.screenshot(path=os.path.join(ARTIFACT_DIR, "verified_after_single_delete.png"))

        print("3. Testing Clear Entire Cart (.btn-cart-clear-ajax)...")
        clear_btn = page.locator(".btn-cart-clear-ajax")
        clear_btn.click()
        page.wait_for_timeout(300)
        assert "active" in (modal_backdrop.get_attribute("class") or ""), "Custom modal did not open on clear cart click!"
        
        confirm_btn.click()
        page.wait_for_timeout(700)
        page.wait_for_load_state("networkidle")

        cart_badge_after_clear = page.locator("#header-cart-count").inner_text().strip()
        print(f"   -> Cart badge after clear: {cart_badge_after_clear}")
        assert cart_badge_after_clear == "0", f"Expected cart count 0 after clear, got {cart_badge_after_clear}"
        assert page.locator("tr[id^='cart-row-']").count() == 0, "Expected 0 rows after clear cart"
        page.screenshot(path=os.path.join(ARTIFACT_DIR, "verified_after_clear_cart.png"))

        print("4. Testing Admin Category Delete via Custom Modal...")
        page.goto(f"{BASE_URL}/quick-login.php?as=admin", wait_until="networkidle")
        page.goto(f"{BASE_URL}/admin/categories.php", wait_until="networkidle")

        # Create a temporary category to delete
        page.fill("#cat-name", "Danh Mục Test Xóa")
        page.fill("#cat-desc", "Danh mục tạm để kiểm thử nút xóa")
        page.click("button:has-text('Tạo Danh Mục')")
        page.wait_for_load_state("networkidle")

        assert "Danh Mục Test Xóa" in page.content(), "Failed to create temporary category"
        
        # Find the delete button for that row via its data-confirm attribute
        del_btn = page.locator("button[data-confirm*='Danh Mục Test Xóa']")
        del_btn.click()
        page.wait_for_timeout(300)
        assert "active" in (modal_backdrop.get_attribute("class") or ""), "Custom modal did not open on Admin Category delete!"
        
        confirm_btn.click()
        page.wait_for_load_state("networkidle")
        assert "Danh Mục Test Xóa" not in page.content(), "Temporary category was not deleted after confirming modal!"
        print("   -> Admin category delete via custom modal: PASS")

        print("ALL DELETE TESTS PASSED 100%!")
        browser.close()

if __name__ == "__main__":
    run_tests()
