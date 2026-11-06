import os
import sys
import ftplib

sys.stdout.reconfigure(encoding='utf-8')

FTP_HOST = "ftpupload.net"
FTP_USER = "if0_43372666"
FTP_PASS = "xununam449977"

files_to_upload = [
    ("index.php", "htdocs/index.php"),
    ("product.php", "htdocs/product.php"),
    ("cart-update.php", "htdocs/cart-update.php"),
    ("config/db.php", "htdocs/config/db.php"),
    ("includes/functions.php", "htdocs/includes/functions.php"),
    ("includes/header.php", "htdocs/includes/header.php"),
    ("includes/footer.php", "htdocs/includes/footer.php"),
    ("css/style.css", "htdocs/css/style.css"),
    ("js/main.js", "htdocs/js/main.js"),
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
print("\nAll Ultrathinking UX/UI files successfully deployed to https://n25dtcn007-haidang.rf.gd/ !")
