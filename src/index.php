<?php
require_once __DIR__ . '/config/database.php';

$page_title = "Bảng điều khiển - Gym Master";
$active_menu = "dashboard";

// Truy vấn các chỉ số thống kê
$total_members = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
$active_members = $pdo->query("SELECT COUNT(*) FROM members WHERE status = 'active'")->fetchColumn();
$total_packages = $pdo->query("SELECT COUNT(*) FROM packages WHERE status = 'active'")->fetchColumn();
$total_trainers = $pdo->query("SELECT COUNT(*) FROM trainers WHERE status = 'active'")->fetchColumn();
$today_checkins = $pdo->query("SELECT COUNT(*) FROM checkins WHERE DATE(checkin_time) = CURDATE()")->fetchColumn();

// Thống kê doanh thu ước tính từ các hội viên đang hoạt động
$revenue_query = $pdo->query("
    SELECT SUM(p.price) as total_rev 
    FROM members m 
    JOIN packages p ON m.package_id = p.id 
    WHERE m.status = 'active'
");
$total_revenue = $revenue_query->fetchColumn() ?: 0;

// Lấy 5 hội viên mới nhất
$recent_members = $pdo->query("
    SELECT m.*, p.package_name, t.full_name AS trainer_name
    FROM members m
    LEFT JOIN packages p ON m.package_id = p.id
    LEFT JOIN trainers t ON m.trainer_id = t.id
    ORDER BY m.id DESC LIMIT 5
")->fetchAll();

// Lấy 5 lượt check-in gần nhất
$recent_checkins = $pdo->query("
    SELECT c.*, m.member_code, m.full_name, p.package_name
    FROM checkins c
    JOIN members m ON c.member_id = m.id
    LEFT JOIN packages p ON m.package_id = p.id
    ORDER BY c.checkin_time DESC LIMIT 5
")->fetchAll();

// Thống kê gói tập cho biểu đồ tròn
$pkg_stats = $pdo->query("
    SELECT p.package_name, COUNT(m.id) as count
    FROM packages p
    LEFT JOIN members m ON p.id = m.package_id
    GROUP BY p.id
")->fetchAll();

$pkg_labels = [];
$pkg_data = [];
foreach ($pkg_stats as $stat) {
    $pkg_labels[] = $stat['package_name'];
    $pkg_data[] = (int)$stat['count'];
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="main-wrapper">
    <header class="topbar">
        <div class="page-title">
            <h2>Tổng quan Hoạt động Phòng Gym</h2>
            <p>Hệ thống giám sát và quản lý thẻ tập hội viên Gym & Fitness</p>
        </div>
        <div class="topbar-actions">
            <span class="badge-tag"><i class="fa-solid fa-server"></i> Server: Online</span>
            <span class="badge-tag"><i class="fa-solid fa-lock"></i> SSL Secured</span>
        </div>
    </header>

    <main class="content-container">
        <!-- STATS CARDS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Tổng Hội viên</h3>
                    <div class="stat-value"><?php echo number_format($total_members); ?></div>
                    <div class="stat-desc">
                        <span style="color:var(--success)"><i class="fa-solid fa-circle-check"></i> <?php echo $active_members; ?> đang hoạt động</span>
                    </div>
                </div>
                <div class="stat-icon icon-red">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <h3>Doanh thu kích hoạt</h3>
                    <div class="stat-value"><?php echo number_format($total_revenue, 0, ',', '.'); ?> đ</div>
                    <div class="stat-desc">
                        <span style="color:var(--info)"><i class="fa-solid fa-arrow-trend-up"></i> Từ các gói đã đăng ký</span>
                    </div>
                </div>
                <div class="stat-icon icon-green">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <h3>Điểm danh hôm nay</h3>
                    <div class="stat-value"><?php echo number_format($today_checkins); ?></div>
                    <div class="stat-desc">
                        <span style="color:var(--warning)"><i class="fa-solid fa-fingerprint"></i> Lượt vào phòng gym</span>
                    </div>
                </div>
                <div class="stat-icon icon-blue">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <h3>Huấn luyện viên PT</h3>
                    <div class="stat-value"><?php echo number_format($total_trainers); ?></div>
                    <div class="stat-desc">
                        <span style="color:var(--text-secondary)"><i class="fa-solid fa-medal"></i> Đạt chuẩn chứng chỉ</span>
                    </div>
                </div>
                <div class="stat-icon icon-amber">
                    <i class="fa-solid fa-user-ninja"></i>
                </div>
            </div>
        </div>

        <!-- CHARTS & SUMMARY SECTION -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 30px;">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-chart-line"></i> Lưu lượng Hội viên theo Ngày (Prometheus Simulated Metrics)</h3>
                </div>
                <div class="card-body">
                    <canvas id="checkinTrendChart" height="130"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-chart-pie"></i> Phân bổ Gói tập</h3>
                </div>
                <div class="card-body" style="display:flex; justify-content:center; align-items:center;">
                    <div style="width: 250px; height: 250px;">
                        <canvas id="packageDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- RECENT TABLES -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <!-- Danh sách hội viên mới -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-user-plus"></i> Hội viên mới nhất</h3>
                    <a href="/members.php" class="btn btn-secondary btn-sm">Xem tất cả</a>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Mã SV / Mã HV</th>
                                    <th>Họ và Tên</th>
                                    <th>Gói tập</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_members as $m): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($m['member_code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($m['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($m['package_name'] ?? 'Chưa đăng ký'); ?></td>
                                    <td>
                                        <span class="pill pill-<?php echo ($m['status'] === 'active') ? 'success' : 'danger'; ?>">
                                            <?php echo ($m['status'] === 'active') ? 'Đang tập' : 'Hết hạn'; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Lịch sử check-in -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-door-open"></i> Lượt điểm danh gần đây</h3>
                    <a href="/checkin.php" class="btn btn-secondary btn-sm">Vào check-in</a>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Hội viên</th>
                                    <th>Gói thẻ</th>
                                    <th>Thời gian</th>
                                    <th>Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_checkins as $c): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($c['full_name']); ?></strong>
                                        <div style="font-size:11px;color:var(--text-muted);"><?php echo htmlspecialchars($c['member_code']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($c['package_name'] ?? 'Thường'); ?></td>
                                    <td><i class="fa-regular fa-clock"></i> <?php echo date('H:i - d/m', strtotime($c['checkin_time'])); ?></td>
                                    <td><small><?php echo htmlspecialchars($c['notes'] ?? 'Vào tập'); ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Chart 1: Checkin Trend Chart
        const ctxTrend = document.getElementById('checkinTrendChart').getContext('2d');
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ Nhật'],
                datasets: [{
                    label: 'Lượt tập (Check-ins)',
                    data: [42, 58, 65, 50, 72, 85, 60],
                    borderColor: '#ff4757',
                    backgroundColor: 'rgba(255, 71, 87, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#ff4757',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { labels: { color: '#94a3b8' } }
                },
                scales: {
                    x: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255, 255, 255, 0.05)' } },
                    y: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255, 255, 255, 0.05)' } }
                }
            }
        });

        // Chart 2: Package Distribution
        const ctxPkg = document.getElementById('packageDistributionChart').getContext('2d');
        new Chart(ctxPkg, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($pkg_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($pkg_data); ?>,
                    backgroundColor: ['#ff4757', '#10b981', '#0ea5e9', '#f59e0b'],
                    borderColor: '#111827',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#94a3b8', boxWidth: 12, padding: 14 }
                    }
                }
            }
        });
    </script>

<?php include __DIR__ . '/includes/footer.php'; ?>
