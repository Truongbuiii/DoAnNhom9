<?php 
include '../include1/header.php'; 
include '../include1/sidebar.php'; 

// 🗑️ Xử lý xóa hoặc khóa khách hàng
if (isset($_GET['xoa'])) {
    $ma = intval($_GET['xoa']);
    $ten = urldecode($_GET['ten'] ?? '');

    // Kiểm tra khách hàng có đơn hàng chưa
    $kiemTra = $conn->query("SELECT * FROM DonHang WHERE MaKH = $ma");

    if ($kiemTra && $kiemTra->num_rows > 0) {
        // Có đơn hàng rồi → hỏi người dùng có muốn khóa thay vì xóa
        echo "
        <div class='position-fixed top-50 start-50 translate-middle bg-light border shadow-lg p-4 rounded text-center' style='z-index:1055;'>
            <h5>🚫 Khách hàng \"$ten\" đã có đơn hàng, không thể xóa!</h5>
            <p>Bạn có muốn <b>ẩn (khóa)</b> khách hàng này không?</p>
            <div class='d-flex justify-content-center gap-2 mt-3'>
                <a href='QuanLyKhachHang.php?khoa=$ma' class='btn btn-warning px-4'>Khóa</a>
                <a href='QuanLyKhachHang.php' class='btn btn-secondary px-4'>Hủy</a>
            </div>
        </div>
        ";
    } else {
        // Không có đơn hàng → xóa luôn
        if ($conn->query("DELETE FROM KhachHang WHERE MaKH = $ma")) {
            echo "
            <div class='position-fixed top-50 start-50 translate-middle bg-success text-white p-4 rounded shadow text-center' style='z-index:1055;'>
                ✅ Đã xóa khách hàng thành công!
            </div>
            <script>
                setTimeout(() => window.location.href='QuanLyKhachHang.php', 1200);
            </script>";
        } else {
            echo "
            <div class='alert alert-danger mt-3'>
                ⚠️ Lỗi khi xóa khách hàng: " . htmlspecialchars($conn->error) . "
            </div>";
        }
    }
}

// 🔒 Xử lý khóa khách hàng
if (isset($_GET['khoa'])) {
    $ma = intval($_GET['khoa']);
    if ($conn->query("UPDATE KhachHang SET TinhTrang = 0 WHERE MaKH = $ma")) {
        echo "
        <div class='position-fixed top-50 start-50 translate-middle bg-warning text-dark p-4 rounded shadow text-center' style='z-index:1055;'>
            Đã khóa khách hàng thành công!
        </div>
        <script>
            setTimeout(() => window.location.href='QuanLyKhachHang.php', 1200);
        </script>";
    } else {
        echo "<div class='alert alert-danger mt-3'>⚠️ Lỗi khi khóa khách hàng.</div>";
    }
}


?>

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
    // ➕ Xử lý thêm khách hàng
    if (isset($_POST['themKhachHang'])) {
        $hoten = trim($_POST['hoten']);
        $sdt = trim($_POST['sdt']);

        $sql = "INSERT INTO KhachHang (HoTen, SDT, TinhTrang)
                VALUES ('$hoten', '$sdt', 1)"; // mặc định tình trạng mở (1)

        if ($conn->query($sql) === TRUE) {
            echo "<script>
                alert('🎉 Thêm khách hàng mới thành công!');
                window.location.href = 'QuanLyKhachHang.php';
            </script>";
            exit;
        } else {
            echo "<div class='alert alert-danger mt-3'>Lỗi: " . $conn->error . "</div>";
        }
    }


    // ✏️ Xử lý sửa khách hàng
    if (isset($_POST['luu_sua'])) {
        $ma = intval($_POST['sua_ma']);
        $ten = trim($_POST['sua_ten']);
        $sdt = trim($_POST['sua_sdt']);
        $tinhtrang = intval($_POST['sua_tinhtrang']);

        $sql = "UPDATE KhachHang 
                SET HoTen='$ten', SDT='$sdt', TinhTrang='$tinhtrang' 
                WHERE MaKH=$ma";

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
      <table class="table table-bordered text-center text-dark align-middle">
    <thead class="table-primary">
        <tr>
            <th>Mã KH</th>
            <th>Họ và tên</th>
            <th>Số điện thoại</th>
            <th>Tình trạng</th>
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
                $tinhtrang = (int)$row['TinhTrang'];

                $badge = $tinhtrang == 1
                    ? "<span class='badge bg-success text-dark px-3 py-2'>Mở</span>"
                    : "<span class='badge bg-danger text-dark px-3 py-2'>Khóa</span>";

               echo "
                    <tr>
                        <td>$ma</td>
                        <td>$ten</td>
                        <td>$sdt</td>
                        <td>$badge</td>
                        <td class='text-center'>
                            <button class='btn btn-warning btn-sm btn-edit me-2'
                                    data-id='$ma' 
                                    data-ten='$ten' 
                                    data-sdt='$sdt' 
                                    data-tinhtrang='$tinhtrang'>
                                <i class='fas fa-edit'></i> Sửa
                            </button>
                            <a href='QuanLyKhachHang.php?xoa=$ma&ten=" . urlencode($ten) . "' 
                            class='btn btn-danger btn-sm'
                            onclick='return confirm(\"⚠️ Bạn có chắc chắn muốn xóa khách hàng $ten không?\")'>
                            🗑️ Xóa
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
</div>

<!-- 🔧 Modal Sửa khách hàng -->
<div class="modal fade" id="modalSuaKhachHang" tabindex="-1" aria-labelledby="modalSuaKhachHangLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      
      <!-- Header -->
      <div class="modal-header bg-warning text-white rounded-top-4">
        <h5 class="modal-title fw-semibold" id="modalSuaKhachHangLabel">
      Sửa thông tin khách hàng
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>

      <!-- Form -->
      <form method="POST" action="">
        <div class="modal-body p-4">
          <input type="hidden" id="sua_ma" name="sua_ma">

          <div class="mb-3">
            <label for="sua_ten" class="form-label fw-semibold">Họ và tên</label>
            <input type="text" class="form-control rounded-3 shadow-sm" id="sua_ten" name="sua_ten" required>
          </div>

          <div class="mb-3">
            <label for="sua_sdt" class="form-label fw-semibold">Số điện thoại</label>
            <input type="text" class="form-control rounded-3 shadow-sm" id="sua_sdt" name="sua_sdt" required>
          </div>

          <div class="mb-3">
            <label for="sua_tinhtrang" class="form-label fw-semibold">Tình trạng</label>
            <select id="sua_tinhtrang" name="sua_tinhtrang" class="form-select rounded-3 shadow-sm">
              <option value="1">Mở</option>
              <option value="0">Khóa</option>
            </select>
          </div>

        </div>

        <!-- Footer -->
        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-outline-secondary rounded-3 px-4" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" name="luu_sua" class="btn btn-success rounded-3 px-4 fw-semibold">
           Lưu
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
    .form-select {
    height: calc(2.25rem + 2px);
    font-size: 1rem;
    border-radius: 5px;
}

    .form-control, .form-select {
  font-size: 15px;
  padding: 10px 14px;
  border: 1px solid #ccc;
  transition: all 0.2s ease-in-out;
}

.form-control:focus, .form-select:focus {
  border-color: #f0ad4e; /* vàng nhạt */
  box-shadow: 0 0 5px rgba(240, 173, 78, 0.4);
}

.btn-success {
  background-color: #28a745;
  border: none;
  transition: 0.2s;
}

.btn-success:hover {
  background-color: #218838;
}

.btn-outline-secondary:hover {
  background-color: #e9ecef;
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Mở modal sửa và gán dữ liệu
    const modalSua = new bootstrap.Modal(document.getElementById('modalSuaKhachHang'));

    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('sua_ma').value = btn.dataset.id;
            document.getElementById('sua_ten').value = btn.dataset.ten;
            document.getElementById('sua_sdt').value = btn.dataset.sdt;
            document.getElementById('sua_tinhtrang').value = btn.dataset.tinhtrang;
            modalSua.show();
        });
    });


});

</script>

<?php include '../include1/footer.php'; ?>
