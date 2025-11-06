<?php
// Bắt đầu session nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Lấy quyền người dùng (nếu có)
$role = isset($_SESSION['PhanQuyen']) ? $_SESSION['PhanQuyen'] : '';
?>

<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">
            <?php echo ($role == 'Admin') ? 'Tôi là Admin' : 'Tôi là Nhân viên'; ?>
        </div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Bán hàng -->
    <li class="nav-item active">
        <a class="nav-link" href="/index.php">
            <i class="fas fa-cash-register fa-sm text-white-50"></i>
            <span>Bán hàng</span>
        </a>
    </li>

    <?php if ($role == 'Admin'): ?>
        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Heading -->
        <div class="sidebar-heading">
            Tùy chọn
        </div>

        <!-- Quản lý nhân viên -->
        <li class="nav-item">
            <a class="nav-link" href="/pages/QuanLyNhanVien.php">
                <i class="fas fa-fw fa-users"></i>
                <span>Quản lý nhân viên</span>
            </a>
        </li>

        <!-- Quản lý bánh -->
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

        <!-- Quản lý khách hàng -->
        <li class="nav-item">
            <a class="nav-link" href="/pages/QuanLyKhachHang.php">
                <i class="fas fa-fw fa-user"></i>
                <span>Quản lý khách hàng</span>
            </a>
        </li>

        <!-- Quản lý đơn hàng -->
        <li class="nav-item">
            <a class="nav-link" href="/pages/QuanLyDonHang.php">
                <i class="fas fa-fw fa-box"></i>
                <span>Quản lý đơn hàng</span>
            </a>
        </li>

        <!-- Thống kê & Báo cáo -->
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

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>
<!-- End of Sidebar -->
