-- ====================================================================
-- KỊCH BẢN GIA CỐ CƠ SỞ DỮ LIỆU MYSQL (DATABASE HARDENING)
-- Dựa trên hướng dẫn Lab 6 (Trang 10 - 14)
-- ====================================================================

-- 1. Cài đặt plugin/component kiểm tra độ phức tạp của mật khẩu
INSTALL COMPONENT 'file://component_validate_password';

-- 2. Thiết lập chính sách mật khẩu ở mức MEDIUM và độ dài tối thiểu 12 ký tự
SET GLOBAL validate_password.policy = MEDIUM;
SET GLOBAL validate_password.length = 12;

-- 3. Kiểm tra các tham số Password Policy vừa thiết lập
SHOW VARIABLES LIKE 'validate_password%';

-- 4. Buộc mật khẩu của tài khoản ứng dụng phải hết hạn sau 90 ngày (Password Rotation)
ALTER USER 'gym_app_user'@'%' PASSWORD EXPIRE INTERVAL 90 DAY;

-- 5. Kích hoạt General Log để lưu lại toàn bộ truy vấn phục vụ điều tra (Forensics/Audit)
SET GLOBAL general_log = 1;
SET GLOBAL general_log_file = '/var/lib/mysql/general.log';

-- 6. Áp dụng toàn bộ phân quyền
FLUSH PRIVILEGES;

-- 7. Kiểm tra trạng thái log
SHOW VARIABLES LIKE 'general_log%';
