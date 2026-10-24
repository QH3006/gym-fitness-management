<?php
require_once __DIR__ . '/config/database.php';

$page_title = "Quản lý Hội viên - Gym Master";
$active_menu = "members";
$message = "";
$error = "";

// Xử lý Thêm hội viên mới (Sử dụng hàm AES_ENCRYPT của MySQL theo yêu cầu Lab Bảo mật)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $member_code = trim($_POST['member_code']);
    $full_name = trim($_POST['full_name']);
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $phone = trim($_POST['phone']);
    $ssn = trim($_POST['ssn']);
    $package_id = (int)$_POST['package_id'];
    $trainer_id = !empty($_POST['trainer_id']) ? (int)$_POST['trainer_id'] : null;
    $start_date = $_POST['start_date'];

    // Lấy số tháng của gói tập để tính ngày kết thúc
    $stmtPkg = $pdo->prepare("SELECT duration_months FROM packages WHERE id = ?");
    $stmtPkg->execute([$package_id]);
    $months = $stmtPkg->fetchColumn() ?: 1;
    $end_date = date('Y-m-d', strtotime("+{$months} months", strtotime($start_date)));

    try {
        $sql = "INSERT INTO members (member_code, full_name, gender, dob, phone, ssn, ssn_encrypted, package_id, trainer_id, start_date, end_date, status)
                VALUES (?, ?, ?, ?, ?, ?, AES_ENCRYPT(?, ?), ?, ?, ?, ?, 'active')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $member_code, $full_name, $gender, $dob, $phone, $ssn,
            $ssn, $aes_key,
            $package_id, $trainer_id, $start_date, $end_date
        ]);
        $message = "Thêm mới hội viên <strong>{$full_name}</strong> thành công! Dữ liệu CCCD đã được mã hóa AES-256.";
    } catch (PDOException $e) {
        $error = "Lỗi thêm hội viên: " . $e->getMessage();
    }
}

// Xử lý Xóa hội viên
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
        $stmt->execute([$del_id]);
        $message = "Xóa hội viên thành công!";
    } catch (PDOException $e) {
        $error = "Lỗi khi xóa: " . $e->getMessage();
    }
}

// Lấy danh sách hội viên (Giải mã AES_DECRYPT để hiển thị kiểm chứng)
$members = $pdo->query("
    SELECT m.*, 
           HEX(m.ssn_encrypted) AS ssn_hex,
           AES_DECRYPT(m.ssn_encrypted, '{$aes_key}') AS ssn_decrypted,
           p.package_name, p.price,
           t.full_name AS trainer_name
    FROM members m
    LEFT JOIN packages p ON m.package_id = p.id
    LEFT JOIN trainers t ON m.trainer_id = t.id
    ORDER BY m.id DESC
")->fetchAll();

// Lấy danh sách gói tập và huấn luyện viên cho form
$packages = $pdo->query("SELECT * FROM packages WHERE status = 'active'")->fetchAll();
$trainers = $pdo->query("SELECT * FROM trainers WHERE status = 'active'")->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="main-wrapper">
    <header class="topbar">
        <div class="page-title">
            <h2>Quản lý Hội viên & Thẻ tập</h2>
            <p>Hồ sơ thành viên, thông tin bảo mật và quản lý gói dịch vụ</p>
        </div>
        <div class="topbar-actions">
            <button class="btn btn-primary" onclick="document.getElementById('modalAddMember').scrollIntoView({behavior: 'smooth'})">
                <i class="fa-solid fa-user-plus"></i> Đăng ký Hội viên mới
            </button>
        </div>
    </header>

    <main class="content-container">
        <?php if ($message): ?>
            <div style="padding:14px 20px; background:var(--success-bg); border:1px solid rgba(16,185,129,0.3); color:var(--success); border-radius:var(--radius-md); margin-bottom:20px;">
                <i class="fa-solid fa-circle-check"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div style="padding:14px 20px; background:var(--danger-bg); border:1px solid rgba(239,68,68,0.3); color:var(--danger); border-radius:var(--radius-md); margin-bottom:20px;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- DANH SÁCH HỘI VIÊN -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-address-card"></i> Danh sách Hội viên Hiện hữu (Tổng số: <?php echo count($members); ?>)</h3>
                <span class="badge-tag"><i class="fa-solid fa-shield"></i> Cột CCCD/SSN bảo vệ bằng AES-256</span>
            </div>
            <div class="card-body" style="padding:0;">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mã Thẻ</th>
                                <th>Họ Tên & Giới tính</th>
                                <th>Số Điện Thoại</th>
                                <th>CCCD Gốc & Mã Hóa AES (HEX)</th>
                                <th>Gói Tập</th>
                                <th>HLV Kèm</th>
                                <th>Hạn Thẻ</th>
                                <th>Trạng Thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $m): ?>
                            <tr>
                                <td><strong style="color:var(--accent-primary);"><?php echo htmlspecialchars($m['member_code']); ?></strong></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($m['full_name']); ?></strong>
                                    <div style="font-size:12px; color:var(--text-muted);"><?php echo htmlspecialchars($m['gender']); ?> • <?php echo date('d/m/Y', strtotime($m['dob'])); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($m['phone']); ?></td>
                                <td>
                                    <div style="font-size:13px; font-weight:600;"><?php echo htmlspecialchars($m['ssn']); ?></div>
                                    <div style="font-family:monospace; font-size:10px; color:#38bdf8; max-width:180px; overflow:hidden; text-overflow:ellipsis;" title="<?php echo $m['ssn_hex']; ?>">
                                        AES: <?php echo substr($m['ssn_hex'], 0, 16); ?>...
                                    </div>
                                </td>
                                <td>
                                    <span class="pill pill-info"><?php echo htmlspecialchars($m['package_name']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($m['trainer_name'] ?? 'Tự tập'); ?></td>
                                <td>
                                    <div style="font-size:12px;"><?php echo date('d/m/Y', strtotime($m['start_date'])); ?> ➔ <strong><?php echo date('d/m/Y', strtotime($m['end_date'])); ?></strong></div>
                                </td>
                                <td>
                                    <span class="pill pill-<?php echo ($m['status'] === 'active') ? 'success' : 'danger'; ?>">
                                        <?php echo ($m['status'] === 'active') ? 'Hiệu lực' : 'Hết hạn'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/members.php?delete=<?php echo $m['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa hội viên này?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- FORM THÊM HỘI VIÊN MỚI -->
        <div class="card" id="modalAddMember">
            <div class="card-header">
                <h3><i class="fa-solid fa-user-plus"></i> Đăng Ký Hội Viên Mới</h3>
            </div>
            <div class="card-body">
                <form action="/members.php" method="POST">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Mã Hội Viên / Mã SV</label>
                            <input type="text" name="member_code" class="form-control" value="GYM<?php echo rand(100, 999); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Họ và Tên</label>
                            <input type="text" name="full_name" class="form-control" placeholder="Ví dụ: Hoàng Tuấn Anh" required>
                        </div>
                        <div class="form-group">
                            <label>Giới Tính</label>
                            <select name="gender" class="form-control">
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Ngày Sinh</label>
                            <input type="date" name="dob" class="form-control" value="2000-01-01" required>
                        </div>
                        <div class="form-group">
                            <label>Số Điện Thoại</label>
                            <input type="text" name="phone" class="form-control" placeholder="09xxxxxxxx" required>
                        </div>
                        <div class="form-group">
                            <label>Số CCCD / CMND (Sẽ tự động mã hóa AES-256)</label>
                            <input type="text" name="ssn" class="form-control" placeholder="001xxxxxxxx" required>
                        </div>
                        <div class="form-group">
                            <label>Chọn Gói Tập</label>
                            <select name="package_id" class="form-control" required>
                                <?php foreach ($packages as $pkg): ?>
                                    <option value="<?php echo $pkg['id']; ?>">
                                        <?php echo htmlspecialchars($pkg['package_name']); ?> (<?php echo $pkg['duration_months']; ?> tháng - <?php echo number_format($pkg['price'], 0, ',', '.'); ?>đ)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Huấn Luyện Viên Hướng Dẫn (PT)</label>
                            <select name="trainer_id" class="form-control">
                                <option value="">-- Không đăng ký PT riêng --</option>
                                <?php foreach ($trainers as $trn): ?>
                                    <option value="<?php echo $trn['id']; ?>">
                                        <?php echo htmlspecialchars($trn['full_name']); ?> (<?php echo htmlspecialchars($trn['specialty']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Ngày Bắt Đầu Tập</label>
                            <input type="date" name="start_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <div style="margin-top: 15px; display: flex; gap: 12px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Lưu & Kích hoạt Thẻ tập
                        </button>
                        <button type="reset" class="btn btn-secondary">Nhập lại</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
