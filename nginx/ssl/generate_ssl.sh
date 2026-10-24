#!/usr/bin/env bash
# ====================================================================
# SCRIPT TẠO CHỨNG CHỈ SSL TỰ KÝ CHO NGINX TRÊN UBUNTU SERVER
# Dựa trên hướng dẫn Lab 1 - Cấu hình HTTPS (Trang 4)
# ====================================================================

set -e

SSL_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SSL_DIR"

echo "[*] Đang khởi tạo thư mục và chứng chỉ SSL tự ký cho gym.local..."

# Lệnh chuẩn hóa theo tài liệu Lab
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout gym.local.key \
    -out gym.local.crt \
    -subj "/C=VN/ST=ThaiNguyen/O=LabWebServer/CN=gym.local" \
    -addext "subjectAltName=DNS:gym.local,DNS:localhost,IP:127.0.0.1"

# Phân quyền bảo mật file private key
chmod 600 gym.local.key
chmod 644 gym.local.crt

echo "[+] Tạo thành công hai file: gym.local.key và gym.local.crt!"
