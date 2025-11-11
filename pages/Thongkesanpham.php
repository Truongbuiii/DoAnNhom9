<?php
include '../include/header.php'; 
include '../include/sidebar.php'; 

// Lấy lựa chọn từ form
$loai = $_GET['loai'] ?? '';
?>

<div class="container mt-5">
    <h3 class="mb-4">Thống kê sản phẩm</h3>

    <!-- Form chọn loại -->
    <form method="get" class="form-inline mb-4">
        <label class="mr-2">Chọn loại thống kê:</label>
        <select name="loai" class="form-control mr-2">
            <option value="">-- Chọn --</option>
            <option value="banchay" <?= $loai=='banchay'?'selected':'' ?>>Sản phẩm bán chạy</option>
            <option value="bancham" <?= $loai=='bancham'?'selected':'' ?>>Sản phẩm bán chậm</option>
        </select>
        <button type="submit" class="btn btn-primary">Thống kê</button>
    </form>

<?php
if ($loai) {
    // SQL tùy theo loại
    if ($loai == 'banchay') {
        $sql = "
        SELECT b.TenBanh, IFNULL(SUM(c.SoLuong),0) AS TongBanRa
        FROM ThongTinBanh b
        LEFT JOIN ChiTietDonHang c ON b.MaBanh = c.MaBanh
        GROUP BY b.MaBanh, b.TenBanh
        ORDER BY TongBanRa DESC
        ";
    } else { // bancham
        $sql = "
        SELECT b.TenBanh, IFNULL(SUM(c.SoLuong),0) AS TongBanRa
        FROM ThongTinBanh b
        LEFT JOIN ChiTietDonHang c ON b.MaBanh = c.MaBanh
        GROUP BY b.MaBanh, b.TenBanh
        ORDER BY TongBanRa ASC
        ";
    }

    $result = mysqli_query($conn, $sql);
    ?>

    <!-- Bảng kết quả với scroll -->
    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>STT</th>
                    <th>Tên bánh</th>
                    <th>Tổng số bán ra</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if($result && mysqli_num_rows($result) > 0):
                    $stt = 1;
                    while($row = mysqli_fetch_assoc($result)): 
                ?>
                        <tr>
                            <td><?= $stt++ ?></td>
                            <td><?= htmlspecialchars($row['TenBanh']) ?></td>
                            <td><?= $row['TongBanRa'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3">Không có dữ liệu</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php } ?>

</div>

<?php include '../include/footer.php'; 
?>