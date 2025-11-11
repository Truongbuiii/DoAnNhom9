<?php
// ✅ KIỂM TRA ĐĂNG NHẬP
// Logic này giờ sẽ chạy trên MỌI TRANG
if (!isset($_SESSION['username'])) {
    // Luôn chuyển hướng về trang login bằng đường dẫn TUYỆT ĐỐI
    header("Location: " . BASE_APP_PATH . "/pages/login.php");
    exit;
}

// ✅ Lấy thông tin từ session
$role = $_SESSION['PhanQuyen'] ?? '';
$MaNV = $_SESSION['MaNV'] ?? 0;
?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo BASE_APP_PATH; ?>/index.php">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">
            <?php echo ($role == 'Admin') ? 'Tôi là Admin' : 'Tôi là Nhân viên'; ?>
        </div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item active">
        <a class="nav-link" href="<?php echo BASE_APP_PATH; ?>/index.php">
            <i class="fas fa-cash-register fa-sm text-white-50"></i>
            <span>Bán hàng</span>
        </a>
    </li>

    <?php if ($role == 'Admin'): ?>
        <hr class="sidebar-divider">

        <div class="sidebar-heading">Tùy chọn</div>

        <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_APP_PATH; ?>/pages/QuanLyNhanVien.php">
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
                    <a class="collapse-item" href="<?php echo BASE_APP_PATH; ?>/pages/QuanLyLoaiBanh.php">Quản lý loại bánh</a>
                    <a class="collapse-item" href="<?php echo BASE_APP_PATH; ?>/pages/QuanLyThongTinBanh.php">Quản lý thông tin bánh</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_APP_PATH; ?>/pages/QuanLyKhachHang.php">
                <i class="fas fa-fw fa-user"></i>
                <span>Quản lý khách hàng</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_APP_PATH; ?>/pages/QuanLyDonHang.php">
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
                    <a class="collapse-item" href="<?php echo BASE_APP_PATH; ?>/pages/ThongKeDoanhThu.php">Thống kê doanh thu</a>
                    <a class="collapse-item" href="<?php echo BASE_APP_PATH; ?>/pages/Thongkesanpham.php">Thống kê sản phẩm</a>
                </div>
            </div>
        </li>
    <?php endif; ?>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>

<div id="content-wrapper" class="d-flex flex-column">

    <div id="content">

        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                            <?php echo ($role == 'Admin') ? 'Admin' : 'Nhân viên'; ?>
                        </span>
                        
                        <img class="img-profile rounded-circle" 
                             src="<?php echo BASE_APP_PATH; ?>/img/undraw_profile.svg">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                        aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Đăng xuất
                        </a>
                    </div>
                </li>
            </ul>
        </nav>