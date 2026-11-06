import urllib.request
import os

images = {
    # 1. Custom Pro 87 TKL Mechanical Keyboard
    "keyboard-pro-87.jpg": "https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=800&auto=format&fit=crop&q=85",
    # 2. Aluminum 75% Gasket Mount Mechanical Keyboard
    "keyboard-alu-75.jpg": "https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?w=800&auto=format&fit=crop&q=85",
    # 3. Slim Low-Profile Wireless Keyboard 68
    "keyboard-slim-68.jpg": "https://images.unsplash.com/photo-1595225476474-87563907a212?w=800&auto=format&fit=crop&q=85",
    # 4. Razer Blade 16 Studio Edition / Dark Gaming Laptop
    "laptop-razer-16.jpg": "https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=800&auto=format&fit=crop&q=85",
    # 5. MacBook Pro 16 M3 Max Space Black
    "macbook-pro-16.jpg": "https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&auto=format&fit=crop&q=85",
    # 6. ThinkPad P1 Gen 6 Workstation Laptop
    "laptop-thinkpad-p1.jpg": "https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=800&auto=format&fit=crop&q=85",
    # 7. Planar Magnetic Studio Pro Headphones
    "headphone-planar-9000.jpg": "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&auto=format&fit=crop&q=85",
    # 8. Wireless Low-Latency Gaming Headset
    "headphone-wireless-7000.jpg": "https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=800&auto=format&fit=crop&q=85",
    # 9. 100W Desktop Active Studio Monitor Speakers
    "speaker-monitor-5000.jpg": "https://images.unsplash.com/photo-1545454675-3531b543be5d?w=800&auto=format&fit=crop&q=85",
    # 10. 39g Magnesium Ultralight Wireless Honeycomb Mouse
    "mouse-magnesium-39g.jpg": "https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=800&auto=format&fit=crop&q=85",
    # 11. Master Ergonomic Pro Mouse (MX Master style)
    "mouse-ergonomic-pro.jpg": "https://images.unsplash.com/photo-1605773527852-c546a8584ea3?w=800&auto=format&fit=crop&q=85",
    # 12. Tempered Glass Lab Mat Pro Mousepad
    "mousepad-glass-lab.jpg": "https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=800&auto=format&fit=crop&q=85"
}

dest_dir = r"C:\Final web\uploads"
headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"}

print("Fetching 12 real product photos...")

for filename, url in images.items():
    file_path = os.path.join(dest_dir, filename)
    try:
        req = urllib.request.Request(url, headers=headers)
        with urllib.request.urlopen(req, timeout=15) as response:
            content = response.read()
            if len(content) > 5000:
                with open(file_path, "wb") as f:
                    f.write(content)
                print(f"[OK] Downloaded: {filename} ({len(content)} bytes)")
            else:
                print(f"[WARN] File too small: {filename} ({len(content)} bytes)")
    except Exception as e:
        print(f"[ERROR] Failed {filename}: {e}")

print("Done fetching product photos!")
