<?php
include '../db/connect.php';
include '../include1/header.php';
include '../include/sidebar.php'; 

// Lấy dữ liệu filter từ form
$ngay = $_GET['ngay'] ?? '';
$thang = $_GET['thang'] ?? '';
$nam = $_GET['nam'] ?? '';

// --- Truy vấn doanh thu theo ngày ---
$sql_ngay = "SELECT DATE(NgayLap) AS Ngay, SUM(TongTien) AS DoanhThu FROM DonHang";
if ($ngay) $sql_ngay .= " WHERE DATE(NgayLap) = '$ngay'";
$sql_ngay .= " GROUP BY DATE(NgayLap) ORDER BY Ngay DESC";
$result_ngay = mysqli_query($conn, $sql_ngay);

// --- Truy vấn doanh thu theo tuần ---
$sql_tuan = "SELECT YEAR(NgayLap) AS Nam, WEEK(NgayLap,1) AS Tuan, SUM(TongTien) AS DoanhThu FROM DonHang WHERE 1";
if ($nam) $sql_tuan .= " AND YEAR(NgayLap) = '$nam'";
$sql_tuan .= " GROUP BY Nam, Tuan ORDER BY Nam DESC, Tuan DESC";
$result_tuan = mysqli_query($conn, $sql_tuan);

// --- Truy vấn doanh thu theo tháng ---
$sql_thang = "SELECT YEAR(NgayLap) AS Nam, MONTH(NgayLap) AS Thang, SUM(TongTien) AS DoanhThu FROM DonHang WHERE 1";
if ($thang) $sql_thang .= " AND MONTH(NgayLap) = '$thang'";
if ($nam) $sql_thang .= " AND YEAR(NgayLap) = '$nam'";
$sql_thang .= " GROUP BY Nam, Thang ORDER BY Nam DESC, Thang DESC";
$result_thang = mysqli_query($conn, $sql_thang);
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Thống kê doanh thu</h1>

    <!-- Filter form -->
    <form method="get" class="form-inline mb-4">
        <label class="mr-2">Ngày:</label>
        <input type="date" name="ngay" value="<?= htmlspecialchars($ngay) ?>" class="form-control mr-3">

        <label class="mr-2">Tháng:</label>
        <select name="thang" class="form-control mr-3">
            <option value="">Chọn tháng</option>
            <?php for($m=1;$m<=12;$m++): ?>
            <option value="<?= $m ?>" <?= $thang==$m?'selected':'' ?>><?= $m ?></option>
            <?php endfor; ?>
        </select>

        <label class="mr-2">Năm:</label>
        <input type="number" name="nam" value="<?= htmlspecialchars($nam) ?>" class="form-control mr-3" placeholder="YYYY">

        <button type="submit" class="btn btn-primary">Thống kê</button>
    </form>

    <!-- Tabs -->
    <ul class="nav nav-tabs" id="thongKeTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="ngay-tab" data-toggle="tab" href="#ngay" role="tab">Theo ngày</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tuan-tab" data-toggle="tab" href="#tuan" role="tab">Theo tuần</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="thang-tab" data-toggle="tab" href="#thang" role="tab">Theo tháng</a>
        </li>
    </ul>

    <div class="tab-content mt-3">
        <!-- Ngày -->
        <div class="tab-pane fade show active" id="ngay" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead><tr><th>Ngày</th><th>Doanh thu (VNĐ)</th></tr></thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result_ngay)): ?>
                        <tr>
                            <td><?= $row['Ngay'] ?></td>
                            <td><?= number_format($row['DoanhThu']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tuần -->
        <div class="tab-pane fade" id="tuan" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead><tr><th>Năm</th><th>Tuần</th><th>Doanh thu (VNĐ)</th></tr></thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result_tuan)): ?>
                        <tr>
                            <td><?= $row['Nam'] ?></td>
                            <td><?= $row['Tuan'] ?></td>
                            <td><?= number_format($row['DoanhThu']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tháng -->
        <div class="tab-pane fade" id="thang" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead><tr><th>Năm</th><th>Tháng</th><th>Doanh thu (VNĐ)</th></tr></thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result_thang)): ?>
                        <tr>
                            <td><?= $row['Nam'] ?></td>
                            <td><?= $row['Thang'] ?></td>
                            <td><?= number_format($row['DoanhThu']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../include1/footer.php'; ?>
