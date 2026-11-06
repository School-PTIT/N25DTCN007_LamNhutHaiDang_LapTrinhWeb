import os
import sys
import ftplib

sys.stdout.reconfigure(encoding='utf-8')

FTP_HOST = "ftpupload.net"
FTP_USER = "if0_43372666"
FTP_PASS = "xununam449977"

files_to_upload = [
    ("css/style.css", "htdocs/css/style.css"),
    ("includes/header.php", "htdocs/includes/header.php"),
    ("login.php", "htdocs/login.php"),
    ("product.php", "htdocs/product.php"),
    ("admin/index.php", "htdocs/admin/index.php"),
    ("admin/categories.php", "htdocs/admin/categories.php"),
    ("admin/orders.php", "htdocs/admin/orders.php"),
    ("admin/products.php", "htdocs/admin/products.php"),
    ("admin/product-form.php", "htdocs/admin/product-form.php"),
]

print("Connecting to FTP...")
ftp = ftplib.FTP(FTP_HOST, FTP_USER, FTP_PASS, timeout=30)
ftp.set_pasv(True)
print("FTP Connected successfully:", ftp.getwelcome())

for local_path, remote_path in files_to_upload:
    print(f"Uploading {local_path} -> {remote_path} ...")
    with open(local_path, "rb") as f:
        ftp.storbinary(f"STOR /{remote_path}", f)
    print(f"[OK] Uploaded {remote_path}")

ftp.quit()
print("\nAll files successfully deployed to hosting https://n25dtcn007-haidang.rf.gd/ !")
