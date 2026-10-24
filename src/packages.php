<?php
require_once __DIR__ . '/config/database.php';

$page_title = "Quản lý Gói tập - Gym Master";
$active_menu = "packages";
$message = "";
$error = "";

// Thêm gói tập
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $package_name = trim($_POST['package_name']);
    $duration_months = (int)$_POST['duration_months'];
    $price = (float)$_POST['price'];
    $description = trim($_POST['description']);

    try {
        $stmt = $pdo->prepare("INSERT INTO packages (package_name, duration_months, price, description, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([$package_name, $duration_months, $price, $description]);
        $message = "Thêm gói tập mới <strong>{$package_name}</strong> thành công!";
    } catch (PDOException $e) {
        $error = "Lỗi thêm gói tập: " . $e->getMessage();
    }
}

// Xóa gói tập
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
        $stmt->execute([$del_id]);
        $message = "Đã xóa gói tập thành công!";
    } catch (PDOException $e) {
        $error = "Không thể xóa gói tập vì đang có hội viên sử dụng.";
    }
}

$packages = $pdo->query("
    SELECT p.*, COUNT(m.id) as subscriber_count 
    FROM packages p 
    LEFT JOIN members m ON p.id = m.package_id 
    GROUP BY p.id 
    ORDER BY p.price ASC
")->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="main-wrapper">
    <header class="topbar">
        <div class="page-title">
            <h2>Gói tập & Bảng giá Dịch vụ</h2>
            <p>Thiết lập các gói thành viên Gym, Yoga, Cardio và Sauna</p>
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
            <!-- DANH SÁCH GÓI TẬP -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-tags"></i> Bảng giá các gói tập hiện hành</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Tên Gói</th>
                                    <th>Thời hạn</th>
                                    <th>Đơn Giá</th>
                                    <th>Đang Đăng ký</th>
                                    <th>Mô tả</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($packages as $pkg): ?>
                                <tr>
                                    <td><strong style="color:#ffffff; font-size:15px;"><?php echo htmlspecialchars($pkg['package_name']); ?></strong></td>
                                    <td><span class="pill pill-info"><?php echo $pkg['duration_months']; ?> Tháng</span></td>
                                    <td><strong style="color:var(--success);"><?php echo number_format($pkg['price'], 0, ',', '.'); ?> đ</strong></td>
                                    <td><strong><?php echo $pkg['subscriber_count']; ?></strong> hội viên</td>
                                    <td><small style="color:var(--text-muted);"><?php echo htmlspecialchars($pkg['description']); ?></small></td>
                                    <td>
                                        <a href="/packages.php?delete=<?php echo $pkg['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">
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

            <!-- FORM TẠO GÓI MỚI -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-plus"></i> Tạo Gói Tập Mới</h3>
                </div>
                <div class="card-body">
                    <form action="/packages.php" method="POST">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="form-group">
                            <label>Tên Gói Tập</label>
                            <input type="text" name="package_name" class="form-control" placeholder="Ví dụ: Gói Siêu Cấp 3 Tháng" required>
                        </div>
                        <div class="form-group">
                            <label>Thời Hạn (Số Tháng)</label>
                            <input type="number" name="duration_months" min="1" max="60" class="form-control" value="1" required>
                        </div>
                        <div class="form-group">
                            <label>Giá Tiền (VNĐ)</label>
                            <input type="number" name="price" step="10000" class="form-control" placeholder="500000" required>
                        </div>
                        <div class="form-group">
                            <label>Mô Tả & Quyền Lợi</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Chi tiết dịch vụ đi kèm..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fa-solid fa-plus"></i> Thêm Gói Tập
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
