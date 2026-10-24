<?php
require_once __DIR__ . '/config/database.php';

$page_title = "Huấn Luyện Viên PT - Gym Master";
$active_menu = "trainers";
$message = "";
$error = "";

// Thêm HLV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $specialty = trim($_POST['specialty']);
    $experience_years = (int)$_POST['experience_years'];

    try {
        $stmt = $pdo->prepare("INSERT INTO trainers (full_name, phone, specialty, experience_years, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([$full_name, $phone, $specialty, $experience_years]);
        $message = "Thêm mới Huấn luyện viên <strong>{$full_name}</strong> thành công!";
    } catch (PDOException $e) {
        $error = "Lỗi thêm HLV: " . $e->getMessage();
    }
}

// Xóa HLV
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM trainers WHERE id = ?");
        $stmt->execute([$del_id]);
        $message = "Đã xóa Huấn luyện viên thành công!";
    } catch (PDOException $e) {
        $error = "Không thể xóa HLV này do đang phụ trách hội viên hoặc lớp học.";
    }
}

$trainers = $pdo->query("
    SELECT t.*, 
           COUNT(DISTINCT m.id) AS trainee_count,
           COUNT(DISTINCT s.id) AS class_count
    FROM trainers t
    LEFT JOIN members m ON t.id = m.trainer_id
    LEFT JOIN schedules s ON t.id = s.trainer_id
    GROUP BY t.id
    ORDER BY t.id DESC
")->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="main-wrapper">
    <header class="topbar">
        <div class="page-title">
            <h2>Đội ngũ Huấn luyện viên (Personal Trainers)</h2>
            <p>Quản lý hồ sơ PT, chuyên môn huấn luyện và số lượng học viên kèm cặp</p>
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

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 26px;">
            <!-- DANH SÁCH HUẤN LUYỆN VIÊN -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-user-ninja"></i> Danh sách Huấn luyện viên chuyên nghiệp</h3>
                </div>
                <div class="card-body" style="padding:0;">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Họ và Tên</th>
                                    <th>Chuyên môn</th>
                                    <th>Kinh nghiệm</th>
                                    <th>Số HV kèm</th>
                                    <th>Lớp phụ trách</th>
                                    <th>Đánh giá</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($trainers as $t): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($t['full_name']); ?></strong>
                                        <div style="font-size:12px; color:var(--text-muted);"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($t['phone']); ?></div>
                                    </td>
                                    <td><span class="pill pill-info"><?php echo htmlspecialchars($t['specialty']); ?></span></td>
                                    <td><strong><?php echo $t['experience_years']; ?></strong> năm</td>
                                    <td><span class="pill pill-success"><?php echo $t['trainee_count']; ?> HV</span></td>
                                    <td><?php echo $t['class_count']; ?> lớp</td>
                                    <td><strong style="color:var(--warning);"><i class="fa-solid fa-star"></i> <?php echo $t['rating']; ?></strong></td>
                                    <td>
                                        <a href="/trainers.php?delete=<?php echo $t['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa HLV này?')">
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

            <!-- FORM THÊM HLV -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-user-plus"></i> Đăng Ký Huấn Luyện Viên</h3>
                </div>
                <div class="card-body">
                    <form action="/trainers.php" method="POST">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="form-group">
                            <label>Họ và Tên Huấn Luyện Viên</label>
                            <input type="text" name="full_name" class="form-control" placeholder="Nguyễn Văn A" required>
                        </div>
                        <div class="form-group">
                            <label>Số Điện Thoại</label>
                            <input type="text" name="phone" class="form-control" placeholder="091xxxxxxx" required>
                        </div>
                        <div class="form-group">
                            <label>Chuyên Môn Huấn Luyện</label>
                            <input type="text" name="specialty" class="form-control" placeholder="Ví dụ: Thể hình, Kickfit, Pilates..." required>
                        </div>
                        <div class="form-group">
                            <label>Số Năm Kinh Nghiệm</label>
                            <input type="number" name="experience_years" min="1" max="40" class="form-control" value="2" required>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fa-solid fa-check"></i> Lưu Huấn Luyện Viên
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
