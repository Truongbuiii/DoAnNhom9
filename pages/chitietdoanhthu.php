<?php
include '../db/connect.php';
include '../include/header.php'; 
include '../include/sidebar.php'; 

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
    echo "<p style='color:red;'>⚠️ Không xác định được điều kiện lọc dữ liệu!</p>";
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
?>

<div class="container-fluid">
    <h2 class="h3 mb-4 text-gray-800">Chi tiết doanh thu <?= ($loai=='ngay'?'ngày '.$ngay:($loai=='thang'?'tháng '.$thang.'/'.$nam:($loai=='nam'?'năm '.$nam:''))) ?></h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Ngày lập</th>
                        <th>Khách hàng</th>
                        <th>Nhân viên</th>
                        <th>Tổng tiền (VNĐ)</th>
                        <th>Chi tiết đơn hàng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['MaDon'] ?></td>
                        <td><?= $row['NgayLap'] ?></td>
                        <td><?= $row['TenKH'] ?></td>
                        <td><?= $row['TenNV'] ?></td>
                        <td><?= number_format($row['TongTien'], 0, ',', '.') ?></td>
                        <td>
                            <a href="chitietdonhang.php?MaDon=<?= $row['MaDon'] ?>" class="btn btn-sm btn-primary">
                                Xem
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>Không có đơn hàng nào trong khoảng thời gian này.</p>
    <?php endif; ?>
</div>



<!-- Nút quay lại căn giữa -->
<div style="text-align: center; margin-top: 20px;">
    <button class="btn btn-secondary" onclick="history.back()">Quay lại</button>
</div>


<?php include '../include/footer.php'; 
 ?>
