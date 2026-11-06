import os
import sys
import ftplib

sys.stdout.reconfigure(encoding='utf-8')

FTP_HOST = "ftpupload.net"
FTP_USER = "if0_43372666"
FTP_PASS = "xununam449977"

files_to_upload = [
    ("css/style.css", "htdocs/css/style.css"),
    ("js/main.js", "htdocs/js/main.js"),
    ("includes/functions.php", "htdocs/includes/functions.php"),
    ("includes/header.php", "htdocs/includes/header.php"),
    ("includes/footer.php", "htdocs/includes/footer.php"),
    ("index.php", "htdocs/index.php"),
    ("product.php", "htdocs/product.php"),
    ("order-detail.php", "htdocs/order-detail.php"),
    ("admin/index.php", "htdocs/admin/index.php"),
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
