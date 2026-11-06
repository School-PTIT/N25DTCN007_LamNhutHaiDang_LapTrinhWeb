import sqlite3
import re

# 1. Update SQLite database
sqlite_path = r"C:\Final web\database\final_web.sqlite"
conn = sqlite3.connect(sqlite_path)
cur = conn.cursor()

cur.execute("SELECT id, image FROM products")
rows = cur.fetchall()

mapping = {
    "keyboard-pro-87.svg": "keyboard-pro-87.jpg",
    "keyboard-alu-75.svg": "keyboard-alu-75.jpg",
    "keyboard-slim-68.svg": "keyboard-slim-68.jpg",
    "laptop-razer-16.svg": "laptop-razer-16.jpg",
    "macbook-pro-16.svg": "macbook-pro-16.jpg",
    "laptop-thinkpad-p1.svg": "laptop-thinkpad-p1.jpg",
    "headphone-planar-9000.svg": "headphone-planar-9000.jpg",
    "headphone-wireless-7000.svg": "headphone-wireless-7000.jpg",
    "speaker-monitor-5000.svg": "speaker-monitor-5000.jpg",
    "mouse-magnesium-39g.svg": "mouse-magnesium-39g.jpg",
    "mouse-ergonomic-pro.svg": "mouse-ergonomic-pro.jpg",
    "mousepad-glass-lab.svg": "mousepad-glass-lab.jpg"
}

for old_img, new_img in mapping.items():
    cur.execute("UPDATE products SET image = ? WHERE image = ?", (new_img, old_img))

conn.commit()
cur.execute("SELECT id, sku, name, image FROM products")
updated_rows = cur.fetchall()
conn.close()

print(f"Updated {len(updated_rows)} products in SQLite.")
for r in updated_rows:
    print(f"ID {r[0]}: {r[1]} -> {r[3]}")

# 2. Update MySQL .sql dump file
sql_file = r"C:\Final web\N25DTCN007_LamNhutHaiDang_Database.sql"
with open(sql_file, "r", encoding="utf-8") as f:
    sql_content = f.read()

for old_img, new_img in mapping.items():
    sql_content = sql_content.replace(f"'{old_img}'", f"'{new_img}'")

with open(sql_file, "w", encoding="utf-8") as f:
    f.write(sql_content)

print("Updated N25DTCN007_LamNhutHaiDang_Database.sql successfully.")
