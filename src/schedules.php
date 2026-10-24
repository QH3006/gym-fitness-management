<?php
require_once __DIR__ . '/config/database.php';

$page_title = "Lịch tập & Lớp học - Gym Master";
$active_menu = "schedules";
$message = "";
$error = "";

// Thêm lớp học
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $class_name = trim($_POST['class_name']);
    $trainer_id = (int)$_POST['trainer_id'];
    $day_of_week = trim($_POST['day_of_week']);
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $room = trim($_POST['room']);
    $max_capacity = (int)$_POST['max_capacity'];

    try {
        $stmt = $pdo->prepare("INSERT INTO schedules (class_name, trainer_id, day_of_week, start_time, end_time, room, max_capacity, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$class_name, $trainer_id, $day_of_week, $start_time, $end_time, $room, $max_capacity]);
        $message = "Thêm lớp học <strong>{$class_name}</strong> thành công!";
    } catch (PDOException $e) {
        $error = "Lỗi tạo lớp học: " . $e->getMessage();
    }
}

// Xóa lớp học
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
        $stmt->execute([$del_id]);
        $message = "Đã xóa lớp học thành công!";
    } catch (PDOException $e) {
        $error = "Lỗi khi xóa: " . $e->getMessage();
    }
}

$schedules = $pdo->query("
    SELECT s.*, t.full_name AS trainer_name, t.specialty
    FROM schedules s
    LEFT JOIN trainers t ON s.trainer_id = t.id
    ORDER BY s.id DESC
")->fetchAll();

$trainers = $pdo->query("SELECT * FROM trainers WHERE status = 'active'")->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="main-wrapper">
    <header class="topbar">
        <div class="page-title">
            <h2>Lịch tập Lớp học theo Tuần</h2>
            <p>Thời khóa biểu các lớp nhóm: Yoga, Kickboxing, Body Pump, Zumba</p>
        </div>
    </header>

    <main class="content-container">
        <?php if ($message): ?>
            <div style="padding:14px 20px; background:var(--success-bg); border:1px solid rgba(16,185,129,0.3); color:var(--success); border-radius:var(--radius-md); margin-bottom:20px;">
                <i class="fa-solid fa-circle-check"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 26px;">
            <!-- DANH SÁCH LỊCH TẬP -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-calendar-check"></i> Các lớp học đang mở</h3>
                </div>
                <div class="card-body" style="padding:0;">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Lớp Học</th>
                                    <th>HLV Phụ Trách</th>
                                    <th>Ngày trong tuần</th>
                                    <th>Khung giờ</th>
                                    <th>Địa điểm</th>
                                    <th>Sĩ số</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $s): ?>
                                <tr>
                                    <td><strong style="color:#ffffff;"><?php echo htmlspecialchars($s['class_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($s['trainer_name']); ?></td>
                                    <td><span class="pill pill-warning"><?php echo htmlspecialchars($s['day_of_week']); ?></span></td>
                                    <td><?php echo substr($s['start_time'], 0, 5); ?> - <?php echo substr($s['end_time'], 0, 5); ?></td>
                                    <td><?php echo htmlspecialchars($s['room']); ?></td>
                                    <td><span class="pill pill-info"><?php echo $s['max_capacity']; ?> người</span></td>
                                    <td>
                                        <a href="/schedules.php?delete=<?php echo $s['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa lớp này?')">
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

            <!-- FORM TẠO LỚP HỌC MỚI -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-calendar-plus"></i> Mở Lớp Học Mới</h3>
                </div>
                <div class="card-body">
                    <form action="/schedules.php" method="POST">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="form-group">
                            <label>Tên Lớp Học</label>
                            <input type="text" name="class_name" class="form-control" placeholder="Ví dụ: Lớp Yoga Nâng Cao" required>
                        </div>
                        <div class="form-group">
                            <label>Huấn Luyện Viên Phụ Trách</label>
                            <select name="trainer_id" class="form-control" required>
                                <?php foreach ($trainers as $trn): ?>
                                    <option value="<?php echo $trn['id']; ?>"><?php echo htmlspecialchars($trn['full_name']); ?> (<?php echo htmlspecialchars($trn['specialty']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Ngày Tập Trong Tuần</label>
                            <input type="text" name="day_of_week" class="form-control" placeholder="Ví dụ: Thứ 2, 4, 6" required>
                        </div>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div class="form-group">
                                <label>Giờ Bắt Đầu</label>
                                <input type="time" name="start_time" class="form-control" value="07:00" required>
                            </div>
                            <div class="form-group">
                                <label>Giờ Kết Thúc</label>
                                <input type="time" name="end_time" class="form-control" value="08:30" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Phòng Tập</label>
                            <input type="text" name="room" class="form-control" value="Phòng Yoga Tầng 2" required>
                        </div>
                        <div class="form-group">
                            <label>Giới hạn học viên (Sức chứa)</label>
                            <input type="number" name="max_capacity" class="form-control" value="20" required>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fa-solid fa-calendar-plus"></i> Lưu Lịch Tập
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
