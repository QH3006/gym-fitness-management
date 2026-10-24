<?php
require_once __DIR__ . '/config/database.php';

$page_title = "Điểm danh Hội viên - Gym Master";
$active_menu = "checkin";
$message = "";
$error = "";

// Xử lý Check-in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkin') {
    $member_code = trim($_POST['member_code']);
    $notes = trim($_POST['notes']);

    // Tìm kiếm hội viên
    $stmt = $pdo->prepare("
        SELECT m.*, p.package_name 
        FROM members m 
        LEFT JOIN packages p ON m.package_id = p.id 
        WHERE m.member_code = ?
    ");
    $stmt->execute([$member_code]);
    $member = $stmt->fetch();

    if (!$member) {
        $error = "Không tìm thấy hội viên với mã thẻ [{$member_code}]!";
    } elseif ($member['status'] !== 'active' || strtotime($member['end_date']) < time()) {
        $error = "Thẻ tập của hội viên <strong>{$member['full_name']}</strong> đã HẾT HẠN vào ngày " . date('d/m/Y', strtotime($member['end_date'])) . ". Vui lòng gia hạn trước khi vào!";
    } else {
        $stmtCheck = $pdo->prepare("INSERT INTO checkins (member_id, notes) VALUES (?, ?)");
        $stmtCheck->execute([$member['id'], $notes ?: 'Vào tập tiêu chuẩn']);
        $message = "Điểm danh thành công! Xin chào <strong>{$member['full_name']}</strong> (Gói: {$member['package_name']}).";
    }
}

// Lấy danh sách điểm danh gần đây
$checkins = $pdo->query("
    SELECT c.*, m.member_code, m.full_name, m.phone, p.package_name
    FROM checkins c
    JOIN members m ON c.member_id = m.id
    LEFT JOIN packages p ON m.package_id = p.id
    ORDER BY c.checkin_time DESC LIMIT 20
")->fetchAll();

$all_members = $pdo->query("SELECT member_code, full_name, status FROM members ORDER BY member_code ASC")->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="main-wrapper">
    <header class="topbar">
        <div class="page-title">
            <h2>Hệ thống Điểm danh Cửa ra vào</h2>
            <p>Xác thực mã thẻ hội viên, kiểm tra hạn sử dụng và lưu vết ra vào phòng tập</p>
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

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 26px;">
            <!-- FORM ĐIỂM DANH -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-barcode"></i> Quét Thẻ / Nhập Mã</h3>
                </div>
                <div class="card-body">
                    <form action="/checkin.php" method="POST">
                        <input type="hidden" name="action" value="checkin">
                        
                        <div class="form-group">
                            <label>Mã Thẻ Hội Viên</label>
                            <input type="text" name="member_code" id="member_code_input" class="form-control" placeholder="Ví dụ: GYM001" autofocus required>
                        </div>

                        <div class="form-group">
                            <label>Chọn Nhanh Từ Danh Sách</label>
                            <select class="form-control" onchange="document.getElementById('member_code_input').value = this.value">
                                <option value="">-- Chọn hội viên --</option>
                                <?php foreach ($all_members as $am): ?>
                                    <option value="<?php echo $am['member_code']; ?>">
                                        <?php echo $am['member_code']; ?> - <?php echo htmlspecialchars($am['full_name']); ?> (<?php echo $am['status']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Ghi chú (Tùy chọn)</label>
                            <input type="text" name="notes" class="form-control" placeholder="Tập ngực, tập cardio, v.v.">
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fa-solid fa-right-to-bracket"></i> Xác nhận Vào Tập
                        </button>
                    </form>
                </div>
            </div>

            <!-- LỊCH SỬ ĐIỂM DANH -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-list-check"></i> Nhật ký Ra Vào Hôm Nay</h3>
                    <span class="badge-tag"><i class="fa-solid fa-bolt"></i> Real-time Logging</span>
                </div>
                <div class="card-body" style="padding:0;">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Mã Thẻ</th>
                                    <th>Họ và Tên</th>
                                    <th>Gói Đăng ký</th>
                                    <th>Thời Gian</th>
                                    <th>Ghi Chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($checkins as $chk): ?>
                                <tr>
                                    <td><strong style="color:var(--accent-primary);"><?php echo htmlspecialchars($chk['member_code']); ?></strong></td>
                                    <td><strong><?php echo htmlspecialchars($chk['full_name']); ?></strong></td>
                                    <td><span class="pill pill-info"><?php echo htmlspecialchars($chk['package_name']); ?></span></td>
                                    <td><i class="fa-regular fa-clock"></i> <?php echo date('H:i:s - d/m/Y', strtotime($chk['checkin_time'])); ?></td>
                                    <td><?php echo htmlspecialchars($chk['notes']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
