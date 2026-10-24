# HỆ THỐNG QUẢN LÝ PHÒNG GYM & FITNESS (ĐỀ TÀI SỐ 23)

> **Môn học:** Triển khai và Quản trị Hệ thống Phần mềm  
> **Kiến trúc triển khai:** Docker Compose Micro-services  
> **Cơ sở dữ liệu:** MySQL 8.0 + phpMyAdmin  
> **Hạ tầng mạng & Bảo mật:** Nginx Reverse Proxy (HTTPS Self-Signed), Network Isolation, Least Privilege, Password Policy, AES-256 Encryption  
> **Giám sát & Quản lý Log:** Prometheus + Grafana + cAdvisor + Node Exporter + MySQL Exporter + Loki + Promtail + Alertmanager  

---

## 📑 MỤC LỤC
1. [Giới thiệu Đề tài & Tính năng Ứng dụng](#1-giới-thiệu-đề-tài--tính-năng-ứng-dụng)
2. [Sơ đồ Kiến trúc Hệ thống](#2-sơ-đồ-kiến-trúc-hệ-thống)
3. [Bảng Phân Bổ Cổng Dịch Vụ & Tài Khoản](#3-bảng-phân-bổ-cổng-dịch-vụ--tài-khoản)
4. [Hướng dẫn Khởi chạy Hệ thống](#4-hướng-dẫn-khởi-chạy-hệ-thống)
5. [Cấu hình Domain Local (gym.local) & HTTPS](#5-cấu-hình-domain-local-gymlocal--https)
6. [Hệ thống Giám sát Prometheus & PromQL](#6-hệ-thống-giám-sát-prometheus--promql)
7. [Hệ thống Log Tập trung Loki & LogQL](#7-hệ-thống-log-tập-trung-loki--logql)
8. [Các Biện pháp Gia Cố Bảo Mật (System Hardening)](#8-các-biện-pháp-gia-cố-bảo-mật-system-hardening)
9. [Quy chuẩn 03 Commit trên GitHub](#9-quy-chuẩn-03-commit-trên-github)

---

## 1. GIỚI THIỆU ĐỀ TÀI & TÍNH NĂNG ỨNG DỤNG
Hệ thống được phát triển phục vụ công tác số hóa và quản trị phòng Gym & Fitness hiện đại:
* **Quản lý Hội viên (Members):** Quản lý hồ sơ, gói tập đăng ký, ngày bắt đầu/kết thúc, phân công huấn luyện viên. Thông tin CCCD/SSN được bảo vệ bằng thuật toán mã hóa đối xứng **AES-256**.
* **Quản lý Gói tập (Packages):** Phân hạng gói tập (1 tháng, 3 tháng, 6 tháng, 12 tháng) với đơn giá và quyền lợi đi kèm (Gym, Yoga, Sauna...).
* **Quản lý Huấn luyện viên PT (Trainers):** Theo dõi danh sách HLV chuyên nghiệp, số năm kinh nghiệm, học viên đang kèm và đánh giá sao.
* **Lịch tập & Lớp học (Schedules):** Lập thời khóa biểu các lớp nhóm theo tuần (Yoga, Body Pump, Kickfit, Pilates) kèm khung giờ và sức chứa phòng tập.
* **Điểm danh Ra/Vào (Check-in):** Quét mã thẻ hội viên, kiểm tra hạn thẻ thời gian thực và ghi nhận lịch sử tập luyện.
* **Hardening Verification Center:** Giao diện trực quan kiểm chứng trực tiếp nguyên tắc Quyền tối thiểu (Least Privilege), cơ chế mã hóa AES, MySQL General Audit Log và Nginx Security Headers.

---

## 2. SƠ ĐỒ KIẾN TRÚC HỆ THỐNG

```
                             [ CLIENT BROWSER ]
                                     |
                                (HTTP / HTTPS)
                                     v
                 +---------------------------------------+
                 |       NGINX REVERSE PROXY             |
                 |     Port 80 (HTTP) -> 443 (HTTPS)     |
                 |   Security Headers + SSL Termination  |
                 +---------------------------------------+
                                     |
              +----------------------+----------------------+
              | (gym-frontend-net)                          |
              v                                             v
    +-------------------+                         +-------------------+
    |    gym-web        |                         |    phpmyadmin     |
    | (PHP 8.3 Apache)  |                         |  Port 8085 (Web)  |
    +-------------------+                         +-------------------+
              |                                             |
              +----------------------+----------------------+
                                     | (gym-backend-net)
                                     v
                 +---------------------------------------+
                 |       CƠ SỞ DỮ LIỆU MYSQL 8.0         |
                 |       Database: gym_db (Port 3306)    |
                 |       User: gym_app_user / exporter   |
                 +---------------------------------------+
                                     ^
                                     | (Cầu nối đọc metrics)
                             +----------------+
                             | mysql-exporter |
                             |   Port 9104    |
                             +----------------+
                                     |
         +---------------------------+---------------------------+
         | (gym-monitoring-net)                                  |
         v                           v                           v
+------------------+        +------------------+        +------------------+
|    cadvisor      |        |  node-exporter   |        |   alertmanager   |
|   (Port 8081)    |        |   (Port 9100)    |        |   (Port 9093)    |
+------------------+        +------------------+        +------------------+
         |                           |                           ^
         +-------------+-------------+                           |
                       v                                         |
             +--------------------+                              |
             |     PROMETHEUS     | -----------------------------+
             |    (Port 9090)     |
             +--------------------+
                       ^
                       | (Metrics)
             +--------------------+
             |      GRAFANA       | <---------------+ (Logs)
             |    (Port 3000)     |                 |
             +--------------------+        +------------------+
                                           |       LOKI       |
                                           |   (Port 3100)    |
                                           +------------------+
                                                    ^
                                                    | (Push Docker Logs)
                                           +------------------+
                                           |     PROMTAIL     |
                                           |  (/var/run/sock) |
                                           +------------------+
```

---

## 3. BẢNG PHÂN BỔ CỔNG DỊCH VỤ & TÀI KHOẢN

| Dịch vụ Container | Tên Container | Cổng Host | Cổng Nội bộ | Mạng Docker (Isolation) | Tài khoản / Mật khẩu mặc định |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Nginx Reverse Proxy** | `gym-nginx` | **80, 443** | 80, 443 | `gym-frontend-net` | Domain: `https://gym.local` |
| **Web Ứng dụng Gym** | `gym-web` | *Proxy qua 443* | 80 | `frontend-net`, `backend-net` | User non-root: `www-data` |
| **Cơ sở dữ liệu** | `gym-db` | *Nội bộ (3306)* | 3306 | `gym-backend-net` | App: `gym_app_user` / `GymApp@Pass2026!Strong`<br>Root: `GymSuperSecretRootPass2026!` |
| **phpMyAdmin** | `gym-phpmyadmin`| **8085** / `/pma/` | 80 | `frontend-net`, `backend-net` | Đăng nhập bằng tài khoản MySQL |
| **Prometheus** | `gym-prometheus`| **9090** | 9090 | `gym-monitoring-net` | Không yêu cầu đăng nhập |
| **Grafana** | `gym-grafana` | **3000** | 3000 | `gym-monitoring-net` | `admin` / `Admin@Gym2026!` |
| **cAdvisor** | `gym-cadvisor` | **8081** | 8080 | `gym-monitoring-net` | Giám sát tài nguyên container |
| **Node Exporter** | `gym-node-exporter` | **9100** | 9100 | `gym-monitoring-net` | Thống kê OS Host |
| **MySQL Exporter** | `gym-mysql-exporter`| **9104** | 9104 | `backend-net`, `monitoring-net` | User: `mysqld_exporter` |
| **Alertmanager** | `gym-alertmanager` | **9093** | 9093 | `gym-monitoring-net` | Quản lý cảnh báo Prometheus |
| **Grafana Loki** | `gym-loki` | **3100** | 3100 | `gym-monitoring-net` | Máy chủ lưu trữ log tập trung |
| **Grafana Promtail** | `gym-promtail` | *Socket Docker* | 9080 | `gym-monitoring-net` | Đọc `/var/run/docker.sock` |

---

## 4. HƯỚNG DẪN KHỞI CHẠY HỆ THỐNG

### Bước 1: Sao chép mã nguồn và chuẩn bị môi trường
```bash
# Clone repository về máy chủ Ubuntu / Windows
git clone git@github.com:<MA_SINH_VIEN>/gym-fitness-management.git
cd gym-fitness-management

# Chuẩn bị file biến môi trường
cp .env.example .env
```

### Bước 2: Khởi tạo chứng chỉ SSL (Nếu chưa có)
Chứng chỉ SSL tự ký đã được sinh sẵn trong thư mục `nginx/ssl/`. Nếu cần tạo lại trên Ubuntu Server:
```bash
chmod +x nginx/ssl/generate_ssl.sh
./nginx/ssl/generate_ssl.sh
```

### Bước 3: Build và khởi chạy toàn bộ hệ thống bằng 1 lệnh duy nhất
```bash
docker compose up -d --build
```

### Bước 4: Kiểm tra trạng thái các Container
```bash
docker compose ps
```
*Tất cả 10 container phải ở trạng thái **Up** (container `gym-db` có trạng thái **healthy**).*

---

## 5. CẤU HÌNH DOMAIN LOCAL (gym.local) & HTTPS

Để truy cập ứng dụng thông qua tên miền và kiểm tra tính năng **HTTPS & Reverse Proxy** theo bài Lab 1:

* **Trên Windows:**
  1. Mở Notepad bằng quyền **Run as Administrator**.
  2. Mở file: `C:\Windows\System32\drivers\etc\hosts`.
  3. Thêm dòng sau vào cuối file (thay `192.168.x.x` bằng IP máy chủ Ubuntu hoặc `127.0.0.1` nếu chạy local):
     ```text
     127.0.0.1 gym.local
     ```
* **Trên Linux / Ubuntu Server:**
  ```bash
  echo "127.0.0.1 gym.local" | sudo tee -a /etc/hosts
  ```
* **Kiểm tra kết quả:**
  * Mở trình duyệt truy cập: `http://gym.local` ➔ Trình duyệt tự động chuyển hướng (HTTP 301) sang `https://gym.local`.
  * Bấm xem chứng chỉ SSL: Cấp cho **gym.local**, tổ chức **LabWebServer**, bang **ThaiNguyen** (chuẩn theo yêu cầu Lab 1).

---

## 6. HỆ THỐNG GIÁM SÁT PROMETHEUS & PROMQL

### 6.1 Kiểm tra Target Health
Truy cập `http://<IP_SERVER>:9090/targets`:
* Toàn bộ 4 target (`prometheus`, `node-exporter`, `cadvisor`, `mysql`) đều ở trạng thái **UP** màu xanh.

### 6.2 Các câu truy vấn PromQL mẫu (Tiêu chí 4)
Truy cập `http://<IP_SERVER>:9090/graph` và thực thi:

1. **Kiểm tra trạng thái sống của toàn bộ dịch vụ:**
   ```promql
   up
   ```
2. **Theo dõi dung lượng RAM khả dụng trên máy chủ:**
   ```promql
   node_memory_MemAvailable_bytes
   ```
3. **Theo dõi mức tiêu thụ RAM của từng Container:**
   ```promql
   container_memory_usage_bytes{container=~"gym-web|gym-db|gym-nginx"}
   ```
4. **Theo dõi số lượng kết nối đang mở tới MySQL:**
   ```promql
   mysql_global_status_threads_connected
   ```
5. **Theo dõi tổng số lệnh truy vấn SQL thực thi:**
   ```promql
   rate(mysql_global_status_queries[5m])
   ```

### 6.3 Import Dashboard Grafana (Cổng 3000)
Hệ thống đã tự động cấu hình Datasource Prometheus & Loki. Để import Dashboard trực quan:
* Dashboard **Node Exporter Full:** ID `1860`
* Dashboard **cAdvisor Container Monitor:** ID `14282` hoặc `193`
* Dashboard **MySQL Overview:** ID `7362`

---

## 7. HỆ THỐNG LOG TẬP TRUNG LOKI & LOGQL

Hệ thống sử dụng **Promtail** tự động bắt các dòng log từ Docker daemon socket, gắn label `container` và `service`, sau đó chuyển về **Loki**.

### Các câu truy vấn LogQL mẫu (Thực hiện trên Grafana Explore -> Loki):

1. **Truy vấn toàn bộ log của ứng dụng Web Gym:**
   ```logql
   {container="gym-web"}
   ```
2. **Truy vấn toàn bộ log của cơ sở dữ liệu MySQL:**
   ```logql
   {container="gym-db"}
   ```
3. **Truy vấn lọc các lỗi (Error) trong Web App:**
   ```logql
   {container="gym-web"} |~ "(?i)error|exception|warning"
   ```
4. **Truy vấn các request HTTP GET gửi tới Web Server:**
   ```logql
   {container="gym-nginx"} |= "GET"
   ```
5. **Truy vấn theo dõi các truy vấn SQL ghi nhận từ MySQL General Log:**
   ```logql
   {container="gym-db"} |= "Query"
   ```
6. **Truy vấn đồng thời cả Web Server và Database:**
   ```logql
   {container=~"gym-web|gym-db"}
   ```

---

## 8. CÁC BIỆN PHÁP GIA CỐ BẢO MẬT (SYSTEM HARDENING)

Hệ thống đã triển khai toàn diện các tiêu chí Hardening theo yêu cầu bài thực hành:
1. **Container Non-Root User:** Web container chạy Apache và PHP dưới user bị giới hạn quyền `www-data` (UID: 33), không chạy quyền `root`.
2. **Network Isolation (Cách ly mạng 3 tầng):**
   * `gym-frontend-net`: Nơi Nginx tiếp nhận client và giao tiếp với Web.
   * `gym-backend-net`: Mạng nội bộ khép kín chỉ giữa Web, phpMyAdmin và MySQL.
   * `gym-monitoring-net`: Mạng riêng cho các công cụ giám sát (Prometheus, Grafana, Loki). Container `mysql-exporter` đóng vai trò cầu nối an toàn.
3. **Nguyên tắc Quyền Tối thiểu (Least Privilege DB):**
   * `gym_app_user`: Chỉ được cấp quyền `SELECT, INSERT, UPDATE, DELETE` trên `gym_db`. Tuyệt đối **không có quyền `DROP, ALTER, GRANT`**.
   * Trên giao diện web có trang **Hardening Verification** (`/security-demo.php`) mô phỏng lệnh `DROP TABLE members` và hiển thị thông báo chặn từ MySQL.
4. **Mã hóa Dữ liệu Nhạy cảm (AES-256):**
   * Số CCCD/SSN của hội viên được mã hóa đối xứng bằng `AES_ENCRYPT(ssn, key)` lưu trong trường `VARBINARY(255)`. Kẻ trộm dump dữ liệu chỉ thấy chuỗi HEX nhị phân mã hóa.
5. **MySQL Password Policy & Expiration:**
   * Cài đặt `validate_password` với độ dài tối thiểu 12 ký tự, độ phức tạp `MEDIUM`.
   * Thiết lập `PASSWORD EXPIRE INTERVAL 90 DAY` cho user ứng dụng.
6. **Audit & Forensics Logging:**
   * Bật `general_log = 1` ghi toàn bộ truy vấn SQL vào `/var/lib/mysql/general.log`.
7. **Nginx Security Headers:**
   * Tắt phiên bản Nginx (`server_tokens off`).
   * Cấu hình các Header bảo vệ: `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Content-Security-Policy`.
8. **Host Security (Iptables + SSH + Fail2Ban):**
   * Kèm sẵn 3 kịch bản: `setup_iptables.sh` (chống SYN Flood, rate-limit ICMP), `setup_ssh_hardening.sh` (đổi SSH sang port 2849, tắt password, chỉ dùng key), `setup_fail2ban.sh` (tự động ban IP quét SSH sau 3 lần sai).

---

## 9. QUY CHUẨN 03 COMMIT TRÊN GITHUB

Để đảm bảo trọn vẹn điểm số Tiêu chí 1 trong Barem chấm thi, hãy thực thi script:
* Trên Linux: `chmod +x setup_git_commits.sh && ./setup_git_commits.sh`
* Trên Windows: chạy `setup_git_commits.bat`

Cấu trúc 03 Commits được tạo ra chuẩn xác gồm:
* **Initial commit:** Mã nguồn Website Quản lý Gym và CSDL MySQL (`init.sql`).
* **Commit 1:** Cấu hình Nginx Reverse Proxy hỗ trợ HTTPS tự ký và Security Headers.
* **Commit 2:** Tích hợp Prometheus, Grafana, cAdvisor, Node Exporter, MySQL Exporter.
* **Commit 3:** Triển khai Loki, Promtail thu thập log tập trung, truy vấn LogQL và Hardening.
