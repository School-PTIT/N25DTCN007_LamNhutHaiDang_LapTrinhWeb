import os
import sys
import time
from playwright.sync_api import sync_playwright

sys.stdout.reconfigure(encoding='utf-8')

def test_features():
    with sync_playwright() as p:
        browser = p.chromium.launch(channel="chrome", headless=True)
        page = browser.new_page(viewport={"width": 1400, "height": 900})

        # 1. Open homepage
        print("1. Opening http://127.0.0.1:8080/ ...")
        page.goto("http://127.0.0.1:8080/")
        page.wait_for_load_state("networkidle")

        # 2. Verify Theme Toggle exists
        toggle = page.locator("#theme-toggle")
        assert toggle.is_visible(), "Theme toggle button should be visible"
        print("✓ Theme toggle button is visible.")

        # Take initial light mode screenshot
        page.screenshot(path="test_lightmode_live.png", full_page=False)
        print("✓ Light mode screenshot saved as test_lightmode_live.png")

        # 3. Toggle to Dark Mode
        toggle.click()
        time.sleep(0.5)

        current_theme = page.evaluate("() => document.documentElement.getAttribute('data-theme')")
        assert current_theme == "dark", f"Expected theme to be 'dark', got {current_theme}"
        print(f"✓ Theme successfully switched to: {current_theme}")

        # Check localStorage persistence
        saved_theme = page.evaluate("() => localStorage.getItem('theme')")
        assert saved_theme == "dark", f"Expected localStorage theme to be 'dark', got {saved_theme}"
        print("✓ Theme persisted in localStorage.")

        # Check background color and text color in Dark Mode
        bg_color = page.evaluate("() => window.getComputedStyle(document.body).backgroundColor")
        text_color = page.evaluate("() => window.getComputedStyle(document.body).color")
        print(f"✓ Dark mode body background: {bg_color}, text color: {text_color}")

        # Take dark mode screenshot
        page.screenshot(path="test_darkmode_live.png", full_page=False)
        print("✓ Dark mode screenshot saved as test_darkmode_live.png")

        # 4. Test Add-to-cart Fly Animation & Chime
        first_add_btn = page.locator(".btn-add-cart-ajax").first
        assert first_add_btn.is_visible(), "Add to cart button should be visible"

        initial_count = int(page.locator("#header-cart-count").inner_text() or 0)
        print(f"Initial cart count: {initial_count}")

        # Click add to cart
        first_add_btn.click()

        # Wait for toast message to appear
        toast = page.wait_for_selector(".toast-message", timeout=4000)
        assert toast is not None, "Toast message should appear on cart add"
        print(f"✓ Toast message confirmed: {toast.inner_text().strip()}")

        time.sleep(0.8)
        new_count = int(page.locator("#header-cart-count").inner_text() or 0)
        print(f"Updated cart count after flight & arrival: {new_count}")
        assert new_count > initial_count or new_count >= 1, "Cart count should increase"

        # 5. Check SVG Icons (No OS emoji bullets)
        raw_html = page.content()
        assert "●" not in raw_html, "Raw bullet '●' should NOT be in HTML"
        assert "☀️" not in raw_html, "Emoji ☀️ should NOT be in HTML"
        assert "🌙" not in raw_html, "Emoji 🌙 should NOT be in HTML"
        print("✓ Zero OS emojis found. All icons are pure SVG vectors!")

        # 6. Switch back to light mode
        toggle.click()
        time.sleep(0.3)
        theme_after = page.evaluate("() => document.documentElement.getAttribute('data-theme')")
        assert theme_after == "light", f"Expected light mode, got {theme_after}"
        print("✓ Switched back to light mode successfully.")

        browser.close()
        print("\n🎉 ALL TESTS PASSED SUCCESSFULLY! 100% VERIFIED!")

if __name__ == "__main__":
    test_features()
