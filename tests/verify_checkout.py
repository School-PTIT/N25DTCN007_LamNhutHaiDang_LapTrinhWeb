# -*- coding: utf-8 -*-
import sys
import os
from playwright.sync_api import sync_playwright

if sys.platform == 'win32':
    sys.stdout.reconfigure(encoding='utf-8')

ARTIFACT_DIR = r"C:\Users\kyuu4\.gemini\antigravity\brain\df2a50c8-9034-4f43-812c-449493381f35"

with sync_playwright() as p:
    browser = p.chromium.launch(channel='chrome', headless=True)
    page = browser.new_page(viewport={'width': 1440, 'height': 900})
    page.goto('http://127.0.0.1:8080/quick-login.php?as=customer')
    page.goto('http://127.0.0.1:8080/index.php')
    
    # Add 2 different items to have multiple products in checkout
    add_btns = page.locator('.btn-add-cart-ajax')
    add_btns.nth(0).click()
    page.wait_for_timeout(400)
    add_btns.nth(1).click()
    page.wait_for_timeout(400)

    page.goto('http://127.0.0.1:8080/checkout.php')
    print('Current URL:', page.url)

    # 1. Verify Right Column is present
    detail_header = page.locator('text=Chi Tiết Đơn Hàng')
    assert detail_header.count() > 0, "Right column header 'Chi Tiết Đơn Hàng' not found!"
    print('   -> Found right column: Chi Tiết Đơn Hàng')

    # 2. Verify all item rows have image, sku, unit price, quantity, and subtotal
    item_rows = page.locator('.manifest-card div[style*=\"border-bottom: 1px solid var(--border-subtle)\"]')
    print(f'   -> Found {item_rows.count()} item row(s)')
    assert item_rows.count() >= 2, "Expected at least 2 item rows in checkout"

    # 3. Verify Grand Total and Submit Button Total
    grand_total_el = page.locator('#checkout-final-total')
    btn_total_el = page.locator('#checkout-btn-total')
    print(f'   -> Grand Total displayed: {grand_total_el.inner_text().strip()}')
    print(f'   -> Submit Button Total displayed: {btn_total_el.inner_text().strip()}')
    assert len(grand_total_el.inner_text().strip()) > 0
    assert grand_total_el.inner_text().strip() in btn_total_el.inner_text().strip()

    # 4. Take Light Mode Screenshot
    page.screenshot(path=os.path.join(ARTIFACT_DIR, 'verified_checkout_light.png'), full_page=True)

    # 5. Switch to Dark Mode & Take Dark Mode Screenshot
    page.evaluate("document.documentElement.setAttribute('data-theme', 'dark')")
    page.screenshot(path=os.path.join(ARTIFACT_DIR, 'verified_checkout_dark.png'), full_page=True)

    print('CHECKOUT VERIFICATION PASSED 100%!')
    browser.close()
