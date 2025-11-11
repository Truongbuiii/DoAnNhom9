<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Lấy thông tin người dùng từ session
$role = $_SESSION['PhanQuyen'] ?? '';
$MaNV = $_SESSION['MaNV'] ?? 0;
$HoTen = $_SESSION['HoTen'] ?? '';
$username = $_SESSION['username'] ?? '';
?>

<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">
            <?= ($role === 'Admin') ? 'Tôi là Admin' : 'Tôi là Nhân viên'; ?>
        </div>
    </a>

    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Bán hàng -->
    <li class="nav-item active">
        <a class="nav-link" href="/index.php">
            <i class="fas fa-cash-register fa-sm text-white-50"></i>
            <span>Bán hàng</span>
        </a>
    </li>

    <?php if ($role === 'Admin'): ?>
        <hr class="sidebar-divider">

        <div class="sidebar-heading">Tùy chọn</div>

        <li class="nav-item">
            <a class="nav-link" href="/pages/QuanLyNhanVien.php">
                <i class="fas fa-fw fa-users"></i>
                <span>Quản lý nhân viên</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBanh" aria-expanded="false" aria-controls="collapseBanh">
                <i class="fas fa-fw fa-bread-slice"></i>
                <span>Quản lý bánh</span>
            </a>
            <div id="collapseBanh" class="collapse" aria-labelledby="headingBanh" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="/pages/QuanLyLoaiBanh.php">Quản lý loại bánh</a>
                    <a class="collapse-item" href="/pages/QuanLyThongTinBanh.php">Quản lý thông tin bánh</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="/pages/QuanLyKhachHang.php">
                <i class="fas fa-fw fa-user"></i>
                <span>Quản lý khách hàng</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="/pages/QuanLyDonHang.php">
                <i class="fas fa-fw fa-box"></i>
                <span>Quản lý đơn hàng</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseThongKe" aria-expanded="false" aria-controls="collapseThongKe">
                <i class="fas fa-fw fa-chart-bar"></i>
                <span>Thống kê & Báo cáo</span>
            </a>
            <div id="collapseThongKe" class="collapse" aria-labelledby="headingThongKe" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="/pages/ThongKeDoanhThu.php">Thống kê doanh thu</a>
                    <a class="collapse-item" href="/pages/Thongkesanpham.php">Thống kê sản phẩm</a>
                </div>
            </div>
        </li>
    <?php endif; ?>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                            <?= ($role === 'Admin') ? 'Admin' : 'Nhân viên'; ?>
                        </span>
                        <img class="img-profile rounded-circle" src="../img/undraw_profile.svg">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                        aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="/pages/logout.php">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Đăng xuất
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
