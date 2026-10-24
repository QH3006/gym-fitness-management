#!/usr/bin/env bash
# ====================================================================
# SCRIPT HƯỚNG DẪN TỰ ĐỘNG THỰC HIỆN 3 COMMIT CHUẨN ĐỀ THI LÊN GITHUB
# Môn: Triển khai và Quản trị Hệ thống Phần mềm (Đề 23 - Quản lý Gym)
# ====================================================================

set -e

echo "=== BẮT ĐẦU QUY TRÌNH TẠO 3 COMMITS THEO ĐÚNG TIÊU CHÍ BAREM CHẤM ĐIỂM ==="

# Khởi tạo Git repo nếu chưa có
if [ ! -d ".git" ]; then
    git init
    git branch -M main
fi

# ----------------------------------------------------
# BƯỚC 1: COMMIT NỀN TẢNG (SOURCE CODE WEB + MYSQL + PHPMYADMIN)
# ----------------------------------------------------
echo "[+] Đang đưa mã nguồn ứng dụng Web và CSDL vào Commit khởi tạo..."
git add src/ data/ Dockerfile .gitignore .env.example
git commit -m "feat(init): Khoi tao ma nguon Website Quan ly Gym va CSDL MySQL (init.sql)" || true

# ----------------------------------------------------
# BƯỚC 2: COMMIT 1 - NGINX REVERSE PROXY & HTTPS & HEADERS
# (Tiêu chí 3: Cấu hình Nginx làm reverse proxy -> commit 1)
# ----------------------------------------------------
echo "[+] Thực hiện COMMIT 1: Nginx Reverse Proxy, HTTPS tự ký và Security Headers..."
git add nginx/
git commit -m "feat(proxy): Cau hinh Nginx Reverse Proxy ho tro HTTPS tu ky va Security Headers -> commit 1" || true

# ----------------------------------------------------
# BƯỚC 3: COMMIT 2 - PROMETHEUS + GRAFANA MONITORING
# (Tiêu chí 4: Tích hợp Prometheus + Grafana để giám sát -> Commit 2)
# ----------------------------------------------------
echo "[+] Thực hiện COMMIT 2: Giám sát Prometheus + Grafana + Exporters..."
git add prometheus/ grafana/ alertmanager/
git commit -m "feat(monitoring): Tich hop Prometheus, Grafana, cAdvisor, Node Exporter, MySQL Exporter -> commit 2" || true

# ----------------------------------------------------
# BƯỚC 4: COMMIT 3 - LOKI + PROMTAIL LOG TẬP TRUNG & HARDENING
# (Tiêu chí 5: Triển khai Loki + Promtail, LogQL -> commit 3)
# ----------------------------------------------------
echo "[+] Thực hiện COMMIT 3: Hệ thống Log tập trung Loki, Promtail và Hardening..."
git add loki/ promtail/ hardening/ docker-compose.yml README.md
git commit -m "feat(logging-hardening): Trien khai Loki, Promtail thu thap log tap trung, truy van LogQL va Hardening -> commit 3" || true

echo "=========================================================="
echo " KIỂM TRA LỊCH SỬ COMMITS ĐẠT CHUẨN:"
echo "=========================================================="
git log --oneline -n 4
echo ""
echo "=> Hãy liên kết với GitHub của bạn bằng lệnh:"
echo "   git remote add origin git@github.com:<MA_SINH_VIEN>/gym-fitness-management.git"
echo "   git push -u origin main"
