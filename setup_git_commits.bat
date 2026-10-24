@echo off
REM ====================================================================
REM SCRIPT TỰ ĐỘNG THỰC HIỆN 3 COMMIT CHUẨN ĐỀ THI TRÊN WINDOWS
REM ====================================================================

echo === BAT DAU TAO 3 COMMIT CHUAN BAREM CHAM DIEM ===

if not exist ".git" (
    git init
    git branch -M main
)

echo [+] Commit khoi tao: Web Gym + Database...
git add src/ data/ Dockerfile .gitignore .env.example
git commit -m "feat(init): Khoi tao ma nguon Website Quan ly Gym va CSDL MySQL (init.sql)"

echo [+] COMMIT 1: Nginx Reverse Proxy & HTTPS & Headers...
git add nginx/
git commit -m "feat(proxy): Cau hinh Nginx Reverse Proxy ho tro HTTPS tu ky va Security Headers -> commit 1"

echo [+] COMMIT 2: Prometheus + Grafana + Exporters...
git add prometheus/ grafana/ alertmanager/
git commit -m "feat(monitoring): Tich hop Prometheus, Grafana, cAdvisor, Node Exporter, MySQL Exporter -> commit 2"

echo [+] COMMIT 3: Loki + Promtail + LogQL + Hardening...
git add loki/ promtail/ hardening/ docker-compose.yml README.md
git commit -m "feat(logging-hardening): Trien khai Loki, Promtail thu thap log tap trung, truy van LogQL va Hardening -> commit 3"

echo === KIEM TRA LICH SU COMMIT ===
git log --oneline -n 4
pause
