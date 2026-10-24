#!/usr/bin/env bash
# ====================================================================
# SCRIPT CẤU HÌNH SSH HARDENING (LAB 9 - PHẦN 2)
# Chuyển cổng sang 2849, cấm mật khẩu, chỉ dùng SSH Key, cấm root
# ====================================================================

set -e

echo "[!] CẢNH BÁO: Hãy đảm bảo bạn ĐÃ COPY SSH PUBLIC KEY vào ~/.ssh/authorized_keys trước khi chạy script này!"
read -p "Bạn đã thêm SSH key chưa? (y/n): " confirm
if [ "$confirm" != "y" ]; then
    echo "Hủy thao tác để tránh bị khóa máy chủ."
    exit 1
fi

SSHD_CONF="/etc/ssh/sshd_config"

echo "[+] 1. Sao lưu cấu hình sshd_config gốc..."
sudo cp "$SSHD_CONF" "${SSHD_CONF}.bak_$(date +%F)"

echo "[+] 2. Ghi đè các tham số bảo vệ nghiêm ngặt (Lab 9)..."
sudo bash -c "cat << 'EOF' > /etc/ssh/sshd_config.d/99-hardening.conf
Port 2849
Protocol 2
PermitRootLogin no
PasswordAuthentication no
PubkeyAuthentication yes
PermitEmptyPasswords no
ClientAliveInterval 300
ClientAliveCountMax 0
MaxAuthTries 3
LogLevel VERBOSE
EOF"

echo "[+] 3. Kiểm tra cú pháp cấu hình SSH..."
sudo sshd -t

echo "[+] 4. Mở cổng 2849 trên tường lửa iptables..."
sudo iptables -A INPUT -p tcp --dport 2849 -j ACCEPT
sudo netfilter-persistent save

echo "[+] 5. Khởi động lại dịch vụ SSH..."
sudo systemctl daemon-reload
sudo systemctl restart ssh

echo "[+] Kiểm tra cổng lắng nghe SSH mới:"
sudo ss -tlnp | grep 2849
echo "=> Cấu hình SSH Hardening thành công!"
