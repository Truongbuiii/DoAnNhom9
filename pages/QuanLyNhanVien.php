<?php include '../include1/header.php'; ?>
<?php include '../include1/sidebar.php'; ?>
<?php
include '../db/connect.php';

// Lấy danh sách nhân viên
$sql = "SELECT * FROM nhanvien";
$result = $conn->query($sql);
?>

<!-- Nội dung chính -->
<div class="container-fluid">
  <h1 class="h3 mb-2 text-gray-800">Quản lý nhân viên</h1>

  <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
      <h6 class="m-0 font-weight-bold text-primary">Danh sách nhân viên</h6>
      <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addModal">+ Thêm nhân viên</button>
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered text-center">
          <thead class="bg-primary text-white">
            <tr>
              <th>Mã NV</th>
              <th>Tên đăng nhập</th>
              <th>Họ tên</th>
              <th>Mật khẩu</th>
              <th>Phân quyền</th>
              <th>Tình trạng</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['MaNV']}</td>";
                echo "<td>{$row['TenDangNhap']}</td>";
                echo "<td>{$row['HoTen']}</td>";
                echo "<td>{$row['MatKhau']}</td>";

                // Phân quyền
                echo "<td><span class='badge " .
                  ($row['PhanQuyen'] == 'Admin' ? "badge-danger" : "badge-secondary") .
                  "'>{$row['PhanQuyen']}</span></td>";

                // ✅ Tình trạng hiển thị đẹp
                echo "<td><span class='badge " .
                  ($row['TinhTrang'] == 1 ? "badge-success'>Mở" : "badge-danger'>Khóa") .
                  "</span></td>";

                // Nút hành động
                echo "<td>
                        <button class='btn btn-warning btn-sm btn-edit'
                            data-id='{$row['MaNV']}'
                            data-tendangnhap='{$row['TenDangNhap']}'
                            data-hoten='{$row['HoTen']}'
                            data-matkhau='{$row['MatKhau']}'
                            data-phanquyen='{$row['PhanQuyen']}'
                            data-tinhtrang='{$row['TinhTrang']}'>
                            <i class='fas fa-edit'></i> Sửa
                        </button>
                        <a href='nhanvien_delete.php?MaNV={$row['MaNV']}'
                           class='btn btn-danger btn-sm'
                           onclick='return confirm(\"Bạn có chắc muốn xóa nhân viên này không?\")'>
                           <i class='fas fa-trash'></i> Xóa
                        </a>
                      </td>";
                echo "</tr>";
              }
            } else {
              echo "<tr><td colspan='7'>Không có nhân viên nào!</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- ✅ Modal thêm nhân viên -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="nhanvien_add_action.php" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Thêm nhân viên</h5>
        <button type="button" class="btn-close btn btn-light border border-danger text-danger" data-dismiss="modal">×</button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Tên đăng nhập</label>
          <input type="text" class="form-control" name="TenDangNhap" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Họ tên</label>
          <input type="text" class="form-control" name="HoTen" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Mật khẩu (6 số)</label>
          <input type="text" class="form-control" name="MatKhau" required pattern="\d{6}" maxlength="6" title="Mật khẩu phải gồm đúng 6 chữ số">
        </div>


        <div class="mb-3">
          <label class="form-label">Phân quyền</label>
          <select name="PhanQuyen" class="form-select">
            <option value="NhanVien">Nhân viên</option>
            <option value="Admin">Admin</option>
          </select>
        </div>
      </div>

      
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Lưu nhân viên</button>
      </div>
    </form>
  </div>
</div>

<!-- ✅ Modal sửa nhân viên -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="nhanvien_update.php" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Sửa thông tin nhân viên</h5>
        <button type="button" class="btn-close btn btn-light border border-danger text-danger" data-dismiss="modal">×</button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="MaNV" id="edit-id">

        <div class="mb-3">
          <label class="form-label">Tên đăng nhập</label>
          <input type="text" class="form-control" id="edit-tendangnhap" name="TenDangNhap" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label">Họ tên</label>
          <input type="text" class="form-control" name="HoTen" id="edit-hoten" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Mật khẩu (6 số)</label>
          <input type="text" class="form-control" name="MatKhau" id="edit-matkhau" required pattern="\d{6}" maxlength="6" title="Mật khẩu phải gồm đúng 6 chữ số">
        </div>

        <div class="mb-3">
          <label class="form-label">Tình trạng</label>
          <select name="tinhtrang" id="edit-tinhtrang" class="form-select">
            <option value="1">Mở</option>
            <option value="0">Khóa</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Phân quyền</label>
          <select name="PhanQuyen" id="edit-phanquyen" class="form-select">
            <option value="Admin">Admin</option>
            <option value="NhanVien">Nhân viên</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Lưu thay đổi</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Gán dữ liệu khi nhấn nút "Sửa"
  document.querySelectorAll('.btn-edit').forEach(button => {
    button.addEventListener('click', () => {
      document.getElementById('edit-id').value = button.dataset.id;
      document.getElementById('edit-tendangnhap').value = button.dataset.tendangnhap;
      document.getElementById('edit-hoten').value = button.dataset.hoten;
      document.getElementById('edit-matkhau').value = button.dataset.matkhau;
      document.getElementById('edit-tinhtrang').value = button.dataset.tinhtrang;
      document.getElementById('edit-phanquyen').value = button.dataset.phanquyen;
      $('#editModal').modal('show');
    });
  });
</script>

<?php include '../include1/footer.php'; ?>