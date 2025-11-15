<?php
include '../include/header.php'; 
include '../include/sidebar.php'; 

// ===============================================
// ✅ KHỐI CSS TẬP TRUNG CHO TOÀN TRANG
// ===============================================
?>
<style>
    /* 1. Hiệu ứng "shadow rise" cho nút */
    .shadow-rise-btn {
        transition: all 0.2s ease-in-out;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .shadow-rise-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    /* 2. Giãn cách icon & text */
    .btn .fas {
        margin-right: 5px;
    }

   /* 3. Sửa nút "X" trong modal (Quan trọng) */
.modal-header .close {
    background: none !important;
    border: none !important;
    opacity: 0.7;
    color: #fff !important; /* Chữ X màu trắng */
    font-size: 1.5rem;
    text-shadow: none !important;
    box-shadow: none !important;
}
.modal-header .close:hover {
    opacity: 1;
}
</style>

<?php
// ✅ Lấy giá trị tìm kiếm nếu có
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// ✅ Câu truy vấn có điều kiện lọc
if ($search !== "") {
    $sql = "SELECT * FROM nhanvien 
            WHERE TenDangNhap LIKE ? 
               OR HoTen LIKE ? 
               OR PhanQuyen LIKE ?";
    $stmt = $conn->prepare($sql);
    $like = "%$search%";
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM nhanvien";
    $result = $conn->query($sql);
}
?>


<div class="container-fluid">
  <h1 class="h3 mb-2 text-gray-800">Quản lý nhân viên</h1>
              <form method="GET" class="mb-3 d-flex align-items-center">
          <input type="text" name="search" 
                class="form-control w-50 mr-3" 
                placeholder="Tìm theo tên đăng nhập, họ tên hoặc phân quyền..."
                value="<?php echo htmlspecialchars($search); ?>">

          <button class="btn btn-primary mr-2 shadow-rise-btn" type="submit">
            <i class="fas fa-search"></i> Tìm
          </button>

          <a href="QuanLyNhanVien.php" class="btn btn-secondary shadow-rise-btn">
            <i class="fas fa-undo"></i> Làm mới
          </a>
        </form>

  <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
      <h6 class="m-0 font-weight-bold text-primary">Danh sách nhân viên</h6>
      
      <button class="btn btn-success shadow-rise-btn" data-toggle="modal" data-target="#addModal" role="button">
       Thêm nhân viên
      </button>
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
                        <button class='btn btn-warning btn-sm btn-edit shadow-rise-btn'
                            data-id='{$row['MaNV']}'
                            data-tendangnhap='{$row['TenDangNhap']}'
                            data-hoten='{$row['HoTen']}'
                            data-matkhau='{$row['MatKhau']}'
                            data-phanquyen='{$row['PhanQuyen']}'
                            data-tinhtrang='{$row['TinhTrang']}'>
                            <i class='fas fa-edit'></i> Sửa
                        </button>
                        <a href='nhanvien_delete.php?MaNV={$row['MaNV']}'
                           class='btn btn-danger btn-sm shadow-rise-btn'
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

<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="nhanvien_add_action.php" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Thêm nhân viên</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>      </div>

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
          <select name="PhanQuyen" class="form-control">
            <option value="NhanVien">Nhân viên</option>
            <option value="Admin">Admin</option>
          </select>
        </div>
      </div>

      
      <div class="modal-footer">
        <button type="submit" class="btn btn-success shadow-rise-btn">Lưu</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="nhanvien_update.php" class="modal-content">
      <div class="modal-header bg-primary text-white">
       <h5 class="modal-title">Sửa thông tin nhân viên</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>      </div>

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
          <select name="tinhtrang" id="edit-tinhtrang" class="form-control">
            <option value="1">Mở</option>
            <option value="0">Khóa</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Phân quyền</label>
          <select name="PhanQuyen" id="edit-phanquyen" class="form-control">
            <option value="Admin">Admin</option>
            <option value="NhanVien">Nhân viên</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-success shadow-rise-btn">Lưu </button>
      </div>
    </form>
  </div>
</div>

<script>
  // Gán dữ liệu khi nhấn nút "Sửa" (Không thay đổi logic)
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

<?php include '../include/footer.php'; 
 ?>