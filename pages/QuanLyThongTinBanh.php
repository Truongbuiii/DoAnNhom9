<?php include 'include/header.php'; ?>
<?php include 'include/sidebar.php'; ?>
<?php include 'include/config.php'; ?>

<div class="container mt-4">
    <h2 class="text-center mb-4">QUẢN LÝ THÔNG TIN BÁNH</h2>

    <!-- Nút thêm bánh -->
    <div class="mb-3 text-end">
        <a href="themBanh.php" class="btn btn-success">+ Thêm bánh mới</a>
    </div>

    <!-- Bảng danh sách bánh -->
    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-primary">
            <tr>
                <th>Mã bánh</th>
                <th>Tên bánh</th>
                <th>Loại bánh</th>
                <th>Giá (VNĐ)</th>
                <th>Số lượng</th>
                <th>Hình ảnh</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT tb.MaBanh, tb.TenBanh, lb.TenLoaiBanh, tb.Gia, tb.SoLuong, tb.HinhAnh 
                    FROM ThongTinBanh tb
                    JOIN LoaiBanh lb ON tb.MaLoaiBanh = lb.MaLoaiBanh";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['MaBanh']}</td>
                            <td>{$row['TenBanh']}</td>
                            <td>{$row['TenLoaiBanh']}</td>
                            <td>" . number_format($row['Gia'], 0, ',', '.') . "</td>
                            <td>{$row['SoLuong']}</td>
                            <td><img src='{$row['HinhAnh']}' width='60' height='60' style='object-fit:cover; border-radius:8px;'></td>
                            <td>
                                <a href='suaBanh.php?id={$row['MaBanh']}' class='btn btn-warning btn-sm'>Sửa</a>
                                <a href='xoaBanh.php?id={$row['MaBanh']}' class='btn btn-danger btn-sm'
                                   onclick=\"return confirm('Bạn có chắc chắn muốn xóa bánh này không?');\">Xóa</a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='7'>Chưa có bánh nào trong hệ thống</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'include/footer.php'; ?>
