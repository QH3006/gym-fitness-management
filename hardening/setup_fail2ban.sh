#!/usr/bin/env bash
# ====================================================================
# SCRIPT CẤU HÌNH FAIL2BAN CHỐNG BRUTE FORCE (LAB 9 - PHẦN 3)
# Tự động ban IP tấn công dò quét SSH cổng 2849
# ====================================================================

set -e

echo "[+] 1. Cài đặt dịch vụ Fail2Ban..."
sudo apt update && sudo apt install -y fail2ban

echo "[+] 2. Tạo file cấu hình jail.local..."
sudo bash -c "cat << 'EOF' > /etc/fail2ban/jail.local
[DEFAULT]
bantime  = 3600
findtime = 600
maxretry = 3

[sshd]
enabled  = true
port     = 2849
logpath  = /var/log/auth.log
backend  = %(sshd_backend)s
EOF"

echo "[+] 3. Khởi động lại và bật tự khởi động Fail2Ban..."
sudo systemctl restart fail2ban
sudo systemctl enable fail2ban

echo "=========================================================="
echo " TRẠNG THÁI JAIL FAIL2BAN HIỆN TẠI:"
echo "=========================================================="
sudo fail2ban-client status
sudo fail2ban-client status sshd
