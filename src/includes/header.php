<?php
if (!isset($page_title)) {
    $page_title = "Hệ thống Quản lý Phòng Gym & Fitness";
}
if (!isset($active_menu)) {
    $active_menu = "dashboard";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - GymMaster Pro</title>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Chart.js for beautiful charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
