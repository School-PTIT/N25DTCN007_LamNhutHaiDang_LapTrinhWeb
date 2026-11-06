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

        print("1. Opening product.php?id=12 (Lab Mat Pro, stock=9)...")
        page.goto(f"{BASE_URL}/product.php?id=12", wait_until="networkidle")

        qty_input = page.locator("#qty-input")
        max_stock = qty_input.get_attribute("max") or qty_input.get_attribute("data-stock")
        print(f"   -> Detected stock limit: {max_stock}")

        # Test typing 11 (exceeding stock)
        print("2. Typing 11 into quantity input and clicking 'Thêm Vào Giỏ'...")
        qty_input.fill("11")
        page.wait_for_timeout(200)

        add_btn = page.locator(".btn-add-cart-ajax").first
        add_btn.click()
        page.wait_for_timeout(400)

        # Verify custom Studio Modal appears instead of native tooltip
        modal = page.locator("#studio-modal-backdrop")
        modal_classes = modal.get_attribute("class") or ""
        modal_title = page.locator("#studio-modal-title").inner_text()
        modal_msg = page.locator("#studio-modal-message").inner_text()
        print(f"   -> Custom Modal Title: '{modal_title}'")
        print(f"   -> Custom Modal Msg: '{modal_msg}'")
        assert "active" in modal_classes, "Custom modal should be active when quantity exceeds stock!"
        assert "Vượt quá" in modal_title or "tồn kho" in modal_title

        # Verify input was auto-clamped to 9
        clamped_val = qty_input.input_value()
        print(f"   -> Quantity input value auto-clamped to: {clamped_val}")
        assert clamped_val == "9", f"Expected clamped value 9, got {clamped_val}"

        page.screenshot(path=os.path.join(ARTIFACT_DIR, "verified_exceed_stock_popup_cart.png"))

        # Click 'Đã hiểu'
        confirm_btn = page.locator("#studio-modal-btn-confirm")
        confirm_btn.click()
        page.wait_for_timeout(300)

        # Test typing 15 and clicking 'Mua Ngay'
        print("3. Typing 15 into quantity input and clicking 'Mua Ngay'...")
        qty_input.fill("15")
        page.wait_for_timeout(200)

        express_btn = page.locator(".btn-express-buy").first
        express_btn.click()
        page.wait_for_timeout(400)

        modal_classes = modal.get_attribute("class") or ""
        modal_title = page.locator("#studio-modal-title").inner_text()
        print(f"   -> Express Buy Custom Modal Title: '{modal_title}'")
        assert "active" in modal_classes, "Custom modal should be active for Express Buy when quantity exceeds stock!"
        assert qty_input.input_value() == "9", "Quantity should be clamped to 9 for Express Buy"

        page.screenshot(path=os.path.join(ARTIFACT_DIR, "verified_exceed_stock_popup_express.png"))

        print("ALL QUANTITY VALIDATION TESTS PASSED 100%!")
        browser.close()

if __name__ == "__main__":
    run_tests()
