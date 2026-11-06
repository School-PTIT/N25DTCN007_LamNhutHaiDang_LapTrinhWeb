import os
import zipfile

ZIP_NAME = "N25DTCN007_LamNhutHaiDang_SourceCode.zip"

EXCLUDE_DIRS = {'.git', '.agents', '.gemini', '.vscode', 'tests', 'NOP_BAI_N25DTCN007', '__pycache__'}
EXCLUDE_EXTS = {'.zip', '.docx', '.pdf'}

with zipfile.ZipFile(ZIP_NAME, 'w', zipfile.ZIP_DEFLATED) as z:
    for root, dirs, files in os.walk('.'):
        dirs[:] = [d for d in dirs if d not in EXCLUDE_DIRS]
        for f in files:
            if f == ZIP_NAME or any(f.endswith(ext) for ext in EXCLUDE_EXTS) or f.endswith('.py'):
                continue
            rel_path = os.path.relpath(os.path.join(root, f), '.')
            z.write(rel_path, rel_path)

print(f"Built {ZIP_NAME} successfully!")
