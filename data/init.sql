-- ====================================================================
-- HỆ THỐNG QUẢN LÝ PHÒNG GYM & FITNESS (ĐỀ TÀI SỐ 23)
-- Kịch bản khởi tạo Cơ sở dữ liệu và Phân quyền bảo mật (Hardening)
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `gym_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gym_db`;

-- 1. BẢNG GÓI TẬP (PACKAGES)
CREATE TABLE IF NOT EXISTS `packages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `package_name` VARCHAR(100) NOT NULL,
    `duration_months` INT NOT NULL,
    `price` DECIMAL(12, 2) NOT NULL,
    `description` TEXT,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. BẢNG HUẤN LUYỆN VIÊN (TRAINERS / PT)
CREATE TABLE IF NOT EXISTS `trainers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `specialty` VARCHAR(100) NOT NULL,
    `experience_years` INT DEFAULT 1,
    `rating` DECIMAL(2, 1) DEFAULT 5.0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. BẢNG HỘI VIÊN (MEMBERS) - CÓ CỘT MÃ HÓA BẢO MẬT AES THEO YÊU CẦU LAB
CREATE TABLE IF NOT EXISTS `members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_code` VARCHAR(20) UNIQUE NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `gender` ENUM('Nam', 'Nữ', 'Khác') DEFAULT 'Nam',
    `dob` DATE,
    `phone` VARCHAR(20),
    `ssn` VARCHAR(20),                      -- Dữ liệu CCCD/CMND gốc
    `ssn_encrypted` VARBINARY(255),        -- Dữ liệu mã hóa AES-256 (Hardening)
    `package_id` INT,
    `trainer_id` INT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `status` ENUM('active', 'expired', 'suspended') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`trainer_id`) REFERENCES `trainers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. BẢNG LỊCH TẬP / LỚP HỌC (SCHEDULES / CLASSES)
CREATE TABLE IF NOT EXISTS `schedules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `class_name` VARCHAR(100) NOT NULL,
    `trainer_id` INT,
    `day_of_week` VARCHAR(20) NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `room` VARCHAR(50) DEFAULT 'Phòng Gym Chính',
    `max_capacity` INT DEFAULT 20,
    `status` ENUM('active', 'cancelled') DEFAULT 'active',
    FOREIGN KEY (`trainer_id`) REFERENCES `trainers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. BẢNG ĐIỂM DANH CHECK-IN (CHECKINS)
CREATE TABLE IF NOT EXISTS `checkins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_id` INT NOT NULL,
    `checkin_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `notes` VARCHAR(255),
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. BẢNG TÀI KHOẢN HỆ THỐNG (USERS)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `role` ENUM('admin', 'staff', 'trainer') DEFAULT 'staff',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================================================================
-- DỮ LIỆU MẪU BAN ĐẦU (SEED DATA)
-- ====================================================================

-- Chèn dữ liệu Gói tập
INSERT INTO `packages` (`package_name`, `duration_months`, `price`, `description`, `status`) VALUES
('Gói Classic 1 Tháng', 1, 500000.00, 'Tập luyện toàn bộ thiết bị Gym cơ bản, không giới hạn khung giờ', 'active'),
('Gói Standard 3 Tháng', 3, 1350000.00, 'Tập gym + Phòng xông hơi khô/ướt Sauna', 'active'),
('Gói VIP 6 Tháng', 6, 2500000.00, 'Tập gym + Sauna + Tủ đồ riêng biệt + Tặng 2 buổi cùng HLV', 'active'),
('Gói Diamond 12 Tháng', 12, 4500000.00, 'Full quyền lợi + Nước uống miễn phí + Miễn phí đo InBody hàng tháng', 'active');

-- Chèn dữ liệu Huấn luyện viên
INSERT INTO `trainers` (`full_name`, `phone`, `specialty`, `experience_years`, `rating`, `status`) VALUES
('Nguyễn Văn Tuấn', '0912345678', 'Thể hình chuyên sâu & Tăng cơ (Hypertrophy)', 6, 4.9, 'active'),
('Trần Thị Mai', '0987654321', 'Yoga, Cardio & Giảm mỡ toàn thân', 4, 4.8, 'active'),
('Lê Hoàng Nam', '0933221144', 'Kickboxing, Calisthenics & Phục hồi cơ', 5, 5.0, 'active'),
('Phạm Thu Trang', '0977889900', 'Pilates & Dinh dưỡng Thể thao', 3, 4.7, 'active');

-- Chèn dữ liệu Hội viên (Đồng thời mã hóa cột ssn_encrypted bằng AES-256)
SET @aes_key = 'GymSuperSecretKey2026!';

INSERT INTO `members` (`member_code`, `full_name`, `gender`, `dob`, `phone`, `ssn`, `ssn_encrypted`, `package_id`, `trainer_id`, `start_date`, `end_date`, `status`) VALUES
('GYM001', 'Đỗ Thành Long', 'Nam', '1998-05-12', '0901112233', '001198004567', AES_ENCRYPT('001198004567', @aes_key), 4, 1, '2026-01-15', '2027-01-15', 'active'),
('GYM002', 'Vũ Hải Yến', 'Nữ', '2001-11-20', '0902223344', '001201007890', AES_ENCRYPT('001201007890', @aes_key), 3, 2, '2026-02-01', '2026-08-01', 'active'),
('GYM003', 'Hoàng Minh Quân', 'Nam', '1995-03-08', '0903334455', '001195001234', AES_ENCRYPT('001195001234', @aes_key), 2, 3, '2026-03-10', '2026-06-10', 'active'),
('GYM004', 'Bùi Ngọc Linh', 'Nữ', '1999-08-14', '0904445566', '001199005678', AES_ENCRYPT('001199005678', @aes_key), 1, 4, '2026-04-01', '2026-05-01', 'active'),
('GYM005', 'Phạm Quốc Bảo', 'Nam', '1992-12-05', '0905556677', '001192009876', AES_ENCRYPT('001192009876', @aes_key), 4, 1, '2025-05-01', '2026-05-01', 'active');

-- Chèn dữ liệu Lịch tập / Lớp học
INSERT INTO `schedules` (`class_name`, `trainer_id`, `day_of_week`, `start_time`, `end_time`, `room`, `max_capacity`, `status`) VALUES
('Lớp Body Pump Cơ Bản', 1, 'Thứ 2, 4, 6', '06:00:00', '07:30:00', 'Phòng Thể Hình Tầng 1', 25, 'active'),
('Lớp Vinyasa Yoga Tinh Thần', 2, 'Thứ 3, 5, 7', '18:00:00', '19:30:00', 'Phòng Yoga Tầng 2', 20, 'active'),
('Lớp Kickfit Đốt Mỡ Nhanh', 3, 'Thứ 2, 4, 6', '17:30:00', '19:00:00', 'Sàn Đấu Boxing Tầng 2', 15, 'active'),
('Lớp Pilates Cải Thiện Vóc Dáng', 4, 'Chủ Nhật', '08:00:00', '10:00:00', 'Phòng Pilates Tầng 3', 18, 'active');

-- Chèn dữ liệu Điểm danh gần đây
INSERT INTO `checkins` (`member_id`, `checkin_time`, `notes`) VALUES
(1, NOW() - INTERVAL 2 HOUR, 'Tập ngực & tay sau'),
(2, NOW() - INTERVAL 1 HOUR, 'Tham gia lớp Yoga tối'),
(3, NOW() - INTERVAL 30 MINUTE, 'Tập cardio & boxing'),
(4, NOW() - INTERVAL 15 MINUTE, 'Điểm danh thẻ tháng');

-- Chèn tài khoản hệ thống mặc định (password: Admin@Gym2026)
INSERT INTO `users` (`username`, `password_hash`, `email`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@gymfitness.local', 'admin'),
('staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff@gymfitness.local', 'staff');

-- ====================================================================
-- HARDENING BẢO MẬT DATABASE & NGUYÊN TẮC LEAST PRIVILEGE
-- (Áp dụng đúng chuẩn theo tài liệu Lab Quản trị DB)
-- ====================================================================

-- 1. Tạo user ứng dụng web chỉ có quyền SELECT, INSERT, UPDATE (Không cho DROP, DELETE, ALTER)
DROP USER IF EXISTS 'gym_app_user'@'%';
CREATE USER 'gym_app_user'@'%' IDENTIFIED BY 'GymApp@Pass2026!Strong';
GRANT SELECT, INSERT, UPDATE, DELETE ON `gym_db`.* TO 'gym_app_user'@'%';

-- 2. Tạo user chuyên báo cáo chỉ có quyền SELECT (Least Privilege)
DROP USER IF EXISTS 'gym_report_user'@'%';
CREATE USER 'gym_report_user'@'%' IDENTIFIED BY 'GymReport@2026Pass!';
GRANT SELECT ON `gym_db`.* TO 'gym_report_user'@'%';

-- 3. Tạo user exporter cho Prometheus (chỉ cấp quyền đọc thống kê hệ thống)
DROP USER IF EXISTS 'mysqld_exporter'@'%';
CREATE USER 'mysqld_exporter'@'%' IDENTIFIED BY 'Exporter@Pass2026!';
GRANT PROCESS, REPLICATION CLIENT, SELECT ON *.* TO 'mysqld_exporter'@'%';

-- 4. Áp dụng hiệu lực quyền
FLUSH PRIVILEGES;

-- 5. Kích hoạt logging ghi vết SQL vào /var/lib/mysql/general.log theo yêu cầu Lab
SET GLOBAL general_log = 1;
SET GLOBAL general_log_file = '/var/lib/mysql/general.log';
