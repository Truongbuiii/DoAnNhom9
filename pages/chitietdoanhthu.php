<?php
include '../db/connect.php';
include '../include/header.php'; 
include '../include/sidebar.php'; 

// ============================
// (LOGIC PHP CỦA BẠN - GIỮ NGUYÊN)
// ============================
$loai = $_GET['loai'] ?? '';
$ngay = $_GET['ngay'] ?? '';
$thang = $_GET['thang'] ?? '';
$nam = $_GET['nam'] ?? '';
$tu_ngay = $_GET['tu_ngay'] ?? '';
$den_ngay = $_GET['den_ngay'] ?? '';

$where = '';

if ($loai == 'ngay' && $ngay) {
    $where = "DATE(d.NgayLap) = '$ngay'";
} elseif ($loai == 'khoang' && $tu_ngay && $den_ngay) {
    $where = "DATE(d.NgayLap) BETWEEN '$tu_ngay' AND '$den_ngay'";
} elseif ($loai == 'thang' && $thang && $nam) {
    $where = "MONTH(d.NgayLap) = '$thang' AND YEAR(d.NgayLap) = '$nam'";
} elseif ($loai == 'nam' && $nam) {
    $where = "YEAR(d.NgayLap) = '$nam'";
}

if (!$where) {
    // SỬA: Hiển thị lỗi bên trong theme
    echo "<div class='container-fluid'><div class='alert alert-danger'>⚠️ Không xác định được điều kiện lọc dữ liệu!</div></div>";
    include '../include/footer.php';
    exit;
}

$sql = "
    SELECT d.MaDon, d.NgayLap, d.TongTien, kh.HoTen AS TenKH, nv.HoTen AS TenNV
    FROM DonHang d
    JOIN KhachHang kh ON d.MaKH = kh.MaKH
    JOIN NhanVien nv ON d.MaNV = nv.MaNV
    WHERE $where
    ORDER BY d.NgayLap DESC
";
$result = $conn->query($sql);

// SỬA: Bổ sung logic tiêu đề cho loại 'khoang'
$tieu_de = '';
if($loai == 'ngay') $tieu_de = 'ngày ' . date('d/m/Y', strtotime($ngay));
if($loai == 'thang') $tieu_de = 'tháng ' . $thang . '/' . $nam;
if($loai == 'nam') $tieu_de = 'năm ' . $nam;
if($loai == 'khoang') $tieu_de = 'từ ' . date('d/m/Y', strtotime($tu_ngay)) . ' đến ' . date('d/m/Y', strtotime($den_ngay));
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Chi tiết doanh thu <?= $tieu_de ?></h1>

    <div class="card shadow mb-4">
        
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách đơn hàng</h6>
            <button class="btn btn-secondary btn-sm" onclick="history.back()">
              Quay lại
            </button>
        </div>
        
        <div class="card-body">
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="bg-primary text-white"> <tr>
                                <th>Mã đơn</th>
                                <th>Ngày lập</th>
                                <th>Khách hàng</th>
                                <th>Nhân viên</th>
                                <th class="text-right">Tổng tiền</th>
                                <th class="text-center">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['MaDon'] ?></td>
                                <td><?= date('d/m/Y H:i:s', strtotime($row['NgayLap'])) ?></td>
                                <td><?= htmlspecialchars($row['TenKH']) ?></td>
                                <td><?= htmlspecialchars($row['TenNV']) ?></td>
                                <td class="text-right"><b><?= number_format($row['TongTien'], 0, ',', '.') ?> đ</b></td>
                                <td class="text-center">
                                    <a href="chitietdonhang.php?MaDon=<?= $row['MaDon'] ?>" class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i> Xem
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Không có đơn hàng nào trong khoảng thời gian này.</div>
            <?php endif; ?>
        </div> </div> </div> <?php include '../include/footer.php'; 
?>