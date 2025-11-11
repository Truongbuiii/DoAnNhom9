<?php
include '../include/header.php'; 
include '../include/sidebar.php'; 

// Lấy dữ liệu từ form
$loai = $_GET['loai'] ?? '';  
$ngay = $_GET['ngay'] ?? '';
$tu_ngay = $_GET['tu_ngay'] ?? '';
$den_ngay = $_GET['den_ngay'] ?? '';
$thang = $_GET['thang'] ?? '';
$nam = $_GET['nam'] ?? '';

// Khởi tạo WHERE cho từng loại thống kê
$where_ngay = $ngay ? "WHERE DATE(NgayLap) = '$ngay'" : "";
$where_khoang = ($tu_ngay && $den_ngay) ? "WHERE DATE(NgayLap) BETWEEN '$tu_ngay' AND '$den_ngay'" : "";
$where_thang = ($thang && $nam) ? "WHERE MONTH(NgayLap) = '$thang' AND YEAR(NgayLap) = '$nam'" : "";
$where_nam = $nam ? "WHERE YEAR(NgayLap) = '$nam'" : "";

// Tạo câu truy vấn tương ứng
if ($loai == 'ngay' && $ngay) {
    $sql = "SELECT DATE(NgayLap) AS Ngay, SUM(TongTien) AS DoanhThu 
            FROM DonHang $where_ngay 
            GROUP BY DATE(NgayLap)";
} elseif ($loai == 'khoang' && $tu_ngay && $den_ngay) {
    $sql = "SELECT DATE(NgayLap) AS Ngay, SUM(TongTien) AS DoanhThu 
            FROM DonHang $where_khoang 
            GROUP BY DATE(NgayLap)";
} elseif ($loai == 'thang' && $thang && $nam) {
    $sql = "SELECT YEAR(NgayLap) AS Nam, MONTH(NgayLap) AS Thang, SUM(TongTien) AS DoanhThu 
            FROM DonHang $where_thang 
            GROUP BY Nam, Thang";
} elseif ($loai == 'nam' && $nam) {
    $sql = "SELECT YEAR(NgayLap) AS Nam, SUM(TongTien) AS DoanhThu 
            FROM DonHang $where_nam 
            GROUP BY Nam";
} else {
    $sql = "";
}

$result = $sql ? mysqli_query($conn, $sql) : null;
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Thống kê doanh thu</h1>

    <!-- Form chọn loại thống kê -->
    <form method="get" class="form-inline mb-4">
        <label class="mr-2">Loại thống kê:</label>
        <select name="loai" id="loai" class="form-control mr-3" onchange="showFields()">
            <option value="">Chọn loại</option>
            <option value="ngay" <?= $loai=='ngay'?'selected':'' ?>>Theo ngày</option>
            <option value="khoang" <?= $loai=='khoang'?'selected':'' ?>>Theo khoảng thời gian</option>
            <option value="thang" <?= $loai=='thang'?'selected':'' ?>>Theo tháng</option>
            <option value="nam" <?= $loai=='nam'?'selected':'' ?>>Theo năm</option>
        </select>

        <!-- Trường nhập cho từng loại -->
        <input type="date" name="ngay" id="ngay" value="<?= htmlspecialchars($ngay) ?>" class="form-control mr-3" style="display:none;">

        <label id="lbl_tu" style="display:none;">Từ:</label>
        <input type="date" name="tu_ngay" id="tu_ngay" value="<?= htmlspecialchars($tu_ngay) ?>" class="form-control mr-2" style="display:none;">
        <label id="lbl_den" style="display:none;">Đến:</label>
        <input type="date" name="den_ngay" id="den_ngay" value="<?= htmlspecialchars($den_ngay) ?>" class="form-control mr-3" style="display:none;">

        <select name="thang" id="thang" class="form-control mr-2" style="display:none;">
            <option value="">Chọn tháng</option>
            <?php for ($m=1; $m<=12; $m++): ?>
                <option value="<?= $m ?>" <?= $thang==$m?'selected':'' ?>><?= $m ?></option>
            <?php endfor; ?>
        </select>

        <input type="year" name="nam" id="nam" value="<?= htmlspecialchars($nam) ?>" class="form-control mr-3" placeholder="YYYY" style="display:none;">

        <button type="submit" class="btn btn-primary">Thống kê</button>
    </form>

    <!-- Kết quả -->
    <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <?php if ($loai=='ngay' || $loai=='khoang'): ?>
                            <th>Ngày</th>
                            <th>Doanh thu (VNĐ)</th>
                        <?php elseif ($loai=='thang'): ?>
                            <th>Năm</th>
                            <th>Tháng</th>
                            <th>Doanh thu (VNĐ)</th>
                        <?php elseif ($loai=='nam'): ?>
                            <th>Năm</th>
                            <th>Doanh thu (VNĐ)</th>
                        <?php endif; ?>
                        <th>Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <?php if ($loai=='ngay' || $loai=='khoang'): ?>
                                <td><?= $row['Ngay'] ?></td>
                                <td><?= number_format($row['DoanhThu']) ?></td>
                                <td>
                                    <a href="chitietdoanhthu.php?loai=ngay&ngay=<?= $row['Ngay'] ?>" 
                                       class="btn btn-info btn-sm">Xem</a>
                                </td>
                            <?php elseif ($loai=='thang'): ?>
                                <td><?= $row['Nam'] ?></td>
                                <td><?= $row['Thang'] ?></td>
                                <td><?= number_format($row['DoanhThu']) ?></td>
                                <td>
                                    <a href="chitietdoanhthu.php?loai=thang&thang=<?= $row['Thang'] ?>&nam=<?= $row['Nam'] ?>" 
                                       class="btn btn-info btn-sm">Xem</a>
                                </td>
                            <?php elseif ($loai=='nam'): ?>
                                <td><?= $row['Nam'] ?></td>
                                <td><?= number_format($row['DoanhThu']) ?></td>
                                <td>
                                    <a href="chitietdoanhthu.php?loai=nam&nam=<?= $row['Nam'] ?>" 
                                       class="btn btn-info btn-sm">Xem</a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php elseif ($sql): ?>
        <p class="text-center">Không có dữ liệu</p>
    <?php endif; ?>
</div>

<script>
function showFields() {
    const loai = document.getElementById('loai').value;
    document.getElementById('ngay').style.display = loai=='ngay' ? 'inline-block' : 'none';
    document.getElementById('tu_ngay').style.display = loai=='khoang' ? 'inline-block' : 'none';
    document.getElementById('den_ngay').style.display = loai=='khoang' ? 'inline-block' : 'none';
    document.getElementById('lbl_tu').style.display = loai=='khoang' ? 'inline-block' : 'none';
    document.getElementById('lbl_den').style.display = loai=='khoang' ? 'inline-block' : 'none';
    document.getElementById('thang').style.display = loai=='thang' ? 'inline-block' : 'none';
    document.getElementById('nam').style.display = (loai=='thang' || loai=='nam') ? 'inline-block' : 'none';
}
showFields();
</script>

<?php include '../include/footer.php'; 
?>
