#!/usr/bin/env bash
# ====================================================================
# SCRIPT THIẾT LẬP TƯỜNG LỬA IPTABLES (LAB 9 - PHẦN 1)
# Áp dụng Hardening bảo vệ máy chủ hệ thống Gym & Fitness
# ====================================================================

set -e

echo "[+] 1. Cập nhật hệ thống và cài đặt iptables-persistent..."
sudo apt update && sudo apt install -y iptables iptables-persistent

echo "[+] 2. Xóa toàn bộ quy tắc (rule) cũ..."
sudo iptables -F
sudo iptables -X
sudo iptables -Z

echo "[+] 3. Cho phép các kết nối đã thiết lập (ESTABLISHED, RELATED)..."
sudo iptables -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT

echo "[+] 4. Cho phép giao tiếp nội bộ Loopback (lo)..."
sudo iptables -A INPUT -i lo -j ACCEPT

echo "[+] 5. Mở cổng SSH tùy biến (Port 2849)..."
sudo iptables -A INPUT -p tcp --dport 2849 -j ACCEPT

echo "[+] 6. Mở cổng Web HTTP (Port 80) và HTTPS (Port 443)..."
sudo iptables -A INPUT -p tcp --dport 80 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 443 -j ACCEPT

echo "[+] 7. Giới hạn Ping (ICMP Rate Limit 1/s chống Ping Flood)..."
sudo iptables -A INPUT -p icmp --icmp-type echo-request -m limit --limit 1/s -j ACCEPT

echo "[+] 8. Phòng chống tấn công SYN Flood..."
sudo iptables -A INPUT -p tcp --syn -m limit --limit 1/s --limit-burst 3 -j ACCEPT

echo "[+] 9. Thiết lập chính sách mặc định: DROP toàn bộ INPUT và FORWARD không xác định..."
sudo iptables -P INPUT DROP
sudo iptables -P FORWARD DROP
sudo iptables -P OUTPUT ACCEPT

echo "[+] 10. Lưu lại quy tắc bảo vệ vĩnh viễn..."
sudo netfilter-persistent save

echo "=========================================================="
echo " KIỂM TRA TRẠNG THÁI IPTABLES HIỆN TẠI:"
echo "=========================================================="
sudo iptables -L -n -v
