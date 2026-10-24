<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">
            <i class="fa-solid fa-dumbbell"></i>
        </div>
        <div class="brand-text">
            <h1>GYM MASTER</h1>
            <p>Fitness Management</p>
        </div>
    </div>

    <ul class="nav-menu">
        <li class="nav-section-title">QUẢN LÝ CHÍNH</li>
        
        <li class="nav-item">
            <a href="/index.php" class="nav-link <?php echo ($active_menu === 'dashboard') ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Bảng điều khiển</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/members.php" class="nav-link <?php echo ($active_menu === 'members') ? 'active' : ''; ?>">
                <i class="fa-solid fa-users"></i>
                <span>Hội viên & Thẻ tập</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/packages.php" class="nav-link <?php echo ($active_menu === 'packages') ? 'active' : ''; ?>">
                <i class="fa-solid fa-tags"></i>
                <span>Gói tập & Dịch vụ</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/trainers.php" class="nav-link <?php echo ($active_menu === 'trainers') ? 'active' : ''; ?>">
                <i class="fa-solid fa-user-ninja"></i>
                <span>Huấn luyện viên (PT)</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/schedules.php" class="nav-link <?php echo ($active_menu === 'schedules') ? 'active' : ''; ?>">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Lịch tập & Lớp học</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/checkin.php" class="nav-link <?php echo ($active_menu === 'checkin') ? 'active' : ''; ?>">
                <i class="fa-solid fa-qrcode"></i>
                <span>Điểm danh Check-in</span>
            </a>
        </li>

        <li class="nav-section-title">QUẢN TRỊ & BẢO MẬT</li>
        
        <li class="nav-item">
            <a href="/security-demo.php" class="nav-link <?php echo ($active_menu === 'security') ? 'active' : ''; ?>">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Hardening & Security</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="http://localhost:8080" target="_blank" class="nav-link">
                <i class="fa-solid fa-database"></i>
                <span>phpMyAdmin (DB)</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="http://localhost:3000" target="_blank" class="nav-link">
                <i class="fa-solid fa-chart-line"></i>
                <span>Grafana Monitor</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="http://localhost:9090" target="_blank" class="nav-link">
                <i class="fa-solid fa-fire-flame-curved"></i>
                <span>Prometheus</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="status-badge">
            <span class="status-dot"></span>
            <span>Hệ thống: Docker Active</span>
        </div>
    </div>
</aside>
