<?php include '../include1/header.php'; ?>
<?php include '../include1/sidebar.php'; ?>

        <!-- Begin Page Content -->
        <div class="container-fluid">
            <h2 class="text-center mb-4 text-primary">Quản lý khách hàng</h2>

            <!-- Nút thêm khách hàng -->
            <div class="mb-3 text-end">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalThemKhachHang">
                    Thêm khách hàng
                </button>
            </div>

            <!-- 💬 Modal Thêm khách hàng -->
            <div class="modal fade" id="modalThemKhachHang" tabindex="-1" aria-labelledby="modalThemKhachHangLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="modalThemKhachHangLabel">Thêm khách hàng</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
                        </div>

                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="hoten" class="form-label">Họ và tên</label>
                                    <input type="text" class="form-control" id="hoten" name="hoten" placeholder="Nhập họ tên..." required>
                                </div>

                                <div class="mb-3">
                                    <label for="sdt" class="form-label">Số điện thoại</label>
                                    <input type="text" class="form-control" id="sdt" name="sdt" placeholder="Nhập số điện thoại..." required>
                                </div>
 
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                <button type="submit" name="themKhachHang" class="btn btn-success">Lưu</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php
            // Xử lý thêm khách hàng
            if (isset($_POST['themKhachHang'])) {
                $hoten = trim($_POST['hoten']);
                $sdt = trim($_POST['sdt']);

                $sql = "INSERT INTO KhachHang (HoTen, SDT)
                        VALUES ('$hoten', '$sdt')";
              if ($conn->query($sql) === TRUE) {
                        echo "<script>
                            alert('🎉 Thêm khách hàng mới thành công!');
                            window.location.href = 'QuanLyKhachHang.php';
                        </script>";
                        exit;
                    }
                    else {
                    echo "<div class='alert alert-danger mt-3'>Lỗi: " . $conn->error . "</div>";
                }
            }
            ?>
            <?php
// 🗑️ Xử lý xóa khách hàng
if (isset($_GET['xoa'])) {
    $ma = intval($_GET['xoa']);

    // Kiểm tra xem khách hàng có đơn hàng chưa
    $kiemTra = $conn->query("SELECT * FROM DonHang WHERE MaKH = $ma");

    if ($kiemTra && $kiemTra->num_rows > 0) {
        echo "
        <div class='alert alert-danger mt-3'>
            ❌ Không thể xóa khách hàng <b>Mã #$ma</b> vì đã có đơn hàng trong hệ thống.
        </div>";
    } else {
        if ($conn->query("DELETE FROM KhachHang WHERE MaKH = $ma")) {
            echo "
            <div class='alert alert-success mt-3'>
                ✅ Đã xóa khách hàng thành công!
            </div>";
        } else {
            echo "
            <div class='alert alert-danger mt-3'>
                ⚠️ Lỗi khi xóa khách hàng: " . htmlspecialchars($conn->error) . "
            </div>";
        }
    }
}

// ✏️ Xử lý sửa khách hàng
if (isset($_POST['luu_sua'])) {
    $ma = intval($_POST['sua_ma']);
    $ten = trim($_POST['sua_ten']);
    $sdt = trim($_POST['sua_sdt']);

    $sql = "UPDATE KhachHang SET HoTen='$ten', SDT='$sdt' WHERE MaKH=$ma";
    if ($conn->query($sql) === TRUE) {
        echo "<script>
            alert('✅ Cập nhật khách hàng thành công!');
            window.location.href = 'QuanLyKhachHang.php';
        </script>";
        exit;
    } else {
        echo "<div class='alert alert-danger mt-3'>⚠️ Lỗi khi cập nhật: " . $conn->error . "</div>";
    }
}


?>
          <!-- 📋 Danh sách khách hàng -->
<div class="card shadow-sm p-4 mb-4">
    <h5 class="text-primary mb-3">Danh sách khách hàng</h5>
    <table class="table table-bordered text-center align-middle">
        <thead class="table-primary">
            <tr>
                <th>Mã KH</th>
                <th>Họ và tên</th>
                <th>Số điện thoại</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM KhachHang ORDER BY MaKH ASC";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $ma = htmlspecialchars($row['MaKH']);
                    $ten = htmlspecialchars($row['HoTen']);
                    $sdt = htmlspecialchars($row['SDT']);
                                echo "
                                <tr>
                                    <td>$ma</td>
                                    <td>$ten</td>
                                    <td>$sdt</td>
                                    <td>
                                        <button class='btn btn-warning btn-sm btn-edit' 
                                                data-id='$ma' 
                                                data-ten='$ten' 
                                                data-sdt='$sdt'>Sửa</button>
                                        <a href='?xoa=$ma' 
                                        class='btn btn-danger btn-sm btn-delete'
                                        data-ten='$ten'
                                        data-id='$ma'>
                                        Xóa
                                        </a>
                                    </td>
                                </tr>";

                }
            } else {
                echo '<tr><td colspan="5">Chưa có khách hàng nào.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

<!-- 🔧 Modal Sửa khách hàng -->
<div class="modal fade" id="modalSuaKhachHang" tabindex="-1" aria-labelledby="modalSuaKhachHangLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="modalSuaKhachHangLabel">Sửa thông tin khách hàng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" id="sua_ma" name="sua_ma">

                    <div class="mb-3">
                        <label for="sua_ten" class="form-label">Họ và tên</label>
                        <input type="text" class="form-control" id="sua_ten" name="sua_ten" required>
                    </div>

                    <div class="mb-3">
                        <label for="sua_sdt" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" id="sua_sdt" name="sua_sdt" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="luu_sua" class="btn btn-success">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Khi bấm nút “Sửa”, mở modal và điền dữ liệu
document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', () => {
        const ma = btn.dataset.id;
        const ten = btn.dataset.ten;
        const sdt = btn.dataset.sdt;

        document.getElementById('sua_ma').value = ma;
        document.getElementById('sua_ten').value = ten;
        document.getElementById('sua_sdt').value = sdt;

        const modal = new bootstrap.Modal(document.getElementById('modalSuaKhachHang'));
        modal.show();
    });
});
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', (e) => {
        const ten = btn.dataset.ten;
        const ma = btn.dataset.id;
        if (!confirm(`⚠️ Bạn có chắc chắn muốn xóa khách hàng "${ten}" (Mã #${ma}) không?\nNếu khách hàng đã có đơn hàng, hệ thống sẽ không cho phép xóa!`)) {
            e.preventDefault(); // hủy link
        }
    });
});
</script>

<?php include '../include1/footer.php'; ?>