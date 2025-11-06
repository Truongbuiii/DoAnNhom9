<?php include '../include1/header.php'; ?>
<?php include '../include1/sidebar.php'; ?>

<div class="container-fluid">
    <h2 class="text-center mb-4 text-primary">Quản lý loại bánh</h2>

    <!-- 🟢 Form thêm loại bánh -->
    <div class="card mb-4 shadow-sm p-4">
        <h5 class="mb-3 text-primary">Thêm loại bánh</h5>
        <form method="POST" action="">
            <div class="row mb-3 align-items-end">
                <div class="col-md-8">
                    <label for="tenLoai" class="form-label">Tên loại bánh</label>
                    <input type="text" class="form-control" id="tenLoai" name="tenLoai" placeholder="Nhập tên loại bánh..." required>
                </div>
                <div class="col-md-4 d-flex justify-content-end">
                    <button type="submit" name="them" class="btn btn-success mt-2">Thêm</button>
                </div>
            </div>
        </form>
    </div>

    <?php
    // 🟢 Thêm loại bánh
    if (isset($_POST['them'])) {
        $tenLoai = trim($_POST['tenLoai']);
        $sqlThem = "INSERT INTO LoaiBanh (TenLoaiBanh, TinhTrang) VALUES ('$tenLoai', 1)";
        if ($conn->query($sqlThem)) {
            echo "<script>alert('🎉 Thêm loại bánh mới thành công!'); window.location='QuanLyLoaiBanh.php';</script>";
        } else {
            echo "<div class='alert alert-danger mt-3'>Lỗi: " . $conn->error . "</div>";
        }
    }

    // ✏️ Sửa loại bánh
    if (isset($_POST['luu_sua'])) {
        $ma = intval($_POST['sua_ma']);
        $ten = trim($_POST['sua_ten']);
        $tinhtrang = intval($_POST['sua_tinhtrang']);

        $sql = "UPDATE LoaiBanh SET TenLoaiBanh='$ten', TinhTrang=$tinhtrang WHERE MaLoaiBanh=$ma";
        if ($conn->query($sql)) {
            echo "<script>alert('✅ Cập nhật loại bánh thành công!'); window.location='QuanLyLoaiBanh.php';</script>";
        } else {
            echo "<div class='alert alert-danger mt-3'>⚠️ Lỗi: " . $conn->error . "</div>";
        }
    }

   // 🗑️ Xóa hoặc khóa loại bánh
if (isset($_GET['xoa'])) {
    $ma = intval($_GET['xoa']);

    // Kiểm tra loại bánh này có sản phẩm nào không
    $sqlCheckSP = "SELECT COUNT(*) AS TongSP FROM ThongTinBanh WHERE MaLoaiBanh = $ma";
    $resSP = $conn->query($sqlCheckSP);
    $tongSP = $resSP->fetch_assoc()['TongSP'];

    if ($tongSP > 0) {
        // Có sản phẩm thuộc loại này → chỉ khóa, không xóa
        $conn->query("UPDATE LoaiBanh SET TinhTrang = 0 WHERE MaLoaiBanh = $ma");
        echo "<script>alert('⚠️ Loại bánh này đã có sản phẩm bán, nên chỉ bị khóa chứ không thể xóa!'); window.location='QuanLyLoaiBanh.php';</script>";
    } else {
        // Không có sản phẩm → cho phép xóa
        if ($conn->query("DELETE FROM LoaiBanh WHERE MaLoaiBanh = $ma")) {
            echo "<script>alert('🗑️ Đã xóa loại bánh thành công!'); window.location='QuanLyLoaiBanh.php';</script>";
        } else {
            echo "<div class='alert alert-danger mt-3'>❌ Lỗi khi xóa: " . $conn->error . "</div>";
        }
    }
}

    ?>

    <!-- 📋 Danh sách loại bánh -->
    <div class="card shadow-sm p-4 mb-4">
        <h5 class="mb-3 text-primary">Danh sách loại bánh</h5>
        <table class="table table-bordered text-center align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Mã loại bánh</th>
                    <th>Tên loại bánh</th>
                    <th>Tình trạng</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM LoaiBanh ORDER BY MaLoaiBanh ASC";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($loai = $result->fetch_assoc()) {
                        $ma = htmlspecialchars($loai['MaLoaiBanh']);
                        $ten = htmlspecialchars($loai['TenLoaiBanh']);
                        $tinhtrang = (int)$loai['TinhTrang'];

                        $badge = $tinhtrang == 1
                            ? "<span class='badge bg-success text-dark px-3 py-2'>Mở</span>"
                            : "<span class='badge bg-danger text-dark px-3 py-2'>Khóa</span>";

                        echo "
                        <tr>
                            <td>$ma</td>
                            <td>$ten</td>
                            <td>$badge</td>
                            <td>
                                <button class='btn btn-warning btn-sm btn-edit'
                                        data-id='$ma'
                                        data-ten='$ten'
                                        data-tinhtrang='$tinhtrang'>
                                    <i class='fas fa-edit'></i> Sửa
                                </button>
                                <a href='?xoa=$ma' class='btn btn-danger btn-sm'
                                   onclick='return confirm(\"⚠️ Bạn có chắc muốn xóa loại bánh này không?\")'>Xóa</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo '<tr><td colspan="4">Chưa có loại bánh nào.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 🔧 Modal Sửa loại bánh -->
<div class="modal fade" id="modalSuaLoaiBanh" tabindex="-1" aria-labelledby="modalSuaLoaiBanhLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-warning text-white rounded-top-4">
        <h5 class="modal-title fw-semibold" id="modalSuaLoaiBanhLabel">Sửa loại bánh</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>

      <form method="POST" action="">
        <div class="modal-body p-4">
          <input type="hidden" id="sua_ma" name="sua_ma">

          <div class="mb-3">
            <label for="sua_ten" class="form-label fw-semibold">Tên loại bánh</label>
            <input type="text" class="form-control rounded-3 shadow-sm" id="sua_ten" name="sua_ten" required>
          </div>

          <div class="mb-3">
            <label for="sua_tinhtrang" class="form-label fw-semibold">Tình trạng</label>
            <select id="sua_tinhtrang" name="sua_tinhtrang" class="form-select rounded-3 shadow-sm">
              <option value="1">Mở</option>
              <option value="0">Khóa</option>
            </select>
          </div>
        </div>

        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-outline-secondary rounded-3 px-4" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" name="luu_sua" class="btn btn-success rounded-3 px-4 fw-semibold">
            Lưu thay đổi
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalSua = new bootstrap.Modal(document.getElementById('modalSuaLoaiBanh'));

    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('sua_ma').value = btn.dataset.id;
            document.getElementById('sua_ten').value = btn.dataset.ten;
            document.getElementById('sua_tinhtrang').value = btn.dataset.tinhtrang;
            modalSua.show();
        });
    });
});
</script>

<style>
.form-select, .form-control {
  font-size: 15px;
  padding: 10px 14px;
  border: 1px solid #ccc;
  transition: all 0.2s ease-in-out;
}

.form-control:focus, .form-select:focus {
  border-color: #f0ad4e;
  box-shadow: 0 0 5px rgba(240, 173, 78, 0.4);
}
</style>

<?php include '../include1/footer.php'; ?>
