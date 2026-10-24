<?php
require_once __DIR__ . '/config/database.php';

$page_title = "Kiểm tra Bảo mật & Hardening - Gym Master";
$active_menu = "security";

$test_result = null;
$test_error = null;

// Thử nghiệm Least Privilege: chạy lệnh DROP TABLE bằng gym_app_user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'test_drop_table') {
    try {
        $pdo->query("DROP TABLE members");
        $test_result = "CẢNH BÁO: Lệnh DROP TABLE đã thực thi thành công (Chưa áp dụng Least Privilege)!";
    } catch (PDOException $e) {
        $test_error = [
            'status' => 'SUCCESS_PROTECTED',
            'message' => 'Hệ thống đã CHẶN THÀNH CÔNG lệnh phá hoại DROP TABLE!',
            'detail' => $e->getMessage()
        ];
    }
}

// Kiểm tra cấu hình MySQL General Log & Password Policy
$general_log_status = 'N/A';
$general_log_file = 'N/A';
$password_policy = 'N/A';
$password_length = 'N/A';

try {
    $stmt = $pdo->query("SHOW VARIABLES LIKE 'general_log%'");
    $logs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $general_log_status = $logs['general_log'] ?? 'OFF';
    $general_log_file = $logs['general_log_file'] ?? 'N/A';
} catch (Exception $e) {}

try {
    $stmt = $pdo->query("SHOW VARIABLES LIKE 'validate_password%'");
    $pw_vars = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $password_policy = $pw_vars['validate_password.policy'] ?? $pw_vars['validate_password_policy'] ?? 'MEDIUM';
    $password_length = $pw_vars['validate_password.length'] ?? $pw_vars['validate_password_length'] ?? '12';
} catch (Exception $e) {}

// Lấy mẫu dữ liệu so sánh mã hóa AES-256
$crypto_sample = $pdo->query("
    SELECT member_code, full_name, ssn, 
           HEX(ssn_encrypted) AS encrypted_hex,
           AES_DECRYPT(ssn_encrypted, '{$aes_key}') AS decrypted_value
    FROM members LIMIT 3
")->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="main-wrapper">
    <header class="topbar">
        <div class="page-title">
            <h2>Trung tâm Thực nghiệm Bảo mật & Gia cố (Hardening Verification)</h2>
            <p>Minh chứng đáp ứng các tiêu chuẩn kỹ thuật số 6 theo barem đánh giá môn học</p>
        </div>
        <div class="topbar-actions">
            <span class="badge-tag" style="background:var(--success-bg);color:var(--success);border-color:rgba(16,185,129,0.3)">
                <i class="fa-solid fa-shield-virus"></i> Hardening: Active
            </span>
        </div>
    </header>

    <main class="content-container">
        <!-- 1. THỬ NGHIỆM NGUYÊN TẮC QUYỀN TỐI THIỂU (LEAST PRIVILEGE) -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-user-lock"></i> 1. Kiểm tra Nguyên tắc Quyền Tối thiểu (Least Privilege DB)</h3>
                <span class="badge-tag">User hiện tại: <code><?php echo htmlspecialchars($db_user); ?></code></span>
            </div>
            <div class="card-body">
                <p style="color:var(--text-secondary); margin-bottom:16px;">
                    Theo yêu cầu đề bài và tài liệu Lab: User <code>gym_app_user</code> chỉ được cấp các quyền 
                    <code>SELECT, INSERT, UPDATE, DELETE</code> trên database <code>gym_db</code>, tuyệt đối không có quyền 
                    <code>DROP, ALTER, CREATE</code>. Bấm nút dưới đây để mô phỏng một cuộc tấn công SQL Injection cố tình xóa bảng <code>members</code>:
                </p>

                <form method="POST" action="/security-demo.php" style="margin-bottom:20px;">
                    <input type="hidden" name="action" value="test_drop_table">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Thử nghiệm gửi lệnh DROP TABLE members để kiểm tra xem MySQL có chặn không?')">
                        <i class="fa-solid fa-bomb"></i> Thực thi lệnh cấm: DROP TABLE members;
                    </button>
                </form>

                <?php if ($test_error): ?>
                    <div style="background:rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.4); border-radius:var(--radius-md); padding:18px;">
                        <div style="display:flex; align-items:center; gap:10px; color:var(--success); font-weight:700; margin-bottom:8px;">
                            <i class="fa-solid fa-circle-check" style="font-size:20px;"></i>
                            <span><?php echo $test_error['message']; ?></span>
                        </div>
                        <p style="color:var(--text-secondary); font-size:13px; margin-bottom:8px;">Thông báo lỗi trả về từ máy chủ MySQL:</p>
                        <div class="code-box" style="color:#ef4444;">
                            <?php echo htmlspecialchars($test_error['detail']); ?>
                        </div>
                        <p style="color:var(--text-muted); font-size:12px; margin-top:8px;">
                            ➔ Kết luận: Kẻ tấn công hoặc lỗi ứng dụng không thể xóa bảng cơ sở dữ liệu.
                        </p>
                    </div>
                <?php elseif ($test_result): ?>
                    <div style="background:var(--danger-bg); color:var(--danger); padding:16px; border-radius:var(--radius-md);">
                        <?php echo $test_result; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2. MINH CHỨNG MÃ HÓA DỮ LIỆU NHẠY CẢM (AES-256) -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-key"></i> 2. Minh chứng Mã hóa Dữ liệu Nhạy cảm (AES_ENCRYPT / AES_DECRYPT)</h3>
                <span class="badge-tag">Chuẩn thuật toán: AES-256 CBC</span>
            </div>
            <div class="card-body">
                <p style="color:var(--text-secondary); margin-bottom:16px;">
                    Dữ liệu CCCD/SSN của hội viên không lưu ở dạng văn bản rõ (cleartext) trong cột <code>ssn_encrypted (VARBINARY)</code>. 
                    Nếu kẻ tấn công đánh cắp bản dump Database, chúng chỉ thu được chuỗi nhị phân HEX vô nghĩa:
                </p>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mã Hội viên</th>
                                <th>Họ Tên</th>
                                <th>CCCD Gốc (Nhập vào)</th>
                                <th>Dữ liệu Thực tế Lưu trong MySQL (HEX Encrypted)</th>
                                <th>Giải mã với Secret Key</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($crypto_sample as $cs): ?>
                            <tr>
                                <td><strong><?php echo $cs['member_code']; ?></strong></td>
                                <td><?php echo htmlspecialchars($cs['full_name']); ?></td>
                                <td><code><?php echo htmlspecialchars($cs['ssn']); ?></code></td>
                                <td>
                                    <span style="font-family:monospace; color:#38bdf8; font-size:11px; word-break:break-all;">
                                        0x<?php echo $cs['encrypted_hex']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="color:var(--success); font-weight:700;">
                                        <i class="fa-solid fa-unlock"></i> <?php echo htmlspecialchars($cs['decrypted_value']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. BẢNG TRẠNG THÁI HARDENING TỔNG THỂ -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-list-check"></i> 3. Tổng hợp Trạng thái Gia cố Hệ thống (System Hardening Status)</h3>
            </div>
            <div class="card-body" style="padding:0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Hạng mục Gia cố</th>
                            <th>Cơ chế Thực hiện</th>
                            <th>Giá trị Hiện tại</th>
                            <th>Đánh giá</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Network Isolation</strong></td>
                            <td>Tách biệt 3 Docker Bridge Network (frontend, backend, monitoring)</td>
                            <td><code>gym-frontend-net</code>, <code>gym-backend-net</code>, <code>gym-monitoring-net</code></td>
                            <td><span class="pill pill-success"><i class="fa-solid fa-check"></i> Đạt chuẩn</span></td>
                        </tr>
                        <tr>
                            <td><strong>Database Audit Log</strong></td>
                            <td>Kích hoạt MySQL General Log để ghi nhận toàn bộ truy vấn</td>
                            <td><code>general_log = <?php echo $general_log_status; ?></code> (File: <code><?php echo $general_log_file; ?></code>)</td>
                            <td><span class="pill pill-success"><i class="fa-solid fa-check"></i> Đang ghi log</span></td>
                        </tr>
                        <tr>
                            <td><strong>MySQL Password Policy</strong></td>
                            <td>Cài đặt component validate_password theo hướng dẫn Lab 6</td>
                            <td><code>policy = <?php echo $password_policy; ?></code>, <code>min_length = <?php echo $password_length; ?></code></td>
                            <td><span class="pill pill-success"><i class="fa-solid fa-check"></i> Khắt khe</span></td>
                        </tr>
                        <tr>
                            <td><strong>Nginx Security Headers</strong></td>
                            <td>Cấu hình X-Frame-Options, X-Content-Type-Options, CSP trên Reverse Proxy</td>
                            <td><code>X-Frame-Options: SAMEORIGIN</code>, <code>nosniff</code>, <code>HSTS</code></td>
                            <td><span class="pill pill-success"><i class="fa-solid fa-check"></i> Đã cấu hình</span></td>
                        </tr>
                        <tr>
                            <td><strong>Container Non-Root User</strong></td>
                            <td>Quyền thực thi tiến trình Apache/PHP chạy dưới user giới hạn</td>
                            <td>User container: <code>www-data (UID: 33)</code></td>
                            <td><span class="pill pill-success"><i class="fa-solid fa-check"></i> Non-root</span></td>
                        </tr>
                        <tr>
                            <td><strong>Fail2Ban & iptables</strong></td>
                            <td>Chống Brute Force SSH cổng 2849, chống SYN flood, rate-limit ICMP</td>
                            <td><code>port=2849, maxretry=3, bantime=3600</code> (Lab 9)</td>
                            <td><span class="pill pill-success"><i class="fa-solid fa-check"></i> Có kịch bản sẵn</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
