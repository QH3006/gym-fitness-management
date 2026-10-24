# BÁO CÁO BÀI TẬP LỚN / THỰC HÀNH TỔNG HỢP
## HỌC PHẦN: TRIỂN KHAI VÀ QUẢN TRỊ HỆ THỐNG PHẦN MỀM

---

```
                       BỘ GIÁO DỤC VÀ ĐÀO TẠO
                  TRƯỜNG ĐẠI HỌC CÔNG NGHỆ THÔNG TIN
                      KHOA HỆ THỐNG THÔNG TIN
                       ----------------------

                     BÁO CÁO THỰC HÀNH TỔNG HỢP

               ĐỀ TÀI SỐ 23: XÂY DỰNG VÀ QUẢN TRỊ HỆ THỐNG 
                  QUẢN LÝ PHÒNG GYM & FITNESS VỚI DOCKER, 
                 NGINX, PROMETHEUS, GRAFANA, LOKI & HARDENING

              Giảng viên hướng dẫn : ThS. Nguyễn Đình Điển
              Sinh viên thực hiện   : [Họ và Tên Sinh Viên]
              Mã số sinh viên       : [Mã Số Sinh Viên]
              Lớp chuyên ngành      : [Tên Lớp Chuyên Ngành]
              Khóa học              : 2022 - 2026

                              Năm học: 2026
```

---

## MỤC LỤC CHI TIẾT
* **LỜI CẢM ƠN VÀ LỜI NÓI ĐẦU**
* **CHƯƠNG 1: TỔNG QUAN ĐỀ TÀI VÀ PHÂN TÍCH YÊU CẦU**
  * 1.1 Giới thiệu bài toán Quản lý Phòng Gym & Fitness
  * 1.2 Mục tiêu đề tài và Barem đánh giá 10 điểm
  * 1.3 Lựa chọn công nghệ triển khai (Tech Stack)
* **CHƯƠNG 2: THIẾT KẾ KIẾN TRÚC HỆ THỐNG TRÊN DOCKER COMPOSE**
  * 2.1 Mô hình kiến trúc tổng thể 10 Services
  * 2.2 Thiết kế Network Isolation (Mạng cô lập 3 tầng)
  * 2.3 Thiết kế Phân vùng Dữ liệu Bền vững (Named Volumes)
* **CHƯƠNG 3: CHI TIẾT THỰC THI 6 TIÊU CHÍ KỸ THUẬT**
  * 3.1 Tiêu chí 1: Quản lý mã nguồn trên GitHub với 03 Commits chuẩn hóa
  * 3.2 Tiêu chí 2: Triển khai Ứng dụng Web Gym, Database MySQL và phpMyAdmin
  * 3.3 Tiêu chí 3: Cấu hình Nginx Reverse Proxy, Domain Local và HTTPS Tự ký (Commit 1)
  * 3.4 Tiêu chí 4: Hệ thống Giám sát Toàn diện với Prometheus & Grafana (Commit 2)
  * 3.5 Tiêu chí 5: Hệ thống Log Tập trung Loki + Promtail và Truy vấn LogQL (Commit 3)
  * 3.6 Tiêu chí 6: Các Biện pháp Gia cố An toàn Hệ thống (Hardening)
* **CHƯƠNG 4: THỰC NGHIỆM VÀ HƯỚNG DẪN DEMO HỆ THỐNG**
  * 4.1 Kịch bản chạy thử nghiệm toàn bộ hệ thống bằng 1 lệnh
  * 4.2 Các vị trí chụp ảnh minh chứng cần đưa vào báo cáo
* **CHƯƠNG 5: KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN**
* **TÀI LIỆU THAM KHẢO**

---

## LỜI CẢM ƠN VÀ LỜI NÓI ĐẦU

Trong xu thế chuyển đổi số mạnh mẽ hiện nay, việc đưa các ứng dụng phần mềm từ môi trường phát triển (Development) vào môi trường vận hành thực tế (Production) đòi hỏi sinh viên chuyên ngành Công nghệ thông tin không chỉ nắm vững kiến thức lập trình mà còn phải làm chủ quy trình DevOps hiện đại: đóng gói ứng dụng với Container (Docker), điều phối dịch vụ với Docker Compose, định tuyến mạng an toàn với Nginx Reverse Proxy, xây dựng hạ tầng giám sát hiệu năng với Prometheus/Grafana, thu thập nhật ký tập trung với Loki/Promtail và áp dụng các tiêu chuẩn gia cố an ninh (Hardening).

Báo cáo này trình bày toàn bộ quá trình nghiên cứu, thiết kế, triển khai và kiểm thử hệ thống cho **Đề tài số 23: Hệ thống Quản lý Phòng Gym / Fitness** thuộc học phần *Triển khai và Quản trị Hệ thống Phần mềm*. Hệ thống được xây dựng hoàn chỉnh theo phương pháp Declarative Infrastructure-as-Code (IaC), đảm bảo tính sẵn sàng cao, tính bảo mật nghiêm ngặt và khả năng mở rộng linh hoạt.

---

## CHƯƠNG 1: TỔNG QUAN ĐỀ TÀI VÀ PHÂN TÍCH YÊU CẦU

### 1.1 Giới thiệu bài toán Quản lý Phòng Gym & Fitness
Một phòng tập Gym và Fitness hiện đại cần quản lý khối lượng dữ liệu nghiệp vụ đa dạng:
1. **Hội viên (Members):** Thông tin cá nhân, phân loại giới tính, ngày sinh, số điện thoại, đặc biệt là dữ liệu định danh CCCD/CMND cần được bảo vệ quyền riêng tư tuyệt đối.
2. **Gói tập (Packages):** Bảng giá, thời hạn sử dụng thẻ (1 tháng, 3 tháng, 6 tháng, 12 tháng) và các quyền lợi đi kèm như dịch vụ phòng xông hơi Sauna, tủ đồ locker riêng.
3. **Huấn luyện viên cá nhân (Personal Trainers - PT):** Danh sách huấn luyện viên, chuyên môn đào tạo (Thể hình Hypertrophy, Cardio giảm mỡ, Kickfit, Yoga, Pilates), kinh nghiệm và đánh giá chất lượng.
4. **Lịch tập & Lớp học nhóm (Schedules/Classes):** Thời khóa biểu theo tuần, phòng tập, khung giờ bắt đầu - kết thúc và giới hạn sĩ số lớp học.
5. **Điểm danh cửa ra vào (Check-in):** Quét mã thẻ hội viên, tự động đối soát hạn sử dụng của thẻ tập theo thời gian thực và ghi nhận nhật ký vào tập.

### 1.2 Mục tiêu đề tài và Barem đánh giá 10 điểm
Theo đề cương môn học, đề tài cần đạt trọn vẹn 10 điểm dựa trên 7 tiêu chí:
* **Tiêu chí 1 (1.5 điểm):** Quản lý toàn bộ mã nguồn trên GitHub với tài khoản đặt tên theo mã sinh viên, có tệp `README.md` hướng dẫn chạy chi tiết và lịch sử Git rõ ràng với đủ 03 Commits nội dung có ý nghĩa.
* **Tiêu chí 2 (1.5 điểm):** Triển khai ứng dụng Web Gym hoạt động ổn định, kết nối cơ sở dữ liệu MySQL thành công và tích hợp công cụ phpMyAdmin quản trị trực quan.
* **Tiêu chí 3 (1.5 điểm):** Cấu hình Nginx làm Reverse Proxy, hỗ trợ tên miền cục bộ `gym.local`, thiết lập HTTPS với chứng chỉ SSL/TLS tự ký và bổ sung các HTTP Security Headers cơ bản (*Commit 1*).
* **Tiêu chí 4 (1.5 điểm):** Tích hợp Prometheus và Grafana để giám sát toàn diện container, web server và MySQL thông qua các exporter chuyên dụng (`node-exporter`, `cadvisor`, `mysql-exporter`), import dashboards trực quan và kiểm tra các câu truy vấn PromQL (*Commit 2*).
* **Tiêu chí 5 (1.5 điểm):** Triển khai hệ thống lưu trữ và truy vấn log tập trung Loki + Promtail, thực hiện tối thiểu 2–3 truy vấn LogQL từ Grafana Explore (*Commit 3*).
* **Tiêu chí 6 (1.5 điểm):** Áp dụng ít nhất 3–4 biện pháp Hardening: Container chạy quyền non-root, Network Isolation (cô lập mạng nội bộ), mật khẩu mạnh lưu qua `.env`, nguyên tắc quyền tối thiểu Least Privilege trong CSDL, mã hóa dữ liệu nhạy cảm AES-256, Nginx Security Headers và tường lửa OS.
* **Tiêu chí 7 (1.0 điểm):** Toàn bộ hệ thống khởi chạy đồng bộ bằng 1 lệnh `docker compose up -d`, demo thực tế mượt mà, hiểu bài và báo cáo đầy đủ screenshot minh chứng.

### 1.3 Lựa chọn công nghệ triển khai (Tech Stack)
* **Ngôn ngữ & Nền tảng Web:** PHP 8.3 chạy trên nền máy chủ Apache (`php:8.3-apache`). Lựa chọn này đồng nhất 100% với tài liệu thực hành môn học (Lab 2), kết nối CSDL thông qua PDO Prepared Statements an toàn trước SQL Injection.
* **Cơ sở dữ liệu:** MySQL 8.0 Community Server.
* **Công cụ quản trị CSDL:** phpMyAdmin phiên bản mới nhất.
* **Reverse Proxy & Web Gateway:** Nginx Alpine (`nginx:alpine`), hỗ trợ HTTP/2, SSL Termination và proxy headers.
* **Giám sát (Monitoring):** Prometheus v2.x, Grafana v11.x, Google cAdvisor, Prometheus Node Exporter, Prometheus MySQL Exporter v0.14.0 và Alertmanager v0.27.x.
* **Ghi nhật ký (Logging):** Grafana Loki và Promtail Collector.

---

## CHƯƠNG 2: THIẾT KẾ KIẾN TRÚC HỆ THỐNG TRÊN DOCKER COMPOSE

### 2.1 Mô hình kiến trúc tổng thể 10 Services
Hệ thống được thiết kế theo mô hình Micro-services đa tầng (Multi-tier Architecture):
1. **Tầng Biên (Edge / Gateway Tier):** `gym-nginx` tiếp nhận mọi yêu cầu từ người dùng tại cổng 80 (HTTP) và 443 (HTTPS), tự động chuyển hướng 301 sang HTTPS, phân phối request vào dịch vụ bên trong.
2. **Tầng Ứng Dụng (Application Tier):** `gym-web` thực thi mã nguồn PHP 8.3 xử lý nghiệp vụ quản lý phòng Gym và `gym-phpmyadmin` phục vụ quản trị CSDL.
3. **Tầng Dữ Liệu (Data Tier):** `gym-db` lưu trữ dữ liệu bền vững trên Docker Volume `gym_db_data`, tích hợp cơ chế `healthcheck` tự động thăm dò cổng 3306.
4. **Tầng Giám Sát (Observability Tier):**
   * Thu thập số liệu hệ điều hành máy chủ: `gym-node-exporter` (cổng 9100).
   * Thu thập số liệu hiệu năng container: `gym-cadvisor` (cổng 8081).
   * Thu thập chỉ số nội bộ cơ sở dữ liệu: `gym-mysql-exporter` (cổng 9104).
   * Máy chủ lưu trữ chuỗi thời gian: `gym-prometheus` (cổng 9090).
   * Máy chủ xử lý cảnh báo: `gym-alertmanager` (cổng 9093).
   * Trực quan hóa Dashboard: `gym-grafana` (cổng 3000).
5. **Tầng Nhật Ký (Logging Tier):**
   * Thu thập log Docker Daemon: `gym-promtail` đọc trực tiếp qua socket `/var/run/docker.sock`.
   * Máy chủ gom log và chỉ mục: `gym-loki` (cổng 3100).

```
+-----------------------------------------------------------------------------------+
|                                 UBUNTU SERVER                                     |
|                                                                                   |
|  +------------------------- [ gym-frontend-net ] ------------------------------+  |
|  |                                                                             |  |
|  |   [ gym-nginx ] :80, :443 (Reverse Proxy + SSL + Security Headers)         |  |
|  |         |                                 |                                 |  |
|  +---------|---------------------------------|---------------------------------+  |
|            v                                 v                                    |
|  +------------------------- [ gym-backend-net ] -------------------------------+  |
|  |                                                                             |  |
|  |   [ gym-web ] :80                         [ gym-phpmyadmin ] :8085          |  |
|  |         |                                         |                         |  |
|  |         +-----------------+-----------------------+                         |  |
|  |                           v                                                 |  |
|  |                   [ gym-db ] :3306 (MySQL 8.0)                              |  |
|  |                           ^                                                 |  |
|  |                           | (Đọc dữ liệu nội bộ)                            |  |
|  |                 [ gym-mysql-exporter ] :9104                                |  |
|  |                           |                                                 |  |
|  +---------------------------|-------------------------------------------------+  |
|                              v                                                    |
|  +------------------------- [ gym-monitoring-net ] ----------------------------+  |
|  |                                                                             |  |
|  |   [ gym-prometheus ] :9090 <==== [ gym-cadvisor ] :8081                     |  |
|  |           |                <==== [ gym-node-exporter ] :9100                |  |
|  |           |                <==== [ gym-mysql-exporter ] :9104               |  |
|  |           v                                                                 |  |
|  |   [ gym-alertmanager ] :9093                                                |  |
|  |           ^                                                                 |  |
|  |           |                                                                 |  |
|  |   [ gym-grafana ] :3000 <========== [ gym-loki ] :3100                      |  |
|  |                                            ^                                |  |
|  |                                            | (Gửi log container)            |  |
|  |                                     [ gym-promtail ]                        |  |
|  +-----------------------------------------------------------------------------+  |
+-----------------------------------------------------------------------------------+
```

### 2.2 Thiết kế Network Isolation (Mạng cô lập 3 tầng)
Thay vì sử dụng một mạng bridge mặc định cho toàn bộ dịch vụ (tiềm ẩn nguy cơ rò rỉ và tấn công nội bộ), hệ thống được cấu hình phân tách ranh giới rõ ràng thành 3 mạng Docker độc lập:
* **`gym-frontend-net`:** Chỉ kết nối Nginx với Web App và phpMyAdmin. Client bên ngoài tuyệt đối không thể nhìn thấy mạng Database hay mạng Monitoring.
* **`gym-backend-net`:** Mạng nội bộ khép kín cho ứng dụng trao đổi dữ liệu với MySQL. Các cổng của MySQL không cần ánh xạ (port forward) trực tiếp ra ngoài Internet.
* **`gym-monitoring-net`:** Mạng dành riêng cho hệ sinh thái Prometheus, Grafana, Loki, cAdvisor, Node Exporter và Alertmanager.
* **Vị trí Cầu Nối An Toàn (Bridge Proxy):** Container `gym-mysql-exporter` được cấu hình tham gia đồng thời vào cả hai mạng `gym-backend-net` và `gym-monitoring-net`. Thiết kế này cho phép Exporter lấy chỉ số từ MySQL (cổng 3306) và cung cấp metrics về Prometheus (cổng 9104) mà không cần phơi bày Database ra mạng giám sát (Áp dụng đúng kỹ thuật nâng cao tại Lab 7, Trang 24-25).

### 2.3 Thiết kế Phân vùng Dữ liệu Bền vững (Named Volumes)
Hệ thống sử dụng các Docker Named Volumes độc lập để bảo toàn dữ liệu ngay cả khi container bị restart hoặc xóa bỏ:
* `gym_db_data`: Lưu trữ dữ liệu vật lý của MySQL tại `/var/lib/mysql`.
* `gym_prometheus_data`: Lưu trữ cơ sở dữ liệu chuỗi thời gian (TSDB) của Prometheus tại `/prometheus`.
* `gym_grafana_data`: Lưu trữ dashboard, tài khoản người dùng và trạng thái của Grafana tại `/var/lib/grafana`.
* `gym_loki_data`: Lưu trữ các khối dữ liệu log (chunks & index) của Loki tại `/loki`.
* `gym_alertmanager_data`: Lưu trữ trạng thái cảnh báo tại `/alertmanager`.

---

## CHƯƠNG 3: CHI TIẾT THỰC THI 6 TIÊU CHÍ KỸ THUẬT

### 3.1 Tiêu chí 1: Quản lý mã nguồn trên GitHub với 03 Commits chuẩn hóa
Để đáp ứng Tiêu chí 1 (1.5 điểm), quy trình làm việc với Git được chuẩn hóa theo đúng tài liệu thực hành Lab Git/SSH:

#### A. Thiết lập khóa SSH an toàn (Lab Kết nối GitHub - Trang 4)
Khởi tạo cặp khóa SSH sử dụng thuật toán mã hóa khóa công khai hiện đại `ed25519`:
```bash
ssh-keygen -t ed25519 -C "sinhvien@gmail.com"
ssh-add ~/.ssh/id_ed25519
cat ~/.ssh/id_ed25519.pub
```
Thêm Public Key vào mục **Settings ➔ SSH and GPG keys** trên tài khoản GitHub mang tên mã sinh viên. Kiểm tra kết nối xác thực:
```bash
ssh -T git@github.com
# Kết quả mong đợi: Hi <username>! You've successfully authenticated...
```

#### B. Quy trình phân tách 03 Commits chuẩn barem
Sinh viên thực hiện kịch bản tự động hóa `setup_git_commits.sh` hoặc chạy lần lượt các lệnh:
1. **Commit khởi tạo (Base Source & DB):**
   ```bash
   git add src/ data/ Dockerfile .gitignore .env.example
   git commit -m "feat(init): Khoi tao ma nguon Website Quan ly Gym va CSDL MySQL (init.sql)"
   ```
2. **Commit 1 (Nginx Reverse Proxy & HTTPS - Tiêu chí 3):**
   ```bash
   git add nginx/
   git commit -m "feat(proxy): Cau hinh Nginx Reverse Proxy ho tro HTTPS tu ky va Security Headers -> commit 1"
   ```
3. **Commit 2 (Prometheus + Grafana Monitoring - Tiêu chí 4):**
   ```bash
   git add prometheus/ grafana/ alertmanager/
   git commit -m "feat(monitoring): Tich hop Prometheus, Grafana, cAdvisor, Node Exporter, MySQL Exporter -> commit 2"
   ```
4. **Commit 3 (Loki + Promtail LogQL & Hardening - Tiêu chí 5 & 6):**
   ```bash
   git add loki/ promtail/ hardening/ docker-compose.yml README.md
   git commit -m "feat(logging-hardening): Trien khai Loki, Promtail thu thap log tap trung, truy van LogQL va Hardening -> commit 3"
   ```
5. **Đẩy mã nguồn lên Remote Repository:**
   ```bash
   git remote add origin git@github.com:<MA_SINH_VIEN>/gym-fitness-management.git
   git push -u origin main
   ```

*Minh chứng ảnh chụp cần có:*
> **[HÌNH 3.1.1: Ảnh chụp màn hình lịch sử `git log --oneline` hiển thị đủ các commits theo đúng barem yêu cầu]**  
> **[HÌNH 3.1.2: Ảnh chụp màn hình giao diện Repository trên GitHub với file README.md trình bày chỉn chu]**

---

### 3.2 Tiêu chí 2: Triển khai Ứng dụng Web Gym, Database MySQL và phpMyAdmin

#### A. Kiến trúc Ứng dụng Quản lý Gym
Ứng dụng Web được xây dựng hoàn thiện các chức năng nghiệp vụ thực tế:
* **`index.php` (Bảng điều khiển thông minh):** Tổng hợp tức thời số lượng hội viên đang hoạt động, doanh thu thu từ các gói tập, lượt điểm danh trong ngày và biểu đồ phân bổ gói tập bằng Chart.js.
* **`members.php` (Quản lý Hội viên):** Đăng ký hội viên mới, gán gói dịch vụ, tự động tính hạn thẻ và ứng dụng thuật toán mã hóa AES-256 cho trường thông tin CCCD.
* **`packages.php` (Quản lý Gói tập):** Bảng giá niêm yết các gói 1 tháng, 3 tháng, 6 tháng, 12 tháng.
* **`trainers.php` (Huấn luyện viên PT):** Danh sách đội ngũ huấn luyện viên chuyên nghiệp kèm kinh nghiệm và đánh giá chất lượng.
* **`schedules.php` (Lịch tập lớp học):** Lên thời khóa biểu theo ca tập hàng tuần.
* **`checkin.php` (Điểm danh tự động):** Quét mã thẻ hội viên, cảnh báo thẻ đã hết hạn hoặc xác nhận vào tập thành công.

#### B. Cơ sở dữ liệu và Cơ chế Healthcheck cho MySQL
Trong tệp `docker-compose.yml`, dịch vụ `gym-db` được cấu hình cơ chế kiểm tra sức khỏe tự động:
```yaml
healthcheck:
  test: ["CMD-SHELL", "mysqladmin ping -uroot -p$${MYSQL_ROOT_PASSWORD} --silent"]
  interval: 10s
  timeout: 5s
  retries: 10
  start_period: 30s
```
Dịch vụ `gym-web` sử dụng điều kiện `depends_on: { gym-db: { condition: service_healthy } }` để đảm bảo Web Server chỉ khởi động khi cơ sở dữ liệu đã nạp xong toàn bộ tệp khởi tạo `init.sql` và sẵn sàng nhận kết nối, loại bỏ triệt để lỗi kết nối cơ sở dữ liệu thường gặp trong môi trường container.

*Minh chứng ảnh chụp cần có:*
> **[HÌNH 3.2.1: Ảnh chụp giao diện Dashboard ứng dụng Quản lý Gym hiển thị biểu đồ và số liệu thống kê]**  
> **[HÌNH 3.2.2: Ảnh chụp giao diện Quản lý Hội viên và tính năng Điểm danh thẻ tập]**  
> **[HÌNH 3.2.3: Ảnh chụp giao diện phpMyAdmin đăng nhập thành công và cấu trúc cơ sở dữ liệu `gym_db`]**

---

### 3.3 Tiêu chí 3: Cấu hình Nginx Reverse Proxy, Domain Local và HTTPS Tự ký (Commit 1)

#### A. Khởi tạo Chứng chỉ SSL/TLS Tự Ký (Lab 1 - Trang 4)
Tuân thủ chính xác kịch bản thực hành tại Lab 1, cặp chứng chỉ SSL được sinh bằng OpenSSL:
```bash
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout nginx/ssl/gym.local.key \
    -out nginx/ssl/gym.local.crt \
    -subj "/C=VN/ST=ThaiNguyen/O=LabWebServer/CN=gym.local"
```
* **`req -x509`:** Tạo chứng chỉ tự ký chuẩn X.509.
* **`-nodes`:** Không mã hóa khóa riêng để Nginx tự động khởi động mà không cần mật khẩu.
* **`-days 365`:** Thời hạn hiệu lực 1 năm.
* **`rsa:2048`:** Độ dài khóa an toàn 2048-bit.

#### B. Cấu hình Nginx Reverse Proxy & Chuyển hướng HTTP 301
Trong tệp `nginx/conf.d/default.conf`:
```nginx
server {
    listen 80;
    server_name gym.local localhost;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name gym.local localhost;

    ssl_certificate /etc/nginx/ssl/gym.local.crt;
    ssl_certificate_key /etc/nginx/ssl/gym.local.key;
    ssl_protocols TLSv1.2 TLSv1.3;

    # CÁC SECURITY HEADERS GIA CỐ BẢO MẬT
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    server_tokens off;

    location / {
        proxy_pass http://gym-web:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location /pma/ {
        proxy_pass http://phpmyadmin:80/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

*Minh chứng ảnh chụp cần có:*
> **[HÌNH 3.3.1: Ảnh chụp chi tiết chứng chỉ số SSL tự ký trên trình duyệt web cho tên miền `gym.local`]**  
> **[HÌNH 3.3.2: Ảnh chụp tính năng tự động chuyển hướng từ cổng 80 (HTTP) sang cổng 443 (HTTPS)]**  
> **[HÌNH 3.3.3: Ảnh chụp Response Headers trong Network Tab (DevTools) thể hiện các Security Headers: `X-Frame-Options`, `X-Content-Type-Options`, `Server: nginx` ẩn phiên bản]**

---

### 3.4 Tiêu chí 4: Hệ thống Giám sát Toàn diện với Prometheus & Grafana (Commit 2)

#### A. Cấu hình Scrape Targets trên Prometheus
Tệp `prometheus/prometheus.yml` khai báo định kỳ 15 giây lấy dữ liệu từ 4 mục tiêu giám sát:
* `prometheus:9090`: Tự giám sát chính máy chủ Prometheus.
* `node-exporter:9100`: Giám sát tài nguyên máy chủ vật lý/máy ảo Ubuntu (CPU, RAM, Disk, I/O).
* `cadvisor:8080`: Giám sát toàn bộ container đang chạy trên Docker Daemon.
* `mysql-exporter:9104`: Giám sát hiệu năng CSDL MySQL (Threads, Connections, Slow Queries).

#### B. Các câu truy vấn PromQL phục vụ giám sát thực tế (Lab 7 - Trang 23)
1. **Kiểm tra trạng thái UP của toàn bộ targets:**
   $$\text{up}$$
2. **Kiểm tra bộ nhớ RAM khả dụng của hệ điều hành:**
   $$\text{node\_memory\_MemAvailable\_bytes}$$
3. **Mức sử dụng CPU tích lũy của máy chủ:**
   $$\text{node\_cpu\_seconds\_total}$$
4. **Mức sử dụng RAM thực tế của container Web và MySQL:**
   $$\text{container\_memory\_usage\_bytes}\{\text{container}=\sim"\text{gym-web}|\text{gym-db}"\}$$
5. **Số lượng kết nối đồng thời đang mở tới MySQL:**
   $$\text{mysql\_global\_status\_threads\_connected}$$

#### C. Thiết lập Cảnh báo Tự động với Alertmanager
Tệp `prometheus/rules/alerts.yml` định nghĩa các quy tắc giám sát tự động kích hoạt cảnh báo gửi sang Alertmanager (cổng 9093):
* `GymWebHighCPU`: Cảnh báo khi mức tải CPU của ứng dụng vượt quá 80% trong 2 phút liên tiếp.
* `GymWebHighMemory`: Cảnh báo khi bộ nhớ RAM container vượt ngưỡng 85%.
* `MySQLTooManyConnections`: Cảnh báo khi số phiên kết nối vào MySQL vượt 80 sessions.
* `HighDiskUsage`: Cảnh báo khi dung lượng ổ đĩa máy chủ còn trống dưới 15%.

#### D. Trực quan hóa trên Grafana Dashboard (Cổng 3000)
Hệ thống sử dụng cơ chế Grafana Provisioning (`grafana/provisioning/datasources/datasources.yml`) tự động liên kết nguồn dữ liệu Prometheus ngay khi khởi chạy. Sinh viên import các Dashboard mẫu chuẩn:
* **Node Exporter Full:** Dashboard ID `1860`.
* **cAdvisor Container Monitor:** Dashboard ID `14282` hoặc `193`.
* **MySQL Overview:** Dashboard ID `7362`.

*Minh chứng ảnh chụp cần có:*
> **[HÌNH 3.4.1: Ảnh chụp màn hình trang Status ➔ Target Health trên Prometheus (cổng 9090) với 4 targets đều UP]**  
> **[HÌNH 3.4.2: Ảnh chụp kết quả thực thi các câu truy vấn PromQL trên giao diện Graph của Prometheus]**  
> **[HÌNH 3.4.3: Ảnh chụp Dashboard Node Exporter và Dashboard cAdvisor trên Grafana]**  
> **[HÌNH 3.4.4: Ảnh chụp Dashboard MySQL Overview trên Grafana thể hiện các thông số của `gym-db`]**

---

### 3.5 Tiêu chí 5: Hệ thống Log Tập trung Loki + Promtail và Truy vấn LogQL (Commit 3)

#### A. Kiến trúc Ghi Log Tập trung (Lab 8)
* **Promtail:** Được cấp quyền truy cập chỉ đọc vào Docker daemon socket (`/var/run/docker.sock:ro`) và thư mục container log (`/var/lib/docker/containers:ro`). Promtail tự động gắn các trường metadata (`container`, `service`) vào từng dòng log trước khi đẩy (push) về Loki qua giao thức HTTP cổng 3100.
* **Loki:** Lưu trữ log theo dạng khối chunk nén và lập chỉ mục (index) siêu nhẹ theo nhãn, giúp tiết kiệm dung lượng đĩa tối đa so với giải pháp Elasticsearch truyền thống.

#### B. Các câu truy vấn LogQL mẫu (Thực hiện trên Grafana Explore -> Loki)
1. **Truy vấn xem toàn bộ nhật ký ứng dụng Web Gym:**
   $$\{\text{container}="\text{gym-web}"\}$$
2. **Truy vấn xem nhật ký của cơ sở dữ liệu MySQL:**
   $$\{\text{container}="\text{gym-db}"\}$$
3. **Truy vấn lọc các lỗi và cảnh báo bất thường trong Web Server:**
   $$\{\text{container}="\text{gym-web}"\} \mid\sim "(?i)\text{error}|\text{exception}|\text{warning}"$$
4. **Truy vấn thống kê các yêu cầu HTTP GET chuyển tiếp qua Nginx:**
   $$\{\text{container}="\text{gym-nginx}"\} \mid= "\text{GET}"$$
5. **Truy vấn kiểm tra việc truy cập CSDL từ General Log:**
   $$\{\text{container}="\text{gym-db}"\} \mid= "\text{Query}"$$
6. **Truy vấn giám sát đồng thời nhật ký của cả Web Server và Database:**
   $$\{\text{container}=\sim"\text{gym-web}|\text{gym-db}"\}$$

*Minh chứng ảnh chụp cần có:*
> **[HÌNH 3.5.1: Ảnh chụp log container Promtail đẩy dữ liệu thành công lên Loki]**  
> **[HÌNH 3.5.2: Ảnh chụp màn hình Grafana Explore truy vấn LogQL `{container="gym-web"}`]**  
> **[HÌNH 3.5.3: Ảnh chụp kết quả lọc log lỗi bằng LogQL `{container="gym-web"} |= "error"`]**

---

### 3.6 Tiêu chí 6: Các Biện pháp Gia cố An toàn Hệ thống (Hardening)

Hệ thống được thiết lập toàn diện các lớp bảo vệ theo yêu cầu nâng cao tại Lab 6 và Lab 9:

#### 1. Container Non-Root User
Trong `Dockerfile`, ứng dụng không chạy dưới quyền `root`. Quyền sở hữu thư mục mã nguồn được bàn giao cho người dùng mặc định của web server:
```dockerfile
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html
```

#### 2. Nguyên tắc Quyền Tối thiểu trong Cơ sở dữ liệu (Least Privilege DB - Lab 6)
Ứng dụng Web `gym-web` không sử dụng tài khoản `root` của MySQL mà sử dụng tài khoản chuyên dụng `gym_app_user`:
* Chỉ được cấp các quyền: `SELECT, INSERT, UPDATE, DELETE` trên CSDL `gym_db`.
* **Bị cấm hoàn toàn:** `DROP, ALTER, CREATE, GRANT`.
* **Thực nghiệm chứng minh (Proof of Concept):** Trên trang `security-demo.php`, khi thực hiện nút bấm gửi lệnh `DROP TABLE members`, MySQL lập tức trả về lỗi:
  ```text
  SQLSTATE[42000]: Syntax error or access violation: 1142 DROP command denied to user 'gym_app_user'@'...' for table 'members'
  ```
  Chứng minh cuộc tấn công xóa dữ liệu đã bị ngăn chặn triệt để.

#### 3. Mã hóa Dữ liệu Nhạy cảm (AES-256 Encryption - Lab 6 Trang 12-13)
Thông tin căn cước công dân (CCCD/SSN) của hội viên phòng Gym là thông tin định danh cá nhân nhạy cảm. Hệ thống thiết kế trường `ssn_encrypted (VARBINARY(255))` và sử dụng hàm mã hóa đối xứng của MySQL:
```sql
UPDATE members SET ssn_encrypted = AES_ENCRYPT(ssn, 'GymSuperSecretKey2026!');
```
Khi đọc dữ liệu thông thường, trường này chỉ hiển thị chuỗi Hex mã hóa nhị phân vô nghĩa (ví dụ `0x9a8f...`), chỉ có người nắm giữ khóa bí mật mới có thể thực hiện lệnh `AES_DECRYPT` để đọc được số CCCD gốc.

#### 4. Chính sách Mật khẩu Nghiêm ngặt (Password Policy - Lab 6 Trang 10-11)
Cơ sở dữ liệu được cài đặt component `validate_password`:
```sql
INSTALL COMPONENT 'file://component_validate_password';
SET GLOBAL validate_password.policy = MEDIUM;
SET GLOBAL validate_password.length = 12;
ALTER USER 'gym_app_user'@'%' PASSWORD EXPIRE INTERVAL 90 DAY;
```
Đảm bảo mật khẩu phải dài tối thiểu 12 ký tự, gồm chữ hoa, chữ thường, chữ số, ký tự đặc biệt và buộc xoay vòng mật khẩu mỗi 90 ngày.

#### 5. Kích hoạt Nhật ký Giám sát Truy vấn (Audit General Log - Lab 6 Trang 13-14)
```sql
SET GLOBAL general_log = 1;
SET GLOBAL general_log_file = '/var/lib/mysql/general.log';
```
Toàn bộ câu lệnh SQL thực thi trong hệ thống đều được ghi vết phục vụ công tác điều tra sự cố (Digital Forensics).

#### 6. Gia cố Bảo mật Hệ điều hành Máy chủ (Host OS Hardening - Lab 9)
Dự án cung cấp sẵn bộ script tự động thiết lập cho máy chủ Ubuntu:
* **Tường lửa `setup_iptables.sh`:** Giới hạn tốc độ Ping ICMP tối đa 1 gói/giây, chống tấn công tràn ngập kết nối SYN Flood và thiết lập chính sách mặc định `INPUT DROP`.
* **Bảo mật SSH `setup_ssh_hardening.sh`:** Đổi cổng SSH mặc định từ 22 sang **2849**, cấm đăng nhập tài khoản `root` trực tiếp (`PermitRootLogin no`), cấm xác thực bằng mật khẩu (`PasswordAuthentication no`), chỉ cho phép xác thực bằng SSH Public Key.
* **Chống Brute Force `setup_fail2ban.sh`:** Cấu hình Jail `[sshd]` theo dõi cổng 2849, tự động cấm (ban) địa chỉ IP trong vòng 3600 giây (1 giờ) nếu đăng nhập sai quá 3 lần liên tiếp trong 600 giây.

*Minh chứng ảnh chụp cần có:*
> **[HÌNH 3.6.1: Ảnh chụp màn hình trang Hardening Verification (`/security-demo.php`) thể hiện lệnh `DROP TABLE` bị chặn thành công]**  
> **[HÌNH 3.6.2: Ảnh chụp minh chứng so sánh dữ liệu CCCD gốc và dữ liệu nhị phân đã mã hóa bằng AES-256]**  
> **[HÌNH 3.6.3: Ảnh chụp cấu hình iptables và trạng thái Jail Fail2Ban trên máy chủ]**

---

## CHƯƠNG 4: THỰC NGHIỆM VÀ HƯỚNG DẪN DEMO HỆ THỐNG

### 4.1 Kịch bản chạy thử nghiệm toàn bộ hệ thống bằng 1 lệnh
Sinh viên hoặc Giảng viên kiểm tra hệ thống theo trình tự các bước:

```bash
# 1. Di chuyển vào thư mục dự án
cd "d:/project/quan tri he thong"

# 2. Khởi chạy toàn bộ 10 container dịch vụ
docker compose up -d --build

# 3. Theo dõi tiến trình khởi tạo
docker compose ps
```

### 4.2 Bảng điều phối cổng truy cập Demo

| Tên Dịch Vụ | Đường Dẫn URL Truy Cập | Mục Đích Demo Cho Giảng Viên |
| :--- | :--- | :--- |
| **Website Quản lý Gym (HTTPS)** | `https://gym.local` hoặc `https://localhost` | Demo các tính năng quản lý hội viên, gói tập, PT, điểm danh và trang bảo mật Hardening |
| **Website (HTTP tự động chuyển hướng)**| `http://gym.local` hoặc `http://localhost` | Demo tính năng Nginx Reverse Proxy redirect 301 sang HTTPS |
| **Quản trị CSDL phpMyAdmin** | `http://localhost:8085` hoặc `https://gym.local/pma/` | Quản trị CSDL `gym_db`, kiểm tra các bảng và phân quyền user |
| **Hệ thống Giám sát Grafana** | `http://localhost:3000` | Đăng nhập (`admin` / `Admin@Gym2026!`), mở Dashboard Node Exporter và cAdvisor |
| **Truy vấn Log Loki (Grafana)** | `http://localhost:3000/explore` | Chọn Data Source Loki, chạy các câu truy vấn LogQL mẫu |
| **Hệ thống Prometheus** | `http://localhost:9090` | Xem danh sách Target Health (UP) và chạy các câu truy vấn PromQL |
| **Quản lý Cảnh báo Alertmanager**| `http://localhost:9093` | Xem các Alert Rule đang hoạt động (`GymWebHighCPU`, `MySQLTooManyConnections`) |
| **Giám sát Container cAdvisor** | `http://localhost:8081` | Xem biểu đồ thời gian thực của Docker Container |

---

## CHƯƠNG 5: KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN

### 5.1 Đánh giá kết quả đạt được
Dự án đã hoàn thành xuất sắc toàn bộ các mục tiêu đặt ra cho **Đề tài số 23: Hệ thống Quản lý Phòng Gym & Fitness**:
1. Đóng gói và phối hợp nhịp nhàng 10 dịch vụ container bằng một tệp `docker-compose.yml` duy nhất.
2. Thiết kế và vận hành website quản lý gym trực quan, hiện đại, đáp ứng tốt nghiệp vụ quản lý phòng tập thể hình.
3. Ứng dụng thành công Nginx Reverse Proxy với HTTPS chứng chỉ tự ký và các Security Headers phòng vệ chủ động.
4. Xây dựng hệ thống giám sát toàn diện chuẩn công nghiệp với bộ ba Prometheus + Exporters + Grafana.
5. Triển khai kiến trúc quản lý nhật ký tập trung hiện đại với Loki và Promtail, tối ưu hóa lưu trữ và hỗ trợ truy vấn LogQL mạnh mẽ.
6. Áp dụng đồng bộ các giải pháp gia cố an toàn hệ thống (Hardening) từ mức container (non-root, network isolation) đến mức CSDL (Least Privilege, AES-256, Audit Log) và hệ điều hành (iptables, SSH, Fail2ban).
7. Quản lý kho mã nguồn chuyên nghiệp trên GitHub với cấu trúc commits chuẩn hóa và tài liệu hướng dẫn tường minh.

### 5.2 Hướng phát triển trong tương lai
* Nghiên cứu triển khai hệ thống trên cụm điều phối container **Kubernetes (K8s)** với các tài nguyên Pods, Deployments và Ingress Controller.
* Mở rộng tích hợp CI/CD Pipeline thông qua **GitHub Actions** để tự động build Docker Image và quét lỗ hổng bảo mật với Trivy.
* Nâng cấp hệ thống cảnh báo qua kênh tin nhắn Telegram Bot hoặc Discord Webhook.

---

## TÀI LIỆU THAM KHẢO
1. Tài liệu bài giảng học phần *Triển khai và Quản trị Hệ thống Phần mềm* - Trường ĐH CNTT.
2. Hướng dẫn thực hành Lab 1: Cấu hình Nginx và thiết lập domain cho website.
3. Hướng dẫn thực hành Lab 2: Đóng gói ứng dụng PHP + MySQL với Dockerfile.
4. Hướng dẫn thực hành Lab 6: Quản trị và gia cố cơ sở dữ liệu MySQL / phpMyAdmin.
5. Hướng dẫn thực hành Lab 7: Thiết lập hệ thống giám sát Prometheus và Grafana.
6. Hướng dẫn thực hành Lab 8: Tích hợp hệ thống log tập trung Loki, Promtail và Alertmanager.
7. Hướng dẫn thực hành Lab 9: Thiết lập tường lửa iptables, SSH Hardening và Fail2Ban.
8. Docker Inc., *Docker Compose Specification and Documentation*, 2024.
9. Nginx Inc., *Nginx Reverse Proxy & Security Controls Guide*, 2024.
10. Grafana Labs, *Prometheus & Loki Operational Best Practices*, 2024.
